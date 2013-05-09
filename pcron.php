<?php
/*
 * PHP Based Scheduler Similar to Cron On Linux
 * 
 * This automatically runs the cron tasks periodically, triggered by people visiting pages of your site via index.php.
 * To run the cron tasks independent of site visits, please configure a cron task to visit this page cron.php perodiacally say 5mins.
 * You can modify the 'PCRON_KEY' @ admincp>Configurations>Xtra Settings
 * 
 * Cron Job>>
 * 0 * * * * wget -O - -q -t 1 <logikssite>/pcron.php?pcron_key=PCRON_KEY
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com on 24/02/2012
 * Author: Kshyana Prava kshyana23@gmail.com on 24/02/2012
 * Version: 1.0
 */
if(!isset($initialized)) {
	include "api/initialize.php";
}
if (ENABLE_AUTO_PCRON=="true" && isset($_REQUEST['pcron_key']) && PCRON_KEY==$_REQUEST['pcron_key']) {
	$q=new PCronQueue();
	$q->run();
	//echo PCronQueue::createTask("Testing132",60,'testjob',array(),"POST","false");
	//printArray(PCronQueue::get_tasks());
}
?>
