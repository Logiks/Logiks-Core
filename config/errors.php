<?php
$ERROR_ICON_LOCATION="media/images/errors/";

$error_pages=array(
					"default"=>PAGES_FOLDER . "errors/default.php",
					//"501"=>PAGES_FOLDER . "errors/underconstruction.php",
				);
$error_codes=array(
			"default"=>array("Unknown Error Code","Unknown Error Occured, Contact Your Admin For Furthur Informations."),
			"100"=>array("Continue",""),
			"101"=>array("Switching Protocols",""),
			"200"=>array("OK","Action completed successfully"),
			"201"=>array("Created","Success following a POST command"),
			"202"=>array("Accepted","The request has been accepted for processing, but the processing has not been completed."),
			"203"=>array("Partial Information","Response to a GET command, indicates that the returned meta information is from a private overlaid web."),
			"204"=>array("No Content","Server has received the request but there is no information to send back."),
			"205"=>array("Reset Content",""),
			"206"=>array("Partial Content","The requested file was partially sent"),
			"300"=>array("Multiple Choices",""),
			"301"=>array("Moved Permanently","Requested a directory instead of a specific file"),
			"302"=>array("Moved Temporarily",""),
			"303"=>array("See Other",""),
			"304"=>array("Not Modified","The cached version of the requested file is the same as the file to be sent."),
			"305"=>array("Use Proxy",""),
			"400"=>array("Bad Requested Made",""),
			"401"=>array("Unauthorized Access",""),
			"403"=>array("Requested Resource Is Forbidden","Access Forbidden To The Requested Page."),
			"403.1"=>array("Access Restricted","Access Forbidden To The Requested Page For Direct View."),
			"404"=>array("Requested Resource Not Found","<h3>Requested Resource From <i>".getConfig("APPS_NAME")."</i> Is Forbidden To Access</h3>Please contact the WebMaster for furthur details.<br/>"),
			"405"=>array("Method Not Allowed",""),
			"406"=>array("Not Acceptable",""),
			"407"=>array("Proxy Authentication Required",""),
			"408"=>array("Request Time-Out",""),
			"409"=>array("Conflict",""),
			"410"=>array("Gone",""),
			"411"=>array("Length Required",""),
			"412"=>array("Precondition Failed",""),
			"413"=>array("Request Entity Too Large",""),
			"414"=>array("Request-URL Too Large",""),
			"415"=>array("Unsupported Media Type",""),
			"500"=>array("Server Error",""),
			"501"=>array("Not Implemented","The server does not support the facility required"),
			"502"=>array("Bad Gateway ",""),
			"503"=>array("Out of Resources","The server cannot process the request due to a system overload"),
			"504"=>array("Gateway Time-Out","The service did not respond within the time frame that the gateway was willing to wait"),
			"505"=>array("HTTP Version not supported",""),
		);
$services_error_codes=array (
		"WrongFormat"=>"Illegal Service Command Format ...",
		"NotFound"=>"Service Not Found !",
		"TypeNotFound"=>"Service Type Not Found !",
		"DataNotFound"=>"Data Not Found !",
		"FileNotFound"=>"File/Page Not Found !",
		"NotSupported"=>"Format Not Supported !",
		"TypeNotSupported"=>"Type Not Supported !",
		"AccessDenial"=>"Access is forbidden !",
		"MethodNotAllowed"=>"Method Not Allowed !",
		"NotAcceptable"=>"Not Acceptable !",
		"PreconditionFailed"=>"Precondition Failed !",
		"ServerError"=>"Server Error !",
		"SourceError"=>"Error At Source Or Parsing Script !",
		"NotImplemented"=>"Not Implemented !",
		"Blacklisted"=>"You have been blacklisted by Server.",
		"404"=>"Sorry, Requested URI Not Available",
		"CrossSite"=>"CrossSite Request With Out Key Not Allowed",
		"*"=>"Unknown Error",
		"Bug"=>"OOOOPs!, Hit A Bug ?",
	);
function phpErrorLevelNames($errLevel) {
    switch($errLevel) {
        case E_ERROR: // 1 //
            return 'E_ERROR';
        case E_WARNING: // 2 //
            return 'E_WARNING';
        case E_PARSE: // 4 //
            return 'E_PARSE';
        case E_NOTICE: // 8 //
            return 'E_NOTICE';
        case E_CORE_ERROR: // 16 //
            return 'E_CORE_ERROR';
        case E_CORE_WARNING: // 32 //
            return 'E_CORE_WARNING';
        case E_COMPILE_ERROR: // 64 //
            return 'E_COMPILE_ERROR';
        case E_COMPILE_WARNING: // 128 //
            return 'E_COMPILE_WARNING';
        case E_USER_ERROR: // 256 //
            return 'E_USER_ERROR';
        case E_USER_WARNING: // 512 //
            return 'E_USER_WARNING';
        case E_USER_NOTICE: // 1024 //
            return 'E_USER_NOTICE';
        case E_STRICT: // 2048 //
            return 'E_STRICT';
        case E_RECOVERABLE_ERROR: // 4096 //
            return 'E_RECOVERABLE_ERROR';
        case E_DEPRECATED: // 8192 //
            return 'E_DEPRECATED';
        case E_USER_DEPRECATED: // 16384 //
            return 'E_USER_DEPRECATED';
    }
    return "";
}
?>
