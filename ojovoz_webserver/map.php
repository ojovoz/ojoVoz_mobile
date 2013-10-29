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

if (isset($_SESSION['language'])) {
	$language = $_SESSION['language'];
} else {
	$language = 0;
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
	CheckMessagesRandomChannel($get_tags_from_subject,$mail_server,$dbh,$time_zone,$get_user_from_message_subject,$get_date_from_exif,$convert_to_mp3,$servpath,$sample_rate,$channel_folder,$static_map_width,$static_map_height,$google_maps_api_key,$get_reverse_geocoding,$ffmpeg_path);
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
	$correlated_tags=$correlated[0];
	$correlated_descriptors=$correlated[1];
} else {
	$correlated_tags="-1";
	$correlated_descriptors="-1";
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
  <title><? echo($global_channel_name); ?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <script type="text/javascript"
  	src="https://maps.googleapis.com/maps/api/js?key=<? echo($google_maps_api_key); ?>&sensor=false">
  </script>
  <script language="JavaScript" src="includes/general.js" language="javascript" type="text/javascript"></script>
  <link rel="SHORTCUT ICON" href="http://sautiyawakulima.net/favicon.ico">
</head>
<body bgcolor="<? echo($bgcolor); ?>" text="#<? echo($textcolor); ?>" link="#<? echo($textcolor); ?>" vlink="#<? echo($textcolor); ?>" alink="#<? echo($textcolor); ?>" leftmargin="50" marginwidth="50" onload="initialize()">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td width="62%"><font size="<? echo($ov_text_font_size); ?>" face="<? echo($ov_text_font); ?>"> 
      <h1 style="font-size: <? echo($ov_text_font_size_header."em"); ?>"> <img src="includes/logos/logoOjo_100px.png" width="100" height="97" border="0" align="absmiddle">
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
if ($has_rss && $crono==0) {
?>
        <a href="<? echo("rss.php?c=".$c); ?>"><img src="includes/images/feed-icon-28x28.png" title="<? echo($ov_rss_feed_title[$language]); ?>" alt="<? echo($ov_rss_feed_title[$language]); ?>" width="28" height="28" align="absmiddle" border="0"></a> 
        <? } ?>
      </h1>
      </font></td>
<td width="26%"><div align="right"><font size="<? echo($ov_text_font_size); ?>" face="<? echo($ov_text_font); ?>">
        <?
$languages = ShowLanguageOptions($main_page,$c,$date,$ov_languages,$language,$from);
echo($languages);
?>
        </font></div></td>
<td width="12%"><div align="right"> </div></td>
</tr>
</table>
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
<div id="map" style="width: 100%; height: 480px"></div>
<script type="text/javascript">
	var marker=null;
	var openMarker=null;
	var latLng=null;
	var infoWindow=new google.maps.InfoWindow();
	var bounds = new google.maps.LatLngBounds();
	
	var image = new Array();
	image[0]={url:'includes/images/marker00_ball.png', size:new google.maps.Size(16,17), origin:new google.maps.Point(0,0), anchor: new google.maps.Point(8,9)};
	image[1]={url:'includes/images/marker01_ball.png', size:new google.maps.Size(16,17), origin:new google.maps.Point(0,0), anchor: new google.maps.Point(8,9)};
	image[2]={url:'includes/images/marker02_ball.png', size:new google.maps.Size(16,17), origin:new google.maps.Point(0,0), anchor: new google.maps.Point(8,9)};
	image[3]={url:'includes/images/marker03_ball.png', size:new google.maps.Size(16,17), origin:new google.maps.Point(0,0), anchor: new google.maps.Point(8,9)};
	image[4]={url:'includes/images/marker04_ball.png', size:new google.maps.Size(16,17), origin:new google.maps.Point(0,0), anchor: new google.maps.Point(8,9)};
	image[5]={url:'includes/images/marker05_ball.png', size:new google.maps.Size(16,17), origin:new google.maps.Point(0,0), anchor: new google.maps.Point(8,9)};
	image[6]={url:'includes/images/marker06_ball.png', size:new google.maps.Size(16,17), origin:new google.maps.Point(0,0), anchor: new google.maps.Point(8,9)};
	image[7]={url:'includes/images/marker07_ball.png', size:new google.maps.Size(16,17), origin:new google.maps.Point(0,0), anchor: new google.maps.Point(8,9)};
	image[8]={url:'includes/images/marker08_ball.png', size:new google.maps.Size(16,17), origin:new google.maps.Point(0,0), anchor: new google.maps.Point(8,9)};
	image[9]={url:'includes/images/marker09_ball.png', size:new google.maps.Size(16,17), origin:new google.maps.Point(0,0), anchor: new google.maps.Point(8,9)};
	image[10]={url:'includes/images/marker10_ball.png', size:new google.maps.Size(16,17), origin:new google.maps.Point(0,0), anchor: new google.maps.Point(8,9)};
	
	var shadow={url:'includes/images/shadow_ball.png', size:new google.maps.Size(26,18), origin:new google.maps.Point(0,0), anchor: new google.maps.Point(8,9)};
	var shape={coord:[1,1,16,17], type:'rect'};
	
	function initialize(){
		latLng = new google.maps.LatLng(<? echo($default_latitude); ?>, <?  echo($default_longitude); ?>);
		var mapOptions = {center: latLng, zoom: 8, mapTypeId: google.maps.MapTypeId.ROADMAP};
    	var map = new google.maps.Map(document.getElementById("map"),mapOptions);
		
		<?
		$query=GetMessagesInMap($qWhere,$max_markers_on_map,$from,$dbh,$message);
		$result = mysql_query($query, $dbh);
		$i=0;
		while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
			$lat=preg_replace("/[^0-9.,\-]/", "", $row[4]);
			$lng=preg_replace("/[^0-9.,\-]/", "", $row[5]);
			echo("latLng = new google.maps.LatLng($lat, $lng);\n\r");
			echo("bounds.extend(latLng);\n\r");
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
			$color=intval(GetMessageColor($dbh,$row[14]));
			if($color>10) { $color=6; }
			echo("marker = new google.maps.Marker({position: latLng, map:map, shadow:shadow, shape:shape, icon:image[$color], html:'$label'});\n\r");
			echo("google.maps.event.addListener(marker, 'click', function () {\n\r");
			echo("infoWindow.setContent(this.html);\n\r");
			echo("infoWindow.open(map,this);\n\r");
			echo("});\n\r");
			if ($message==$row[14]) {
				echo("openMarker = marker;\n\r");
			}
		}
		?>
		map.fitBounds(bounds);
		
		if(openMarker!=null){
			google.maps.event.trigger(openMarker, 'click');
		}
	}
	
	
</script>
<br>
</body>
</html>