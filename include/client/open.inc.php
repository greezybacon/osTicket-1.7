<?php
if(!defined('OSTCLIENTINC')) die('Access Denied!');
$info=array();
if($thisclient && $thisclient->isValid()) {
    $info=array('name'=>$thisclient->getName(),
                'email'=>$thisclient->getEmail(),
                'phone'=>$thisclient->getPhone(),
                'phone_ext'=>$thisclient->getPhoneExt());
}

$info=($_POST && $errors)?Format::htmlchars($_POST):$info;
?>

	<div class="container">
		<div class="row">
			<div class="twelvecol last">
				<p class="headline">Please fill in the form below to open a new ticket.</p>
			</div>
		</div>
	</div>
	
	<div class="container greyBlock">
		<div class="row">
			<div class="twelvecol last">
				<form id="ticketForm" method="post" action="open.php" enctype="multipart/form-data">
				  <?php csrf_token(); ?>
				  <input type="hidden" name="a" value="open">
				  
				    <div>
				        <label>Full Name: <span class="required">(required)</span></label>
				        
				            <?php
				            if($thisclient && $thisclient->isValid()) {
				                echo $thisclient->getName();
				            } else { ?>
				                <input id="name" type="text" name="name" size="25" value="<?php echo $info['name']; ?>">
				                <font class="error">*&nbsp;<?php echo $errors['name']; ?></font>
				            <?php
				            } ?>
				    </div>
				    
				    <div>
				        <label>Email Address: <span class="required">(required)</span></label>
				            
				            <?php
				            if($thisclient && $thisclient->isValid()) { 
				                echo $thisclient->getEmail();
				            } else { ?>
				                <input id="email" type="text" name="email" size="25" value="<?php echo $info['email']; ?>">
				                <font class="error">*&nbsp;<?php echo $errors['email']; ?></font>
				            <?php
				            } ?>
				    </div>
				    
				    <div>
				        <label>Telephone:</label>
				
				            <input id="phone" type="text" name="phone" size="17" value="<?php echo $info['phone']; ?>">
				            <font class="error">&nbsp;<?php echo $errors['phone']; ?>&nbsp;&nbsp;<?php echo $errors['phone_ext']; ?></font>  
				    </div>
				    
				    <div>
				        <label>Help Topic: <span class="required">(required)</span></label>
				       
				            <select id="topicId" name="topicId">
				                <option value="" selected="selected">&mdash; Select a Help Topic &mdash;</option>
				                <?php
				                if($topics=Topic::getPublicHelpTopics()) {
				                    foreach($topics as $id =>$name) {
				                        echo sprintf('<option value="%d" %s>%s</option>',
				                                $id, ($info['topicId']==$id)?'selected="selected"':'', $name);
				                    }
				                } else { ?>
				                    <option value="0" >General Inquiry</option>
				                <?php
				                } ?>
				            </select>
				            <font class="error">*&nbsp;<?php echo $errors['topicId']; ?></font>
				        
				    </div>
				    
				    <div>
				        <label>Subject: <span class="required">(required)</span></label>
				        
				            <input id="subject" type="text" name="subject" size="40" value="<?php echo $info['subject']; ?>">
				            <font class="error">*&nbsp;<?php echo $errors['subject']; ?></font>
				    </div>
				    
				    <div>
				        <label>Message:</label>
				        
				            <div><em>Please provide as much detail as possible so we can best assist you.</em></div>
				            <textarea id="message" cols="60" rows="8" name="message"><?php echo $info['message']; ?></textarea>
				            <font class="error">*&nbsp;<?php echo $errors['message']; ?></font>
				    </div>
				
				    <?php if(($cfg->allowOnlineAttachments() && !$cfg->allowAttachmentsOnlogin())
				            || ($cfg->allowAttachmentsOnlogin() && ($thisclient && $thisclient->isValid()))) { ?>
				    <div>
				        <label>Attachments:</label>
				        
				            <div class="uploads"></div><br>
				            <input type="file" class="multifile" name="attachments[]" id="attachments" size="30" value="" />
				            <font class="error">&nbsp;<?php echo $errors['attachments']; ?></font>
				    </div>
				    
				    <?php } ?>
				    <?php
				    if($cfg->allowPriorityChange() && ($priorities=Priority::getPriorities())) { ?>
				    <div>
				        <label>Ticket Priority:</label>
				        
				            <select id="priority" name="priorityId">
				                <?php
				                    if(!$info['priorityId'])
				                        $info['priorityId'] = $cfg->getDefaultPriorityId(); //System default.
				                    foreach($priorities as $id =>$name) {
				                        echo sprintf('<option value="%d" %s>%s</option>',
				                                        $id, ($info['priorityId']==$id)?'selected="selected"':'', $name);
				                        
				                    }
				                ?>
				            </select>
				            <font class="error">&nbsp;<?php echo $errors['priorityId']; ?></font>
				      
				    </div>
				    <?php
				    }
				    ?>
				    <?php
				    if($cfg && $cfg->isCaptchaEnabled() && (!$thisclient || !$thisclient->isValid())) {
				        if($_POST && $errors && !$errors['captcha'])
				            $errors['captcha']='Please re-enter the text again';
				        ?>
				        
				    <div class="captchaRow">
				        
				            <span class="captcha"><img src="captcha.php" border="0" align="left"></span>
				            &nbsp;&nbsp;
				            <input id="captcha" type="text" name="captcha" size="6">
				            <em>Enter the text shown on the image.</em>
				            <font class="error">*&nbsp;<?php echo $errors['captcha']; ?></font>
				    </div>
				    
				    <?php
				    } ?>

				  
			        <input type="submit" class="button" value="Create Ticket">
				  
				</form>
				
			</div>
		</div>
	</div>
