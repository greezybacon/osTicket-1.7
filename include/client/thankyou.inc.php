<?php
if(!defined('OSTCLIENTINC') || !is_object($ticket)) die('Kwaheri rafiki!');
//Please customize the message below to fit your organization speak!
?>

	<div class="container">
		<div class="row">
			<div class="twelvecol last">
				<p class="headline">Thank you for contacting us <?php echo Format::htmlchars($ticket->getName()); ?>.</p>
			</div>
		</div>
	</div>

	<div class="container greyBlock">
		<div class="row">
			<div class="twelvecol last"> 
				<p>A support ticket request has been created and a representative will be getting back to you shortly if necessary.</p>
			    <?php if($cfg->autoRespONNewTicket()){ ?>
			    <p>An email with the ticket number has been sent to <b><?php echo $ticket->getEmail(); ?></b>. You'll need the ticket number along with your email to view status and progress online. If you wish to send additional comments or information regarding same issue, please follow the instructions on the email.</p>
			    <?php } ?>
			    <p class="sig">Support Team</p>
			</div>
		</div>
	</div>
