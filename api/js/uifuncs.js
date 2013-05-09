//General UI Components
function showCalendar(ref) {
	NewCal(ref.id,'DDMMYYYY',false,24)
}

function showTimeCalendar(ref) {
	NewCal(ref.id,'DDMMYYYY',true,24)
}

function insertSimpleSearch(parentID) {
	insertSearch(document.getElementById(parentID),"search","Search");
}

function insertSearch(parent, searchFieldName,defaultText) {
	parent.innerHTML="<input id='" + searchFieldName +"' class='searchfield' name='" + searchFieldName +"' type='text' value='" + defaultText + "' maxlength='150' onfocus='this.value=(this.value=='" + defaultText +"') ? '' : this.value;' onblur='this.value=(this.value=='') ? '" + defaultText + "' : this.value;' >";
}

//Progress Bar Controlls
function insertProgressBar(parent,percentage) {
	if(parent.className=="progress")
		parent.innerHTML= "<div class='progressValue' style='width: " + percentage + "%;'><div class='progressText'>" + percentage + " %</div></div>";
	else
		parent.innerHTML= percentage + " %";
	return percentage;
}
function insertProgressBarControls(parent,increment) {
	if(parent.className=="progressBar") {
		var prg=null;
		for(var i=0;i<parent.children.length;i++) {
			if(parent.children[i].className=="progress") {
				prg=parent.children[i];
				break;
			}
		}
		if(prg==null) {
			return;
		}
		var newdiv = document.createElement("div");

		newdiv.setAttribute('class',"progressControls");
		newdiv.innerHTML = "<div class='progressButtonUp'><a onClick='javascript:setProgressBarValue(\"" + prg.id + "\",getProgressBarValue(\"" + prg.id + "\") + " + increment + ");'>&nbsp;&nbsp;&nbsp;</a></div>" +
		"<div class='progressButtonDown'><a onClick='javascript:setProgressBarValue(\"" + prg.id + "\",getProgressBarValue(\"" + prg.id + "\") - " + increment + ");'>&nbsp;&nbsp;&nbsp;</a></div>";
		parent.appendChild(newdiv);
	} else if(parent.className=="progress") {
		if(parent.parentNode.className=="progressBar") {
			var newdiv = document.createElement("div");
			newdiv.setAttribute('class',"progressControls");
			newdiv.innerHTML = "<div class='progressButtonUp'><a onClick='javascript:setProgressBarValue(\"" + parent.id + "\",getProgressBarValue(\"" + parent.id + "\") + " + increment + ");'>&nbsp;&nbsp;&nbsp;</a></div>" +
			"<div class='progressButtonDown'><a onClick='javascript:setProgressBarValue(\"" + parent.id + "\",getProgressBarValue(\"" + parent.id + "\") - " + increment + ");'>&nbsp;&nbsp;&nbsp;</a></div>";
			parent.parentNode.appendChild(newdiv);

		}
	}
}
function getProgressBarValue(progId) {
	var bar=document.getElementById(progId);
	if(bar.className=="progress") {
		if(bar.children.length==0) {
			return 0;
		} else {
			if(bar.children[0].children.length==0) {
				return 0;
			} else {
				var str="" + bar.children[0].children[0].innerHTML;
				str = str.replace("%","");
				str = str.trim();
				return parseInt(str);
			}
		}
	}
	return -1;
}

function setProgressBarValue(progId,value) {
	var bar=document.getElementById(progId);
	if(bar.className=="progress") {
		insertProgressBar(bar,value);
	}
}

function getEvent(e) {
	var keynum
	var keychar
	var numcheck

	if(window.event) { // IE
		keynum = e.keyCode
	} else if(e.which) { // Netscape/Firefox/Opera
		keynum = e.which
	}
	keychar = String.fromCharCode(keynum)
	//numcheck = /\d/
	//return !numcheck.test(keychar)
	return keychar;
}

function initUI(id) {
	if(id.length>0) id=id+" ";
	
	$(id+"input, textarea, select").uniform();//, button
	$(id+".datepicker").datepicker();
	$(id+".progressbar").progressbar({value:37});
	$(id+".slider").slider();
	$(id+"#draggable").draggable();
	$(id+".accordion").accordion({
			fillSpace: true
		});
	$(id+".tabs").tabs();
}
