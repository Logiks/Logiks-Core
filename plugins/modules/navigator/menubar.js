/** 
 * Menubar menus
 * Invisible SubMenus for menu list
 * */
function loadMenubar(barID) {
	$(barID+" ul h2>a:not(.noauto)").dblclick(function(event) {
			event.preventDefault();
			var r=$(this).attr("href");
			if(r!=null && r.length>0 && r!="#") return openLink(this);
			else return false;
		});
	$(barID+" ul li>a:not(.noauto)").click(function(event) {
			event.preventDefault();
			var r=$(this).attr("href");
			if(r!=null && r.length>0 && r!="#") return openLink(this);
			else return false;
		});
	$(barID+">ul>li").each(function() {
			if($(this).find("ul").length>0) {
				$(this).addClass("dropdown");
				$(this).removeClass('nosubmenu');
			} else {
				$(this).removeClass("dropdown");
				$(this).addClass('nosubmenu');
			}
		});
	$(barID+" li.dropdown>ul li>ul").each(function() {
			$(this).parent("li").addClass("dropdown");
		});
}
