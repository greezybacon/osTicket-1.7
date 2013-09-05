<?php
/******************************************************************************
 * Copyright (c) 2010 Jevon Wright and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Jevon Wright - initial API and implementation
 *    Jared Hancock - html table implementation
 ****************************************************************************/

/**
 * Tries to convert the given HTML into a plain text format - best suited for
 * e-mail display, etc.
 *
 * <p>In particular, it tries to maintain the following features:
 * <ul>
 *   <li>Links are maintained, with the 'href' copied over
 *   <li>Information in the &lt;head&gt; is lost
 * </ul>
 *
 * @param html the input HTML
 * @return the HTML converted, as best as possible, to text
 */
function convert_html_to_text($html, $width=74) {
    $html = fix_newlines($html);

    $doc = new DOMDocument('1.0', 'utf-8');
    if (!@$doc->loadHTML($html))
        return $html;

    $elements = identify_node($doc);
    $options = array();
    if (is_object($elements))
        $output = $elements->render($width, $options);
    else
        $output = $elements;

    return trim($output);
}

/**
 * Unify newlines; in particular, \r\n becomes \n, and
 * then \r becomes \n. This means that all newlines (Unix, Windows, Mac)
 * all become \ns.
 *
 * @param text text with any number of \r, \r\n and \n combinations
 * @return the fixed text
 */
function fix_newlines($text) {
    // replace \r\n to \n
    // remove \rs
    $text = str_replace("\r\n?", "\n", $text);

    return $text;
}

function identify_node($node) {
    if ($node instanceof DOMText)
        return $node;
    if ($node instanceof DOMDocumentType)
        // ignore
        return "";

    $name = strtolower($node->nodeName);

    // start whitespace
    switch ($name) {
        case "hr":
            return new HtmlHrElement($node);
        case "br":
            return "\n";

        case "style":
        case "head":
        case "title":
        case "meta":
        case "script":
            // ignore these tags
            return "";

        case "div":
            return new HtmlBlockElement($node);

        case "blockquote":
            return new HtmlBlockquoteElement($node);
        case "cite":
            return new HtmlCiteElement($node);

        case "h1":
        case "h2":
        case "h3":
        case "h4":
        case "h5":
        case "h6":
            // add two newlines
            return new HtmlHeadlineElement($node);

        case "p":
        case "div":
            // add one line
            return new HtmlBlockElement($node);

        case "a":
            return new HtmlAElement($node);

        case "b":
        case "strong":
            return new HtmlBElement($node);

        case "u":
            return new HtmlUElement($node);

        case "ol":
            return new HtmlListElement($node);
        case "ul":
            return new HtmlUnorderedListElement($node);

        case 'table':
            return new HtmlTable($node);

        case "img":
            return new HtmlImgElement($node);

        case "pre":
            return new HtmlPreElement($node);
        case "code":
            return new HtmlCodeElement($node);

        default:
            // print out contents of unknown tags
            if ($node->hasChildNodes() && $node->childNodes->length == 1)
                return identify_node($node->childNodes->item(0));

            return new HtmlInlineElement($node);
    }
}

class HtmlInlineElement {
    var $children = array();

    function HtmlInlineElement($node) {
        $this->node = $node;
        $this->traverse($node);
    }

    function traverse($node) {
        if ($node instanceof DOMText) {
            $this->content = preg_replace("/\\s+/im", " ", $node->wholeText);
        }
        elseif ($node->hasChildNodes()) {
            for ($i = 0; $i < $node->childNodes->length; $i++) {
                $n = $node->childNodes->item($i);
                $this->children[] = identify_node($n);
            }
        }
    }

    function render($width, &$options) {
        $output = (isset($this->content)) ? $this->content : "";
        foreach ($this->children as $c) {
            if ($c instanceof DOMText) {
                if (isset($options['preserve-whitespace']))
                    $output .= $c->wholeText;
                else
                    $output .= preg_replace("/\\s+/im", " ", $c->wholeText);
            }
            elseif ($c instanceof HtmlInlineElement) {
                $output .= $c->render($width, $options);
            }
            else {
                $output .= $c;
            }
        }
        return $output;
    }

    function getWeight() {
        if (!isset($this->weight)) {
            if (isset($this->content))
                $this->weight = strlen($this->content);
            else
                $this->weight = strlen($this->node->textContent);
        }
        return $this->weight;
    }
}

class HtmlBlockElement extends HtmlInlineElement {
    var $min_width = false;

    function render($width, $options) {
        $options['wordwrap'] = true;
        if (!isset($options['trim']))
            $options['trim'] = true;
        $output = parent::render($width, $options);
        if ($options['trim'])
            $output = trim($output);
        if (!strlen(trim($output)))
            return "";
        if ($options['wordwrap'])
            $output = wordwrap($output, $width);
        return "\n" . $output . "\n";
    }

    function getMinWidth() {
        if ($this->min_width === false) {
            foreach ($this->children as $c)
                if ($c instanceof HtmlBlockElement)
                    $this->min_width = max($c->getMinWidth(), $this->min_width);
            if (!$this->min_width) {
                $words = explode(' ', $this->node->textContent);
                foreach ($words as $w)
                    $this->min_width = max(strlen($w), $this->min_width);
            }
        }
        return $this->min_width;
    }
}

class HtmlUElement extends HtmlInlineElement {
    function render($width, $options) {
        $output = parent::render($width, $options);
        return "_".str_replace(" ", "_", $output)."_";
    }
}

class HtmlBElement extends HtmlInlineElement {
    function render($width, $options) {
        $output = parent::render($width, $options);
        return "*".$output."*";
    }
}

class HtmlHrElement extends HtmlBlockElement {
    function render($width, $options) {
        return "\n".str_repeat('-', $width)."\n";
    }
    function getWeight() { return 1; }
    function getMinWidth() { return 0; }
}

class HtmlHeadlineElement extends HtmlBlockElement {
    function render($width, $options) {
        $headline = parent::render($width, $options);
        $line = false;
        switch ($this->node->nodeName) {
            case 'h1':
            case 'h2':
                $line = '=';
                break;
            case 'h3':
            case 'h4':
                $line = '-';
                break;
        }
        if ($line)
            $headline .= str_repeat($line, strpos($headline, "\n", 1) - 1) . "\n";
        return $headline;
    }
}

class HtmlBlockquoteElement extends HtmlBlockElement {
    function render($width, $options) {
        return str_replace("\n", "\n> ",
            rtrim(parent::render($width-2, $options)))."\n";
    }
}

class HtmlCiteElement extends HtmlBlockElement {
    function render($width, $options) {
        $options['trim'] = false;
        $lines = explode("\n", ltrim(parent::render($width-3, $options)));
        $lines[0] = "-- " . $lines[0];
        // Right justification
        foreach ($lines as &$l)
            $l = str_pad($l, $width, " ", STR_PAD_LEFT);
        unset($l);
        return implode("\n", $lines);
    }
}

class HtmlImgElement extends HtmlInlineElement {
    function render($width, $options) {
        // Images are returned as [alt: title]
        $title = $this->node->getAttribute("title");
        if ($title)
            $title = ": $title";
        $alt = $this->node->getAttribute("alt");
        return "[$alt$title]";
    }
}

class HtmlAElement extends HtmlInlineElement {
    function render($width, $options) {
        // links are returned in [text](link) format
        $output = parent::render($width, $options);
        $href = $this->node->getAttribute("href");
        if ($href == null) {
            // it doesn't link anywhere
            if ($this->node->getAttribute("name") != null) {
                $output = "[$output]";
            }
        } else {
            if ($href != $output) {
                $output = "[$output]($href)";
            }
        }
        return $output;
    }
}

class HtmlListElement extends HtmlBlockElement {
    var $marker = "  %d. ";

    function render($width, $options) {
        $options['marker'] = $this->marker;
        $options['trim'] = false;
        return parent::render($width, $options);
    }

    function traverse($node, $number=1) {
        if ($node instanceof DOMText)
            return;
        switch (strtolower($node->nodeName)) {
            case "li":
                $this->children[] = new HtmlListItem($node, $number++);
                return;
            // Anything else is ignored
        }
        for ($i = 0; $i < $node->childNodes->length; $i++)
            $this->traverse($node->childNodes->item($i), $number);
    }
}

class HtmlUnorderedListElement extends HtmlListElement {
    var $marker = "  * ";
}

class HtmlListItem extends HtmlBlockElement {
    function HtmlListItem($node, $number) {
        parent::HtmlBlockElement($node);
        $this->number = $number;
    }

    function render($width, $options) {
        $prefix = sprintf($options['marker'], $this->number);
        $lines = explode("\n", trim(parent::render($width-strlen($prefix), $options)));
        $lines[0] = $prefix . $lines[0];
        return implode("\n".str_repeat(" ", strlen($prefix)), $lines)."\n";
    }
}

class HtmlPreElement extends HtmlBlockElement {
    function render($width, $options) {
        $options['preserve-whitespace'] = true;
        $content = explode("\n", trim(parent::render($width-4, $options)));
        $output = ',-'.str_repeat('-', $width-4)."-.\n";
        foreach ($content as $l)
            $output .= '| '.str_pad($l, $width-4)." |\n";
        $output .= '`-'.str_repeat('-', $width-4)."-'\n";
        return $output;
    }
    function getMinWidth() { return parent::getMinWidth() + 4; }
    function getWeight() { return parent::getWeight() + 4; }
}

class HtmlCodeElement extends HtmlInlineElement {
    function render($width, $options) {
        return '`'.parent::render($width-2, $options).'`';
    }
}

class HtmlTable extends HtmlBlockElement {
    function HtmlTable($node) {
        $this->body = array();
        $this->foot = array();
        $this->rows = &$this->body;
        parent::HtmlBlockElement($node);
    }

    function getMinWidth() {
        return parent::getMinWidth() + 4;
    }

    function traverse($node) {
        if ($node instanceof DOMText)
            return;

        $name = strtolower($node->nodeName);
        switch ($name) {
            case 'th':
            case 'td':
                $this->row[] = new HtmlTableCell($node);
                // Don't descend into this node. It should be handled by the
                // HtmlTableCell::traverse
                return;

            case 'tr':
                unset($this->row);
                $this->row = array();
                $this->rows[] = &$this->row;
                break;

            case 'caption':
                $this->caption = new HtmlBlockElement($node);
                return;

            case 'tbody':
            case 'thead':
                unset($this->rows);
                $this->rows = &$this->body;
                break;

            case 'tfoot':
                unset($this->rows);
                $this->rows = &$this->foot;
                break;
        }
        for ($i = 0; $i < $node->childNodes->length; $i++)
            $this->traverse($node->childNodes->item($i));
    }

    /**
     * Ensure that no column is below its minimum width. Each column that is
     * below its minimum will borrow from a column that is above its
     * minimum. The process will continue until all columns are above their
     * minimums
     */
    function _fixupWidths(&$widths, $mins) {
        foreach ($widths as $i=>$w) {
            if ($w < $mins[$i]) {
                // Borrow from another column -- the furthest one away from
                // its minimum width
                $best = 0; $bestidx = false;
                foreach ($widths as $j=>$w) {
                    if ($i == $j)
                        continue;
                    if ($w > $mins[$j]) {
                        if ($w - $mins[$j] > $best) {
                            $best = $w - $mins[$j];
                            $bestidx = $j;
                        }
                    }
                }
                if ($bestidx !== false) {
                    $widths[$bestidx]--;
                    $widths[$i]++;
                    return $this->_fixupWidths($widths, $mins);
                }
            }
        }
    }

    function render($width, &$options) {
        $cols = 0;
        $rows = array_merge($this->body, $this->foot);

        # Count the number of columns
        foreach ($rows as $r)
            $cols = max($cols, count($r));

        # Find the largest cells in all columns
        $weights = $mins = array_fill(0, $cols, 0);
        foreach ($rows as $r) {
            $i = 0;
            foreach ($r as $cell) {
                for ($j=0; $j<$cell->cols; $j++) {
                    $weights[$i] = max($weights[$i], $cell->getWeight());
                    $mins[$i] = max($mins[$i], $cell->getMinWidth());
                }
                $i += $cell->cols;
            }
        }

        # Subtract internal padding and borders from the available width
        $inner_width = $width - $cols*3 - 1;

        # Optimal case, where the preferred width of all the columns is
        # doable
        if (array_sum($weights) <= $inner_width)
            $widths = $weights;
        # Worst case, where the minimum size of the columns exceeds the
        # available width
        elseif (array_sum($mins) > $inner_width)
            $widths = $mins;
        # Most likely case, where the table can be fit into the available
        # width
        else {
            $total = array_sum($weights);
            $widths = array();
            foreach ($weights as $c)
                $widths[] = (int)($inner_width * $c / $total);
            $this->_fixupWidths($widths, $mins);
        }
        $outer_width = array_sum($widths) + $cols*3 + 1;

        $contents = array();
        $heights = array();
        foreach ($rows as $y=>$r) {
            $heights[$y] = 0;
            for ($x = 0, $i = 0; $x < $cols; $i++) {
                if (!isset($r[$i])) {
                    // No cell at the end of this row
                    $contents[$y][$i][] = "";
                    break;
                }
                $cell = $r[$i];
                # Compute the effective cell width
                $cwidth = 0;
                for ($j = 0; $j < $cell->cols; $j++)
                    $cwidth += $widths[$x+$j];
                # Add extra space for the unneeded border padding
                $cwidth += ($cell->cols - 1) * 3;
                # Stash the computed width so it doesn't need to be
                # recomputed again below
                $cell->width = $cwidth;
                unset($data);
                $data = explode("\n", $cell->render($cwidth, $options));
                $heights[$y] = max(count($data), $heights[$y]);
                # Adjust the columns widths if the data is oversized
                if ($cell->cols == 1)
                    $widths[$x] = max($widths[$x], max(array_map('strlen', $data)));
                $contents[$y][$i] = &$data;
                $x += $cell->cols;
            }
        }

        # Build the header
        $header = "";
        for ($i = 0; $i < $cols; $i++)
            $header .= "+-" . str_repeat("-", $widths[$i]) . "-";
        $header .= "+";

        # Emit the rows
        $output = "\n";
        if (isset($this->caption)) {
            $this->caption = $this->caption->render($outer_width, $options);
        }
        foreach ($rows as $y=>$r) {
            $output .= $header . "\n";
            for ($x = 0, $k = 0; $k < $heights[$y]; $k++) {
                $output .= "|";
                foreach ($r as $x=>$cell) {
                    $content = (isset($contents[$y][$x][$k]))
                        ? $contents[$y][$x][$k] : "";
                    $pad = $cell->width - mb_strlen($content, 'utf8');
                    $output .= " ".$content;
                    if ($pad > 0)
                        $output .= str_repeat(" ", $pad);
                    $output .= " |";
                    $x += $cell->cols;
                }
                $output .= "\n";
            }
        }
        $output .= $header . "\n";
        $options['wordwrap'] = false;
        return $output;
    }
}

class HtmlTableCell extends HtmlBlockElement {
    function HtmlTableCell($node) {
        parent::HtmlBlockElement($node);
        $this->cols = $node->getAttribute('colspan');
        $this->rows = $node->getAttribute('rowspan');

        if (!$this->cols) $this->cols = 1;
        if (!$this->rows) $this->rows = 1;
    }

    function render($width, &$options) {
        return trim(parent::render($width, $options));
    }

    function getWeight() {
        return parent::getWeight() / ($this->cols * $this->rows);
    }

    function getMinWidth() {
        return parent::getMinWidth() / $this->cols;
    }
}

// Enable use of html2text from command line
// The syntax is the following: php html2text.php file.html

do {
  if (PHP_SAPI != 'cli') break;
  if (empty ($_SERVER['argc']) || $_SERVER['argc'] < 2) break;
  if (empty ($_SERVER['PHP_SELF']) || FALSE === strpos ($_SERVER['PHP_SELF'], 'html2text.php') ) break;
  $file = $argv[1];
  echo convert_html_to_text (file_get_contents ($file));
} while (0);
