<?php
if (!defined('ROOT')) exit('No direct script access allowed');


$q=new PCronQueue();
echo $q->run();
?>