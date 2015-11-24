#cssLinks#
<div class='container error'>
	<h1><i class='icon-Error #level_class#'></i> #level_name# (#level#)</h1>
	<p class='logiksMsg'>OOOOPs, an unhandled error occurred. Please see the details below.</p>
	<div class='exception-name-block'>
		<div>#msg# [#code#]</div>
		<p>#file# <span>line</span> #line#</p>
	</div>
	<pre name='code' data-start='#source_startIndex#' data-highlight='#line#'>#source_snapshot#</pre>
	<h3><i class='icon-Code'></i> Stack trace</h3>
	<table class='data-table'>
			<thead>
					#stackTrace-Header#
			</thead>
			<tbody>
				#stackTrace-Table#
			</tbody>
	</table>
</div>
#jsLinks#
<script>
if(typeof prettyPrint=='function') {
	$('pre[name=code]').addClass('prettyprint linenums:'+$('pre[name=code]').data('start'));
	prettyPrint();
	$($('.prettyprint .linenums li')[$('pre[name=code]').data('highlight')-$('pre[name=code]').data('start')-1])
	  .addClass('current');
	$($('.prettyprint .linenums li')[$('pre[name=code]').data('highlight')-$('pre[name=code]').data('start')])
	  .addClass('current active');
	$($('.prettyprint .linenums li')[$('pre[name=code]').data('highlight')-$('pre[name=code]').data('start')+1])
	  .addClass('current');
} else if(typeof dp.SyntaxHighlighter=='object') {
	$('pre[name=code]').addClass('php');
	dp.SyntaxHighlighter.HighlightAll('code',true,true,false,$('pre').data('start'),false);
	$($('.dp-highlighter>ol>li')[$('pre').data('highlight')-$('pre').data('start')]).addClass('active current');
}
</script>
