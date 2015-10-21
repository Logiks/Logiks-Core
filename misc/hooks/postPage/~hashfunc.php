<?php if(!isset($GLOBALS['LOGIKS']["_SERVER"]["HTTP_REFERER"]) || strlen($GLOBALS['LOGIKS']["_SERVER"]["HTTP_REFERER"])<=0) { ?>	
<script language='javascript'>
$(function() {
	if(location.hash.slice(1).length>0) {
		hs=location.hash.slice(1);
		hso=hs.split("=");
		
		if(hso[0]=="overlay") {
			loadOverlay("#"+hso[1]);
		}
	}
});
function loadOverlay(g) {
	if($(g).length>0) {
		ele=$(g).prop('tagName');
		if(ele=="DIV") {
			jqPopupDiv(g, null, true,$(window).width()-50,$(window).height()-50,"fade");
		} else if(ele=="A") {
			r=$(g).attr("href");
			t=$(g).html();
			jqPopupURL(r,t, null, true,$(window).width()-50,$(window).height()-50,"fade");
		}
	}
}
</script>
<?php } ?>