<?php
if(!defined('ROOT')) exit('No direct script access allowed');
LoadConfigFile(ROOT . "config/captcha.cfg");

if(!isset($_REQUEST['captchaId'])) {
	exit("No Captcha ID Found");
}

$fontDir=FONTS_FOLDER;
$font=ROOT.$fontDir.getConfig("CAPTCHA_FONT");
if(!file_exists($font)) {
	$font=ROOT."media/fonts/Courier.ttf";
}
$arrChars=array();
$arrChars[0]=array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z");
$arrChars[1]=array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
$arrChars[2]=array("0","1","2","3","4","5","6","7","9","9");
$arrChars[3]=array("!","@","#","$","%","^","&","*","_","-","+","=",".");

$complex=getConfig("CAPTCHA_COMPLEXITY");
if($complex>sizeOf($arrChars)-1) {
	$complex=sizeOf($arrChars)-1;
}

ob_start();
$phrase="";
for($o=0;$o<getConfig("CAPTCHA_WORD");$o++) {
	for($i=0;$i<getConfig("CAPTCHA_NUMBER");$i++){
		//$phrase .=chr(rand(97,122));
		$n=rand(0,$complex);
		$p=rand(0,sizeOf($arrChars[$n]));
		$phrase.=$arrChars[$n][$p];
	}
	$phrase.=" ";
}
$_SESSION["CAPTCHA_".$_REQUEST['captchaId']]=md5(trim($phrase));

//create the image
$img=imagecreatetruecolor(getConfig("CAPTCHA_WIDTH"),getConfig("CAPTCHA_HEIGHT"));

$clr1=explode(",",getConfig("CAPTCHA_BG_COLOR"));
$clr2=explode(",",getConfig("CAPTCHA_FONT_COLOR"));
$clr3=explode(",",getConfig("CAPTCHA_OTHERS_COLOR"));

//set white background color,black text color,grey graphics
$bg_color=imagecolorallocate($img,$clr1[0],$clr1[1],$clr1[2]);
$text_color=imagecolorallocate($img,$clr2[0],$clr2[1],$clr2[2]);
$graphics_color=imagecolorallocate($img,$clr3[0],$clr3[1],$clr3[2]);

//image fill the background
imagefilledrectangle($img,0,0,getConfig("CAPTCHA_WIDTH"),getConfig("CAPTCHA_HEIGHT"),$bg_color);
//draw pass phrase
imagettftext($img,getConfig("CAPTCHA_FONTSIZE"),getConfig("CAPTCHA_ANGLE"),getConfig("CAPTCHA_OFFSET"),getConfig("CAPTCHA_HEIGHT")-6,$text_color,$font,$phrase);
drawDots($img,$graphics_color);
drawLines($img,$graphics_color);
drawCircles($img,$graphics_color);

// prevent client side  caching
header("Expires: Wed, 1 Jan 1997 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Content-Transfer-Encoding: binary");
header("Pragma: no-cache");
header("Content-type:image/png");
imagepng($img);
imagedestroy($img); //clean up memory
ob_flush();
exit();

function drawDots($img, $graphics_color) {
	for($i=0;$i<getConfig("CAPTCHA_DOTS_COUNT");$i++){
		imagesetpixel($img,rand()%getConfig("CAPTCHA_WIDTH"),rand()%getConfig("CAPTCHA_HEIGHT"),$graphics_color);
	}
}
function drawLines($img, $graphics_color) {
	$x0=0;
	$y0=0;
	$w=getConfig("CAPTCHA_WIDTH");
	$h=getConfig("CAPTCHA_HEIGHT");
	for($i=0;$i<getConfig("CAPTCHA_LINES_COUNT");$i++) {
		$x1=rand()%($w/2);
		$y1=rand()%$h;
		$x2=($w/2)+rand()%($w/2);
		$y2=rand()%$h;
		imageline($img,$x1,$y1,$x2,$y2,$graphics_color);		
	}
}
function drawCircles($img, $graphics_color, $cnt=0) {
	$x0=0;
	$y0=0;
	$w=getConfig("CAPTCHA_WIDTH")-20;
	$h=getConfig("CAPTCHA_HEIGHT")-3;
	for($i=0;$i<getConfig("CAPTCHA_CIRCLES_COUNT");$i++) {
		$x1=rand()%($w)+10;
		$y1=rand()%$h;
		$r=$h/rand(2,6);
		imagecircle($img,$r,$x1,$y1,$graphics_color);		
	}
}
//Other Functions
function imagecircle($source,$r,$x,$y,$color){ 
  for($i = 0;$i<=2*pi();$i+=(pi()/180)){ 
    imageline($source,cos($i)*$r+$x,sin($i)*$r+$y,cos($i+(pi()/180))*$r+$x,sin($i+(pi()/180))*$r+$y,$color); 
  } 
}
?>
