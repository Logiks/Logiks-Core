<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(isset($_REQUEST["stops"])) {
	$stopData=$_REQUEST["stops"];
	$stopData=str_replace("%20"," ",$stopData);
	$stopData=explode(",",$stopData);
	$stops=array();
	$i=0;
	foreach($stopData as $a=>$b) {
		$temp=explode(" ",$b);		
		if(sizeOf($temp)==0) {			
			$oset=(($i*100)/sizeOf($stopData))."%";
			$clr="#".dechex(rand(1,255)).dechex(rand(1,255)).dechex(rand(1,255));
			$opa="1";
			$stops[sizeOf($stops)]=array("offset"=>$oset,"color"=>$clr,"opacity"=>$opa);
		} elseif(sizeOf($temp)==1) {
			$oset=(($i*100)/sizeOf($stopData))."%";
			$clr="#".$temp[0];
			$opa="1";
			if(strlen($clr)<=1) $clr="#".dechex(rand(1,255)).dechex(rand(1,255)).dechex(rand(1,255));
			$stops[sizeOf($stops)]=array("offset"=>$oset,"color"=>$clr,"opacity"=>$opa);
		} elseif(sizeOf($temp)==2) {
			$oset=$temp[1];
			$clr="#".$temp[0];
			$opa="1";
			$stops[sizeOf($stops)]=array("offset"=>$oset,"color"=>$clr,"opacity"=>$opa);
		} elseif(sizeOf($temp)==3) {
			$oset=$temp[1];
			$clr="#".$temp[0];
			$opa=$temp[2];
			$stops[sizeOf($stops)]=array("offset"=>$oset,"color"=>$clr,"opacity"=>$opa);
		} else {
			$oset=$temp[1];
			$clr="#".$temp[0];
			$opa=$temp[2];
			$stops[sizeOf($stops)]=array("offset"=>$oset,"color"=>$clr,"opacity"=>$opa);
		}
		$i++;
	}
	
	if(sizeOf($stops)==1) {
		$oset="100%";
		$clr="#".dechex(rand(1,255)).dechex(rand(1,255)).dechex(rand(1,255));
		$opa="1";
		$stops[sizeOf($stops)]=array("offset"=>$oset,"color"=>$clr,"opacity"=>$opa);
	}	
} else {
	if(isset($_REQUEST["from"])) $from="#".$_REQUEST["from"]; else $from="#ABCEEE";
	if(isset($_REQUEST["to"])) $to="#".$_REQUEST["to"]; else $to="#8AA8CA";

	if(isset($_REQUEST["o"])) $o1=$_REQUEST["o"]; else $o1="1";//Opactiy
	if(isset($_REQUEST["o1"])) $o1=$_REQUEST["o1"];// else $o1="1";//Opactiy From
	if(isset($_REQUEST["o2"])) $o2=$_REQUEST["o2"]; else $o2=$o1;//Opactiy To
	
	$stops=array();
	$stops[sizeOf($stops)]=array("offset"=>"0%","color"=>"$from","opacity"=>"$o1");
	$stops[sizeOf($stops)]=array("offset"=>"100%","color"=>"$to","opacity"=>"$o2");
}

if(isset($_REQUEST["w"])) $w=$_REQUEST["w"]; else $w="100%";
if(isset($_REQUEST["h"])) $h=$_REQUEST["h"]; else $h="100%";

if(isset($_REQUEST["defs"])) $defs="#".$_REQUEST["defs"]; else $defs="#g1";

header('Content-type: image/svg+xml');
/*
<rect width="100%" height="100%" fill="url(#linear-gradient)"/>
<ellipse fill="url(#linear-gradient)" rx="100.0" ry="100.0" cx="200.0" cy="200.0" stroke="#000000" />
<line x1="0.0" x2="400.0" y1="0.0" y2="400.0" stroke="url(#linear-gradient)" stroke-width="2"/>
<text x="128.0" y="171.0"><tspan fill="url(#linear-gradient)" stroke="#d10d0d" xml:space="preserve">Bismay </tspan></text>
*/
ob_start();
echo '<?xml version="1.0" standalone="no"?>';
?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">

<svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="<?=$w?>" height="<?=$h?>">
        <defs>
			<linearGradient id="g1" x1="0%" y1="0%" x2="0%" y2="100%">
			<?php
				foreach($stops as $a=>$b) {
					$q1=trim($b["offset"]);
					$q2=trim(strtoupper($b["color"]));
					$q3=trim($b["opacity"]);
					echo "\t<stop offset=\"$q1\" stop-color=\"$q2\" stop-opacity=\"$q3\"/>\n\t\t\t";
				}
			?></linearGradient>
			<linearGradient id="g2" x1="0%" y1="0%" x2="100%" y2="0%">
			<?php
				foreach($stops as $a=>$b) {
					$q1=$b["offset"];
					$q2=$b["color"];
					$q3=$b["opacity"];
					echo "\t<stop offset=\"$q1\" stop-color=\"$q2\" stop-opacity=\"$q3\"/>\n\t\t\t";
				}
			?></linearGradient>
        </defs>
        <g>
			<rect width="100%" height="100%" fill="url(<?=$defs?>)"/>
        </g>
</svg>
<?php
$data=ob_get_contents();
ob_clean();
echo $data;

/*
<stop offset="0%" stop-color="<?=$from?>" stop-opacity="<?=$o1?>"/>
<stop offset="100%" stop-color="<?=$to?>" stop-opacity="<?=$o2?>"/>
*/
?>
