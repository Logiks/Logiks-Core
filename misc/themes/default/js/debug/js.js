$(function() {
	$("body").append("<style>.debugElement {border:2px solid red;cursor:arrow;}</style>");	
});
function initDebug(divs) {
	$(divs).hover(function() {$(this).addClass("debugElement");},function() {$(this).removeClass("debugElement");});
}
function cleanDebug() {
	$("*").unbind('hover');
}
initDebug("img");
