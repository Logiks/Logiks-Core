{$PAGE.DOCTYPE}

<html {$PAGE.HTML_ATTRIBUTES}>
  <head {$PAGE.HEAD_ATTRIBUTES}>
    <title>{$APPS_NAME}</title>
    {$PAGE.FAVICON}
    
    <!-- start: META -->
    <meta name='description' content='{$PAGE.DESCRIPTION}' />
	<meta name='keywords' content='{$PAGE.KEYWORDS}' />
	<meta name='robots' content='{$PAGE.ROBOTS}' />
	
	{pageseometa}
	<!-- end: META -->
	
	{$PAGE.VIEWPORT}
	{$PAGE.CSS}
	{$PAGE.JS_PRELOAD}
	
	{logikscripts}
	
	{hook src='postHTMLHead'}
  </head>
  <body {$PAGE.BODY_CLASS} {$PAGE.BODY_ATTRIBUTES} >
  	{viewpage}
  </body>
</html>
{hook src='postHTML'}
