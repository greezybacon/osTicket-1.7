<?php
if(!defined('OSTCLIENTINC') || !$thisclient || !$ticket || !$ticket->checkClientAccess($thisclient)) die('Access Denied!');

$info=($_POST && $errors)?Format::htmlchars($_POST):array();

$dept = $ticket->getDept();
//Making sure we don't leak out internal dept names
if(!$dept || !$dept->isPublic())
    $dept = $cfg->getDefaultDept();

?>

	<div class="container">
		<div class="row">
			<div class="twelvecol last">
				<ul id="ticketInfo">
					<li><i class="icon-doc-text"></i> Ticket #<?php echo $ticket->getExtId(); ?></li>
					<li><i class="icon-comment"></i> Subject: <?php echo Format::htmlchars($ticket->getSubject()); ?></li>
				</ul>
			</div>
		</div>
	</div>
	
	<div class="container greyBlock ticketOverview">
		<div class="row">
			<div class="sixcol">
		        <div>
					<span class="heading">Ticket Status:</span>
					<p>
					<?php if (strpos($ticket->getStatus(),'closed') !== false) {
					    $class = 'green';
					} ?>
						<span class="ticketStatus <?php echo $class; ?>"><?php echo $ticket->getStatus(); ?></span>
					</p>
				</div>
	            <div>
	                <span class="heading">Department:</span>
	                <p><?php echo Format::htmlchars($dept->getName()); ?></p>
	            </div>
				<div>
	                <span class="heading">Create Date:</span>
	                <p><?php echo Format::db_datetime($ticket->getCreateDate()); ?></p>
	            </div>
						
			</div>
			<div class="sixcol last">
	            <div>
	                <span class="heading">Name:</span>
	                <p><?php echo Format::htmlchars($ticket->getName()); ?></p>
	            </div>
	            <div>
	                <span class="heading">Email:</span>
	                <p><?php echo $ticket->getEmail(); ?></p>
	            </div>
	            <div>
	                <span class="heading">Phone:</span>
	                <p><?php echo Format::phone($ticket->getPhoneNumber()); ?></p>
	            </div>
			</div>
		</div>
	</div>
	
	<div class="container">
		<div class="row">
			<div class="twelvecol last">
				<p class="headline"><i class="icon-mail"></i> Ticket Thread</p>
			</div>
		</div>
	</div>

	<div class="container greyBlock">
		<div class="row">
			<div class="twelvecol last">		
			    <div id="ticketthread">
				<?php    
				if($ticket->getThreadCount() && ($thread=$ticket->getClientThread())) {
				    $threadType=array('M' => 'message', 'R' => 'response');
				    foreach($thread as $entry) {
                        if ($entry['body'] == '-')
                            $entry['body'] = '(EMPTY)';
				        //Making sure internal notes are not displayed due to backend MISTAKES!
				        if(!$threadType[$entry['thread_type']]) continue;
				        $poster = $entry['poster'];
				        if($entry['thread_type']=='R' && ($cfg->hideStaffName() || !$entry['staff_id']))
				            $poster = ' ';
				        ?>
				        <div class="ticketMsg">
				            <p><?php echo Format::display($entry['body']); ?></p>
				            <?php
				            if($entry['attachments']
				                    && ($tentry=$ticket->getThreadEntry($entry['id']))
				                    && ($links=$tentry->getAttachmentsLinks())) { ?>
				                <tr><td class="info"><?php echo $links; ?></td></tr>
				            <?php
				            } ?>
				            <span class="msgDate"><?php echo Format::db_datetime($entry['created']); ?> &nbsp;&nbsp;<span><?php echo $poster; ?></span></span>
				        </div>
				    <?php
				    }
				}
				?>	
			</div>
			<div>
				<div id="reply" class="clear" style="padding-bottom:10px;">
				<?php if($ticket->isClosed()) { ?>
		        <div class="msg">Ticket will be reopened on message post</div>
		        <?php } ?>
				<?php if($errors['err']) { ?>
				    <div id="msg_error"><?php echo $errors['err']; ?></div>
				<?php }elseif($msg) { ?>
				    <div id="msg_notice"><?php echo $msg; ?></div>
				<?php }elseif($warn) { ?>
				    <div id="msg_warning"><?php echo $warn; ?></div>
				<?php } ?>
				<form id="reply" action="tickets.php?id=<?php echo $ticket->getExtId(); ?>#reply" name="reply" method="post" enctype="multipart/form-data">
				    <?php csrf_token(); ?>
				    <input type="hidden" name="id" value="<?php echo $ticket->getExtId(); ?>">
				    <input type="hidden" name="a" value="reply">
				        <div>
			                <label>Enter Message:</label>
			                <span id="msg"><em><?php echo $msg; ?> </em></span>
			                <textarea name="message" id="message" cols="50" rows="9" wrap="soft"><?php echo $info['message']; ?></textarea>
				        </div>
				        <?php
				        if($cfg->allowOnlineAttachments()) { ?>
				        <div>
				            <label for="attachment">Attachments:</label>
				                <div class="uploads">
				                </div>
				                <div class="file_input">
				                    <input class="multifile" type="file" name="attachments[]" size="30" value="" />
				                </div>
				        </div>
				        <?php
				        } ?>
				        <input type="submit" value="Post Reply" class="button" id="postReply">
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
