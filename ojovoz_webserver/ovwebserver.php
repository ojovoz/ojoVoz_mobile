<?
//initialize
session_start();
include_once "includes/all.php";
$dbh=initDB();

if (isset($_GET['l'])) {
	$_SESSION['language']=$_GET['l'];
}

if (isset($_SESSION['language'])) {
	$language = $_SESSION['language'];
} else {
	$language = 1;
}

if (isset($_GET['c'])) {
	$c = $_GET['c'];
} else {
	$c = $default_channel_id;
}

if (!isset($_SESSION['selection_list']) || $_GET['r'] == 1) {
	$_SESSION['selection_list']=$c;
	$_SESSION['q']="";
} else {
	$a=explode(",",$_SESSION['selection_list']);
	if ($a[0]!=$c) {
		$_SESSION['selection_list']=$c;
		$_SESSION['q']="";
	}
}

if (isset($_POST['q'])) {
	if($_POST['q']!="" && $_POST['q']!=$ov_search_text[$language]) {
		$_SESSION['q']=$_POST['q'];
		$date=-1;
	} else {
		$_SESSION['q']="";
	}
	$_SESSION['selection_list']=$c;
} else if (isset($_GET['tag'])) {
	$_SESSION['selection_list']=UpdateSelectionList($_SESSION['selection_list'],$_GET['tag']);
	$_SESSION['q']="";
}

$page_filter=GetTagNames($_SESSION['selection_list'],$dbh,$language);
if ($page_filter != "") {
	$page_filter=$ov_page_filter_prefix[$language]." ".$page_filter;
} else if($_SESSION['q']!="") {
	$page_filter=$ov_search_text[$language].": ".$_SESSION['q'];
}

/////////////////////////////////////

if (($c >= 0) && ($c != "")) {
	
	//get channel attributes
	$query = "SELECT channel_mail,channel_pass,channel_folder,is_active,is_crono,messages_per_page,background_color, text_color,data_color,channel_description_color,channel_description,open_closed,has_thumbnails,show_time,show_date,show_sender,channel_name,is_ascending,show_tags,is_study,show_descriptors,tag_color,descriptor_color,tag_mode,show_map,show_legend,legend_color,parent_channel_id,allow_search,tag_minimum_date,has_rss FROM channel WHERE channel_id = $c";
	$result = mysql_query($query, $dbh);
	$row = mysql_fetch_array($result, MYSQL_NUM);
	$user = $row[0];
	$pass = $row[1];
	$folder = $row[2];
	$is_active = $row[3];
	$crono = $row[4];
	$nmessages = $row[5];
	$bgcolor = $row[6];
	$textcolor = $row[7];
	$datacolor = $row[8];
	$desccolor = $row[9];
	$description = urldecode($row[10]);
	$openclosed = $row[11];
	$has_thumbnails = $row[12];
	$show_time = $row[13];
	$show_date = $row[14];
	$show_sender = $row[15];
	$channel_name = $row[16];
    $ascending = $row[17];
	$show_tags = $row[18];
	$is_study=$row[19];
	$show_descriptors=$row[20];
	$tag_color="#".$row[21];
	$descriptor_color="#".$row[22];
	$tag_mode=$row[23];
	$show_map=$row[24];
	$show_legend=$row[25];
	$legend_color=$row[26];
	$parent_channel_id=$row[27];
	$allow_search=$row[28];
	$tag_minimum_date=$row[29];
	$has_rss=$row[30];
	
	if ($ascending != 0) {
		$order = "";
	} else {
  		$order = "DESC";
	}
	
	if ($crono == 1) {
		$children=GetChildChannels($c,$dbh);
	}

	//check for incoming messages
	if ($crono == 0 && $is_active == 1) {
		CheckMessages($user,$pass,$c,$folder,$get_tags_from_subject,$mail_server,$dbh,$time_zone,$get_user_from_message_subject,$get_date_from_exif,$convert_to_mp3,$servpath,$sample_rate,$channel_folder,$static_map_width,$static_map_height,$google_maps_api_key,$get_reverse_geocoding,$ffmpeg_path,$max_messages_from_inbox);
	} else if ($crono == 1 && $crono_random_check == true) {
		CheckMessagesRandomChannel($get_tags_from_subject,$mail_server,$dbh,$time_zone,$get_user_from_message_subject,$get_date_from_exif,$convert_to_mp3,$servpath,$sample_rate,$channel_folder,$static_map_width,$static_map_height,$google_maps_api_key,$get_reverse_geocoding,$ffmpeg_path,$max_messages_from_inbox);
	}
		
	//////////////////////////////////////////////////////
	
	//get search filter
	if($_SESSION['q']=="") {
		$qWhere=GetFilterMessageList($_SESSION['selection_list'],$dbh);
	} else {
		$qWhere=GetQMessageList($c,$_SESSION['q'],$dbh);
	}
	
	//TODO: refine for grouped cronos! DONE (check)
	//if channel is bound to tags, refine filter. thumbnails = false!
	$channel_filter=RefineChannelFilter($qWhere,$c,$crono,$dbh,$children);
	if ($channel_filter!="") {
		$qWhere=$channel_filter;
		$has_thumbnails=0;
	}	
	
	//TODO: refine for grouped cronos! --> NOT NEEDED (?)
	//get list of correlated tags & descriptors
	if ($qWhere!="") {
		$correlated=GetCorrelated($qWhere,$crono,$c,$dbh);
		$correlated_tags=$correlated;
		//$correlated_descriptors=$correlated[1];
	} else {
		$correlated_tags="-1";
		//$correlated_descriptors="-1";
	}
	
	if ($show_tags) {
		$tc=TagCloud($c,$crono,$max_tags_in_cloud,$channels_excluded_from_crono,$tag_toolbox_1_time,$tag_toolbox_n_times,$main_page,$textcolor,$bgcolor,$tag_min_size,$tag_max_size,$dbh,$_SESSION['selection_list'],$tag_cloud_title,$correlated_tags,$map_tag_mode,false,false,'0000-00-00',"",$tag_page_tag_hilite_color,$tag_page_hilite_size,$main_crono_channel,$language,$ov_locales);	
	} else {
		$tc[0]="";
		$tc[1]="";
	}
	
	//manage current section
	if (!isset($_GET['date'])) {
		$date=GetLatestDate($c,$crono,$qWhere,$channels_excluded_from_crono,$dbh,$children);
	} else {
		if ($_GET['date']=="" || $date==-1) {
			$date=GetLatestDate($c,$crono,$qWhere,$channels_excluded_from_crono,$dbh,$children);
		} else {
			$date = $_GET['date'];
		}
	}
	
	if(!isset($_GET['from'])) {
		$from=0;
	} else {
		if ($_GET['from']=="") {
			$from=0;
		} else {
			$from=$_GET['from'];
		}
	}
	
	$total = GetTotalMessages($c,$crono,$qWhere,$channels_excluded_from_crono,$dbh,$children,$date);

	/////////////////////////////////////////////////////////

}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=0.9">
  <title><? echo($global_channel_name); ?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script src="includes/general.js"></script>
<script src="includes/audio.min.js"></script>
<script>
  audiojs.events.ready(function() {
    var as = audiojs.createAll();
  });
</script>
<link rel="SHORTCUT ICON" href="http://sautiyawakulima.net/favicon.ico">
<style>
	.audiojs { width: <? echo($audio_width); ?>px; background: #497A2B; }
</style>
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
		/*if($menu_ids[$i] == $c) {
			$menu_link="";
		} else {*/
			$menu_link="<a href=\"".$main_page."?c=".$menu_ids[$i]."&r=1\">";
		//}
	} else {
		switch($menu_ids[$i]) {
			case "-1":
				$menu_link="<a href=\"".$main_page."?c=".$default_channel_id."\">";
				break;
			case "-2":
				$menu_link="";
				break;
			case "-3":
				$menu_link="<a href=\"map.php\">";
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
?>
    | 
    <input type="text" name="q" id="q" value="<? if ($_SESSION['q']!="") echo($_SESSION['q']); else echo($ov_search_text[$language]); ?>" style="width:160px; height:22px; border:2px solid #000000" onClick="SelectText();">
<?
	$languages = ShowLanguageOptions($main_page,$c,$date,$ov_languages,$language,$from);
	echo($languages);
?>
</h1>
</font> 
</form>
<hr>
<?
if ($show_tags) {
?><font size="<? echo($ov_text_font_size); ?>" face="<? echo($ov_text_font); ?>">
<ul id="cloud" style="padding: 1px; line-height:2.0em; text-align:justify; margin: 0;">
<? echo($tc[0]);
?></ul>
</font><?
}
?>
<font size="<? echo($ov_text_font_size); ?>" face="<? echo($ov_text_font); ?>">
<?

if ($c!=$media_channel_id) {
	$crono_channels=GetParentChannels($dbh);
	
	$current_crono=-1;
	echo("<h1 title=\"$ov_choose_crono_text\" style=\"font-size: ".$ov_text_font_size_header."em\">");
	if (sizeof($crono_channels)==1 && $is_study==0) {
		$crono_parts=explode(",",$crono_channels[0]);
		echo($ov_current_crono_text[$language]." ".$crono_parts[1]);
		$current_crono=$crono_parts[0];
	} else {
		for($i=0;$i<sizeof($crono_channels);$i++) {
			$crono_parts=explode(",",$crono_channels[$i]);
			if($crono_parts[0]==$c || $crono_parts[0]==$parent_channel_id) {
				$current_crono=$c;
				echo($ov_current_crono_text[$language]." ".$crono_parts[1]." <a href=\"".$main_page."?c=".$default_channel_id."#parent\">".$ov_choose_other_crono_text[$language]."</a>");
				break;
			}
		}
		if ($current_crono==-1) {
			echo($ov_choose_crono_text[$language]." ");
			for($i=0;$i<sizeof($crono_channels);$i++) {
				$crono_parts=explode(",",$crono_channels[$i]);
				echo("<a href=\"".$main_page."?c=".$crono_parts[0]."#children\">".$crono_parts[1]."</a> ");
			}
		}
	}
?></h1><?
}

if ($c!=$media_channel_id) {
	$crono_channels=GetParentChannels($dbh);
	$current_crono=-1;
	if (sizeof($crono_channels)==1 && $is_study==0) {
		$crono_parts=explode(",",$crono_channels[0]);
		$current_crono=$crono_parts[0];
	} else {
		for($i=0;$i<sizeof($crono_channels);$i++) {
			$crono_parts=explode(",",$crono_channels[$i]);
			if($crono_parts[0]==$c || $crono_parts[0]==$parent_channel_id) {
				$current_crono=$c;
				break;
			}
		}
	}
	echo("<h1 title=\"".$ov_choose_child_text[$language]."\" style=\"font-size: ".$ov_text_font_size_header."em\">");
	if ($current_crono!=-1 && $parent_channel_id==-1 && $is_study==0) {
		$child_channels=GetChildChannelsName($current_crono,$dbh);
		echo($ov_choose_child_text[$language]." ");
		for($i=0;$i<sizeof($child_channels);$i++) {
			$child_parts=explode(",",$child_channels[$i]);
			echo("<a href=\"".$main_page."?c=".$child_parts[0]."\">".$child_parts[1]."</a> ");
		}
	} else if ($parent_channel_id!=-1) {
		echo($ov_current_child_text[$language]." ".$channel_name." <a href=\"".$main_page."?c=".$parent_channel_id."\">".$ov_choose_other_child_text[$language]."</a>");
	}
} 
echo("</h1>");

if ($page_filter!="") {
	echo("<h1 title=\"".$page_filter."\" style=\"font-size: ".$ov_text_font_size_header."em\">");
	echo($page_filter);
	echo(" <a href=\"".$main_page."?c=$c&r=1\">".$ov_deselect_tags[$language]."</a>");
}
?></h1></font><hr><a name="content"></a>
<?
//channel message queries
//TODO: refine grouped cronos! --> DONE (check)
$query = GetMessagesQueryByDate($c,$date,$nmessages,$crono,$qWhere,$order,$channels_excluded_from_crono,$children,$from);
$result = mysql_query($query, $dbh);
//get all messages
$nm=0;
while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
	$nm++;
	$message_text = str_replace("\n","<br>",trim(urldecode($row[0])));
	if ($message_text!="") {
		if((strrpos($message_text,".")!=(strlen($message_text)-1)) && (strrpos($message_text,"!")!=(strlen($message_text)-1)) && (strrpos($message_text,"?")!=(strlen($message_text)-1))) {
			$message_text.=".";
		}
	}
	$message_date = $row[1];
	$message_sender = $row[2];
	$message_subject = $row[3];
	$message_id = $row[4];
	$date_parts = explode(' ', $message_date);
	$this_date = $date_parts[0];
	$d=strtotime($this_date);
	setlocale(LC_TIME, $ov_locales[$language]);
	$this_date=strftime("%A %e",$d)." $ov_day_month_prep[$language] ".strftime("%B",$d)." $ov_month_year_prep[$language] ".date("Y",$d);
	$time = $date_parts[1];
	$data_string="";
	if ($c!=$media_channel_id) {
		if ($show_sender==1) {
			$data_string = "<i>".$message_sender.":</i> ";
		}
		if ($show_date==1) {
			if($data_string=="") {
				$data_string=$ov_message_datetime_text[$language]." ".$ov_message_date_text[$language]." ".$this_date." ";
			} else {
				$data_string.=$ov_message_date_text[$language]." ".$this_date." ";
			}
		}
		if ($show_time==1) {
			if($data_string=="") {
				$data_string=$ov_message_datetime_text[$language]." ".$ov_message_time_text[$language]." ".$time;
			} else {
				$data_string.=$ov_message_time_text[$language]." ".$time;
			}
		}
	} else {
		$message_sender = '<a href="'.$message_subject.'" target="_blank">'.$message_sender.'</a>';
	}
	$message_tags = GetMessageTags($message_id,$c,$main_page,$textcolor,$dbh,false,$language);
	if (strip_tags($message_tags)!="") {
		$image_title = $ov_image_title_text[$language]." ".strip_tags($message_tags);
	} else {
		$image_title = $ov_non_descripted_mesage_text[$language];
	}
	?>
	<a name="<? echo($message_id); ?>"></a><font size="<? echo($ov_text_font_size); ?>" face="<? echo($ov_text_font); ?>">
	<?
	echo($data_string."<br>");
	if($show_tags == 1 && $message_tags!="") {
		echo($ov_image_title_text[$language]." ".$message_tags."<br>");
	}
	echo("</font>");
	//get message attachments
	$att_query = "SELECT filename, original_filename, image_width, image_height, content_type, attachment_id, latitude, longitude, map_filename, map_address, is_published FROM attachment WHERE message_id = ".$message_id." ORDER BY content_type, attachment_id";
	$att_result = mysql_query($att_query, $dbh);
	$prev_type = "";
	$pending = false;
	while ($att_row = mysql_fetch_array($att_result, MYSQL_NUM)) {
		$filename = "channels/".$att_row[0];
		$title = $att_row[1];
		$width = $att_row[2];
		$height = $att_row[3];
		$type = $att_row[4];
        $attachment_id = $att_row[5];
		$latitude = $att_row[6];
		$longitude = $att_row[7];
		$map_filename = $att_row[8];
		$map_address = $att_row[9];
		$is_published = $att_row[10];
		
		if (($type == 4) && ($discard_short_audio == true)) {
			if (filesize($filename) < $min_audio_size) {
				continue;
			}
		}
		
		if ($is_published){
			if ($type == 1) {
			
				//show image
				if ($width>1) {
					if ($width > $max_image_width_1 && $width < $max_image_width_2) {
						$height = $height*($max_image_width_2/$width);
						$width = $max_image_width_2;
					} else if ($width > $max_image_width_2) {
						$height = $height*($max_image_width_2/$width);
						$width = $max_image_width_2;
					} else {
						$height = $height*($max_image_width_2/$width);
						$width = $max_image_width_2;
					}
					$prev_type = 1;
					if ($latitude!="" && $longitude!="") { ?><a href="<? echo("map.php?m=$message_id"); ?>"> <? } ?>
					<img src="<? echo($filename); ?>" width="<? echo($width); ?>" height="<? echo($height); ?>" border="0" /><?
					if ($latitude!="" && $longitude!="") { ?></a><?
					}
					$added_text="";
				} 
		
			} else {
				//show audio
				if ($type==2) {
					if ($prev_type==1) {
						echo("<br>");
					} else if ($prev_type=="" && $message_tags!="") {
						echo("<font size=\"$ov_text_font_size\" face=\"$ov_text_font\">".$ov_image_title_text[$language]." ".strip_tags($message_tags)."</font><br>");
					}
					$prev_type=$type;
					if($ov_show_player) {
						$width = $audio_width;
						$height = $audio_height;
?><audio src="<? echo($filename); ?>" preload="none"></audio><?
					} else {
						echo("<font size=\"$ov_text_font_size\" face=\"$ov_text_font\"><a href=\"$filename\">$ov_audio_link_text[$language]</a></font><br>");
					}
				} 
			}
		} else {
			if(!$pending){
				echo("<font size=\"$ov_text_font_size\" face=\"$ov_text_font\">$ov_message_pending_approval[$language]</font><br>");
				$pending=true;
			}
		}
	}
	//show message text
	if ($prev_type == 1) {
		echo("<br>");
  	}
?><font size="1" face="<? echo($ov_text_font); ?>"><?	
	if(!$pending) { echo('<a href="share.php?id='.$message_id.'" target="_blank">'.$ov_share_page_text[$language].'</a><br><br>'); }
?></font><font size="<? echo($ov_text_font_size); ?>" face="<? echo($ov_text_font); ?>"><?		
	if ($message_text != "" && !$pending) {
		print("<div style=\"width:520px;text-align:justify\"><i>".stripslashes($message_text)."</i></div><br>");
		if ($c==$media_channel_id) {
			echo("<font size=\"$ov_text_font_size\" face=\"$ov_text_font\">$message_sender</font><br>");
		}		
	}
	
	if(!$pending) {
		$nc=GetNComments($message_id,$dbh);
		if ($nc==0) {
			echo("<a href=\"comment.php?id=$message_id&c=$c&date=$date&from=$from\">".$ov_no_comments_text[$language]."</a><br>");
		} else if ($nc==1) {
			echo("<a href=\"comment.php?id=$message_id&c=$c&date=$date&from=$from\">".$ov_1_comments_text[$language]."</a><br>");
		} else {
			echo("<a href=\"comment.php?id=$message_id&c=$c&date=$date&from=$from\">$nc ".$ov_n_comments_text[$language]."</a><br>");
		}
	}
	
?><hr></font><br><?
}
if ($nm==0) {
?>
<font size="<? echo($ov_text_font_size); ?>" face="<? echo($ov_text_font); ?>"><? echo($channel_name." ".$ov_no_messages_text[$language]); ?><br>
</font> 
<?
}
$dates = CalculateChannelDates($c,$crono,$qWhere,$channels_excluded_from_crono,$dbh,$children,$date,$ov_locales[$language]);
?>
<form method="post" action=""><h1>
<font size="<? echo($ov_text_font_size); ?>" face="<? echo($ov_text_font); ?>"> 
<?
$links=0;
if ($from>0) {
	$prev=$from-$nmessages;
	$links=1;
	echo("<a href=\"$main_page?c=$c&date=$date&from=$prev\">$ov_previous_page_text[$language]</a> ");
}
if (($from+$nmessages)<$total) {
	$next=$from+$nmessages;
	$links=1;
	echo("<a href=\"$main_page?c=$c&date=$date&from=$next\">$ov_next_page_text[$language]</a> ");
}
if ($links==1) {
	echo("<br><br>");
}

$all_dates=$dates[0];
if (sizeof($all_dates)>1) {
?>
<select name="dates" style="color: <? echo($textcolor); ?>; background-color: <? echo($bgcolor); ?>; font-size: <? echo($font_size); ?>em;" onChange="ChangeDate(<? echo("this.form,'$main_page',$c"); ?>)">
<?
	for($i=0;$i<sizeof($all_dates);$i++) {
		$all_dates_parts=explode(",",$all_dates[$i]);
		if (strpos($all_dates_parts[1],"*")>0) {
			$all_dates_parts[1] = str_replace("*","",$all_dates_parts[1]);
			echo("<option value=\"".$all_dates_parts[1]."\" selected>".$all_dates_parts[0]."</option>");
		} else {
			echo("<option value=\"".$all_dates_parts[1]."\">".$all_dates_parts[0]."</option>");
		}
	}
?>
</select>
    <?
} else if (sizeof($all_dates)==1) {
	$all_dates_parts=explode(",",$all_dates[0]);
	echo("<font size=\"$ov_text_font_size\" face=\"$ov_text_font\">$all_dates_parts[0]. ");
}
if ($nm>0) {
	$days=explode(",",$dates[1]);
	echo($ov_days_prefix[$language]);
	for ($i=0;$i<sizeof($days);$i++) {
		$day_parts=explode("-",$days[$i]);
		$day=$day_parts[2];
		if ($days[$i] == $date) {
			echo(" $day");
		} else {
			echo(" <a href=\"$main_page?c=$c&date=".$days[$i]."#content\">$day</a>");
		}
	}
}
?>
    </font></h1>
</form>
</body>
</html>