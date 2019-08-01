<?
include_once("./../includes/all.php");
$dbh = initDB();

$located=false;
if (isset($_POST['locate'])) {
	if ($_POST['located']==1) {
		$latitude=$_POST['latitude'];
		$longitude=$_POST['longitude'];
		$id=$_POST['id'];
		LocateAttachment($dbh,$id,$latitude,$longitude);
		$address=GetReverseGeocoding($latitude,$longitude);
		$aid=UpdateMessageAddress($dbh,$id,$address);
		$map_filename="";
		/* $map_filename=GrabMapImageLocate($latitude,$longitude,$aid,$static_map_width,$static_map_height,$google_maps_api_key);
		if ($map_filename!="") {
			$query="UPDATE attachment SET map_filename = '$map_filename' WHERE attachment_id=$aid";
			$result = mysql_query($query, $dbh);
		} */
		$located=true;
	}
} 

if ($located==false) {
	if (isset($_GET['id'])) {
		$id=$_GET['id'];
	} else if (isset($_POST['id'])) {
		$id=$_POST['id'];
	}
	if (isset($_GET['date'])) {
		$date=$_GET['date'];
	} else if (isset($_POST['date'])) {
		$date=$_POST['date'];
	}
	if (isset($_GET['lang'])) {
		$from=$_GET['lang'];
	} else if (isset($_POST['lang'])) {
		$from=$_POST['lang'];
	}
	if (isset($_GET['c'])) {
		$c=$_GET['c'];
	} else if (isset($_POST['c'])) {
		$c=$_POST['c'];
	}

	if (isset($_POST['search']) && isset($_POST['address'])) {
		$address=$_POST['address'];
		$display_address=$address;
		if ($prefered_city!="" && $use_prefered_city==true) {
			if (strpos($address,$prefered_city)===false) {
				$address=$address.", ".$prefered_city;
			}
		}
	} else {
		$address="";
	}

	$point=GetAttachmentLocation($dbh,$id,$default_latitude,$default_longitude);
	$latitude=preg_replace("/[^0-9.,\-]/", "", $point[0]);
	$longitude=preg_replace("/[^0-9.,\-]/", "", $point[1]);
	$already_located=$point[2];
?>

<!DOCTYPE html>
<html>
<head>
<title><? echo($global_channel_name); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="./../includes/leaflet/leaflet.css" />
<script src="./../includes/leaflet/leaflet.js"></script>
<script language="JavaScript" src="./../includes/general.js" language="javascript" type="text/javascript"></script>
</head>

<body onload="initialize()">
<form name="form1" method="post" action="">
  <input name="c" type="hidden" id="c" value="<? echo($c); ?>">
  <input name="date" type="hidden" id="date" value="<? echo($date); ?>">
  <input name="id" type="hidden" id="id" value="<? echo($id); ?>">
  <input name="lang" type="hidden" id="lang" value="<? echo($lang); ?>">
</form>
<br>
<div id="map" style="width: 640px; height: 480px;"></div>
<script type="text/javascript">

	var latLng=null;
	var popup = L.popup();
	
	<? if($already_located==1){
?>		
	var ovMap = L.map('map').setView([<? echo($latitude); ?>, <?  echo($longitude); ?>], 13);
<?		
	} else {
?>
	var ovMap = L.map('map').setView([<? echo($default_latitude); ?>, <?  echo($default_longitude); ?>], 13);
<?
	}
?>
	
	
	L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=<? echo($mapbox_api_key); ?>', {
		maxZoom: 18,
		attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, ' +
			'<a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
			'Imagery (C) <a href="http://mapbox.com">Mapbox</a>',
		id: '<? echo($mapbox_id); ?>',
		accessToken: '<? echo($mapbox_api_key); ?>'
	}).addTo(ovMap);
	
	function onMapClick(e) {
		popup
			.setLatLng(e.latlng)
			.setContent(e.latlng.toString())
			.openOn(ovMap);
		document.forms[1].latitude.value=e.latlng.lat;
		document.forms[1].longitude.value=e.latlng.lng;
		document.forms[1].located.value='1';
	}

	ovMap.on('click', onMapClick);
	
	<? if($already_located==1) {
?>
	var popupOrigin = L.popup();
	var coords = L.latLng(<? echo($latitude); ?>, <? echo($longitude); ?>);
	popupOrigin
		.setLatLng(coords)
		.setContent(coords.toString())
		.openOn(ovMap);
<?
	}
?>
	
	/*
	
	function initialize(){
		latLng = new google.maps.LatLng(<? echo($latitude); ?>, <?  echo($longitude); ?>);
		var mapOptions = {center: latLng, zoom: 8, mapTypeId: google.maps.MapTypeId.ROADMAP};
    	var map = new google.maps.Map(document.getElementById("map"),mapOptions);
		
		<?
		if ($address!=""){
		?>
		geocoder = new google.maps.Geocoder();
		geocoder.geocode({'address':'<? echo($address); ?>'},function(results,status){
      		if (status==google.maps.GeocoderStatus.OK) {
        		map.setCenter(results[0].geometry.location);
        		marker = new google.maps.Marker({map:map,position:results[0].geometry.location});
				document.forms[1].latitude.value=results[0].geometry.location.lat();
				document.forms[1].longitude.value=results[0].geometry.location.lng();
				document.forms[1].located.value='1';
      		} else {
				marker = new google.maps.Marker({position:latLng, map:map});
				document.forms[0].address.value="<? echo($address_not_found_text); ?>"
				document.forms[1].located.value='0';
			}
    	});
		<?
		} else {
		?>
		marker = new google.maps.Marker({position:latLng, map:map});
		document.forms[1].latitude.value=latLng.lat();
		document.forms[1].longitude.value=latLng.lng();
		document.forms[1].located.value='1';
		<?
		}
		?>
		
		google.maps.event.addListener(map,'click',function(event) {
			document.forms[1].latitude.value = event.latLng.lat();
			document.forms[1].longitude.value = event.latLng.lng();
			document.forms[1].located.value='1';
			marker.setPosition(event.latLng);
		});
	}
	*/
    </script>
<p>
<form action="" method="post">  <input name="locate" type="submit" id="locate" style="color: <? echo($form_color); ?>; font-size: <? echo($font_size); ?>em;" value="Locate"> 
  <input name="latitude" type="hidden" id="latitude" value="<? echo($latitude); ?>">
  <input name="longitude" type="hidden" id="longitude" value="<? echo($longitude); ?>">
  <input name="located" type="hidden" id="located" value="<? echo($already_located); ?>">
  <input name="c" type="hidden" id="c" value="<? echo($c); ?>">
  <input name="date" type="hidden" id="date" value="<? echo($date); ?>">
  <input name="id" type="hidden" id="id" value="<? echo($id); ?>">
  <input name="lang" type="hidden" id="lang" value="<? echo($lang); ?>">
</form>
</p>
<p><font face="Courier New, Courier, mono" size="2"><a href="edit_channel.php?c=<? echo($c); ?>&date=<? echo($date); ?>#<? echo($id); ?>">Back</a> </font>
</p>
</body>
</html>
<?
} else {
	$c=$_POST['c'];
	$from=$_POST['from'];
	header("Location:edit_channel.php?c=$c&date=$date#$id");
}
?>
