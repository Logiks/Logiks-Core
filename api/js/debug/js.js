$(function() {
	$("body").append("<style>.debugelement {border:2px solid red;cursor:arrow;}</style>");	
});
function initDebug(divs) {
	$(divs).hover(function() {$(this).addClass("debugelement");},function() {$(this).removeClass("debugelement");});
}
function cleanDebug() {
	$("*").unbind('hover');
}
initDebug("img");
