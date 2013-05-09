<?php
if(ENABLE_AUTO_PCRON=="true") {
	$pcPage="pcron.php";
	$pcQuery="pcron_key=".PCRON_KEY."&site=".SITENAME;
	$printPCronCMD=false;
	if(isset($_SESSION["PCRON_LAST_RUN"])) {
		if(time()-$_SESSION["PCRON_LAST_RUN"]>AUTO_PCRON_PERIOD) {
			$_SESSION["PCRON_LAST_RUN"]=time();
			$printPCronCMD=true;
		} else {
			$printPCronCMD=false;
		}
	} else {
		$_SESSION["PCRON_LAST_RUN"]=time();
		$printPCronCMD=true;
	}
	if($printPCronCMD) {
?>
	<script language='javascript'>
	lastRun="<?=$_SESSION["PCRON_LAST_RUN"]?>";
	$(function() {
		$.ajax({
			   type:"POST",
			   url:"<?=$pcPage?>",
			   data:"<?=$pcQuery?>",
			   cache:false,
			   success:function(msg){
					//alert("PCron Job Complete : "+msg);
			   }
		 });
	});
	</script>
<?php 
	} }
?>
