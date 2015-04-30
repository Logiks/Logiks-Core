<?php
if(!defined('ROOT')) exit('No direct script access allowed');

$layouts=array(
		"layout10"=>"",
		"layout20"=>"",
		"layout21"=>"",
		"layout30"=>"",
		"layout31"=>"",
		"layout32"=>"",
		"layout33"=>"",
		"layout40"=>"",
	);
if(!is_array($_SESSION["page_params"]["contentarea"])) {
	$_SESSION["page_params"]["layout"]="layout00";
} elseif(!isset($_SESSION["page_params"]["layout"]) ||
	!array_key_exists($_SESSION["page_params"]["layout"],$layouts)) {
		
	$nx=count($_SESSION["page_params"]["contentarea"]);
	if($nx>4) $nx=4;
	if($nx<=0) $nx=1;
	$_SESSION["page_params"]["layout"]="layout{$nx}0";
} else {
	$_SESSION["page_params"]["layout"]="layout00";
	$_SESSION["page_params"]["contentarea"]="<h3 align=center><br/>Sorry,No Supported Layout Found</h3>";
}
if(!function_exists("printPGFrame")) {
	function printPGFrame($src,$style,$name) {
		if(substr($src,0,1)=="#") $src=_link(substr($src,1));
		elseif(substr($src,0,1)=="@") $src=_service(substr($src,1))."&format=html";
		elseif(substr($src,0,7)=="http://" || substr($src,0,8)=="https://") $src=$src;
		else $src=SiteLocation.$src;
		
		echo "<iframe id='{$name}' name='{$name}' width=100% height=100% src='{$src}' frameborder=0 style='border:0px;{$style}' ></iframe>";
	}
}
?>
<div id=page class='page <?=getPageComponentClass("ui-widget-content")?>'>
	<?php if(isset($_SESSION["page_params"]["toolbar"])) { ?>
	<div id=toolbar class="toolbar <?=getPageComponentClass("toolbar")?>">
		<div class='left' style='margin-left:5px;'>
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
			if($_SESSION["page_params"]["layout"]=="layout00") {
				$ca="";
				if(isset($_SESSION["page_params"]["contentarea"])) $ca=$_SESSION["page_params"]["contentarea"];
				if($ca!=null && strlen($ca)>0) {
					if(file_exists($ca) && is_file($ca)) {
						include $l;
					} elseif(function_exists($ca)) {
						call_user_func($ca);
					} else {
						echo $ca;
					}
				} else {
					echo "<h3 align=center><br/>Sorry,No Supported Layout Found</h3>";
				}
			} else {
				switch($_SESSION["page_params"]["layout"]) {
					case "layout10":
						if(isset($_SESSION["page_params"]["contentarea"][0]['src'])) {
							printPGFrame($_SESSION["page_params"]["contentarea"][0]['src'],
								"width:100%;height:99%;","frm0");
						} else {
							echo "<h3 align=center><br/>Sorry,No Supported Layout Content Found</h3>";
						}
					break;
					case "layout20":
						if(isset($_SESSION["page_params"]["contentarea"][0]['src'])) {
							printPGFrame($_SESSION["page_params"]["contentarea"][0]['src'],
								"width:50%;height:99%;","frm0");
						}
						if(isset($_SESSION["page_params"]["contentarea"][1]['src'])) {
							printPGFrame($_SESSION["page_params"]["contentarea"][1]['src'],
								"width:50%;height:99%;","frm1");
						}
					break;
					case "layout21":
						if(isset($_SESSION["page_params"]["contentarea"][0]['src'])) {
							printPGFrame($_SESSION["page_params"]["contentarea"][0]['src'],
								"width:100%;height:49%;","frm0");
						}
						if(isset($_SESSION["page_params"]["contentarea"][1]['src'])) {
							printPGFrame($_SESSION["page_params"]["contentarea"][1]['src'],
								"width:100%;height:49%;","frm1");
						}
					break;
					case "layout30":
						if(isset($_SESSION["page_params"]["contentarea"][0]['src'])) {
							printPGFrame($_SESSION["page_params"]["contentarea"][0]['src'],
								"width:50%;height:49%;","frm0");
						}
						if(isset($_SESSION["page_params"]["contentarea"][1]['src'])) {
							printPGFrame($_SESSION["page_params"]["contentarea"][1]['src'],
								"width:50%;height:49%;","frm1");
						}
						if(isset($_SESSION["page_params"]["contentarea"][2]['src'])) {
							printPGFrame($_SESSION["page_params"]["contentarea"][2]['src'],
								"width:100%;height:49%;","frm2");
						}
					break;
					case "layout31":
						if(isset($_SESSION["page_params"]["contentarea"][0]['src'])) {
							printPGFrame($_SESSION["page_params"]["contentarea"][0]['src'],
								"width:100%;height:49%;","frm0");
						}
						if(isset($_SESSION["page_params"]["contentarea"][1]['src'])) {
							printPGFrame($_SESSION["page_params"]["contentarea"][1]['src'],
								"width:50%;height:49%;","frm1");
						}
						if(isset($_SESSION["page_params"]["contentarea"][2]['src'])) {
							printPGFrame($_SESSION["page_params"]["contentarea"][2]['src'],
								"width:50%;height:49%;","frm2");
						}
					break;
					case "layout32":
						echo "<div style='width:50%;height:99%;display:inline-block;'>";
						if(isset($_SESSION["page_params"]["contentarea"][0]['src'])) {
							printPGFrame($_SESSION["page_params"]["contentarea"][0]['src'],
								"width:100%;height:50%;","frm0");
						}
						if(isset($_SESSION["page_params"]["contentarea"][1]['src'])) {
							printPGFrame($_SESSION["page_params"]["contentarea"][1]['src'],
								"width:100%;height:50%;","frm1");
						}
						echo "</div>";
						echo "<div style='width:50%;height:99%;display:inline-block;'>";
						if(isset($_SESSION["page_params"]["contentarea"][2]['src'])) {
							printPGFrame($_SESSION["page_params"]["contentarea"][2]['src'],
								"width:100%;height:100%;","frm2");
						}
						echo "</div>";
					break;
					case "layout33":
						echo "<div style='width:50%;height:99%;display:inline-block;'>";
						if(isset($_SESSION["page_params"]["contentarea"][0]['src'])) {
							printPGFrame($_SESSION["page_params"]["contentarea"][0]['src'],
								"width:100%;height:100%;","frm0");
						}
						echo "</div>";
						echo "<div style='width:50%;height:99%;display:inline-block;'>";
						if(isset($_SESSION["page_params"]["contentarea"][1]['src'])) {
							printPGFrame($_SESSION["page_params"]["contentarea"][1]['src'],
								"width:100%;height:50%;","frm1");
						}
						if(isset($_SESSION["page_params"]["contentarea"][2]['src'])) {
							printPGFrame($_SESSION["page_params"]["contentarea"][2]['src'],
								"width:100%;height:50%;","frm2");
						}
						echo "</div>";
					break;
					case "layout40":
						if(isset($_SESSION["page_params"]["contentarea"][0]['src'])) {
							printPGFrame($_SESSION["page_params"]["contentarea"][0]['src'],
								"width:50%;height:49%;","frm0");
						}
						if(isset($_SESSION["page_params"]["contentarea"][1]['src'])) {
							printPGFrame($_SESSION["page_params"]["contentarea"][1]['src'],
								"width:50%;height:49%;","frm1");
						}
						if(isset($_SESSION["page_params"]["contentarea"][2]['src'])) {
							printPGFrame($_SESSION["page_params"]["contentarea"][2]['src'],
								"width:50%;height:49%;","frm2");
						}
						if(isset($_SESSION["page_params"]["contentarea"][3]['src'])) {
							printPGFrame($_SESSION["page_params"]["contentarea"][3]['src'],
								"width:50%;height:49%;","frm3");
						}
					break;
					default:
						echo "<h3 align=center><br/>Sorry, {$_SESSION["page_params"]["layout"]} Layout Not Supported Yet</h3>";
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
</div>
<script language=javascript>
var resizePageTimer=null;
function resizePageUI() {
	w=getWindowSize();
	width=w.w;
	height=w.h;
	if($("#pgworkspace").parent().width()!=null) width=$("#pgworkspace").parent().width();
	if($("#pgworkspace").parent().height()!=null) height=$("#pgworkspace").parent().height();
	
	$("#pgworkspace").css("height",(height-$("#toolbar").height()-4)+"px");
	$("#pgworkspace").css("width",(width-0)+"px");
}
$(function() {
	$(window).bind('resize', function() {
		if(resizePageTimer) {
			clearTimeout(resizePageTimer);
		}
		resizePageTimer=setTimeout(resizePageUI, 100);
	});
	resizePageUI();

	initPageUI("body");
});
//ToDoS :: add Connectivity Between Frames
//$(window.frames.frm0.crossFrame).click(function() {alert('asd');});
function connectFrame(srcFrame,element,event,destFrame,func) {
	//console.log(element);
	//console.log(window.frames[srcFrame][element]);
	//console.log($("#"+srcFrame).contents());
}
<?php
if(isset($_SESSION["page_params"]["script"])) {
	$ca=$_SESSION["page_params"]["script"];
	if(strlen($ca)>0) {
		if(file_exists($ca) && is_file($ca)) {
			include $l;
		} elseif(function_exists($ca)) {
			call_user_func($ca);
		} else {
			echo $ca;
		}
	}
}
?>
</script>
