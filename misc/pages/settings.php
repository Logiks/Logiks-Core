<?php
if(!defined('ROOT')) exit('No direct script access allowed');
session_check();

_css(array("formfields","settings"));
$etype="user";

if(isset($_REQUEST["type"])) $etype=$_REQUEST["stype"];

$userid=$_SESSION["SESS_USER_ID"];
$site=SITENAME;

$sql="";
if($etype=="site") {
	$sql="SELECT id,site,scope,name,value,type,edit_params FROM "._dbtable("config_sites",true)." WHERE site='$site' and scope<>'system' order by id";
} else {
	$sql="SELECT id,site,scope,name,value,type,edit_params FROM "._dbtable("config_users",true)." WHERE userid='$userid' and site='$site' and scope<>'system' order by id";
}
$res=_dbQuery($sql,true);
$arrConfigs=array();
if($res) {
	while($data=_db()->fetchData($res)) {
		$n=sizeOf($arrConfigs);
		$arrConfigs[$n]=array();
		$arrConfigs[$n]["title"]=$data["name"];
		$arrConfigs[$n]["value"]=$data["value"];
		$arrConfigs[$n]["scope"]=$data["scope"];
		$arrConfigs[$n]["type"]=$data["type"];
		$arrConfigs[$n]["edit_params"]=$data["edit_params"];
	}
} else {
	exit("Permission Denied ?");
}
?>
<Style>
#configurations {
	margin-top:0px;
	min-width:500px;
}
#toolbar {
	border:0px;
}
input,textarea {
	width:91% !important;
	text-align:left;
}
input[type=number],.numberfield,.datefield {
	text-align:center;
	font-weight:bold;
	width:150px !important;
}
select[multiple] {
	height:80px !important;
}
</Style>
<div class='ui-state-active' style='float:right;width:200px;margin:0px;margin-top:10px;margin-right:20px;color:#102F69;font:Georgia;text-align:Center;'>
<h2 style=''>All My Settings</h2>
</div>
<div id=configurations>
	<ul>
		<?php
			//supports :: string, int, AutoDataSelector::Types
			foreach($arrConfigs as $cfg) {
				$id=$cfg["title"];
				$title=str_replace("_"," ",$cfg["title"]);
				$title=ucwords($title);
				$value=$cfg["value"];
				$scope=$cfg["scope"];
				$type=strtolower($cfg["type"]);
				$editParams=$cfg["edit_params"];
				echo "<li class=title>$title</li>";
				$ads=new AutoDataSelector();
				if(strlen($editParams)>0) {
					$atr=array();
					if(strpos($type,",")>1) $atr=explode(",",$type);
					else $atr=array($type,"");
					
					$type=$atr[0];
					$multi=$atr[1]=="multi"?true:false;
					
					if($type=="string") {
						echo "<li class=input><input id=$id value='$value' scope='$scope' type=text class='textfield' onblur='setConfig(this)' /></li>";
					} 
					elseif(in_array($type, $ads->supportedTypes())) {
						if($multi) {
							echo "<li class=input><select id='$id' value='$value' scope='$scope' multiple='multiple' onchange='setConfig(this)'>";
						} else {
							echo "<li class=input><select id='$id' value='$value' scope='$scope' onchange='setConfig(this)'>";
						}
						echo $ads->printDataSelector($type, $editParams, $value);
						echo "</select></li>";
					}
					else {
						echo "<li class=input><span style='color:maroon;'>Type Format Not Supported Yet</span></li>";
					}
				} else {
					if($type=="int") {
						echo "<li class=input><input id=$id value='$value' scope='$scope' type=number class='numberfield' onblur='setConfig(this)' /></li>";
					} elseif($type=="date") {
						echo "<li class=input><input id=$id value='$value' scope='$scope' type=text class='datefield' readonly onchange='setConfig(this)' /></li>";
					} elseif($type=="email") {
						echo "<li class=input><input id=$id value='$value' scope='$scope' type=text class='emailfield' onblur='setConfig(this)' /></li>";
					} elseif($type=="phone") {
						echo "<li class=input><input id=$id value='$value' scope='$scope' type=text class='phonefield' onblur='setConfig(this)' /></li>";
					} elseif($type=="url") {
						echo "<li class=input><input id=$id value='$value' scope='$scope' type=text class='urlfield' onblur='setConfig(this)' /></li>";
					} else {
						echo "<li class=input><input id=$id value='$value' scope='$scope' type=text class='textfield' onblur='setConfig(this)' /></li>";
					}
				}
			}
		?>
	</ul>
</div>

<div id=settings align=right style='display:none;'>
	<table class='ui-widget-content ui-corner-all' width=100%>
		<caption>Sidebar Kit</caption>
		<tr>
			<td align=left>Show Sidebar</td>
			<td width=30px align=left><input name=title id=title type=checkbox checked=checked/></td>
		</tr>
		<tr><td colspan=10><hr/></td></tr>
		<tr>
			<td align=left>Compose Message</td>
			<td align=left><input name=title id=title type=checkbox checked=checked /></td>
		</tr>
		<tr>
			<td align=left>Send SMS</td>
			<td align=left><input name=sms id=title type=checkbox checked=checked /></td>
		</tr>
		<tr><td colspan=10><hr/></td></tr>
		<tr><td colspan=10 align=center>
			<button>Save</button>
		</td></tr>
	</table>
</div>

<script language='javascript'>
etype="<?=$etype?>";
$(function() {
	$("#toolbar button").button();
	$("select").addClass("ui-widget-header");
	$(".datefield").datepicker({
			changeMonth: true,
			changeYear: true,
			showButtonPanel: false,
			yearRange:"<?=date("Y")-getConfig("DATE_YEAR_RANGE")?>:<?=date("Y")+getConfig("DATE_YEAR_RANGE")?>",
			dateFormat:"yy-m-d",//"<?=getConfig("DATE_FORMAT")?>",
		});
});
function setConfig(ele) {
	url="services/?scmd=settings&site=<?=SITENAME?>&mod=save&name="+$(ele).attr("id")+"&value="+$(ele).val()+"&scope="+$(ele).attr("scope")+"&etype="+etype;
	$(ele).parent("li").append("<div class=ajaxloading4></div>");
	processAJAXQuery(url,function(txt) {
			$(ele).parents("li").find("div.ajaxloading4").detach();
		});
}
</script>
