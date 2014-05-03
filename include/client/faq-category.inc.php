<?php
if(!defined('OSTCLIENTINC') || !$category || !$category->isPublic()) die('Access Denied');
?>

	<div class="container">
		<div class="row">
			<div class="topWrap">
				<p class="headline"><?php echo $category->getName() ?></p>
			</div>
		</div>
	</div>

	<div class="container greyBlock">
		<div class="row">
			<div class="twelvecol last">
				<p><?php echo Format::safe_html($category->getDescription()); ?></p>
			</div>
		</div>
	</div>

	<div class="container faqWrap">
		<div class="row">
			<div class="twelvecol last">
				<?php
				$sql='SELECT faq.faq_id, question, count(attach.file_id) as attachments '
				    .' FROM '.FAQ_TABLE.' faq '
				    .' LEFT JOIN '.FAQ_ATTACHMENT_TABLE.' attach ON(attach.faq_id=faq.faq_id) '
				    .' WHERE faq.ispublished=1 AND faq.category_id='.db_input($category->getId())
				    .' GROUP BY faq.faq_id '
                    .' ORDER BY question';
				if(($res=db_query($sql)) && db_num_rows($res)) {
				    echo '
				         <ul id="kb">';
				    while($row=db_fetch_array($res)) {
				        $attachments=$row['attachments']?'<span class="Icon file"></span>':'';
				        echo sprintf('
				            <li><h4><a href="faq.php?id=%d" >%s &nbsp;%s</a></</li>',
				            $row['faq_id'],Format::htmlchars($row['question']), $attachments);
				    }
				    echo '</ul>';
				}else {
				    echo '<strong>Category does not have any FAQs. <a href="index.php">Back To Index</a></strong>';
				}
				?>
			</div>
		</div>
	</div>
