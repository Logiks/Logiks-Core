<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("phpErrorLevelNames")) {

	define("E_EXCEPTION",32768);
	define("E_LOGIKS_ERROR",65536);

	function getErrorList() {
		return array (
			//DEFAULT HTML ERRORS
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Page <b>#page#</b> Is Forbidden For Access',
        403.1 => 'Site <b>#site#</b> Is Forbidden For Access',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended',
			//LOGIKS ERRORS
				800 => 'Internal Logiks Error',
				801 => 'Data Not Found',
				802 => 'You have been blacklisted by/on server',
				803 => 'Error At Source Or Parsing Script',
				804 => 'Site <b>#site#</b> Is Down For Maintenance',
				805 => 'Site <b>#site#</b> Is Under-Construction',
				806 => 'Site <b>#site#</b> Is Currently In Restrictive Only Mode',
				806.1 => 'Site <b>#site#</b> Is Currently In Whitelist Only Mode',
				807	=> 'Site <b>#site#</b> Is Currently Blocked',
				808	=> 'Site <b>#site#</b>/<b>#page#</b> Is In Restrictive Development Mode',
			//LOGIKS SERVICE ERRORS
				900 => 'Internal Logiks Service Error',
				901 => 'Illegal Service Command Format',
				902 => 'Service Format Not Supported',
				903 => 'Service Type Not Found',
				904 => 'Service Command Not Found',
				905 => 'CrossSite Request With Out Key Not Allowed',
				906 => 'Service Command Missing',
    );
	}
	function getErrorTitle($code) {
		$err=getErrorList();
		if(isset($err[$code])) {
			if(function_exists("_replace")) return _replace($err[$code]);
			else {
				$lr=new LogiksReplace();
				return $lr->_replace($err[$code]);
			}
		}
		else return "Unknown Error Code";
	}
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
					case E_EXCEPTION:	// 32768 //
							return 'E_EXCEPTION';
					case E_LOGIKS_ERROR: // 65536 //
							return 'E_LOGIKS_ERROR';
	    }
	    return "";
	}
}
?>
