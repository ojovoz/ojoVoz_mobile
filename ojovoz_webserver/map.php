<?
header("Cache-Control: no-cache, must-revalidate");
//initialize
session_start();
include_once "includes/all.php";
$dbh=initDB();

$click=-1;

if (isset($_SESSION['kiosk'])) {
	$kiosk = $_SESSION['kiosk'];
} else {
	$kiosk=false;
}

if (isset($_GET['l'])) {
	$_SESSION['language']=$_GET['l'];
}

if (isset($_GET['m'])) {
	$message=$_GET['m'];
} else {
	$message=-1;
}

if (isset($_GET['from']) && isset($_GET['to'])){
	$from=$_GET['from'];
	$to=$_GET['to'];
} else {
	$from="";
	$to="";
}

if (isset($_SESSION['language'])) {
	$language = $_SESSION['language'];
} else {
	$language = 1;
}

if (isset($_GET['s'])) {
	$_SESSION['surf_mode']=$_GET['s'];
}

if (!isset($_SESSION['surf_mode'])) {
	$_SESSION['surf_mode']=1;
} 

if(isset($_GET['cc'])) {
	$_SESSION['cc']=$_GET['cc'];
}
	
if(isset($_SESSION['cc'])) {
	switch($_SESSION['cc']) {
		case "0":
			$bgcolor="FFFFFF";
			$textcolor="000000";
			$datacolor="000000";
			$desccolor="000000";
			$tag_color="#000000";
			$descriptor_color="#000000";
			break;
		case "1":
			$bgcolor="CCCCFF";
			$textcolor="000066";
			$datacolor="000066";
			$desccolor="000066";
			$tag_color="#000066";
			$descriptor_color="#000066";
			break;
		case "2":
			$bgcolor="FAE49D";
			$textcolor="000000";
			$datacolor="000000";
			$desccolor="000000";
			$tag_color="#000000";
			$descriptor_color="#000000";
			break;
		case "3":
			$bgcolor="000000";
			$textcolor="FFFF00";
			$datacolor="FFFF00";
			$desccolor="FFFF00";
			$tag_color="#FFFF00";
			$descriptor_color="#FFFF00";
			break;
	}
}

if (!isset($bgcolor)) {
	$bgcolor="FFFFFF";
	$textcolor="000000";
}

if ($crono_random_check == true) {
	CheckMessagesRandomChannel($get_tags_from_subject,$mail_server,$dbh,$time_zone,$get_user_from_message_subject,$get_date_from_exif,$convert_to_mp3,$servpath,$sample_rate,$channel_folder,$static_map_width,$static_map_height,$google_maps_api_key,$get_reverse_geocoding,$ffmpeg_path,$max_messages_from_inbox);
}

if (!isset($_SESSION['selection_list']) || $_GET['r'] == 1) {
	$_SESSION['selection_list']=-3;
} else {
	$a=explode(",",$_SESSION['selection_list']);
	if ($a[0]!=-3) {
		$_SESSION['selection_list']=-3;
	}
}
$selection_list=$_SESSION['selection_list'];

//address search
if (isset($_POST['search']) && isset($_POST['address'])) {
	if ($_POST['address']!=$search_address_text && $_POST['address']!="") {
		$address=$_POST['address'];
		$display_address=$address;
		if ($prefered_city!="" && $use_prefered_city==true) {
			if (strpos($address,$prefered_city)===false) {
				$address=$address.", ".$prefered_city;
			}
		}
	}
} else {
	$address="";
	//initialize search variables
	if (isset($_GET['tag'])) {
		$_SESSION['selection_list']=UpdateSelectionList($selection_list,$_GET['tag']);
	}

	if (isset($_GET['d'])) {
		$_SESSION['selection_list']=UpdateSelectionList($selection_list,-$_GET['d']);
	}
}

$selection_list=$_SESSION['selection_list'];

//get search filter
$qWhere=GetFilterMessageList($selection_list,$dbh);
$qWhere=GetFilterMapMessageList($qWhere,$dbh);
//get list of correlated tags & descriptors
if ($qWhere!="") {
	$correlated=GetCorrelated($qWhere,1,-3,$dbh);
	$correlated_tags=$correlated;
	//$correlated_descriptors=$correlated[1];
} else {
	$correlated_tags="-1";
	//$correlated_descriptors="-1";
}
/////////////////////////////
$page_filter=GetTagNames($_SESSION['selection_list'],$dbh,$language);
if ($page_filter != "") {
	$page_filter=$ov_page_filter_prefix[$language]." ".$page_filter;
}

$total_messages=GetNMessagesInMap($qWhere,$dbh);

if ($show_tags_in_map) {
	$tc=TagCloud(-3,1,$max_tags_on_map,$channels_excluded_from_crono,$tag_toolbox_1_time,$tag_toolbox_n_times,'map.php',$textcolor,$bgcolor,$tag_min_size,$tag_max_size,$dbh,$selection_list,$tag_cloud_title,$correlated_tags,$map_tag_mode,$show_legend_in_map,false,'0000-00-00',"",$tag_page_tag_hilite_color,$tag_page_hilite_size,$main_crono_channel,$language,$ov_locales);
} else {
	$tc[0]="";
	$tc[1]="";
}
if ($show_descriptors_in_map) {
	$dc=DescriptorCloud($dbh,-3,1,$map_descriptor_color,substr($map_background_color,1),$descriptor_language,$descriptor_toolbox_1_time,$descriptor_toolbox_n_times,'map.php',$descriptor_category,$descriptor_cloud_refresh,$selection_list,$descriptor_cloud_title,$tag_min_size,$tag_max_size,$correlated_descriptors);
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=0.9">
  <title><? echo($global_channel_name); ?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <link rel="stylesheet" href="includes/leaflet/leaflet.css" />
  <script src="includes/leaflet/leaflet.js"></script>
  <script language="JavaScript" src="includes/general.js" language="javascript" type="text/javascript"></script>
  <link rel="SHORTCUT ICON" href="http://sautiyawakulima.net/favicon.ico">
</head>
<body bgcolor="#<? echo($bgcolor); ?>" text="#<? echo($textcolor); ?>" link="#<? echo($textcolor); ?>" vlink="#<? echo($textcolor); ?>" alink="#<? echo($textcolor); ?>" leftmargin="50" marginwidth="50">
<form action="" method="post">
<font size="<? echo($ov_text_font_size); ?>" face="<? echo($ov_text_font); ?>"> 
  <h1 style="font-size: <? echo($ov_text_font_size_header."em"); ?>"> 
    <?
$menu_ids=explode(",",$ov_menu_ids);
$menu_titles=explode(",",$ov_menu_titles[$language]);
for($i=0;$i<sizeof($menu_ids);$i++) {
	if($menu_ids[$i] > 0) {
		if($menu_ids[$i] == $c) {
			$menu_link="";
		} else {
			$menu_link="<a href=\"".$main_page."?c=".$menu_ids[$i]."\">";
		}
	} else {
		switch($menu_ids[$i]) {
			case "-1":
				$menu_link="<a href=\"".$main_page."?c=".$default_channel_id."\">";
				break;
			case "-2":
				$menu_link="";
				break;
			case "-3":
				$menu_link="<a href=\"map.php?r=1\">";
				break;
			case "-4":
				$menu_link="<a href=\"tags.php#tags\">";
				break;
			case "-5":
				$menu_link="<a href=\"about.php#about\">";
				break;
			case "-6":
				$menu_link="<a href=\"./edit/index.php?prev=1\">";
				break;
		}
	}
	echo($menu_link.$menu_titles[$i]);
	if ($menu_link != "") {
		echo("</a> ");
	} else {
		echo(" ");
	}
}
$languages = ShowLanguageOptions("map.php",$c,$date,$ov_languages,$language,$from);
echo(" || ".$languages);
?>
  </h1>
</font></form>
<hr>
<? if ($show_tags_in_map) {
?>
<font face="Arial, Helvetica, sans-serif">
<ul id="cloud" style="padding: 1px; line-height:1.5em; text-align:justify; margin: 0;">
<? echo($tc[0]);
?></ul>
</font>
<?
if ($page_filter!="") {
?>
<br><font size="<? echo($ov_text_font_size); ?>" face="<? echo($ov_text_font); ?>">
<?
	echo($page_filter);
	echo(" <a href=\"map.php?r=1\">".$ov_deselect_tags[$language]."</a>");
}
?>
</font><hr>
<?
}
if ($show_descriptors_in_map) {
?>
<font face="Arial, Helvetica, sans-serif">
<ul id="cloud2" style="padding: 1px; line-height:1.5em; text-align:justify; margin: 0;">
<? echo($dc);
?></ul>
</font>
<?
}
if (($show_tags_in_map==1 && $tc[0]!="") || $show_descriptors_in_map==1) {
	echo("<br>");
}
?>
<div id="map" style="width: 100%; height: 600px"></div>
<script type="text/javascript">
	var ovMap = L.map('map').setView([<? echo($default_latitude); ?>, <?  echo($default_longitude); ?>], 13);
	
	var icon = new Array();
	var n;
	for(var i=0;i<=23;i++){
		if(i<10){
			n='0'+i.toString();
		} else {
			n=i.toString();
		}
		icon[i] = L.icon({iconUrl:'includes/images/marker'+ n +'.png',shadowURL:'includes/images/shadow.png',iconSize:[34,54],shadowSize:[63,54],iconAnchor:[19,53],shadowAnchor:[17,53],popupAnchor:[-1,-55]});
	}
	
	
	L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=<? echo($mapbox_api_key); ?>', {
		maxZoom: 18,
		attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, ' +
			'<a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
			'Imagery (C) <a href="http://mapbox.com">Mapbox</a>',
		id: '<? echo($mapbox_id); ?>',
		accessToken: '<? echo($mapbox_api_key); ?>'
	}).addTo(ovMap);
	
	var latLng = null;
	var marker = null;
	var openMarker = null;
	var latLngOpenMarker = null;
	var markersLayer = new L.featureGroup();
	
	<?
		$min_lat=999.99;
		$max_lat=0;
		$min_lng=999.99;
		$max_lng=0;
		$query=GetMessagesInMap($qWhere,$max_markers_on_map,$dbh,$message,$from,$to);
		$result = mysql_query($query, $dbh);
		$i=0;
		while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
			$lat=preg_replace("/[^0-9.,\-]/", "", $row[4]);
			$lng=preg_replace("/[^0-9.,\-]/", "", $row[5]);
			echo("latLng = L.latLng($lat, $lng);");
			if(floatval($lat)<$min_lat){
				$min_lat=floatval($lat);
			} else if(floatval($lat)>$max_lat){
				$max_lat=floatval($lat);
			}
			if(floatval($lng)<$min_lng){
				$min_lng=floatval($lng);
			} else if(floatval($lng)>$max_lng){
				$max_lng=floatval($lng);
			}
			
			if ($row[11]==1) {
				$asc="";
			} else {
				$asc="DESC";
			}
			if ($row[6] == 1) {
				if ($row[9] > 200) {
					$h=$row[10]*(200/$row[9]);
					$w=200;
				}
				$d=explode(" ",$row[1]);
				$img="<a href=\"calc.php?c=".$row[7]."&date=".$d[0]."&id=".$row[14]."\"><img src=\"channels/".$row[3]."\" height=\"$h\" width=\"$w\" border=\"0\"></a>";
			} else {
				$img = "";
			}
			$label = $img."<br><font color=\"$map_data_color\" size=\"2\" face=\"Arial, Helvetica, sans-serif\"> ".$row[1]."</font>";
			$color=ltrim($row[14],"0");
			$color=intval(GetMessageColor($dbh,$color));
			echo("marker = L.marker(latLng,{icon:icon[$color]}).addTo(markersLayer);\n");
			echo("marker.bindPopup('".$label."');\n");
			
			if ($message==$row[14]) {
				echo("openMarker = marker;");
				echo("latLngOpenMarker = L.latLng($lat, $lng);");
			}
		}
		?>	
		
	ovMap.addLayer(markersLayer);
	ovMap.fitBounds(markersLayer.getBounds(), {padding:[50,50]});
	if(openMarker!=null){
		setTimeout(function(){ ovMap.panTo(latLngOpenMarker,{animate:true});
		openMarker.openPopup();
		}, 1000);
	}
	
</script>
<?
$dates=CalculateMapDates($qWhere,$dbh,$ov_locales[$language]);
$dates_from=$dates[0];
$dates_to=$dates[1];
if(sizeof($dates_from)>1 && sizeof($dates_to)>1){
?>
<p><font size="<? echo($ov_text_font_size); ?>" face="<? echo($ov_text_font); ?>">
<? echo($ov_map_dates_between[$language]." "); ?>
<select name="date_from" id="date_from" style="color: <? echo($textcolor); ?>; background-color: <? echo($bgcolor); ?>; font-size: <? echo($font_size); ?>em;">
<?
	for($i=0;$i<sizeof($dates_from);$i++) {
		$dates_parts=explode(",",$dates_from[$i]);
		if (($i==(sizeof($dates_from)-1) && $from=="") || ($from==$dates_parts[1])) {
			echo("<option value=\"".$dates_parts[1]."\" selected>".$dates_parts[0]."</option>");
		} else {
			echo("<option value=\"".$dates_parts[1]."\">".$dates_parts[0]."</option>");
		}
	}
?>
</select>
<? echo(" ".$ov_map_dates_and[$language]." "); ?>
<select name="date_to" id="date_to" style="color: <? echo($textcolor); ?>; background-color: <? echo($bgcolor); ?>; font-size: <? echo($font_size); ?>em;">
<?
	for($i=0;$i<sizeof($dates_to);$i++) {
		$dates_parts=explode(",",$dates_to[$i]);
		if (($i==0 && $to=="") || ($to==$dates_parts[1])) {
			echo("<option value=\"".$dates_parts[1]."\" selected>".$dates_parts[0]."</option>");
		} else {
			echo("<option value=\"".$dates_parts[1]."\">".$dates_parts[0]."</option>");
		}
	}
?>
</select>&nbsp;
<input type="button" name="dates" id="dates" value="<? echo($ov_map_dates_button[$language]); ?>" style="font-size: <? echo($font_size); ?>em;">
<script type="text/javascript">
    document.getElementById("dates").onclick = function () {
		select_from=document.getElementById("date_from");
		date_from=select_from.options[date_from.selectedIndex].value;
		select_to=document.getElementById("date_to");
		date_to=select_to.options[date_to.selectedIndex].value;
        location.href = "map.php?from=" + date_from + "&to=" + date_to;
    };
</script>
</font></p>
<? } ?>
</body>
</html>