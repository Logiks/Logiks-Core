function formToHTML(id) {
	var txt="<table border=0 style='margin:auto;width:400px;border:2px solid #777;padding:10px;' align=left>";

	$(id + " caption").each(function(i, selected) {
			txt+="<caption>"+$(selected).html()+"</caption>";
		});

	$(id).find("label").each(function() {
		var ip=$(id + " [name="+$(this).attr("for")+"]");
		if(ip.length>0) {
			var type=ip.attr("type");
			if(type=="radio" || type=="checkbox") {
				s="";
				ip.each(function(i,selected) {
						if($(selected).attr("checked")) {
							s+=$(selected).val()+",";
						}
					});
				ip=s;
				//ip=$(id + " [name="+$(this).attr("for")+"]:checked").val();
			} else {
				ip=ip.val();
			}
		} else {
			ip="";
		}
		if(ip==null) {
			ip="";
		}

		txt+="<tr>";
		txt+="<td width=150px>"+$(this).html()+"</td>";
		txt+="<td id='"+$(this).attr("for")+"'>"+ip+"</td>";
		txt+="</tr>";
	});


	txt+="</table>";
	return txt;
}
function mailForm(id,path,to,subject,target) {
	$("form#mailfrm159").detach();
	if(to==null) {
		to=prompt("Form Mail TO ::");
	}
	if(subject==null) {
		subject="Form Mail Service";
	}
	if(target==null) {
		target="_blank";
	}

	var s="<div class=mailform style='margin:auto;margin-top:10px;' align=center>"+formToHTML(id)+"</div>";

	var form="<form id=mailfrm159 method=post target="+target+" action='"+path+"' style='display:none;'>";
	form+="<input name='mailto' type=text value='"+to+"'/>";
	form+="<input name='subject' type=text value='"+subject+"'/>";
	form+="<textarea name='body'>" + s + "</textarea>";
	form+="</form>";
	$(id).parent().append(form);
	ajaxMailSubmit("form#mailfrm159");
}

function ajaxMailSubmit(id) {
	var params = {};
	l=$(id)
	.find("input[type='text'], input[type='hidden'],textarea")//, input[checked], input[type='password'], input[type='submit'], option[selected],
	.filter(":enabled")
	.each(function() {
		params[ this.name || this.id || this.parentNode.name || this.parentNode.id ] = this.value;
	});
	$("body").addClass("curWait");

	$.post($(id).attr("action"), params, function(xml){
		/*$("body").removeClass("curWait");
		strError = "Unable to submit form. Please try again later.";
		oFocus = null;
		$("AjaxResponse", xml).each(function() {
			strRedirect = this.getAttribute("redirecturl");
			strError = this.getAttribute("error");
			oFocus = this.getAttribute("focus");
		});
		if (strError.length == 0) {
			window.location = strRedirect;
		} else {
			alert("The following errors were encountered:\n" + strError);
			$("div.formErrors").html("<h3>Error<\/h3><ul>" + strError.replace(/(\t)(.+)/g, "<li>$2<\/li>") + "<\/ul>").filter(":hidden").fadeIn("normal");
			if (oFocus) $("#" + oFocus).get(0).focus();
		}*/
		//alert(xml);
		$("form#mailfrm159").detach();
	});
	return false;
}
