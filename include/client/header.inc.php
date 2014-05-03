<?php
$title=($cfg && is_object($cfg) && $cfg->getTitle())?$cfg->getTitle():'osTicket :: Support Ticket System';
header("Content-Type: text/html; charset=UTF-8\r\n");
?>
<!DOCTYPE html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title><?php echo Format::htmlchars($title); ?></title>
    <meta name="description" content="customer support platform">
    <meta name="keywords" content="osTicket, Customer support system, support ticket system">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <!--[if lt IE 9]>
		<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
    <link rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/main.css" media="screen">
    <link rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/1140.css" media="screen">
    <link rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/fontello.css" media="screen">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script src="<?php echo ROOT_PATH; ?>scripts/functions.js"></script>
    <script src="<?php echo ROOT_PATH; ?>js/jquery.multifile.js"></script>
    <script src="<?php echo ROOT_PATH; ?>js/osticket.js"></script>
</head>

<body class="<?php echo $bodyclass; ?>">
    <header class="container">
		<div class="row">
			<div class="twelvecol last">
				<h1><a href="<?php echo ROOT_PATH; ?>index.php" title="Support Ticket Center" class="title">Support Ticket Center</a></h1>
				<nav id="mainNav">
					<ul id="topNav">
					 <li><a class="home" href="<?php echo ROOT_PATH; ?>index.php">Home</a></li>
					<?php if($thisclient && is_object($thisclient) && $thisclient->isValid()) { ?>
			         <li><a class="my_tickets" href="<?php echo ROOT_PATH; ?>tickets.php">My Tickets</a></li>
			         <?php } else { ?>
			         <li><a class="ticket_status" href="<?php echo ROOT_PATH; ?>tickets.php">Ticket Status</a></li>
			         <?php } ?>
			         <li><a class="new_ticket" href="<?php echo ROOT_PATH; ?>open.php">New Ticket</a></li>
			         <?php if($thisclient && is_object($thisclient) && $thisclient->isValid()) { ?>
			         <li><a class="log_out" href="<?php echo ROOT_PATH; ?>logout.php">Log Out</a></li>
			         <?php } ?>
					</ul>
				</nav>
				<a href="#" id="mobileNavLink">Nav</a>
			</div>
		</div>
	</header>
