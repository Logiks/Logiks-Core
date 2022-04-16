/*
* Event Trigger System in JQuery
*
* @version v1.0 (04/2022)
*
* Copyright 2011, LGPL License
*
* Homepage:
*   http://openlogiks.org/
*
* Authors:
*   Bismay Kumar Mohapatra
*/

const logiksTriggers={
    eventList: {
      'onapppreload':[], 
      'onappload':[],
      'onfirstpage':[], 
      'onpageload':[],
      'onpagepreload':[],
      'ondeviceready':[],
      'ononline':[],
      'onofline':[],
      'ondevicemotion':[],
      'ondeviceorientation':[],
      'ondeviceorientationabsolute':[],
      'onunload':[],

      'onOnline':[],
      'onOffline':[],
      },
    initialize: function() {

    },
    addTrigger: function(eventName,func) {
        if(eventName==null || eventName.length<=0) return false;
        eventName=eventName.toLowerCase();

        if(_TRIGGERS.eventList[eventName]==null) {
            _TRIGGERS.eventList[eventName]=[];
        }
        _TRIGGERS.eventList[eventName].push(func);
    },
    runTriggers: function(eventName,params) {
        if(eventName==null || eventName.length<=0) return false;
        eventName=eventName.toLowerCase();

        if(_TRIGGERS.eventList[eventName]==null) {
            _TRIGGERS.eventList[eventName]=[];
        }

        $.each(_TRIGGERS.eventList[eventName],function(k,v) {
            if(typeof window[v]=="function") {
                window[v](params);
            } else if(typeof v=="function") {
                v(params);
            }
        });
    }
}
//logiksTriggers.init();
