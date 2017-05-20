<?php
/*
 * This class is used for Sendmail EMail dispatch. 
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
class GENERICMSGDriver extends LogiksMSGDriver {
  public function send($to,$subject,$body,$params=[]) {
    if(is_array($to)) {
			$to=implode(",",$to);
		}
    return mail($to,$subject,$body,"",$params);
 	}
}
?>