<?php
if(!defined('OSTCLIENTINC') || !$faq  || !$faq->isPublished()) die('Access Denied');

$category=$faq->getCategory();

?>
	<div class="container">
		<div class="row">
			<div class="topWrap">
				<p class="headline"><?php echo $faq->getQuestion() ?></p>
			</div>
		</div>
	</div>
	
	<div class="container greyBlock">
		<div class="row">
			<div class="twelvecol last">
				<p><?php echo Format::safe_html($faq->getAnswer()); ?></p>
				<?php
				if($faq->getNumAttachments()) { ?>
				 <div><span class="faded"><b>Attachments:</b></span>  <?php echo $faq->getAttachmentsLinks(); ?></div>
				<?php
				} ?>
				
				<?php if($faq->getHelpTopics()) { ?>
				
				<div class="article-meta"><span class="faded"><b>Help Topics:</b></span>
				    <?php echo ($topics=$faq->getHelpTopics())?implode(', ',$topics):' '; ?>
				</div>
				<?php } ?>
				<p class="faded">Last updated <?php echo Format::db_daydatetime($category->getUpdateDate()); ?></p>
			</div>
		</div>
	</div>
	
	