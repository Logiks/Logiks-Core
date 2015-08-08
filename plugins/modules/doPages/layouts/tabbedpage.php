<?php
if(!defined('ROOT')) exit('No direct script access allowed');
?>
<style>
#pgworkspace ul.ui-tabs-nav a {
	text-decoration:none;
}
#pgworkspace ul.ui-tabs-nav li .ui-icon {
	zoom: 1.2;
	margin: 2px;
	cursor:pointer;
}
#pgworkspace .iframetab {
	width:100%;height:100%;
	border:0px;
	margin:0px;padding:0px;
}
#pgworkspace div.ui-tabs-panel.ui-widget-content {
	border:0px;
}
</style>
<div id="page" class='page <?=getPageComponentClass("ui-widget-content")?>'>
	<?php if(isset($_SESSION["page_params"]["toolbar"])) { ?>
	<div id="toolbar" class="toolbar <?=getPageComponentClass("toolbar")?>">
		<div class='left' style='margin-left:5px;'>
			<?php
				$btns=array();
				if(isset($_SESSION["page_params"]["toolbar"])) $btns=$_SESSION["page_params"]["toolbar"];
				printPageToolbar($btns,$_SESSION["page_opts"]["toolButtons"]);
			?>
		</div>
		<div class='right' style='margin-right:5px;padding:10px;'>
			<div id="loadingmsg" class='ajaxloading4'></div>
		</div>
	</div>
	<?php } ?>
	<div id="pgworkspace" style='<?=getPageComponentClass("pgworkspace")?>' style='width:100%;'>
		<?php
			if(isset($_SESSION["page_params"]["contentarea"])) {
				if(!is_array($_SESSION["page_params"]["contentarea"])) {
					$title=$_SESSION["page_params"]["contentarea"];
					$title=str_replace("http","",$title);
					$title=str_replace("://","",$title);
					$title=strip_tags($title);
					$_SESSION["page_params"]["contentarea"]=array(
							array("src"=>$_SESSION["page_params"]["contentarea"])
						);
				}
				if(count($_SESSION["page_params"]["contentarea"])>1 && isset($_SESSION["page_params"]["contentarea"]['src'])) {
					$_SESSION["page_params"]["contentarea"]=array($_SESSION["page_params"]["contentarea"]);
				}
				if(count($_SESSION["page_params"]["contentarea"])>0) {
					//Itereate over array
					$nav="<ul>";
					$html="";
					foreach($_SESSION["page_params"]["contentarea"] as $key=>$pane) {
						if(strlen($pane['src'])<=2) continue;
						$hash=md5($pane['src'].$key);
						$_SESSION["page_params"]["contentarea"][$key]['hash']=$hash;
						if(isset($pane['title'])) $title=$pane['title'];
						else {
							$title=str_replace("http","",$pane['src']);
							$title=str_replace("://","",$title);
							$title=strip_tags($title);
							$_SESSION["page_params"]["contentarea"][$key]['title']=$title;
						}
						if(!isset($pane['closable']) || $pane['closable']==true) {
							$nav.="<li><a href='#{$hash}'>{$title}</a><span class='ui-icon ui-icon-close ui-icon-closethick'>Remove Tab</span></li>";
							$html.="<div id='{$hash}' class='' style='overflow:hidden;width:100%;height:100%;'>";
						} else {
							$nav.="<li><a href='#{$hash}'>{$title}</a></li>";
							$html.="<div id='{$hash}' class='unclosable' style='overflow:hidden;width:100%;height:100%;'>";
						}
						$html.="<iframe src='{$pane['src']}' class='iframetab' frameborder=0></iframe>";
						$html.="</div>";
					}
					$nav.="</ul>";
					echo $nav;
					echo $html;
				} else {
					echo "<ul></ul>";
				}
			} else {
				echo "<ul></ul>";
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
var $tabs=null;
var sitepath="<?=generatePageRequest("","")?>";
var tabTemplate="<li><a class='tabref' href='#{refid}' rel='#{href}'>#{label}</a> <span class='ui-icon ui-icon-close ui-icon-closethick'>Remove Tab</span></li>";
function resizePageUI() {
	w=getWindowSize();
	width=w.w;
	height=w.h;
	if($("#pgworkspace").parent().width()!=null) width=$("#pgworkspace").parent().width();
	if($("#pgworkspace").parent().height()!=null) height=$("#pgworkspace").parent().height();
	
	$("#pgworkspace").css("height",(height-$("#toolbar").height()-4)+"px");
	$("#pgworkspace").css("width",(width-0)+"px");
	
	$("#pgworkspace>div").css("height",($("#pgworkspace").height()-$("#pgworkspace>ul.ui-tabs-nav").height()-4)+"px");
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
	
	$tabs = $("#pgworkspace").tabs({
			ajaxOptions:{
				beforeSend:function() {
					return false;
				},
			},
			spinner: 'Loading ...',
			crossDomain:false,
			cache:true,
			//show: { effect: "slideUp", duration: 800 },
			//hide: { effect: "slideDown", duration: 800 }
		});
	$("#pgworkspace>ul.ui-tabs-nav").append("<a class='workspaceclose' onclick='closeCurrentTab()'>&nbsp;X</a>");

	$("#pgworkspace").delegate("span.ui-icon-close,span.ui-icon-closethick","click",function() {
		var index = $( "li", $tabs ).index( $( this ).parent() );
		closeTab(index);
	});
});
function indexOfTab(url) {
	var links = $("#pgworkspace > ul").find("li a");
	for(i=0;i<links.length;i++) {
		//var lnk = $.data(links[i], 'load.tabs');
		var lnk = $(links[i]).attr("rel");
		if(lnk==url) return i;
	}
	return -1;
}
function closeTab(index) {
	if(!$($tabs.find(">div")[index]).hasClass("unclosable")) {
		//$tabs.tabs("remove",index);
		refid=$($tabs.find(">div")[index]).attr("id")
		$tabs.find( ".ui-tabs-nav>li[aria-controls='"+refid+"']" ).remove().attr( "aria-controls" );
		$( "#" + refid ).remove();
		$tabs.tabs( "refresh" );
		return true;
	} else {
		return false;
	}
	//if(index==0) return false;
	//$tabs.tabs( "remove",index);
}
function closeCurrentTab() {
	var n = $tabs.tabs('option', 'active');
	closeTab(n);
}
function openInNewTab(title, url, autoSwitch) {
	if(autoSwitch==null) autoSwitch=<?=getSettings("UI.TABBED.AUTOSWITCH","true")?>;

	if(indexOfTab(url)>=0) {
		//$tabs.tabs("select",indexOfTab(url));
		$tabs.tabs("option", "active",indexOfTab(url));
		return;
	}
	if(!(url.indexOf("http:")>=0) && !(url.indexOf("https:")>=0)) {
		if(!(url.indexOf("?")>=0)) {
			if(sitepath.indexOf("?")>=0) {
				url=sitepath+"&"+url;
			} else {
				url=sitepath+"?"+url;
			}
		} else {
			url=SiteLocation+url;
		}
	}

	var id = "tabs-" + title.replace(/ /g,"_"),
        li = $( tabTemplate.replace( /#\{href\}/g, url ).replace( /#\{label\}/g, title ).replace( /#\{refid\}/g, "#"+id ) ),
        tabContentHtml = "<div class='ajaxloading'>Loading ...</div>";

    $tabs.find(".ui-tabs-nav").append( li );
	$tabs.append( "<div id='" + id + "'>" + tabContentHtml + "</div>" );
	$tabs.tabs("refresh");

	$("#pgworkspace div.ui-tabs-panel").each(function() {
			if($(this).html().length<=0) $(this).detach();
		});
	if(autoSwitch) {
		loadTabFrame("#"+id,url);
		$tabs.tabs('option', 'active',($tabs.find(".ui-tabs-nav li").length-1));
	} else {
		$("a.tabref").click(function(event) {
			loadTabFrame($(this).attr("href"),$(this).attr("rel"));
		});
	}
}
function openInCurrentTab(url) {
	var n = $tabs.tabs('option', 'active');
	refid=$($tabs.find(">div")[n]).attr("id");
	$( "#" + refid ).find("iframe").attr("src",url);
}
function loadTabFrame(tab, url) {
	if ($(tab).find("iframe").length == 0) {
		var html = [];
		html.push('<iframe class="iframetab" src="' + url + '" width=100% height=100% frameborder=0 style="padding:0px;border:0px;">Load Failed?</iframe>');
		$(tab).html(html.join(""));
		//$(tab).find("iframe").height($("#dashboard").height());
		$(tab).find("iframe").height(($("#pgworkspace").height()-$("#pgworkspace>ul.ui-tabs-nav").height()-4)+"px");
	}
	return false;
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
