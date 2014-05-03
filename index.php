<?php
/*********************************************************************
    index.php

    Helpdesk landing page. Please customize it to fit your needs.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('client.inc.php');
$bodyclass = 'home';
require(CLIENTINC_DIR.'header.inc.php');
?>

	
    <div class="container">
		<div class="row">
			<div class="twelvecol last">
<?php
if($cfg && ($page = $cfg->getLandingPage())) {
    echo $page->getBody();
} else { ?>
				<p class="headline">In order to streamline support requests and better serve you, we utilize a support ticket system.</p>
				<p>Every support request is assigned a unique ticket number which you can use to track the progress and responses online. For your reference we provide complete archives and history of all your support requests. A valid email address is required.</p>
<?php } ?>
			</div>
		</div>
	</div>

	<div class="container greyBlock">
		<div class="row">
			<div class="sixcol new">
				<h2><i class="icon-plus-circled"></i> Open A New Ticket</h2>
				<p>Please provide as much detail as possible so we can best assist you. To update a previously submitted ticket, please login.</p>
				<a href="open.php" class="button">Open New Ticket</a>
			</div>
			<div class="sixcol check last">
				<h2><i class="icon-info-circled"></i> Check Ticket Status</h2>
				<p>We provide archives and history of all your current and past support requests complete with responses.</p>
				<a href="view.php" class="button">Check Ticket Status</a>
			</div>
		</div>
	</div>

<?php
if($cfg && $cfg->isKnowledgebaseEnabled()){
    //FIXME: provide ability to feature or select random FAQs ??
?>
	<div class="container faqWrap">
		<div class="row">
			<div class="twelvecol last">
				<p>Be sure to browse our <a href="kb/index.php">Frequently Asked Questions (FAQs)</a>, before opening a ticket.</p>
			</div>
		</div>
	</div>

<?php
} ?>
<?php require(CLIENTINC_DIR.'footer.inc.php'); ?>
