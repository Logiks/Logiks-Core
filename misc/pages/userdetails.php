<?php
if (!defined('ROOT')) exit('No direct script access allowed');

if(!isset($_SESSION["USER_DETAILS_MODE"])) {
	$_SESSION["USER_DETAILS_MODE"]="view";
}
if((!isset($_SESSION["USER_DETAILS_ID"])) && !(isset($_SESSION["USER_DETAILS_USERID"]) && isset($_SESSION["USER_DETAILS_SITE"]))) {
	exit("Wrong User Informations");
}
$mode=strtolower($_SESSION["USER_DETAILS_MODE"]);
$site=$_SESSION["USER_DETAILS_SITE"];
$userid=$_SESSION["USER_DETAILS_USERID"];
$rid=$_SESSION["USER_DETAILS_ID"];

unset($_SESSION["USER_DETAILS_MODE"]);
unset($_SESSION["USER_DETAILS_SITE"]);
unset($_SESSION["USER_DETAILS_USERID"]);
unset($_SESSION["USER_DETAILS_ID"]);

include_once ROOT.API_FOLDER."helpers/countries.php";

$frmHash=_randomid("USER_DETAILS_");
$_SESSION[$frmHash]=$mode;

$usrRecordFormat="<tr align=left valign=top><th width=150px>%s</th><td width=25px align=center><b>::</b></td><td>%s</td><td width=65px>%s</td><td width=100px align=right>%s</td></tr>";
$arrUser=array();

$userCols="id,privilege,access,name,email,mobile,address,region,country,zipcode,blocked,expires,remarks,notes,privacy,avatar,q1,a1";
$userDetails=explode(",",$userCols);
$userDetails=array_flip($userDetails);

$userDetails["id"]=$rid;
$userDetails["userid"]=$userid;
$userDetails["site"]=$site;

$userDetails["access_name"]="";
$userDetails["privilege_name"]="";

$sql="SELECT $userCols FROM "._dbtable("users",true)." WHERE id=$rid AND userid='$userid' AND site='$site' LIMIT 0,1";

if($mode!="create" || $mode!="totedit" || $mode!="edit") {
	$sql="";
	$tbl1=_dbtable("users",true);
	$tbl2=_dbtable("access",true);
	$tbl3=_dbtable("privileges",true);
	foreach($userDetails as $a=>$b) {
		if($a=="access_name") $sql.="{$tbl2}.master as access_name,";
		elseif($a=="privilege_name") $sql.="{$tbl3}.name as privilege_name,";
		else $sql.="{$tbl1}.{$a},";
	}
	$sql=trim($sql);
	$sql=substr($sql,0,strlen($sql)-1);
	$sql="SELECT {$sql} FROM {$tbl1},{$tbl2},{$tbl3} WHERE {$tbl1}.access={$tbl2}.id AND {$tbl1}.privilege={$tbl3}.id AND ";
	$sql.="{$tbl1}.id=$rid AND {$tbl1}.userid='$userid' AND {$tbl1}.site='$site' LIMIT 0,1";
}
//echo $sql;
$r=_dbQuery($sql,true);
if($r) {
	$data=_dbData($r);
	if(isset($data[0])) {
		$data=$data[0];
	}
	foreach($userDetails as $a=>$b) {
		if(isset($data[$a])) {
			$userDetails[$a]=$data[$a];
		} else {
			$userDetails[$a]="";
		}
	}
} else {
	foreach($userDetails as $a=>$b) {
		$userDetails[$a]="<span style='color:maroon'>XXX</span>";
	}
}
//printArray($userDetails);exit();
_db(true)->freeResult($r);
$allowButtonBar=true;

/*
if(defined("ADMIN_USERIDS"))
	$acp=explode(",",ADMIN_USERIDS);
	if(in_array($userid,$acp)) {
		$mode="view";
	}
}*/

if($mode=="create") {
	$arrUser[sizeOf($arrUser)]=array("UserID","<input type=text name='userid' id='useridfield' class='required' value=''
					onfocus=\"$(this).parents('tr').find('td.checkcol div').attr('class','info_icon');\"
					onblur='checkUniqueUserID(this);' /><div id=suggestionBox></div>",
					"<div class='checkcol info_icon' onclick='checkUniqueUserID()'></div>","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("---");
	$arrUser[sizeOf($arrUser)]=array("For Site","<select name=s id=forsite  class='required allsites ui-state-active ui-corner-all'
					onchange='updatePrivilegeList($(this).val());updateAccessList($(this).val());' ></select>","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Privilege","<select name=privilege class='required ui-state-active ui-corner-all'></select>","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Access Point","<select name=access class='required ui-state-active ui-corner-all'></select>","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("---");
	$arrUser[sizeOf($arrUser)]=array("User Name","<input type=text name='name' class='required' />","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Email","<input type=text name='email' class='emailfield required' />","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Mobile","<input type=text name='mobile' class='mobilefield' />","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Address","<input type=text name='address' />","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Region","<input type=text name='region' />","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Country","X","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("ZipCode","<input type=text name='zipcode' />","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("---");
	$arrUser[sizeOf($arrUser)]=array("Expires On","<input type=text name='expires' class='datefield' readonly />","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Remarks","<textarea name=remarks></textarea>","&nbsp;","&nbsp;");

	$arrUser[11][1]=sprintf("<select name='country' class='allsites ui-state-active ui-corner-all'>%s</select>",getCountrySelector(getConfig("DEFAULT_COUNTRY")));
} elseif($mode=="totedit") {
	$arrUser[sizeOf($arrUser)]=array("UserID","<input type=text name='userid' id='useridfield' class='required' value='{$userid}' />",
					"<div class='checkcol ok_icon'></div>","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("---");
	$arrUser[sizeOf($arrUser)]=array("For Site","<select name=s id=forsite  class='required allsites ui-state-active ui-corner-all'
					onchange='updatePrivilegeList($(this).val());updateAccessList($(this).val());' ></select>","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Privilege","<select name=privilege class='required ui-state-active ui-corner-all' v='{$userDetails['privilege']}' ></select>","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Access Point","<select name=access class='required ui-state-active ui-corner-all' v='{$userDetails['access']}' ></select>","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("---");
	$arrUser[sizeOf($arrUser)]=array("User Name","<input type=text name='name' class='required' value='{$userDetails['name']}' />","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Email","<input type=text name='email' class='emailfield' value='{$userDetails['email']}' />","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Mobile","<input type=text name='mobile' class='mobilefield' value='{$userDetails['mobile']}' />","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Address","<input type=text name='address' value='{$userDetails['address']}' />","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Region","<input type=text name='region' value='{$userDetails['region']}' />","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Country","","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("ZipCode","<input type=text name='zipcode' value='{$userDetails['zipcode']}' />","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("---");
	$arrUser[sizeOf($arrUser)]=array("Expires On","<input type=text name='expires' class='datefield'  value='{$userDetails['expires']}' readonly />","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Remarks","<textarea name=remarks>{$userDetails['remarks']}</textarea>","&nbsp;","&nbsp;");

	$arrUser[11][1]=sprintf("<select name='country' class='allsites ui-state-active ui-corner-all'>%s</select>",getCountrySelector($userDetails['country']));
} elseif($mode=="edit") {
	$arrUser[sizeOf($arrUser)]=array("UserID","<input type=text name='userid' value='{$userid}' readonly />","<div class='ok_icon'></div>","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("---");
	$arrUser[sizeOf($arrUser)]=array("For Site","<select name=s id=forsite  class='required allsites ui-state-active ui-corner-all'
					onchange='updatePrivilegeList($(this).val());updateAccessList($(this).val());' ></select>","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Privilege","<select name=privilege class='required ui-state-active ui-corner-all' v='{$userDetails['privilege']}' ></select>","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Access Point","<select name=access class='required ui-state-active ui-corner-all' v='{$userDetails['access']}' ></select>","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("---");
	$arrUser[sizeOf($arrUser)]=array("User Name","<input type=text name='name' class='required' value='{$userDetails['name']}' />","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Email","<input type=text name='email' class='emailfield' value='{$userDetails['email']}' />","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Mobile","<input type=text name='mobile' class='mobilefield' value='{$userDetails['mobile']}' />","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Address","<input type=text name='address' value='{$userDetails['address']}' />","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Region","<input type=text name='region' value='{$userDetails['region']}' />","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Country","","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("ZipCode","<input type=text name='zipcode' value='{$userDetails['zipcode']}' />","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("---");
	$arrUser[sizeOf($arrUser)]=array("Expires On","<input type=text name='expires' class='datefield'  value='{$userDetails['expires']}' readonly />","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Remarks","<textarea name=remarks>{$userDetails['remarks']}</textarea>","&nbsp;","&nbsp;");

	$arrUser[11][1]=sprintf("<select name='country' class='allsites ui-state-active ui-corner-all'>%s</select>",getCountrySelector($userDetails['country']));
} elseif($mode=="infoedit") {
	$arrUser[sizeOf($arrUser)]=array("UserID","<input type=text name='userid' value='{$userid}' readonly />","<div class='ok_icon'></div>","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("---");

	$arrUser[sizeOf($arrUser)]=array("For Site","<div class='txtfield'>{$site}</div>","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Privilege","<div class='txtfield'>{$userDetails['privilege_name']}</div>","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Access Point","<div class='txtfield'>{$userDetails['access_name']}</div>","&nbsp;","&nbsp;");

	$arrUser[sizeOf($arrUser)]=array("---");
	$arrUser[sizeOf($arrUser)]=array("User Name","<input type=text name='name' class='required' value='{$userDetails['name']}' />","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Email","<input type=text name='email' class='emailfield' value='{$userDetails['email']}' />","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Mobile","<input type=text name='mobile' class='mobilefield' value='{$userDetails['mobile']}' />","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Address","<input type=text name='address' value='{$userDetails['address']}' />","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Region","<input type=text name='region' value='{$userDetails['region']}' />","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Country","","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("ZipCode","<input type=text name='zipcode' value='{$userDetails['zipcode']}' />","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("---");
	$arrUser[sizeOf($arrUser)]=array("Expires On","<input type=text name='expires' class='datefield'  value='{$userDetails['expires']}' readonly />","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Remarks","<textarea name=remarks>{$userDetails['remarks']}</textarea>","&nbsp;","&nbsp;");

	$arrUser[11][1]=sprintf("<select name='country' class='allsites ui-state-active ui-corner-all'>%s</select>",getCountrySelector($userDetails['country']));
} elseif($mode=="view") {
	$usrRecordFormat="<tr class='nohover' align=left valign=top><th width=150px>%s</th><td width=25px align=center><b>::</b></td><td>%s</td><td width=100px align=right>%s</td></tr>";

	$arrUser[sizeOf($arrUser)]=array("User Name","<div class='txtfield'>{$userDetails['name']}</div>","<div class='info_icon'></div>","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("UserID","<div class='txtfield'>{$userid}</div>","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("---");
	$arrUser[sizeOf($arrUser)]=array("For Site","<div class='txtfield'>{$site}</div>","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Privilege","<div class='txtfield'>{$userDetails['privilege_name']}</div>","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Access Point","<div class='txtfield'>{$userDetails['access_name']}</div>","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("---");
	$arrUser[sizeOf($arrUser)]=array("Email","<div class='txtfield'>{$userDetails['email']}</div>","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Mobile","<div class='txtfield'>{$userDetails['mobile']}</div>","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Address","<div class='txtarea ui-corner-all'>{$userDetails['address']}</div>","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Region","<div class='txtfield'>{$userDetails['region']}</div>","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Country","<div class='txtfield'>{$userDetails['country']}</div>","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("ZipCode","<div class='txtfield'>{$userDetails['zipcode']}</div>","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Expires On","<div class='txtfield'>{$userDetails['expires']}</div>","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Remarks","<div class='txtarea ui-corner-all'>{$userDetails['remarks']}</div>","&nbsp;","&nbsp;");

	$allowButtonBar=false;
} else {
	$usrRecordFormat="<tr class='nohover' align=left valign=top><th width=150px>%s</th><td width=25px align=center><b>::</b></td><td>%s</td><td width=65px>%s</td><td width=100px align=right>%s</td></tr>";

	$arrUser[sizeOf($arrUser)]=array("UserID","<div class='txtfield'>{$userid}</div>","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("User Name","<div class='txtfield'>{$userDetails['name']}</div>","&nbsp;","<div class='info_icon'></div>");
	$arrUser[sizeOf($arrUser)]=array("For Site","<div class='txtfield'>{$site}</div>","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("---");
	$arrUser[sizeOf($arrUser)]=array("Email","<div class='txtfield'>{$userDetails['email']}</div>","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Mobile","<div class='txtfield'>{$userDetails['mobile']}</div>","&nbsp;","&nbsp;");
	$arrUser[sizeOf($arrUser)]=array("Country","<div class='txtfield'>{$userDetails['country']}</div>","&nbsp;","&nbsp;");

	$allowButtonBar=false;
}
?>
<style>
#user_editor,#user_editor td {
	border:0px;
}
#user_editor tr th {
	text-align:right;
}
#user_editor tbody tr:not(.nohover):hover td, #user_editor tbody tr:not(.nohover):hover th {
	background-color:#D3FFA3;
	cursor:pointer;
}
#user_editor .txtfield {width:100%;height:20px;border-bottom:1px solid #cecece;font-size:13px;padding:3px;padding-left:5px;}
#user_editor .txtarea {width:100%;height:50px;border:1px solid #aaa;overflow:auto;font-size:15px;padding:3px;padding-left:5px;}

#user_editor input[type=text], #user_editor textarea, {height:23px;font-weight:bold;border:1px solid #aaa;}
#user_editor textarea {width:97%;height:50px;resize:none;border:1px solid #aaa;}

#user_editor select {
	width:100%;height:22px;border:1px solid #aaa;
}

#user_editor .checkcol button {
	height:23px;
}
#user_editor .checkcol .ui-button-text {
	padding:4px;margin:2px;
}
#user_editor #suggestionBox {width:95%;margin-top:4px;padding:4px;}

#user_editor .ok_icon {
	background:url(media/images/accept.png) no-repeat center center;
	width:48px;height:48px;
}
#user_editor .error_icon {
	background:url(media/images/delete.png) no-repeat center center;
	width:48px;height:48px;
}
#user_editor .info_icon {
	background:url(media/images/info.png) no-repeat center center;
	width:48px;height:48px;
}
#user_editor .emailfield {
	background:#fff url(misc/themes/default/images/forms/email.png) no-repeat right center;
}
#user_editor .mobilefield {
	background:#fff url(misc/themes/default/images/forms/mobile.png) no-repeat right center;
}
#user_editor .required_field {
	background:#fff url(misc/themes/default/images/forms/notify/required.png) no-repeat right center;
}
</style>
<form>
<table id=user_editor width=99% cellpadding=3 cellspacing=0 border=0>
	<thead>
		<input type=hidden name='id' value="<?=$rid?>" />
		<input type=hidden name='modeCmd' value="<?=$frmHash?>" />
	</thead>
	<tbody>
		<?php
			foreach($arrUser as $user) {
				if($user[0]=="|" || $user[0]=="---") {
					echo "<tr class='nohover'><td colspan=10><hr/></td></tr>";
				} else {
					echo sprintf($usrRecordFormat,$user[0],$user[1],$user[2],$user[3]);
				}
			}
		?>
	</tbody>
	<tfoot>
	<?php if($allowButtonBar) { ?>
		<tr><td colspan=10 align=center>
			<hr/>
			<button type=button onclick='resetForm()'>Reset</button>
			<button type=button onclick="saveUserForm('#user_editor');">Save</button>
		</td></tr>
	<?php } else { ?>
		<tr><td colspan=10 align=center>&nbsp;</td></tr>
	<?php } ?>
	</tfoot>
</table>
</form>
<script language=javascript>
frmMode="<?=$mode?>";
$(function() {
	$(".datefield").datepicker({
			dateFormat:'yy-mm-d'
		});
	$("#user_editor .required").parents("tr").find("td:last-child").attr("class","required_field");
	$("button").button();
	$("#user_editor select#forsite").load("services/?scmd=userinfo&action=sitelist&format=select",function() {
			$("#user_editor select#forsite").val("<?=$site?>");
			if($("#user_editor select#forsite").val().length>0) {
				updatePrivilegeList($("#user_editor select#forsite").val());
				updateAccessList($("#user_editor select#forsite").val());
			}
		});
});
function resetForm() {
	$("#user_editor").find("input,select,textarea").each(function() {
			val=$(this).val();
			valO=$(this).attr("value");
			if($(this).attr('v')!=null && $(this).attr('v').length>0) {
				valO=$(this).attr('v');
			}
			$(this).val(valO);
		});
}
function updatePrivilegeList(site) {console.log(this);
	$("#user_editor select[name=privilege]").html("<option>Loading ...</option>");
	$("#user_editor select[name=privilege]").load(getServiceCMD("qtools")+"&action=privilegelist&format=select&forsite="+site,function() {
			if($(this).attr('v')!=null && $(this).attr('v').length>0) {
				$(this).val($(this).attr('v'));
			}
		});
}
function updateAccessList(site) {
	$("#user_editor select[name=access]").html("<option>Loading ...</option>");
	$("#user_editor select[name=access]").load(getServiceCMD("qtools")+"&action=accesslist&format=select&forsite="+site,function() {
			if($(this).attr('v')!=null && $(this).attr('v').length>0) {
				$(this).val($(this).attr('v'));
			}
		});
}
function checkUniqueUserID(txt) {
	if(txt==null) txt=document.getElementById("useridfield");
	tr=$(txt).parents('tr');
	if($(txt).val().length>0) {
		l="services/?scmd=formaction&action=unique";
		q="&tbl=<?=_dbtable("users",true)?>&col=userid&term="+$(txt).val();
		processAJAXPostQuery(l,q,function(txt) {
				if(txt=="unique") {
					tr.find('td div.checkcol').attr('class','checkcol ok_icon');
				} else if(txt=="not unique") {
					tr.find('td div.checkcol').attr('class','checkcol error_icon');
				} else {
					tr.find('td div.checkcol').attr('class','checkcol error_icon');
				}
			});
		//tr.find('td.checkcol div').attr('class','ok_icon');
	} else {
		tr.find('td div.checkcol').attr('class','checkcol error_icon');
	}
}
</script>
