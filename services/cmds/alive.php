<?php
header('Access-Control-Allow-Origin:*');
header('Content-Type:text/event-stream');
header('Cache-Control:no-cache');

$time=date("r");
echo "data: SERVER $time \n\n";

flush();
?>