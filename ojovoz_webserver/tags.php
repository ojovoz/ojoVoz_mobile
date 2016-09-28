<?
session_start();
include_once "includes/all.php";
if (isset($_SESSION['kiosk'])) {
	$kiosk = $_SESSION['kiosk'];
} else {
	$kiosk=false;
}
$dbh=initDB();

/*
if (isset($_GET['l'])) {
	$_SESSION['language']=$_GET['l'];
}
*/

//if (isset($_SESSION['language'])) {
//	$language = $_SESSION['language'];
//} else {
	$language = 0;
//}

if (isset($_SESSION['selection_list'])) {
	$_SESSION['selection_list']="";
}

if (isset($_GET['t'])) {
	$tag_mode=$_GET['t'];
} else {
	$tag_mode=0;
}

if(isset($_GET['cc'])) {
	$_SESSION['cc']=$_GET['cc'];
}
	
if(isset($_SESSION['cc'])) {
	switch($_SESSION['cc']) {
		case "0":
			$tag_page_background_color="#FFFFFF";
			$textcolor="#000000";
			$tag_page_tag_color="#000000";
			$tag_page_tag_hilite_color="#000000";
			break;
		case "1":
			$tag_page_background_color="#CCCCFF";
			$textcolor="#000066";
			$tag_page_tag_color="#000066";
			$tag_page_tag_hilite_color="#000066";
			break;
		case "2":
			$tag_page_background_color="#FAE49D";
			$textcolor="#000000";
			$tag_page_tag_color="#000000";
			$tag_page_tag_hilite_color="#000000";
			break;
		case "3":
			$tag_page_background_color="#000000";
			$textcolor="#FFFF00";
			$tag_page_tag_color="#FFFF00";
			$tag_page_tag_hilite_color="#FFFF00";
			break;
		}
} else {
	$tag_page_background_color="#FFFFFF";
	$textcolor="#000000";
	$tag_page_tag_color="#000000";
	$tag_page_tag_hilite_color="#000000";
}

if ($crono_random_check == true) {
	CheckMessagesRandomChannel($get_tags_from_subject,$mail_server,$dbh,$time_zone,$get_user_from_message_subject,$get_date_from_exif,$convert_to_mp3,$servpath,$sample_rate,$channel_folder,$static_map_width,$static_map_height,$google_maps_api_key,$get_reverse_geocoding,$ffmpeg_path,$max_messages_from_inbox);
}

$tc=TagCloud(-4,1,10000,$channels_excluded_from_crono,$tag_toolbox_1_time,$tag_toolbox_n_times,$main_page,$tag_page_tag_color,$tag_page_background_color,$tag_min_size_tag_page,$tag_max_size_tag_page,$dbh,"",$tag_cloud_title,-1,$tag_mode,false,false,'0000-00-00',"",$tag_page_tag_hilite_color,$tag_page_hilite_size,$main_crono_channel,$language,$ov_locales);	

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title><? echo($global_channel_name.". ".$ov_tag_page_title_prefix[$language]." ".$tc[1]); ?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <script language="JavaScript" src="includes/general.js" language="javascript" type="text/javascript"></script>
</head>
<body bgcolor="<? echo($tag_page_background_color); ?>" text="<? echo($textcolor); ?>" alink="<? echo($textcolor); ?>" vlink="<? echo($textcolor); ?>" link="<? echo($textcolor); ?>" leftmargin="50" marginwidth="50">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
<td width="62%"><font size="<? echo($ov_text_font_size); ?>" face="<? echo($ov_text_font); ?>">
      <h1 style="font-size: <? echo($ov_text_font_size_header."em"); ?>"> 
        <?
$menu_ids=explode(",",$ov_menu_ids);
$menu_titles=explode(",",$ov_menu_titles[$language]);
for($i=0;$i<sizeof($menu_ids);$i++) {
	if($menu_ids[$i] > 0) {
		$menu_link="<a href=\"".$main_page."?c=".$menu_ids[$i]."\">";
	} else {
		switch($menu_ids[$i]) {
			case "-1":
				$menu_link="<a href=\"".$main_page."?c=".$default_channel_id."\">";
				break;
			case "-2":
				$menu_link="<a href=\"http://www.megafone.net\">";
				break;
			case "-3":
				$menu_link="<a href=\"map.php\">";
				break;
			case "-4":
				$menu_link="";
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
?>
      </h1>
</font></td>
<td width="26%"><div align="right"><font size="<? echo($ov_text_font_size); ?>" face="<? echo($ov_text_font); ?>"><?
//$languages = ShowLanguageOptions("tags.php",$t,"",$bv_languages,$language);
//echo($languages);
?></font></div></td>
<td width="12%"><div align="right"> </div></td>
</tr>
</table><hr>
<font size="<? echo($ov_text_font_size); ?>" face="<? echo($ov_text_font); ?>"><a name="tags"></a>
<? 
echo("<h1 style=\"font-size: ".$ov_text_font_size_header."em\">");
//echo($bv_tags_mode_text[$language]." "); 
//$t_modes=explode(",",$tag_modes[$language]);
//if ($tag_mode==0) {
//	echo($t_modes[0].". $bv_tags_other_mode_text[$language] <a href=\"tags.php?t=1#tags\">".$t_modes[1]."</a>");
//} else {
//	echo($t_modes[1].". $bv_tags_other_mode_text[$language] <a href=\"tags.php?t=0#tags\">".$t_modes[0]."</a>");
//}
echo("</h1>");?>
</font>
<font face="<? echo($ov_text_font); ?>">
<ul id="cloud" style="padding: 1px; line-height:<? echo($tag_page_line_height); ?>em; text-align:justify; margin: 0;">
<? echo($tc[0]);
?></ul>
</font>
<br>
</body>
</html>