<?php
/*
 * LogiksValidator makes data validation in Logiks very easy and quick.
 * LogiksValidator includes a wide variety of convenient validation rules that you may apply to data. 
 *
 * Todo:
 * + Providing the ability to validate if values are unique in a given database table.
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 1.0
 */
class LogiksValidator {

    private static $RULESET=[
        "alpha"=> ["type"=>"regex", "regex"=>"/[a-zA-Z]+/im"],
        "alpha_dash"=> ["type"=>"regex", "regex"=>"/[a-zA-Z\-_]+/im"],
        "alpha_num"=> ["type"=>"regex", "regex"=>"/[a-zA-Z0-9]+/im"],
        "alpha_num_dash"=> ["type"=>"regex", "regex"=>"/[a-zA-Z0-9\-_]+/im"],
        "numeric"=> ["type"=>"regex", "regex"=>"/^[0-9]+$/im"],
        
        // "dated_after"=>[],//date
        // "dated_before"=>[],//date
        // "dated_between"=>[],//date
    ];

    private static $MESSAGES = [

    ];

    private static $ERROR_MESSAGES = [
        "required"=>"{key} is required",
        "accepted"=>"Please accept the {key}",
        "min"=>"{key} should be at least {ref}",
        "max"=>"{key} should be maximum {ref}",
        "size"=>"{key} should be equal to {ref}",
        "in"=>"{key} should be one of {ref}",
        "not_in"=>"{key} should not be one of {ref}",
    ];

    public static function registerRule($ruleKey, $userFunc) {
        LogiksValidator::$RULESET[$ruleKey] = [
            "type"=>"function",
            "function"=>$userFunc
        ];
    }

    public static function registerFieldMessage($key, $err, $msg) {
        if(!isset(LogiksValidator::$MESSAGES[$key])) LogiksValidator::$MESSAGES[$key] = [];
        LogiksValidator::$MESSAGES[$key][$err] = $msg;
    }

    public static function getFieldMessage($key, $err, $msg) {
        if(!isset(LogiksValidator::$MESSAGES[$key]) || !isset(LogiksValidator::$MESSAGES[$key][$err])) return "";
        LogiksValidator::$MESSAGES[$key][$err] = $msg;
    }

    public static function registerErrorMessage($err, $msg) {
        LogiksValidator::$ERROR_MESSAGES[$err] = $msg;
    }

    public static function getErrorMessage($err) {
        if(isset(LogiksValidator::$ERROR_MESSAGES[$err])) return LogiksValidator::$ERROR_MESSAGES[$err];
        return "";
    }

    public static function validate($data, $rules = [], $params = []) {
        if(!$data || !is_array($data)) return [
            "status"=> false,
            "errors"=> ["Data not found"]
        ];
        if(!$rules) return [
            "status"=> false,
            "errors"=> ["Rules need to be in array format"]
        ];

        $err = [];
        
        //Validate required paramaters
        foreach ($rules as $key => $ruleStr) {
            $rules[$key] = explode("|", strtolower($ruleStr));
            if(in_array("required", $rules[$key])) {
                if(!isset($data[$key])) $err[] = LogiksValidator::getOnErrorMessage($key, "required");// || strlen($data[$key])<=0
            } elseif(strlen($data[$key])<=0) {
                unset($data[$key]);
            }
        }

        foreach($data as $k=>$v) {
            if(isset($rules[$k])) {
                //Validate $v
                foreach($rules[$k] as $rule) {
                    if(in_array($rule, ["required"])) continue;

                    $ans = LogiksValidator::processRule($k, $rule, $v);

                    if(!$ans) {
                        $err[] = LogiksValidator::getOnErrorMessage($k, $rule);
                    }
                }
            }
        }
        
        // printArray([
        //     "data"=>$data,
        //     "rules"=>$rules,
        // ]);

        if($err) return $err;
        return true;
    }

    private static function processRule($key, $rule, $value) {
        $rule = explode(":", $rule);
        if(!isset($rule[1])) $rule[1] = 0;

        // printArray([$key, $rule, $value]);

        if(isset(LogiksValidator::$RULESET[$rule[0]])) {
            $ruleParams = LogiksValidator::$RULESET[$rule[0]];

            switch($ruleParams['type']) {
                case "regex":
                    $regex = $ruleParams['regex'];
                    preg_match($regex, $value, $output);
                    return ($output && count($output)>0);
                break;
                case "function":
                    if(function_exists($ruleParams['function'])) return call_user_func($ruleParams['function'], $key, $value, $rule);
                    else {
                        _log("Validation Function Not Defined - {$ruleParams['function']}", "console");
                        return false;
                    }
                break;
            }
        } else {
            switch($rule[0]) {
                case "accepted":
                    return ($value===true || in_array(strtolower($value), ["yes", "on", "true"]));
                break;
                case "boolean":
                    return ($value===true || $value===false || $value===0 || $value===1 || in_array(strtolower($value), ["false", "true"]));
                break;
                case "integer":
                    return is_integer($value);
                break;
                case "number":
                    return is_numeric($value);
                break;
                case "max":
                    if(is_numeric($value))
                        return ($value<$rule[1]);
                    else
                        return (strlen($value)<$rule[1]);
                break;
                case "min":
                    if(is_numeric($value))
                        return ($value>=$rule[1]);
                    else
                        return (strlen($value)>=$rule[1]);
                break;
                case "between":
                    $rule[1] = explode(",", $rule[1]);
                    if(is_numeric($value))
                        return ($value>=$rule[1][0] && $value<$rule[1][1]);
                    else
                        return (strlen($value)>=$rule[1][0] && strlen($value)<$rule[1][1]);
                break;
                case "size":
                    if(is_numeric($value))
                        return ($value==$rule[1]);
                    else
                        return (strlen($value)==$rule[1]);
                break;
                case "email":
                    $value = filter_var($value, FILTER_SANITIZE_EMAIL);
                    return filter_var($value, FILTER_VALIDATE_EMAIL);
                break;
                case "url":
                    $value = filter_var($value, FILTER_SANITIZE_URL);
                    return filter_var($value, FILTER_VALIDATE_URL);
                break;
                case "array":
                    return is_array($value);
                break;
                case "json":
                    try {
                        $value = json_decode($value,true);
                        if($value) return true;
                        return false;
                    } catch(Exception $e) {
                        return false;
                    }
                break;
                case "regex":
                    $regex = "/{$rule[1]}/im";
                    preg_match($regex, $value, $output);
                    //printArray([$key, $value, $rule, $output]);
                    return ($output && count($output)>0);
                break;
                case "in":
                    $rule[1] = explode(",", $rule[1]);
                    return in_array(strtolower($value), $rule[1]);
                break;
                case "not_in":
                    $rule[1] = explode(",", $rule[1]);
                    return !in_array(strtolower($value), $rule[1]);
                break;
                case "not":
                    //printArray([$key, $value, $rule]);
                break;
                // "not_in"=>[],//in comma separated value
                // "not"=>[],//
            }
        }

        return true;
    }

    private static function getOnErrorMessage($key, $errRule, $ref = false) {
        $errRule = explode(":", $errRule);
        if(isset($errRule[1])) $ref = $errRule[1];
        $errRule = $errRule[0];

        if(isset(LogiksValidator::$MESSAGES[$key])) {
            if(isset(LogiksValidator::$MESSAGES[$key][$errRule])) {
                return _ling(LogiksValidator::$MESSAGES[$key][$errRule]);
            } else {
                return ucfirst(LogiksValidator::$MESSAGES[$key]." "._ling($errRule));
            }
        } else {
            if(isset(LogiksValidator::$ERROR_MESSAGES[$errRule])) {
                return ucfirst(str_replace("{key}", $key, str_replace("{ref}", $ref, _ling(LogiksValidator::$ERROR_MESSAGES[$errRule]))));
            } else {
                return ucfirst("{$key} is invalid {$errRule}");
            }
        }
    }
}
