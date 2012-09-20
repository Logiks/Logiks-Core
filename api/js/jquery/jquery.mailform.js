/**
 * MailForm Plugin
 * Copyright (c) 2012 Bismay Kumar Mohapatra (bismay4u@gmail.com)
 * Apache 2.0 Licensed
 */

jQuery.mailform = function(to, subject, body, submitURL) {
	height=$(window).height()-300;
	//width=$(document).width()-50;
	if(submitURL==null || submitURL.length<=0) {
		submitURL="services/?scmd=mail";
	}
	id='mailform_'+to;
	if((typeof lgksConfirm) == "function") {
		s="<div style='width:770px;' align=left title='Mail To :"+to+"'>";
		s+="<table id="+id+" width=100% border=0>";
		s+="<tr valign=top><td width=120px><b>Mail To</b></td><td><input name=mailto class='emailfield' type=text style='width:100%;border:1px solid #aaa;' value='"+to+"' /></td></tr>";
		s+="<tr valign=top><td width=120px><b>Subject</b></td><td><input name=subject type=text style='width:100%;border:1px solid #aaa;' value='"+subject+"'/></td></tr>";
		s+="<tr valign=top><td width=120px><b>Body</b></td>";
		s+="<td><div name=body class='ui-widget-content' style='background:white;width:660px;height:"+height+"px;border:1px solid #aaa;overflow:auto;' ";
		s+="onClick='this.contentEditable=true'>"+body+"<br/><br/>Thank You,</div></td></tr>";
		s+="</table><hr/>";
		s+="<div class='ui-state-highlight ui-corner-all' style='padding:4px;'><span class='ui-icon ui-icon-info' style='float: left; margin-right: .3em;'></span>After Successfully Sending Mail, A Reponse Box Will Appear.</div>";
		s+="</div>";
		
		return lgksConfirm(s,"Mail Form",function(){
				q="&mailto="+$("#"+id+" input[name=mailto]").val();
				q+="&subject="+$("#"+id+" input[name=subject]").val();
				q+="&body="+$("#"+id+" div[name=body]").html();
				jQuery.ajax({
						type:"POST",
						url:submitURL,
						data:q,
						error:function(html) {
							lgksAlert(html);
						},
						success: function(html) {
							lgksAlert(html);
						}
					});
			});
	} else {
		alert("jqPopup Not Found");
	}
};
