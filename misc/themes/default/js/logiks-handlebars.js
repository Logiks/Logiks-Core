/*
* jQuery Based Element Event Binding System. 
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
*
* Maintainer:
*   Bismay Kumar Mohapatra
*
* Dependencies:
*   jQuery v1.4+
*   jQuery UI v1.8+
*/

const logiksHandler = {
	init:function() {
		if(typeof Handlebars=="object") {
			Handlebars.registerHelper('link', function(text, url) {
			  text = Handlebars.Utils.escapeExpression(text);
			  url  = Handlebars.Utils.escapeExpression(url);

			  var result = '<a href="' + url + '">' + text + '</a>';

			  return new Handlebars.SafeString(result);
			});

			Handlebars.registerHelper('ling', function(text) {
			  if(text==null) return "";
			  if(typeof _ling!="function") return text;
			  return _ling(text);
			});

			Handlebars.registerHelper('if_eq', function(a, b, opts) {
		        if (a == b) {
		            return opts.fn(this);
		        } else {
		            return opts.inverse(this);
		        }
		    });
		    
			Handlebars.registerHelper('formatDatetime', function(date, format) {
		        if (date == null || date <= 0) return "";
		        if(format==null) format = 'D MMM y ,hh:mm A';
		        return moment(date).format(format);
		    });
		    Handlebars.registerHelper('formatDate', function(date, format) {
		        if (date == null || date == "0000-00-00" || date == "0000-00-00 00:00:00") return "";
		        if(format==null) format = 'D MMM y';
		        return moment(date).format(format);
		    });

		    Handlebars.registerHelper('uppercase', function(str) {
		        if (str == null || str <= 0) return "";
		        return str.toUpperCase();
		    });
		    Handlebars.registerHelper('lowercase', function(str) {
		        if (str == null || str <= 0) return "";
		        return str.toLowerCase();
		    });
		    Handlebars.registerHelper('toTitle', function(str) {
		        if (str == null || str <= 0) return "";
		        return str[0].toUpperCase()+str.substr(1).toLowerCase();
		    });

		    Handlebars.registerHelper('shortStr', function(str, len) {
		        if (str == null || str <= 0) return "";
		        if(str.length<=len) {
		            return str;
		        }
		        return str.substring(str.length-len);
		    });

		    Handlebars.registerHelper('currency', function(amount, decimals) {
		        if (amount == null) return "0.00";
		        if(decimals==null) decimals = 2;
		        amount = parseFloat(amount);
		        return amount.toFixed(decimals);
		    });
		    
		    Handlebars.registerHelper('formateBoolean', function(status) {
		        if (status == null || status <= 0) return "";

		        if (status.toLowerCase() == "true") {
		            return `Yes`;
		        } else {
		            return `No`;
		        }
		    });
		}
	}
}
//logiksHandler.init()
