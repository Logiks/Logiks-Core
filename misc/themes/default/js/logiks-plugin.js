/*
* jQuery Sub Plugin System. This system implements a queue mechanisim to iniate the 
* loaders of the registered plugins systematically after the complete window load and
* bootstrap functions loaded manually.
*
* @version v1.0 (03/2012)
* @version v2.0 (07/2015)
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

const logiksPlugin={
	pluginLoader:{},
	init:function() {
		$(function() {
			logiksPlugin.loadPlugins("onload");
		});
	},
	registerPluginLoader:function(func,pageState) {
		if(pageState==null) pageState="onload";
		if(logiksPlugin.pluginLoader[pageState]==null) logiksPlugin.pluginLoader[pageState]=[];
		logiksPlugin.pluginLoader[pageState].push(func);
	},
	loadPlugins:function(params,pageState) {
		if(pageState==null) pageState="onload";
		if(logiksPlugin.pluginLoader[pageState]==null) return false;
		$(logiksPlugin.pluginLoader[pageState]).each(function(k,func) {
				if(func==null) return;
				if(typeof(func)=='function') func(params);
				else window[func](params);
			});
	}
}
//logiksPlugin.init()
