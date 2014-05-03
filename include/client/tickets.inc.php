<?php
if(!defined('OSTCLIENTINC') || !is_object($thisclient) || !$thisclient->isValid() || !$cfg->showRelatedTickets()) die('Access Denied');

$qstr='&'; //Query string collector
$status=null;
if(isset($_REQUEST['status'])) { //Query string status has nothing to do with the real status used below.
    $qstr.='status='.urlencode($_REQUEST['status']);
    //Status we are actually going to use on the query...making sure it is clean!
    switch(strtolower($_REQUEST['status'])) {
     case 'open':
     case 'closed':
        $status=strtolower($_REQUEST['status']);
        break;
     default:
        $status=''; //ignore
    }
} elseif($thisclient->getNumOpenTickets()) {
    $status='open'; //Defaulting to open
}

$sortOptions=array('id'=>'ticketID', 'name'=>'ticket.name', 'subject'=>'ticket.subject',
                    'email'=>'ticket.email', 'status'=>'ticket.status', 'dept'=>'dept_name','date'=>'ticket.created');
$orderWays=array('DESC'=>'DESC','ASC'=>'ASC');
//Sorting options...
$order_by=$order=null;
$sort=($_REQUEST['sort'] && $sortOptions[strtolower($_REQUEST['sort'])])?strtolower($_REQUEST['sort']):'date';
if($sort && $sortOptions[$sort])
    $order_by =$sortOptions[$sort];

$order_by=$order_by?$order_by:'ticket_created';
if($_REQUEST['order'] && $orderWays[strtoupper($_REQUEST['order'])])
    $order=$orderWays[strtoupper($_REQUEST['order'])];

$order=$order?$order:'ASC';
if($order_by && strpos($order_by,','))
    $order_by=str_replace(','," $order,",$order_by);

$x=$sort.'_sort';
$$x=' class="'.strtolower($order).'" ';

$qselect='SELECT ticket.ticket_id,ticket.ticketID,ticket.dept_id,isanswered, dept.ispublic, ticket.subject, ticket.name, ticket.email '.
           ',dept_name,ticket. status, ticket.source, ticket.created ';

$qfrom='FROM '.TICKET_TABLE.' ticket '
      .' LEFT JOIN '.DEPT_TABLE.' dept ON (ticket.dept_id=dept.dept_id) ';

$qwhere =' WHERE ticket.email='.db_input($thisclient->getEmail());

if($status){
    $qwhere.=' AND ticket.status='.db_input($status);
}

$search=($_REQUEST['a']=='search' && $_REQUEST['q']);
if($search) {
    $qstr.='&a='.urlencode($_REQUEST['a']).'&q='.urlencode($_REQUEST['q']);
    if(is_numeric($_REQUEST['q'])) {
        $qwhere.=" AND ticket.ticketID LIKE '$queryterm%'";
    } else {//Deep search!
        $queryterm=db_real_escape($_REQUEST['q'],false); //escape the term ONLY...no quotes.
        $qwhere.=' AND ( '
                ." ticket.subject LIKE '%$queryterm%'"
                ." OR thread.body LIKE '%$queryterm%'"
                .' ) ';
        $deep_search=true;
        //Joins needed for search
        $qfrom.=' LEFT JOIN '.TICKET_THREAD_TABLE.' thread ON ('
               .'ticket.ticket_id=thread.ticket_id AND thread.thread_type IN ("M","R"))';
    }
}

$total=db_count('SELECT count(DISTINCT ticket.ticket_id) '.$qfrom.' '.$qwhere);
$page=($_GET['p'] && is_numeric($_GET['p']))?$_GET['p']:1;
$pageNav=new Pagenate($total, $page, PAGE_LIMIT);
$pageNav->setURL('tickets.php',$qstr.'&sort='.urlencode($_REQUEST['sort']).'&order='.urlencode($_REQUEST['order']));

//more stuff...
$qselect.=' ,count(attach_id) as attachments ';
$qfrom.=' LEFT JOIN '.TICKET_ATTACHMENT_TABLE.' attach ON  ticket.ticket_id=attach.ticket_id ';
$qgroup=' GROUP BY ticket.ticket_id';

$query="$qselect $qfrom $qwhere $qgroup ORDER BY $order_by $order LIMIT ".$pageNav->getStart().",".$pageNav->getLimit();
//echo $query;
$res = db_query($query);
$showing=($res && db_num_rows($res))?$pageNav->showing():"";
$showing.=($status)?(' '.ucfirst($status).' Tickets'):' All Tickets';
if($search)
    $showing="Search Results: $showing";

$negorder=$order=='DESC'?'ASC':'DESC'; //Negate the sorting

?>

	<div>
	    <?php if($errors['err']) { ?>
	        <p align="center" id="errormessage"><?php echo $errors['err']; ?></p>
	    <?php } elseif($msg) { ?>
	        <p align="center" id="infomessage"><?php echo $msg; ?></p>
	    <?php } elseif($warn) { ?>
	        <p id="warnmessage"><?php echo $warn; ?></p>
	    <?php } ?>
	</div>
	
	<div class="container">
		<div class="row">
			<div class="topWrap">
				<p class="headline"><?php echo $results_type; ?> <span class="showing"><?php echo $showing; ?></span></p>
				<ul class="ticketButtons">
					<li><i class="icon-book-open"></i><a href="view.php?status=open">View Open</a></li>
					<li class="grey"><i class="icon-book-open"></i><a href="view.php?status=closed">View Closed</a></li>         
			        <li><i class="icon-ccw"></i><a href="">Refresh</a></li>
			    </ul>
			</div>
		</div>
	</div>
	
	<div class="container greyBlock">
		<div class="row">

			<?php
			 $class = "row1";
		     if($res && ($num=db_num_rows($res))) {
		     $i = 1;
		        $defaultDept=Dept::getDefaultDeptName(); //Default public dept.
		        while ($row = db_fetch_array($res)) {
		        	$even = ($i%2 == 0) ? true : false;
		            $dept=$row['ispublic']?$row['dept_name']:$defaultDept;
		            $subject=Format::htmlchars(Format::truncate($row['subject'],40));
		            if($row['attachments'])
		                $subject.='  &nbsp;&nbsp;<span class="Icon file"></span>';
		
		            $ticketID=$row['ticketID'];
		            if($row['isanswered'] && !strcasecmp($row['status'],'open')) {
		                $subject="<b>$subject</b>";
		                $ticketID="<b>$ticketID</b>";
		            }
		            $phone=Format::phone($row['phone']);
		            if($row['phone_ext'])
		                $phone.=' '.$row['phone_ext'];
		            $i++;
		            ?>
			
			
			<div class="sixcol <?php if($even) { echo 'last'; } ?> <?php echo $class; ?>" id="<?php echo $row['ticketID']; ?>">
            	<div class="ticket">
            		
		            		<?php if (strpos($row['status'],'closed') !== false) {
							    $class = 'green';
							} ?>
							<span class="ticketStatus <?php echo $class; ?>"><?php echo ucfirst($row['status'])?></span>
	            	<ul>
	            		<li class="large">
	            			<span class="heading">Ticket #:</span>
	            			<a class="<?php echo strtolower($row['source']); ?>Ticket" title="<?php echo $row['email']; ?>" href="view.php?id=<?php echo $row['ticketID']; ?>"><?php echo $ticketID; ?></a>
	            		</li>
	            		<li>
		            		<span class="heading">Create Date:</span>
		            		<?php echo Format::db_date($row['created']); ?>
	            		</li>
	            		<li>
		            		<span class="heading">Subject:</span>
		            		<a href="tickets.php?id=<?php echo $row['ticketID']; ?>"><?php echo $subject; ?></a>
		            		<?php echo $row['attachments']?"<span class='Icon file'>&nbsp;</span>":''; ?>
	            		</li>
	            		<li>
		            		<span class="heading">Department:</span>
		            		<?php echo Format::truncate($dept,30); ?>
	            		</li>
	            		<li>
		            		<span class="heading">Email:</span>
		            		<?php echo Format::truncate($row['email'],40); ?>
	            		</li>
	            	</ul>
	            	<a href="tickets.php?id=<?php echo $row['ticketID']; ?>" class="button">View ticket</a>
            	</div>
            </div>
            
			<?php
		        }
		
		     } else {
		         echo '<tr><td colspan="7">Your query did not match any records</td></tr>';
		     }
		    ?>
    
		<?php
		if($res && $num>0) {
		    echo '<div>&nbsp;Page:'.$pageNav->getPageLinks().'&nbsp;</div>';
		}
		?>
		</div>
	</div>


    
        
    
  
            
            
            
            
        
