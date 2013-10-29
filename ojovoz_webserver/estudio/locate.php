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
		$map_filename=GrabMapImageLocate($latitude,$longitude,$aid,$static_map_width,$static_map_height,$google_maps_api_key);
		if ($map_filename!="") {
			$query="UPDATE attachment SET map_filename = '$map_filename' WHERE attachment_id=$aid";
			$result = mysql_query($query, $dbh);
		}
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
		$from=$_GET['date'];
	} else if (isset($_POST['date'])) {
		$from=$_POST['date'];
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
	$latitude=$point[0];
	$longitude=$point[1];
	$already_located=$point[2];
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title><? echo($global_channel_name); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script src="http://maps.google.com/maps?file=api&v=2&key=<? echo($google_maps_api_key); ?>" type="text/javascript"></script>
<script language="JavaScript" src="./../includes/general.js" language="javascript" type="text/javascript"></script>
</head>

<body>
<form name="form1" method="post" action="">
  <input name="address" type="text" id="address" style="color: <? echo($form_color); ?>; font-size: <? echo($font_size); ?>em;" value="<? if ($address=="") { echo($search_address_text); } else { echo($display_address); } ?>" onFocus="DeletePrompt(2,'<? echo($search_address_text); ?>')"> 
  <input name="search" type="submit" id="search" style="color: <? echo($form_color); ?>; font-size: <? echo($font_size); ?>em;" value="<? echo($search_button_text); ?>">
  <input name="c" type="hidden" id="c" value="<? echo($c); ?>">
  <input name="date" type="hidden" id="date" value="<? echo($date); ?>">
  <input name="id" type="hidden" id="id" value="<? echo($id); ?>">
  <input name="lang" type="hidden" id="lang" value="<? echo($lang); ?>">
</form>
<br>
<div id="map" style="width: 640px; height: 480px;"></div>
<script type="text/javascript">
//<![CDATA[

    if (GBrowserIsCompatible()) { 
	
	  var icon = new GIcon();
      icon.image = "./../includes/images/marker00.png";
      icon.shadow = "http://www.google.com/mapfiles/shadow50.png";
      icon.iconSize = new GSize(12, 20);
      icon.shadowSize = new GSize(24, 18);
      icon.iconAnchor = new GPoint(6, 18);
      icon.infoWindowAnchor = new GPoint(6, 1);
      icon.infoShadowAnchor = new GPoint(18, 16);
      icon.transparent = "http://www.google.com/intl/en_ALL/mapfiles/markerTransparent.png";
	  
	  var invisible = new GIcon();
      invisible.image = "./../includes/images/invisible.png";
      invisible.iconSize = new GSize(2,2);
      invisible.iconAnchor = new GPoint(0,0);
      invisible.infoWindowAnchor = new GPoint(0,0);

      var map = new GMap2(document.getElementById("map"));
      map.addControl(new GSmallZoomControl());
	  map.setCenter(new GLatLng(<? echo($latitude); ?>,<? echo($longitude); ?>));
	  <?
	  if ($address!="") { 
	  ?>
	  var geo = new GClientGeocoder();
	  geo.getLatLng("<? echo($address); ?>", function (point) { 
	  if (point) {
	  	map.setZoom(15);
		map.setCenter(point);
		map.clearOverlays();
		var marker = new GMarker(point,icon,true);
		map.addOverlay(marker);
		marker.openInfoWindowHtml("<b><font face=\"Arial, Helvetica, sans-serif\" color=\"<? echo($form_color); ?>\"><? echo($display_address); ?></font></b>");
		document.forms[1].latitude.value=point.y;
		document.forms[1].longitude.value=point.x;
		document.forms[1].located.value='1';
	  } else {
	    map.setCenter(new GLatLng(<? echo($latitude); ?>,<? echo($longitude); ?>));
		document.forms[0].address.value="<? echo($address_not_found_text); ?>"
		document.forms[1].located.value='0';
	  }
	  });
	  <?
	  }
	  if ($already_located==1) {
	  ?>
	  var newMarker = new GMarker(new GLatLng(<? echo($latitude); ?>,<? echo($longitude); ?>),icon,true);
  	  map.addOverlay(newMarker);
	  map.setZoom(15);
	  <?
	  } else {
	  ?>
	  map.setZoom(13);
	  <? 
	  }
	  ?>
	  
	  var listener = GEvent.addListener(map, "click", function(overlay, point) {
      	if (overlay) {
        	map.removeOverlay(overlay);
        } else {
			map.clearOverlays();
            createMarker(point);
			map.setCenter(point);
			document.forms[1].latitude.value=point.y;
			document.forms[1].longitude.value=point.x;
			document.forms[1].located.value='1';
        }
      });
	  
	  function createMarker(point) {
  	  	var newMarker = new GMarker(point,icon,true);
  		map.addOverlay(newMarker);
  		return newMarker;
      }
	  
	}
	  
//]]>
    </script>
<p>
<form action="" method="post">  <input name="locate" type="submit" id="locate" style="color: <? echo($form_color); ?>; font-size: <? echo($font_size); ?>em;" value="<? echo($locate_button_text); ?>"> 
  <input name="latitude" type="hidden" id="latitude" value="<? echo($latitude); ?>">
  <input name="longitude" type="hidden" id="longitude" value="<? echo($longitude); ?>">
  <input name="located" type="hidden" id="located" value="0">
  <input name="c" type="hidden" id="c" value="<? echo($c); ?>">
  <input name="date" type="hidden" id="date" value="<? echo($date); ?>">
  <input name="id" type="hidden" id="id" value="<? echo($id); ?>">
  <input name="lang" type="hidden" id="lang" value="<? echo($lang); ?>">
</form>
</p>
<p><font face="Courier New, Courier, mono" size="2"><a href="edit_channel.php?c=<? echo($c); ?>&date=<? echo($date); ?>#<? echo($id); ?>">Volver</a> </font>
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
