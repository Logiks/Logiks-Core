<?php
if(!defined('ROOT')) exit('No direct script access allowed');

$popupParams=null;

$cArea=array();
$defaultArea=array(
	"title"=>"LGKS",
	"type"=>"",
	"src"=>"",
	//"icon"=>"",
	//"buttons"=>"",
	//"search"=>"",
	//"onload"=>"",
	//"onshow"=>"",
);

$ca=array();
if(isset($_SESSION["page_params"]["contentarea"])) $ca=$_SESSION["page_params"]["contentarea"];
if(is_array($ca)) {
	foreach($ca as $a=>$b) {
		$x=array_merge($defaultArea,$b);
		$cArea["dp-$a"]=$x;
	}
} else {
	$cArea["dp-0"]=$defaultArea;
	$cArea["dp-0"]['src']=$ca;
	$cArea["dp-0"]['type']="phpfunc";
}
if(!isset($_SESSION["page_opts"]["active_index"])) $_SESSION["page_opts"]["active_index"]=0;
if(!isset($_SESSION["page_classes"]["popupButton"])) $_SESSION["page_classes"]["popupButton"]="clr_darkblue";
if(!isset($_SESSION["page_classes"]["switchBar"])) $_SESSION["page_classes"]["switchBar"]="ui-widget-header";// ui-corner-all
?>
<style>
/*.switchBtn.noload{display:none !important;}*/
</style>
<div id=page class='page <?=getPageComponentClass("page")?>'>
	<?php if(isset($_SESSION["page_params"]["toolbar"])) { ?>
	<div id=toolbar class="toolbar toolbarx <?=getPageComponentClass("toolbar")?>" style='height:75px;'>
		<div style='width:100%;height:40px;overflow:hidden;'>
			<h1 class='left'>
				<?=$_SESSION["page_opts"]["title"]?>&nbsp;&nbsp;
			</h1>
			<div class='right noselection searchBar' style='margin-right: 10px;'>
				<input name=term type=text  class='searchText ghosttext' title='Search ...' placeholder='Search ...' />
				<select name='searchCols' class='searchCols nostyle'>
					<option value=''>All</option>
				</select>
			</div>
		</div>
		<div style='width:100%;height:35px;overflow:hidden;'>
			<div id=dpToolbar1 class='left noselection toolbar1'>
				<?php
					$btns=array();
					if(isset($_SESSION["page_params"]["toolbar"])) $btns=$_SESSION["page_params"]["toolbar"];
					printPageToolbar($btns,$_SESSION["page_opts"]["toolButtons"]);
				?>
			</div>
			<div id=dpToolbar2 class='left noselection toolbar2'>
				<?php
					foreach($cArea as $a=>$b) {
						if(isset($b['buttons']) && strlen($b['buttons'])>0) {
							echo "<span rel='$a' class='dpToolPanes'>";
							printPageToolbar($b['buttons'],false);
							echo "</span>";
							unset($cArea[$a]['buttons']);
						}
					}
				?>
			</div>
			<div id=dpToolbar3 class='left noselection toolbar3'></div>
			<div id=switchBar class='left switchBar noselection <?=getPageComponentClass("switchBar")?>'>
				<?php
					foreach($cArea as $a=>$b) {
						$clz="";
						$attrs="";
						if(isset($b['search']) && strlen($b['search'])>0 && $b['type']!="iframe") {
							$attrs.="search='{$b['search']}' ";
							$clz.="search";
						}
						if(isset($b['onload']) && strlen($b['onload'])>0) {
							$attrs.="onload='{$b['onload']}' ";
						}
						if(isset($b['onshow']) && strlen($b['onshow'])>0) {
							$attrs.="onshow='{$b['onshow']}' ";
						}
						if(isset($b['attrs']) && strlen($b['attrs'])>0) {
							$attrs.=$b['attrs'];
						}
						if($a=="dp-0") {
							$b['icon']='.homeicon';
							$b['title']="";
						}
						if(isset($b['icon']) && strlen($b['icon'])>1) {
							if(substr($b['icon'],0,1)==".") {
								$b['icon']=substr($b['icon'],1);
								echo "<span rel='{$a}' class='switchBtn $clz {$b['icon']}' $attrs title='{$b['title']}'>{$b['title']}</span><span class='arrowRight'></span>";
							} elseif(substr($b['icon'],0,1)=="#") {
								$b['icon']=substr($b['icon'],1);
								$b['icon']=loadMedia($b['icon']);
								echo "<span rel='{$a}' class='switchBtn $clz' $attrs title='{$b['title']}'><img src='{$b['icon']}' alt='' width=18px height=18px />{$b['title']}</span><span class='arrowRight'></span>";
							} else {
								echo "<span rel='{$a}' class='switchBtn $clz' $attrs title='{$b['title']}'><img src='{$b['icon']}' alt='' width=18px height=18px />{$b['title']}</span><span class='arrowRight'></span>";
							}
						} else {
							echo "<span rel='{$a}' class='switchBtn $clz noicon' $attrs title='{$b['title']}'>{$b['title']}</span><span class='arrowRight'></span>";
						}
						unset($cArea[$a]['icon']);unset($cArea[$a]['title']);
					}
				?>
			</div>
			
		</div>
	</div>
	<?php } ?>
	<div id=pgworkspace class='<?=getPageComponentClass("pgworkspace")?>' style='width:100%;'>
		<?php
			foreach($cArea as $a=>$b) {
				if(!isset($b['src']) || strlen($b['src'])<=0) {
					echo "<span rel='$a' class='dpPanes' src='{$b['src']}'>";
					echo "<h3 align=center><br/>Sorry, Pane contains no data.</h3>";
					echo "</span>";
					continue;
				}

				if($b['type']=="link" || $b['type']=="href") {
					if($b['src']=="##") {
					} else {
						if(substr($b['src'],0,1)=="#") $b['src']=_link(substr($b['src'],1));
						elseif(substr($b['src'],0,1)=="@") $b['src']=_service(substr($b['src'],1))."&format=html";
						elseif(substr($b['src'],0,7)=="http://" || substr($b['src'],0,8)=="https://") $b['src']=$b['src'];
						else $b['src']=SiteLocation.$b['src'];
					}
					echo "<span rel='$a' class='dpPanes' src='{$b['src']}'>";
					//echo $b['src'];
				} elseif($b['type']=="iframe") {
					if(substr($b['src'],0,1)=="#") $b['src']=_link(substr($b['src'],1));
					elseif(substr($b['src'],0,1)=="@") $b['src']=_service(substr($b['src'],1))."&format=html";
					elseif(substr($b['src'],0,7)=="http://" || substr($b['src'],0,8)=="https://") $b['src']=$b['src'];
					else $b['src']=SiteLocation.$b['src'];

					echo "<span rel='$a' class='dpPanes' style=''>";
					echo "<iframe width=100% height=100% src='{$b['src']}' frameborder=0 style='border:0px;width:100%;height:99%;' ></iframe>";
				} elseif($b['type']=="php" && file_exists($b['src'])) {
					echo "<span rel='$a' class='dpPanes'>";
					include $b['src'];
				} elseif($b['type']=="module") {
					echo "<span rel='$a' class='dpPanes'>";
					loadModule($b['src']);
				} elseif($b['type']=="media") {
					echo "<span rel='$a' class='dpPanes'>";
					loadAllMedia($b['src']);
				} elseif($b['type']=="phpfunc") {
					echo "<span rel='$a' class='dpPanes'>";
					call_user_func($b['src']);
				} else {
					echo "<span rel='$a' class='dpPanes'>";
					if(file_exists($b['src'])) file_get_contents($b['src']);
					else echo $b['src'];
				}
				echo "</span>";
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

	//$("#switchBar .switchBtn:first-child").addClass("active");
	//$("#toolbar .searchBar").hide();

	$("#switchBar").delegate(".switchBtn","click",function(e) {
			if($(this).hasClass("active")) return;
			rel=$(this).attr("rel");
			dpAction(this,"switch",rel);
		});
	$("input.searchText","#toolbar .searchBar").keypress(function(e) {
			if(e.keyCode==13) {
				dpAction(this,"search");
			}
		});
	$("#toolbar").delegate("a.button[cmd],button[cmd]","click",function(e) {
			cmd=$(this).attr("cmd");
			rel=$(this).parents(".dpToolPanes").attr("rel");
			dpAction(this,cmd,rel);
		});
	$("#toolbar").delegate("a.button[href]","click",function(e) {
			e.preventDefault();
			href=$(this).attr("href");
			rel=$(this).parents(".dpToolPanes").attr("rel");
			dpLink($(this).text(),href);
		});
	updateDPHash();
	dpAction(null,"gohome");
});
function updateDPHash() {
	$(".switchBtn:not(:first-child):not([hash])","#switchBar").each(function() {
			hash=hashCode($(this).text());
			$(this).attr("hash",hash);
		});
	$("#pgworkspace .dpPanes[rel]").each(function() {
			rel=$(this).attr("rel");
			if($(this).attr("src")=="##") {
				$(".switchBtn[rel="+rel+"]","#switchBar").addClass("noload");
			}
		});
}
function dpAction(src,cmd,params) {
	switch(cmd) {
		case "switch":
			if($("#pgworkspace .dpPanes[rel="+params+"]").attr("src")=="##") {
				//lgksAlert("Not Enabled Yet.");
				return false;
			}
			
			$("#pgworkspace .dpPanes.active").removeClass("active").hide();
			$("#toolbar .dpToolPanes.active").removeClass("active").hide();
			$("#switchBar .switchBtn.active").removeClass("active");

			$("#pgworkspace .dpPanes[rel="+params+"]").addClass("active").show();
			$("#toolbar .dpToolPanes[rel="+params+"]").addClass("active").show();
			$("#toolbar .switchBtn[rel="+params+"]").removeClass("noload").addClass("active");
			
			if($(src).hasClass("search")) {
				$("#toolbar .searchBar").show();
			} else {
				$("#toolbar .searchBar").hide();
			}
			$("select.searchCols","#toolbar .searchBar").html('<option value="">All</option>');
			$("input,select","#toolbar .searchBar").val('');
			
			if($("#pgworkspace .dpPanes.active").attr("src")!=null &&
				$("#pgworkspace .dpPanes.active").attr("src").length>0) {

					if($("#pgworkspace .dpPanes.active").attr("src")==
						$("#pgworkspace .dpPanes.active").attr("src_old")) {
						checkDPCompatibilty('show');
						if($(src).attr("onshow")!=null && $(src).attr("onshow").length>0) {
							srch=$(src).attr("onload");
							if(typeof srch=="function") srch("show");
							else if(typeof srch=="string" && srch.length>0 && window[srch]!=null) window[srch]("show");
						}
					} else {
						$("#pgworkspace .dpPanes.active").html("<div class='ajaxloading'>Loading ...</div>");
						if($(src).attr("onload")!=null && $(src).attr("onload").length>0) {
							srch=$(src).attr("onload");
							if(typeof srch=="function") srch("load");
							else if(typeof srch=="string" && srch.length>0 && window[srch]!=null) window[srch]("load");
						}
						$("#pgworkspace .dpPanes.active").load($("#pgworkspace .dpPanes.active").attr("src"),function() {
								checkDPCompatibilty('load');
								if($(src).attr("onload")!=null && $(src).attr("onload").length>0) {
									srch=$(src).attr("onload");
									if(typeof srch=="function") srch("loaded");
									else if(typeof srch=="string" && srch.length>0 && window[srch]!=null) window[srch]("loaded");
								}
								checkDPCompatibilty('show');
								if($(src).attr("onshow")!=null && $(src).attr("onshow").length>0) {
									srch=$(src).attr("onload");
									if(typeof srch=="function") srch("show");
									else if(typeof srch=="string" && srch.length>0 && window[srch]!=null) window[srch]("show");
								}
								//$("#pgworkspace .dpPanes.active").attr("src_old",
								//	$("#pgworkspace .dpPanes.active").attr("src"));
							});
					}
			} else {
				checkDPCompatibilty('show');
				if($(src).attr("onshow")!=null && $(src).attr("onshow").length>0) {
					srch=$(src).attr("onload");
					if(typeof srch=="function") srch("show");
					else if(typeof srch=="string" && srch.length>0 && window[srch]!=null) window[srch]("show");
				}
			}
		break;
		case "search":
			v=$(src).val();
			f=$(src).parent().find(".searchCols").val();
			$(src).val("");
			srch=$("#switchBar .switchBtn.active").attr("search");
			if(typeof srch=="function") srch(v,f,src);
			else if(typeof srch=="string" && srch.length>0 && window[srch]!=null) window[srch](v,f,src);
		break;
		case "gohome":
			dpAction($("#switchBar .switchBtn[rel=dp-<?=$_SESSION["page_opts"]["active_index"]?>]"),"switch",
		"dp-<?=$_SESSION["page_opts"]["active_index"]?>");
		break;
		case "reload":
			//alert("asdasd");
		break;
		default:
			//alert("Not Yet Implemented : "+cmd);
	}
}
function checkDPCompatibilty(act) {
	$("#page .toolbarx #dpToolbar3").html("");
}
function dpLink(name,link,search,btns,icon) {
	link+="&site=<?=SITENAME?>";
	hash=hashCode(name);
	if($("#switchBar").find(".switchBtn[hash='"+hash+"']").length>0) {
		html1=$("#switchBar").find(".switchBtn[hash='"+hash+"']");
		dp=$(html1).attr("rel");
		$("#pgworkspace .dpPanes[rel="+dp+"]").attr("src",link);
		dpAction(html1,"switch",dp);
	} else {
		dp="dp-"+hash;
		attr="";
		clas='switchBtn';
		cntnt="";
		if(search!=null && search.length>0)  {
			attr+=" search='"+search+"'";
			clas+=" search";
		}
		if(icon==null || icon.length<=0) clas+=" noicon";
		else {
			if(icon.substr(0,1)==".") {
				clas+=" "+icon.substr(1);
			} else {
				clas+=" withimg";
				cntnt="<img src='"+icon+"' alt='' width=18px height=18px />";
			}
		}
		html1="<span rel='"+dp+"' "+attr+" hash='"+hash+"' class='"+clas+"'>"+cntnt+name+"</span><span class='arrowRight'></span>";
		html1=$(html1);
		$("#switchBar").append(html1);

		html2="<span rel='"+dp+"' class='dpPanes' src='"+link+"'><h3 align=center><br/>Sorry, Pane contains no data.</h3></span>";
		$("#pgworkspace").append(html2);

		if(btns!=null && btns.length>0)  {
			html3="<span rel='"+dp+"' class='dpToolPanes'>"+btns+"</span>";
			$("#dpToolbar2").append(html3);
		}

		dpAction(html1,"switch",dp);
	}
}
function dpRemoveLink(name) {
	hash=hashCode(name);
	if($("#switchBar").find(".switchBtn[hash='"+hash+"']").length>0) {
		html1=$("#switchBar").find(".switchBtn[hash='"+hash+"']");
		dp=$(html1).attr("rel");
		$("#switchBar").find(".switchBtn[rel='"+dp+"']").detach();
		$("#pgworkspace").find(".dpPanes[rel='"+dp+"']").detach();
	}
}
</script>
