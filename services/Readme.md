# Logiks Services


The Service Engine is the base for all the Logiks based REST communications and ajax calls.
It provides the basic architecture for all the remote command extecutions.
Logiks Service Engine (LSE) provides multiple developement language support including
    			php, py, perl, ruby, js (node) via engines

URL Structure : URL`/SCMD/ACTION/SLUGS?`QUERY


Service Handler For Logiks 4.0+

+ Output Formats : json, xml, table, list, select, raw, txt, css, js
+ Source Formats : php, py, perl, ruby, js (node) via engines

Special Extra Parameters

+	autoformat 		Use toTitle/UCWORDS or not 
+	debug 			Enable debug mode or not 
+	cache 			To use cache or not 
+	stype 			Type of command (py, php, etc.) 


Supported Remote Call Interface
+	AJAX
+	REST
-	SOAP		(In Plan)


Supported REST Commands

+ GET
+ POST

Upcoming Rest Commands

+ PUT
+ VIEW
+ PURGE
+ DELETE
+ COPY
+ PATCH
+ HEAD
+ OPTIONS
+ LINK
+ UNLINK
+ LOCK
+ UNLOCK
+ PROPFIND
