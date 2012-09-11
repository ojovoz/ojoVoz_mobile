<?
include_once("misc_functions.php");
include_once("channel_vars.php");

function IsCoordinate($x) {
	$ret=false;
	if (strpos($x,".")>0 || strpos($x,",")>0) {
		$ret = true;
	}
	return $ret;
}

function GetCoordinatesFromNokiaLandmark($x) {
	//$list = trim(utf8_decode(imap_base64($attachment)));
	
}

function GetCoordinatesFromFilename($f) {
//receives the filename of an image
//returns an array with: latitude, longitude and timestamp
	$coord=array();
	$ct=0;
	$timestamp="";
	$parts=explode("_",$f);
	for($i=0;$i<sizeof($parts);$i++) {
		if(IsCoordinate($parts[$i])) {
			$coord[$ct]=str_replace(",",".",$parts[$i]);
			$ct++;
		} else if (is_numeric($parts[$i])) {
			$timestamp .= $parts[$i]." ";
		}
	}
	if ($timestamp != "") {
		$coord[$ct]=trim($timestamp);
	}
	return $coord;
}

function GetNumber($s) {
	$parts=explode("/",$s);
	return floatval($parts[0])/floatval($parts[1]);
}

function GetCoordinatesFromExif($img) {
//receives the filename of an image
//returns an array with: latitude, longitude and timestamp
	$exif = exif_read_data($img, 0, true);
	//if (strpos($exif['IFD0']['ImageDescription'],"GeoZexe")===FALSE) {
	if(!isset($exif['GPS']['GPSLatitude'][0])) {
		$coord=array();
		return $coord;
	} else {
		$coord[0]=GetNumber($exif['GPS']['GPSLatitude'][0])+(GetNumber($exif['GPS']['GPSLatitude'][1])/60)+(GetNumber($exif['GPS']['GPSLatitude'][2])/3600);
		if ($exif['GPS']['GPSLatitudeRef']=="S") {
			$coord[0]=$coord[0]*-1;
		}
		$coord[1]=GetNumber($exif['GPS']['GPSLongitude'][0])+(GetNumber($exif['GPS']['GPSLongitude'][1])/60)+(GetNumber($exif['GPS']['GPSLongitude'][2])/3600);
		if ($exif['GPS']['GPSLongitudeRef']=="W") {
			$coord[1]=$coord[1]*-1;
		}
		if ($coord[0]==0 || $coord[1]==0) {
			$coord=array();
		}
		return $coord;
	}
}

function GrabMapImage($lat,$long,$attachment_id,$static_map_width,$static_map_height,$api_key) {
	$ret="";
	$img_url="http://maps.google.com/staticmap?center=".$lat.",".$long."&zoom=16&size=".$static_map_width."x".$static_map_height."&maptype=mobile&markers=".$lat.",".$long."&key=".$api_key."&sensor=false";
	$img=curl_init ($img_url); 
	curl_setopt($img, CURLOPT_HEADER, 0); 
	curl_setopt($img, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($img, CURLOPT_BINARYTRANSFER,1); 
	$rawdata=curl_exec($img); 
	curl_close ($img);
	//
	$img_filename = "map".$attachment_id.".gif";
	$fp = fopen("maps/".$img_filename,'w'); 
	fwrite($fp, $rawdata); 
	fclose($fp);
	//
	$ret=$img_filename;
	return $ret;
}

function GrabMapImageLocate($lat,$long,$attachment_id,$static_map_width,$static_map_height,$api_key) {
	$ret="";
	$img_url="http://maps.google.com/staticmap?center=".$lat.",".$long."&zoom=16&size=".$static_map_width."x".$static_map_height."&maptype=mobile&markers=".$lat.",".$long."&key=".$api_key."&sensor=false";
	$img=curl_init ($img_url); 
	curl_setopt($img, CURLOPT_HEADER, 0); 
	curl_setopt($img, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($img, CURLOPT_BINARYTRANSFER,1); 
	$rawdata=curl_exec($img); 
	curl_close ($img);
	//
	$img_filename = "map".$attachment_id.".gif";
	$fp = fopen("./../maps/".$img_filename,'w'); 
	fwrite($fp, $rawdata); 
	fclose($fp);
	//
	$ret=$img_filename;
	return $ret;
}

function UpdateMapImage($lat,$long,$attachment_id,$static_map_width,$static_map_height,$api_key) {
	$img_url="http://maps.google.com/staticmap?center=".$lat.",".$long."&zoom=16&size=".$static_map_width."x".$static_map_height."&maptype=mobile&markers=".$lat.",".$long."&key=".$api_key."&sensor=false";
	$img=curl_init ($img_url); 
	curl_setopt($img, CURLOPT_HEADER, 0); 
	curl_setopt($img, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($img, CURLOPT_BINARYTRANSFER,1); 
	$rawdata=curl_exec($img); 
	curl_close ($img);
	//
	$img_filename = "map".$attachment_id.".gif";
	unlink("./../maps/".$img_filename);
	$fp = fopen("./../maps/".$img_filename,'w'); 
	fwrite($fp, $rawdata); 
	fclose($fp);
	//
}

function GetReverseGeocoding($lat, $long) {
	$ret="";
	$query="http://maps.google.com/maps/geo?q=".$lat.",".$long."&output=csv&key=".$google_maps_api_key;
	$address=GetPage($query);
	$a=explode(",",$address);
	$n=sizeof($a)-1;
	$a[2]=str_replace("\"","",$a[2]);
	$a[$n]=str_replace("\"","",$a[$n]);
	for($i=2;$i<=$n;$i++) {
		$a[$i]=str_replace("Spain","Espanya",$a[$i]);
		if($ret=="") {
			$ret=$a[$i];
		} else {
			$ret.=",".$a[$i];
		}
	}
	return $ret;
}

function GetGeocoding($address) {
	$ret="";
	$query="http://maps.googleapis.com/maps/api/geocode/xml?address=".urlencode($address)."&sensor=false";
	$xml=GetPage($query);
	$address_start=strpos($xml,"<formatted_address>")+19;
	if ($address_start > 19) {
		$address_end=strpos($xml,"</",$address_start);
		$address_length=$address_end-$address_start;
		$address=substr($xml,$address_start,$address_length);
		$address=str_replace("Spain","Espanya",$address);
	
		$location_start=strpos($xml,"<location>")+10;
		$lat_start=strpos($xml,"<lat>",$location_start)+5;
		$lat_end=strpos($xml,"</",$lat_start);
		$lat_length=$lat_end-$lat_start;
		$lat=substr($xml,$lat_start,$lat_length);
	
		$lng_start=strpos($xml,"<lng>",$location_start)+5;
		$lng_end=strpos($xml,"</",$lng_start);
		$lng_length=$lng_end-$lng_start;
		$lng=substr($xml,$lng_start,$lng_length);
	
		$ret=$address."*".$lat."*".$lng;
	
	}
	
	return $ret;
}
?>