<?php
/*
 * This class contains all the supporting structure for Logiks Error System
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
 if(!defined('ROOT')) exit('No direct script access allowed');

class LogiksError {

  protected $snapshotInset=ERROR_SOURCE_LINES;

  public static function handle($severity, $errMsg, $file, $line, $errorCode=500) {
    //This error code is not included in error_reporting
    if(!(error_reporting() & $severity)) { return true; }     //Test PHP settings allows printing error (will be removed in future)
    if(!(ERROR_REPORTING & $severity)) { return true; }       //Test Logiks settings allows printing error

    $dontCatch=explode(",",MASTER_DEBUG_DONT_CATCH);
    $dontPrint=explode(",",MASTER_DEBUG_DONT_PRINT_QUIT);
    if(in_array($severity, $dontCatch)) return true;
    if(in_array($severity, $dontPrint)) {
      $printError=false;
    } else {
      $printError=true;
    }

    $lr=new LogiksError();

    $errName=phpErrorLevelNames($severity);
    $errLogMsg="$errName($severity) :: $errMsg :: $file($line)";

    $levelName=substr($errName, 2);
    $levelClass=strtolower(substr($errName, 2));

    if($errorCode==null || !is_numeric($errorCode)) $errorCode=500;

    $errData=[
        "code"=>$errorCode,
        "level"=>$severity,
        "level_name"=>$levelName,
				"level_class"=>$levelClass,
        "level_id"=>$errName,
        "file"=>$file,
        "line"=>$line,
        "msg"=>$errMsg,
        "timesnap"=>0,
      ];

    if(isset($GLOBALS['LOGIKS']["_SERVER"]['REQUEST_PAGE_START'])) {
      $errData['timesnap']=(microtime(true)-$GLOBALS['LOGIKS']["_SERVER"]['REQUEST_PAGE_START']);
    } elseif(isset($GLOBALS['LOGIKS']["_SERVER"]['REQUEST_SERVICE_START'])) {
      $errData['timesnap']=(microtime(true)-$GLOBALS['LOGIKS']["_SERVER"]['REQUEST_SERVICE_START']);
    }
    
    //Try Logging the error first
    if(ERROR_LOGGING & $severity) {
      $lr->log($errLogMsg,$errData,LogiksLogger::severityToLevel($severity));
    }

    if($printError) {
      $lr->printPage($errData);
      exit();
    } else return true;
  }

  public static function handleJSON($severity, $errMsg, $file, $line, $errorCode=500) {
    //This error code is not included in error_reporting
    if(!(error_reporting() & $severity)) { return true; }     //Test PHP settings allows printing error (will be removed in future)
    if(!(SERVICE_ERROR_REPORTING & $severity)) { return true; }       //Test Logiks settings allows printing error

    $lr=new LogiksError();

    $errName=phpErrorLevelNames($severity);
    $errLogMsg="$errName($severity) :: $errMsg :: $file($line)";

    $levelName=substr($errName, 2);
    $levelClass=strtolower(substr($errName, 2));

    if($errorCode==null || !is_numeric($errorCode)) $errorCode=500;

    $errData=[
        "code"=>$errorCode,
        "level"=>$severity,
        "level_name"=>$levelName,
				"level_class"=>$levelClass,
        "level_id"=>$errName,
        "file"=>$file,
        "line"=>$line,
        "msg"=>$errMsg,
        "timesnap"=>0,
      ];

    if(isset($GLOBALS['LOGIKS']["_SERVER"]['REQUEST_PAGE_START'])) {
      $errData['timesnap']=(microtime(true)-$GLOBALS['LOGIKS']["_SERVER"]['REQUEST_PAGE_START']);
    } elseif(isset($GLOBALS['LOGIKS']["_SERVER"]['REQUEST_SERVICE_START'])) {
      $errData['timesnap']=(microtime(true)-$GLOBALS['LOGIKS']["_SERVER"]['REQUEST_SERVICE_START']);
    }

    //Try Logging the error first
    if(SERVICE_ERROR_LOGGING & $severity) {
      $lr->log($errLogMsg,$errData,LogiksLogger::severityToLevel($severity));
    }
    $lr->printJSON($errData);
    exit();
  }

  public static function handleExpection($severity, $errMsg, $file, $line, $errorCode=500) {
    if(EXCEPTION_LOGGING & $severity) {
      $lr=new LogiksError();

      $errName=phpErrorLevelNames($severity);
      $errLogMsg="$errName($severity) :: $errMsg :: $file($line)";

      $levelName=substr($errName, 2);
      $levelClass=strtolower(substr($errName, 2));

      if($errorCode==null || !is_numeric($errorCode)) $errorCode=500;

      $errData=[
          "code"=>$errorCode,
          "level"=>$severity,
          "level_name"=>$levelName,
  				"level_class"=>$levelClass,
          "level_id"=>$errName,
          "file"=>$file,
          "line"=>$line,
          "msg"=>$errMsg,
          "timesnap"=>0,
        ];

      if(isset($GLOBALS['LOGIKS']["_SERVER"]['REQUEST_PAGE_START'])) {
        $errData['timesnap']=(microtime(true)-$GLOBALS['LOGIKS']["_SERVER"]['REQUEST_PAGE_START']);
      } elseif(isset($GLOBALS['LOGIKS']["_SERVER"]['REQUEST_SERVICE_START'])) {
        $errData['timesnap']=(microtime(true)-$GLOBALS['LOGIKS']["_SERVER"]['REQUEST_SERVICE_START']);
      }

      $lr->log($errLogMsg,$errData,LogiksLogger::severityToLevel($severity));
    }
  }

  private function printJSON($errData) {
    if(MASTER_DEBUG_MODE && getConfig("SERVICE_DEBUG_TRACE")) {
      $trace=debug_backtrace();
      $out=array(
          "error"=>$errData,
          "trace"=>$trace,
        );
      header('Content-Type: text/html');
      printArray($out);
    } else {
      $out=array(
          "error"=>$errData,
        );
      header('Content-Type: application/json');
      echo json_encode($out);
    }
  }
  private function printPage($errData) {
    if(!isset($errData['code'])) $errData['code']=500;
    $errData['code_title']=getErrorTitle($errData['code']);
    //printArray($errData);exit();

    $trace=debug_backtrace();

    $errTmplData=[
          'cssLinks'=>"",
          'jsLinks'=>"",
          'stackTrace'=>"",
          'stackTrace-Header'=>"",
          'stackTrace-Table'=>"",
          'source_snapshot'=>"",
          'source_startIndex'=>"",
          'source_endIndex'=>"",
        ];

    $errCFG=$this->getErrorTemplate($errData['code']);

    if(is_object($errCFG)) {
      
    } else {
      $errorTemplate=$errCFG['template'];

      foreach ($errCFG['css'] as $css) {
        $errTmplData['cssLinks'].="<link href='$css' rel='stylesheet' />\n";
      }
      foreach ($errCFG['js'] as $js) {
        $errTmplData['jsLinks'].="<script src='$js'></script>\n";
      }
    }
    
    if(MASTER_DEBUG_MODE) {
      //Source Snaptshot
      $errTmplData['source_startIndex']=$errData['line']-$this->snapshotInset;
      $errTmplData['source_endIndex']=$errData['line']+$this->snapshotInset;
      if($errTmplData['source_startIndex']<0) $errTmplData['source_startIndex']=0;

      $errTmplData['source_snapshot']=LogiksError::getCodeSnapshot($errData['file'],
          $errTmplData['source_startIndex'],$errTmplData['source_endIndex']);

      $errTmplData['source_startIndex']++;


      //Stack Trace Snaptshot
      $errTmplData['stackTrace']=$trace;
      $errTmplData['stackTrace-Header']="<tr>
            <th class='right' width=30px>#</th>
            <th>Source File</th>
            <th>Function/Method</th>
            <th class='right'>Line</th>
        </tr>";
      $errTmplData['stackTrace-Table']=[];

      //$max=count($trace);
      $trace=array_reverse($trace);
  		foreach ($trace as $key => $value) {
        $id=$key+1;
        if(isset($value['class'])) $func="{$value['class']}->{$value['function']}";
  			else $func=$value['function'];

        if(isset($value['file'])) $value['file']=str_replace(ROOT, "", $value['file']);
        else $value['file']="";

        if(!isset($value['line'])) $value['line']="";

  			if($value['function']=="errorHandler") {
          $html="<tr>";
    			$html.="<td class='right'>$id</td>";
          $html.="<td>{$value['file']}</td>";
    			$html.="<td></td>";//<b>ERROR</b>
    			$html.="<td class='right'>{$value['line']}</td>";
    			$html.="</tr>";
    			$errTmplData['stackTrace-Table'][]=$html;

          break;
        }

  			$html="<tr>";
  			$html.="<td class='right'>$id</td>";
  			$html.="<td>{$value['file']}</td>";
        $html.="<td>{$func}(*)</td>";
  			$html.="<td class='right'>{$value['line']}</td>";
  			$html.="</tr>";
  			$errTmplData['stackTrace-Table'][]=$html;
  		}
      $errTmplData['stackTrace-Table']=array_reverse($errTmplData['stackTrace-Table']);
      $errTmplData['stackTrace-Table']=implode("", $errTmplData['stackTrace-Table']);
    }
    if(is_object($errCFG)) {
      $html=$errCFG->getPageContent();
      if(!$html) $html="";
    } else {
      $html=file_get_contents($errorTemplate);
    }
    $lr=new LogiksReplace();
    $lr->addData($errData);
    $lr->addData($errTmplData);

    echo $lr->replace($html);
    exit();
  }
  private function log($errLogMsg,$errData,$errLevel=null) {
    if($errLevel==null) $errLevel=LogiksLogger::LOG_ERROR;

    LogiksLogger::log("error",$errLevel,$errLogMsg,$errData);
  }
  
  protected function getCodeSnapshot($srcFile, $start, $end) {//, $lineNo
    if(!file_exists($srcFile)) return "";

    $codeData=[];
    if($start<0) $start=0;

    $fileData=file($srcFile);
    //printArray($fileData);exit("$srcFile, $start, $end");

    for ($i=$start;$i<$end;$i++) {
      if(!isset($fileData[$i])) break;
      $codeData[] = htmlentities($fileData[$i]);
    }

    return implode("", $codeData);
  }
  protected function getDefaultTemplate($errorCode=500) {
    $errorTemplate=ROOT."misc/themes/default/templates/error.tpl";
    if(MASTER_DEBUG_MODE) {
      $errorTemplate=ROOT."misc/themes/default/templates/error-debug.tpl";
      if(!file_exists($errorTemplate)) {
        $errorTemplate=ROOT."misc/themes/default/templates/error.tpl";
      }
    }
    $errorCSS=[
      WEBROOT."misc/themes/default/error.css",
      WEBROOT."misc/themes/default/errorPrint.css",
      // WEBROOT."plugins/vendors/SyntaxHighlighter/styles/SyntaxHighlighter.css",
    ];
    $errorJS=[
      WEBROOT."api/js/jquery/jquery.js",
      // WEBROOT."plugins/vendors/SyntaxHighlighter/scripts/shCore.js",
      // WEBROOT."plugins/vendors/SyntaxHighlighter/scripts/shBrushPhp.js",
      // WEBROOT."plugins/vendors/SyntaxHighlighter/scripts/shBrushJScript.js",
      // WEBROOT."plugins/vendors/SyntaxHighlighter/scripts/shBrushXml.js",
      WEBROOT."api/js/prettify.js",
    ];
    return array("template"=>$errorTemplate,"css"=>$errorCSS,"js"=>$errorJS);
  }
  protected function getErrorTemplate($errorCode="error") {
    if(defined("SITENAME")) {
      $errorCode="error";

      $lt=new LogiksTheme(APPS_THEME,SITENAME);

      $lp=new LogiksPage($lt);
      $pg=$lp->loadPage($errorCode);
      if($pg) {
        return $lp;
      } else {
        return $this->getDefaultTemplate();
      }
    }
    return $this->getDefaultTemplate();
  }
}
?>
