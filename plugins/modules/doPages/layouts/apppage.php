<?php
if(!defined('ROOT')) exit('No direct script access allowed');
$popupParams=null;
?>
<style>
html,body {
	overflow: hidden;
}
</style>
<div id=page class='page <?=getPageComponentClass("ui-widget-content")?>'>
	<?php if(isset($_SESSION["page_params"]["toolbar"])) { ?>
	<div id=toolbar class="toolbar <?=getPageComponentClass("toolbar")?>">
		<div class='left'>
			<?php
				$btns=array();
				if(isset($_SESSION["page_params"]["toolbar"])) $btns=$_SESSION["page_params"]["toolbar"];
				printPageToolbar($btns,$_SESSION["page_opts"]["toolButtons"]);
			?>
		</div>
		<div class='right' style='margin-right:5px;padding:10px;'>
			<div id=loadingmsg class='ajaxloading4'></div>
		</div>
	</div>
	<?php } ?>
	<div id=pgworkspace style='<?=getPageComponentClass("pgworkspace")?>' style='width:100%;'>
		<?php
			$ca="";
			if(isset($_SESSION["page_params"]["contentarea"])) $ca=$_SESSION["page_params"]["contentarea"];
			if(strlen($ca)>0) {
				if(file_exists($ca) && is_file($ca)) {
					include $l;
				} elseif(function_exists($ca)) {
					call_user_func($ca);
				} else {
					echo $ca;
				}
			}
		?>
	</div>
</div>
<div style='display:none'>
	<?php
		$noDisp="";
		if(isset($_SESSION["page_params"]["hidden"])) $noDisp=$_SESSION["page_params"]["hidden"];
		if(strlen($noDisp)>0) {
			if(function_exists($noDisp)) {
				call_user_func($noDisp);
			} else {
				echo $noDisp;
			}
		}
	?>
	<div id=popupdiv class='ui-widget-content' title='Popup Message' style='display:none;'>
		<?php
			$autoPopup="";
			if(isset($_SESSION["page_params"]["autopopup"])) $autoPopup=$_SESSION["page_params"]["autopopup"];
			if(is_array($autoPopup)) {
				$c="";
				if(isset($autoPopup["content"])) $c=$autoPopup["content"];
				echo $c;
				$popupParams=$autoPopup;
			} elseif(strlen($autoPopup)>0) {
				if(function_exists($autoPopup)) {
					call_user_func($autoPopup);
				} else {
					echo $autoPopup;
				}
			}
		?>
	</div>
</div>
<script language=javascript>
var resizePageTimer=null;
function resizePageUI() {
	w=getWindowSize();
	width=w.w;
	height=w.h;
	if($("#pgworkspace").parent().width()!=null) width=$("#pgworkspace").parent().width();
	if($("#pgworkspace").parent().height()!=null) height=$("#pgworkspace").parent().height();
	
	//$("#toolbar").css("height","32px !important");
	$("#pgworkspace").css("height",(height-$("#toolbar").height()-3)+"px");
	$("#pgworkspace").css("width",(width-4)+"px");
}
$(function() {
	$(window).bind('resize', function() {
		if (resizePageTimer) {
			clearTimeout(resizePageTimer);
		}
		resizePageTimer=setTimeout(resizePageUI, 100);
	});
	resizePageUI();
	initPageUI("body");
	if($(".datatable tbody").length>0) {
		$(".datatable tbody").delegate("input[type=checkbox][name=rowselect]", "change", function() {
					if($(this).is(":checked")) {
						$(this).parent().parent().addClass("highlight");
					} else {
						$(this).parent().parent().removeClass("highlight");
					}
			});
	}

	if($("#popupdiv").html().trim().length>0) {
		<?php
			if($popupParams!=null) {
				$w="500";
				$h="300";
				$run="";
				if(isset($popupParams["run"])) $run=$popupParams["run"];
				if(isset($popupParams["w"])) $w=$popupParams["w"];
				if(isset($popupParams["h"])) $h=$popupParams["h"];

				echo "invokePopup('#popupdiv',$run,$w,$h);";
			} else {
				echo "invokePopup('#popupdiv');";
			}
		?>
	}
});
</script>
