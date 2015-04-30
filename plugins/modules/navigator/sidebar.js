/** 
 * Sidebar menus
 * Slidetoggle for menu list
 * */
var currentMenu = null; 
function loadSidebar(barID) {
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
	$(barID+'>ul>li').each(function(){
		if($(this).find('li').length == 0){
			$(this).addClass('nosubmenu');
		} else {
			$(this).addClass('dropdown');
		}
	})
	$(barID+'>ul>li[class!="nosubmenu"]>h2').each(function(){
		if(!$(this).parent().hasClass('current')){
			$(this).parent().find('ul:first').hide();
		}else{
			currentMenu = $(this); 
		}
		$(this).click(function(event) {
			$(barID+'>ul>li.current').removeClass('current'); 
			if(currentMenu != null && currentMenu.text() != $(this).text()){
				currentMenu.parent().find('ul:first').slideUp(); 
			}
			if(currentMenu != null && currentMenu.text() == $(this).text()){
				currentMenu.parent().find('ul:first').slideUp(); 
				currentMenu = null;
			}else{
				currentMenu = $(this);
				currentMenu.parent().addClass('current'); 
				currentMenu.parent().find('ul:first').slideDown(); 
			}
			return false;
		});
	});
}
