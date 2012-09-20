/*
* jQuery Sub Plugin System. This system implements a queue mechanisim to iniate the 
* loaders of the registered plugins systematically after the complete window load and
* bootstrap functions loaded manually.
*
* @version v1.0 (03/2012)
*
* Copyright 2011, LGPL License
*
* Homepage:
*   http://openlogiks.org/
*
* Authors:
*   Bismay Kumar Mohapatra
*
* Maintainer:
*   Bismay Kumar Mohapatra
*
* Dependencies:
*   jQuery v1.4+
*   jQuery UI v1.8+
*/

var pluginLoader=[];

function registerPluginLoader(func) {
	pluginLoader.push(func);
}
function loadPlugins(id) {
	$(pluginLoader).each(function(k,func) {
			if(func==null) return;
			if(typeof(func)=='function') func(id);
			else window[func](id);
		});
}
