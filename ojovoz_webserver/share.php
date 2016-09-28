<?
//initialize
session_start();
include_once "includes/all.php";
$dbh=initDB();

if(isset($_GET['id'])){
	$id=$_GET['id'];
}

if (isset($_GET['l'])) {
	$_SESSION['language']=$_GET['l'];
}

if (isset($_SESSION['language'])) {
	$language = $_SESSION['language'];
} else {
	$language = 1;
}

$default_image=GetDefaultImage($id,$dbh);

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title><? echo($global_channel_name); ?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <meta property="og:image" content="http://sautiyawakulima.net/milpaalta/<? echo($default_image); ?>"/>
  <meta property="og:url" content="http://sautiyawakulima.net/milpaalta/compartir.php?id=<? echo($id); ?>"/>
  <meta property="og:title" content="<? echo($global_channel_name); ?>"/>
  <link rel="image_src" href="http://sautiyawakulima.net/milpaalta/<? echo($default_image); ?>"/>
<script language="JavaScript" src="includes/general.js" language="javascript" type="text/javascript"></script>
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
<body bgcolor="#FFFFFF" text="#000000" link="#000000" vlink="#000000" alink="#000000" leftmargin="50" marginwidth="50">
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
    | </h1>
</font> 
</form>
</font><hr><a name="content"></a>
<?
//channel message queries
//TODO: refine grouped cronos! --> DONE (check)
$query = GetMessagesQueryPermalink($id);
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
	$data_string = $ov_message_sender_text[$language]." ".$message_sender." ";
	$data_string.=$ov_message_date_text[$language]." ".$this_date." ";
	$message_tags = GetMessageTags($message_id,$default_channel_id,$main_page,'000000',$dbh,false,$language);
	if (strip_tags($message_tags)!="") {
		$image_title = $ov_image_title_text[$language]." ".strip_tags($message_tags);
	} else {
		$image_title = $ov_non_descripted_mesage_text[$language];
	}
	?>
	<a name="<? echo($message_id); ?>"></a><font size="<? echo($ov_text_font_size); ?>" face="<? echo($ov_text_font); ?>">
	<?
	echo($data_string."<br>");
	if($message_tags!="") {
		echo($ov_image_title_text[$language]." ".$message_tags."<br>");
	}
	echo("</font>");
	//get message attachments
	$att_query = "SELECT filename, original_filename, image_width, image_height, content_type, attachment_id, latitude, longitude, map_filename, map_address, is_published FROM attachment WHERE message_id = ".$message_id." ORDER BY content_type, attachment_id";
	$att_result = mysql_query($att_query, $dbh);
	$prev_type = "";
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
		
		if ($type == 1) {
			
			//show image
			if ($width>1  && $is_published == 1) {
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
			} else {
				$width=$static_map_width;
				$height=$static_map_height;
				$added_text=". ".$image_title;
			}
			if ($map_filename!="") {
				$prev_type = 1;
?><img src="maps/<? echo($map_filename); ?>" width="<? echo($static_map_width); ?>" height="<? echo($static_map_height); ?>" alt="<? echo(utf8_decode($map_address).$added_text); ?>" border="0" title="<? echo(utf8_decode($map_address).$added_text); ?>" /><?
			}
			if ($width==1 && $map_filename=="" && $image_title!="") {
				//echo("<font size=\"$ov_text_font_size\" face=\"$ov_text_font\">$image_title</font>");
			}
		} else {
			//show video or sound using quicktime
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
			} else {
				if ($prev_type==1) {
					echo("<br>");
				} else if ($prev_type=="" && $message_tags!="") {
					echo("<font size=\"$ov_text_font_size\" face=\"$ov_text_font\">".$ov_image_title_text[$language]." ".strip_tags($message_tags)."</font><br>");
				}
				$prev_type=$type;
				echo("<font size=\"$ov_text_font_size\" face=\"$ov_text_font\"><a href=\"$filename\">$ov_video_link_text[$language]</a></font><br>");
			}
		}
	}
	//show message text
	if ($prev_type == 1) {
		echo("<br>");
  	}
?><font size="<? echo($ov_text_font_size); ?>" face="<? echo($ov_text_font); ?>"><?	
	if ($message_text != "") {
		print("<div style=\"width:520px;text-align:justify\"><i>".stripslashes($message_text)."</i></div><br>");
		if ($c==$media_channel_id) {
			echo("<font size=\"$ov_text_font_size\" face=\"$ov_text_font\">$message_sender</font><br>");
		}		
	}
	/*
 	$nc=GetNComments($message_id,$dbh);
	if ($nc==0) {
		echo("<a href=\"comment.php?id=$message_id&c=$c&date=$date&from=$from\">".$ov_no_comments_text[$language]."</a><br><hr>");
	} else if ($nc==1) {
		echo("<a href=\"comment.php?id=$message_id&c=$c&date=$date&from=$from\">".$ov_1_comments_text[$language]."</a><br><hr>");
	} else {
		echo("<a href=\"comment.php?id=$message_id&c=$c&date=$date&from=$from\">$nc ".$ov_n_comments_text[$language]."</a><br><hr>");
	}
	*/
?><hr></font><br><?
}
?>
    </font>
</body>
</html>