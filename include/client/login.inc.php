<?php
if(!defined('OSTCLIENTINC')) die('Access Denied');

$email=Format::input($_POST['lemail']?$_POST['lemail']:$_GET['e']);
$ticketid=Format::input($_POST['lticket']?$_POST['lticket']:$_GET['t']);
?>

	<div>
	    <?if($errors['err']) {?>
	        <p align="center" id="errormessage"><?=$errors['err']?></p>
	    <?}elseif($warn) {?>
	        <p class="warnmessage"><?=$warn?></p>
	    <?}?>
	</div>

	<div class="container">
		<div class="row">
			<div class="twelvecol last">
			    <p class="headline">To view the status of a ticket, provide us with your login details below.</p>
			    <p>If this is your first time contacting us or you've lost the ticket ID, please <a href="open.php">click here</a> to open a new ticket.</p>
	    	</div>
		</div>
	</div>
	
	<div class="container greyBlock">
		<div class="row">
			<div class="twelvecol last">
			    <form class="status_form ticket_status_form" action="login.php" method="post" id="clientLogin">
			    <?php csrf_token(); ?>
					<div>
						<label>Email Address</label>
						<input id="email" type="text" name="lemail" size="50" value="<?php echo $email; ?>">
					</div>
					<div>
						<label>Ticket #</label>
						<input id="ticketno" type="text" name="lticket" size="50" value="<?php echo $ticketid; ?>">
					</div>
					<input type="submit" class="button" value="View Status">
				</form>
			    <span class="error"><?=Format::htmlchars($loginmsg)?></span>
	    	</div>
		</div>
	</div>
