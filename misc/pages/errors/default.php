<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!isset($errorParams["pagelinks"]) || !is_array($errorParams["pagelinks"])) {
	$lnk=SiteLocation;
	if(function_exists("_link")) $lnk=_link("");
	$errorParams["pagelinks"]=array(
			"Home"=>$lnk,
		);
	if(function_exists("session_check") && !session_check(false)) {
		$errorParams["pagelinks"]["Logout"]=array(
					"class"=>"right",
					"url"=>SiteLocation."api/logout.php",
				);
	}
}
if(!isset($_SERVER['HEADER-PRINTED']) || $_SERVER['HEADER-PRINTED']!=true) {
	if(function_exists("_css")) {
		printHTMLPageHeader(true);
	}
} else {
	echo "</head>";
}

//printArray($errorParams);
?>
<body class='errorpage'>
	<ul class='errorbox'>
		<li class='errorCode'><?=$errorParams['errorType']?></li>
		<li class='msg_header'><?=$errorParams['msg_header']?></li>
		<li class='msg_body'><?=$errorParams['msg_body']?></li>
		<li class='posted_on'><?=$errorParams['posted_on']?></li>
		<li class='posted_by'><?=$errorParams['posted_by']?></li>
	</ul>
	<ul class='pagelinks'>
		<?php
			foreach ($errorParams["pagelinks"] as $key => $value) {
				if(file_exists("_ling")) $key=_ling($key);
				if(is_array($value)) {
					$clz="";
					if(isset($value['class'])) $clz=$value['class'];
					$lnk="";
					if(isset($value['url'])) $lnk=$value['url'];
					echo "<li class='$clz'><a href='{$lnk}'>$key</a></li>";
				} else {
					echo "<li><a href='{$value}'>$key</a></li>";
				}
			}
		?>
	</ul>
	<div id="footer">
		All rights reserved, © <?=getConfig("APPS_COPYRIGHT_YEAR")?> - <a href="<?=getConfig("APPS_COMPANY_SITE")?>" target="_blank"><?=getConfig("APPS_COMPANY")?></a>
	</div>
<?php
if(!function_exists("_css")) {
	echo "</body>";
}
if(MASTER_DEBUG_MODE=='true') {
	printTrace();
}
?>

<div id="onlyprint" align=center>
	<h1 class=header>Error : <?=$msg_header?></h1>
	<br/><br/>
	<p align=justify style='width:60%;margin:auto;'>
		<?=$msg_body?>
	</p>
	<br/>
	<div align=center style='background:#ccc;border:2px solid #aaa;border-left:0px;border-right:0px;'><h3 style='margin:0px;'>Trace</h3></div>
	<div align=left>
		<pre style='font-size:12px;'>
			<?php 
				if(MASTER_DEBUG_MODE=='true') {
					printTrace();
				}
			?>
		</pre>
	</div>
</div>
</body>