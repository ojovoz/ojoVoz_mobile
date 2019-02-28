<?
session_start();
include_once("./../includes/all.php");
$dbh = initDB();

import_request_variables("gp");

if (!isset($_GET['date'])) {
	$date=GetLatestDate($c,0,"",$channels_excluded_from_crono,$dbh,"");
} else {
	if ($_GET['date']=="") {
		$date=GetLatestDate($c,0,"",$channels_excluded_from_crono,$dbh,"");
	} else {
		$date = $_GET['date'];
	}
}

//$lang=GetUserLanguage($c,$dbh);
$lang=1;

if (isset($delete)) {
	if ($delete > 0) {
		DeleteMessage($dbh,$delete);
		$delete=-1;
	}
}

if (isset($edit)) {
	if (isset($delete_att)) {
		DeleteAttachments($delete_att,$dbh);
	}
	if (isset($approve_msg)) {
		ApproveMessage($m,1,$dbh);
	} else {
		ApproveMessage($m,0,$dbh);
	}
	/*
	if (isset($publish_att)) {
		PublishAttachments($m,$publish_att,$dbh);
	} else {
		PublishAttachments($m,"",$dbh);
	}
	*/
	ChangeMessageText($dbh,$m,$t);
	UpdateTags($dbh,$m,$tags);
	$date=$d;
}

?>
<html>
<head>
<title><? echo($global_channel_name); ?></title>
<meta http-equiv="Content-Type"
 content="text/html; charset=iso-8859-1">
<script src="./../includes/general.js"></script>
<script src="./../includes/audio.min.js"></script>
<script>
  audiojs.events.ready(function() {
    var as = audiojs.createAll();
  });
</script>
<script>
function confirmDelete(m,c,date,t) {
	if (confirm(t)) {
	      document.location = "edit_channel.php?c=" + c + "&delete=" + m + "&date=" + date
	}
} 
</script>
<style>
	.audiojs { width: <? echo($edit_audio_width); ?>px; background: #497A2B; }
</style>
</head>
<body bgcolor="#FFFFFF" text="#000000" link="#000000" vlink="#000000" alink="#000000">
<table align="left" border="0" width="100%">
<tr>
<td>
<form method="post" action="" style="border: 0px; padding: 0px;"><font size="<? echo($ov_text_font_size); ?>" face="<? echo($ov_text_font); ?>"> 
<?
$dates = CalculateChannelDates($c,0,"",$channels_excluded_from_crono,$dbh,"",$date,$ov_locales[$lang]);
$all_dates=$dates[0];
if (sizeof($all_dates)>1) {
?>
<select name="dates" style="color: <? echo($textcolor); ?>; background-color: <? echo($bgcolor); ?>; font-size: <? echo($font_size); ?>em;">
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
<input type="button" name="goto" value="<? echo($ov_goto_page_button_label[$lang]); ?>" onClick="ChangeDate(<? echo("this.form,'$edit_page',$c"); ?>)" style="color: <? echo($textcolor); ?>; background-color: <? echo($bgcolor); ?>; font-size: <? echo($font_size); ?>em;">
<?
} else if (sizeof($all_dates)==1) {
	$all_dates_parts=explode(",",$all_dates[0]);
	echo("<font size=\"$ov_text_font_size\" face=\"$ov_text_font\">$all_dates_parts[0]. ");
}
?>
<input name="c" type="hidden" id="c" value="<? echo($c); ?>">
<?
$days=explode(",",$dates[1]);
echo($ov_days_prefix[$lang]);
for ($i=0;$i<sizeof($days);$i++) {
	$day_parts=explode("-",$days[$i]);
	$day=$day_parts[2];
	if ($days[$i] == $date) {
		echo(" $day");
	} else {
		echo(" <a href=\"$edit_page?c=$c&date=".$days[$i]."#content\">$day</a>");
	}
}
?>
</font></form>
</td>
<td></td>
</tr>

<tr><td bgcolor="#<? echo($textcolor); ?>" height="3"></td></tr>
<?
$order=GetChannelAscendDescend($dbh,$c);
$query = "SELECT message_text, message_date, message_sender, message_subject, message_id, message_order FROM message WHERE channel_id = $c AND DATE(message_date) = '$date' ORDER BY message_order $order";
$result = mysql_query($query, $dbh);
$ct = 0;
while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
	$message_text = urldecode($row[0]);
	$message_date = $row[1];
	$message_sender = $row[2];
	$message_subject = $row[3];
	$message_id = $row[4];
	$message_order = $row[5];
	$val = $message_id;
?>
<tr><td><form name="form1" method="post" action="edit_channel.php?c=<? echo($c); ?>#<? echo($message_id); ?>" style="border: 0px; padding: 0px;">
  <a name="<? echo($message_id); ?>"></a><font face="<? echo($ov_text_font); ?>" size="<?  echo($ov_text_font_size); ?>">
  <input type="hidden" name="m" value="<? echo($message_id); ?>">
  <input name="d" type="hidden" id="d" value="<? echo($date); ?>"><?
  	$message_address=GetMessageAddress($message_id,$dbh);
	if (trim($message_address)=="") {
		if (HasImage($message_id,$dbh)) {
			$message_address="<a href=\"locate.php?id=$message_id&lang=$lang&c=$c&date=$date\">".$ov_locate_message_text[$lang]."</a>";
		}
	}
  ?>
  <? echo($message_date); ?> <? echo($message_address); ?></font><br>
<?
$tagList=GetMessageTagsCSV($message_id,$dbh);
?><font face="<? echo($ov_text_font); ?>" size="<?  echo($ov_text_font_size); ?>">
<? echo($ov_tag_input_text[$lang]); ?></font>  
 <input name="tags" type="text" id="tags" size="30" style="font-face: <? echo($ov_text_font); ?>; font-size: <? echo($font_size); ?>em;" value="<? echo($tagList); ?>">
<font color="#<? echo($textcolor); ?>" face="<? echo($ov_text_font); ?>" size="<?  echo($ov_text_font_size); ?>"><span id="Suggestions<? echo($val); ?>"></span></font>
<br>
<table border="0" cellpadding="1" cellspacing="1"><tr>
<?
    $att_query = "SELECT filename, original_filename, image_width, image_height, content_type, attachment_id, latitude, longitude, is_published FROM attachment WHERE message_id = $message_id ORDER BY content_type, attachment_id DESC";
	$att_result = mysql_query($att_query, $dbh);
	$n_attachments = mysql_num_rows($att_result);
	$n_check_photo = 0;
	$n_check_audio = 0;
	$n_check_video = 0;
	$n_publish_photo = 0;
	$second_row = array();
	$columns = 0;
	$first_av = 1;
	$attachment_letters="ABCDEFGHIJK";
	while ($att_row = mysql_fetch_array($att_result, MYSQL_NUM)) {
		$filename = "./../channels/".$att_row[0];
		$original_filename = $att_row[1];
		$image_width = $att_row[2];
		$image_height = $att_row[3];
		$content_type = $att_row[4];
        $attachment_id = $att_row[5];
		$latitude = $att_row[6];
		$longitude = $att_row[7];
		$is_published = $att_row[8];
		if ($latitude!="" && $longitude!="") {
			$is_located=true;
		} else {
			$is_located=false;
		}
		if(!$is_published){
			$approved=false;
		} else {
			$approved=true;
		}
		if ($content_type == 1 && $image_width > 1) {
			if ($image_width > $max_image_width_edit) {
				$height = $image_height*($max_image_width_edit/$image_width);
  				$width = $max_image_width_edit;
			} else {
				$height = $image_height;
  				$width = $image_width;
			}
			/*
			if ($is_published==1) {
				$publish_photo[$n_publish_photo] = "<input type=\"checkbox\" name=\"publish_att[]\" value=\"".$attachment_id."\" checked> $ov_photo_is_published_text[$lang]";
			} else {
				$publish_photo[$n_publish_photo] = "<input type=\"checkbox\" name=\"publish_att[]\" value=\"".$attachment_id."\"> $ov_photo_is_published_text[$lang]";
			}
			$n_publish_photo++;
			*/
?>
<td height="215" valign="middle"><img src="<? echo($filename); ?>" border="0" width="<? echo($width); ?>" height="<? echo($height); ?>"></td>
<? 

			if ($n_attachments > 1 || $message_text != "") {
				$check_photo[$n_check_photo] = "<input type=\"checkbox\" name=\"delete_att[]\" value=\"".$attachment_id."\"> $ov_delete_photo_text[$lang]";
				$n_check_photo++;
			}
			$second_row[$columns]=$attachment_id;
			$columns++;
////////////////////////////

		} else if($content_type != 1) {
			if ($first_av == 1) {
				$second_row[$columns]=-1;
				$columns++;
?>
<td valign="bottom"><font face="<? echo($ov_text_font); ?>" size="<? echo($ov_text_font_size); ?>"><? echo($ov_edit_message_text[$lang]); ?></font><br><textarea name="t" cols="32" rows="11" style="font-face: <? echo($ov_text_font); ?>; font-size: <? echo($font_size); ?>em;"><? echo(str_replace("<br>","
",stripslashes($message_text))); ?></textarea></td>
<?
				$first_av = 0;
			}
			$second_row[$columns]=-1;
			$columns++;
			if ($content_type == 3) {
				$width = $edit_video_width;
				$height = $edit_video_height;
				if ($n_attachments > 1 || $message_text != "") {
					$check_video[$n_check_video] = "<input type=\"checkbox\" name=\"delete_att[]\" value=\"".$attachment_id."\"> $ov_delete_video_text[$lang]";
					$n_check_video++;
				}
				$alt="<td valign=\"bottom\"><font face=\"$ov_text_font\" size=\"$ov_text_font_size\"><a href=\"$filename\" target=\"_blank\">$ov_video_link_text[$lang]</a></font></td>";
			} else {
				$width = $edit_audio_width;
				$height = $edit_audio_height;
				if ($n_attachments > 1 || $message_text != "") {
					$check_audio[$n_check_audio] = "<input type=\"checkbox\" name=\"delete_att[]\" value=\"".$attachment_id."\"> $ov_delete_audio_text[$lang]";
					$n_check_audio++;
				}
				$alt="<tr><td valign=\"bottom\"><audio src=\"$filename\" preload=\"none\"></audio></td></tr>";
			}
			echo($alt);
		}
	}
	if ($first_av == 1) {
		$second_row[$columns]=-1;
		$columns++;
?><td valign="bottom"><font face="<? echo($ov_text_font); ?>" size="<? echo($ov_text_font_size); ?>"><? echo($ov_edit_message_text[$lang]); ?></font><br><textarea name="t" cols="32" rows="11" style="font-face: <? echo($ov_text_font); ?>; font-size: <? echo($font_size); ?>em;"><? echo(preg_replace("<br>","
",stripslashes($message_text))); ?></textarea></td>
<?
	}
?>
</tr>
<tr>
<?
for($s_col=0;$s_col<sizeof($second_row);$s_col++){
?><td><?
	if($second_row[$s_col]!=-1) {
?>
<font face="<? echo($ov_text_font); ?>" size="<?  echo($ov_text_font_size); ?>"><a href="rotate.php?id=<? echo($second_row[$s_col]); ?>&c=<? echo($c); ?>&date=<? echo($date); ?>&mid=<? echo($message_id); ?>"><? echo($ov_rotate_photo_text[$lang]); ?></a><?
	if(GetPublishedDefault($c,$dbh)==0) {
		echo("<br>".$publish_photo[$s_col]);
	}
?></font>
<?
	}
?></td><?	
}
?>
</tr>
</table><br>
<input name="delete_message" type="button" id="delete_message" value="<? echo($ov_delete_message_text[$lang]); ?>" onClick="confirmDelete(<? echo("$message_id,$c,'$date','$ov_confirm_delete_message_text[$lang]'"); ?>)" style="font-face: <? echo($ov_text_font); ?>; font-size: <? echo($font_size); ?>em;">
<font face="<? echo($ov_text_font); ?>" size="<?  echo($ov_text_font_size); ?>">      
<input type="checkbox" name="approve_msg[]" value="<? echo($message_id); ?>" <? if ($approved) { echo("checked");} ?>> <? echo($ov_approve_message_text[$lang]); ?>
<?
		for ($i=0; $i<$n_check_photo;$i++) {
			if ($n_check_photo > 1) {
				echo($check_photo[$i])." ".substr($attachment_letters,$i,1)." ";
			} else {
				echo($check_photo[$i])." ";
			}
		}
		for ($i=0; $i<$n_check_audio;$i++) {
			if ($n_check_audio > 1) {
				echo($check_audio[$i])." ".substr($attachment_letters,$i,1)." ";
			} else {
				echo($check_audio[$i])." ";
			}
		}
		for ($i=0; $i<$n_check_video;$i++) {
			if ($n_check_video > 1) {
				echo($check_video[$i])." ".substr($attachment_letters,$i,1)." ";
			} else {
				echo($check_video[$i])." ";
			}
		}
		?>
</font>
	  <input name="edit" type="submit" id="edit" value="<? echo(strtoupper($ov_edit_channel_button_text[$lang])); ?>" style="font-face: <? echo($ov_text_font); ?>; font-size: <? echo($font_size); ?>em;">
</form>
	  </td></tr>
	  <tr><td bgcolor="#<? echo($textcolor); ?>" height="3"></td></tr>
<?
	$ct++;
}
$dates = CalculateChannelDates($c,0,"",$channels_excluded_from_crono,$dbh,"",$date,$ov_locales[$lang]);
?>

<tr>
<td>
<form method="post" action="" style="border: 0px; padding: 0px;"><font size="<? echo($ov_text_font_size); ?>" face="<? echo($ov_text_font); ?>"> 
<?
$all_dates=$dates[0];
if (sizeof($all_dates)>1) {
?>
<select name="dates" style="color: <? echo($textcolor); ?>; background-color: <? echo($bgcolor); ?>; font-size: <? echo($font_size); ?>em;">
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
<input type="button" name="goto" value="<? echo($ov_goto_page_button_label[$lang]); ?>" onClick="ChangeDate(<? echo("this.form,'$edit_page',$c"); ?>)" style="color: <? echo($textcolor); ?>; background-color: <? echo($bgcolor); ?>; font-size: <? echo($font_size); ?>em;">
<?
} else if (sizeof($all_dates)==1) {
	$all_dates_parts=explode(",",$all_dates[0]);
	echo("<font size=\"$ov_text_font_size\" face=\"$ov_text_font\">$all_dates_parts[0]. ");
}
?>
<input name="c" type="hidden" id="c" value="<? echo($c); ?>">
<?
$days=explode(",",$dates[1]);
echo($ov_days_prefix[$lang]);
for ($i=0;$i<sizeof($days);$i++) {
	$day_parts=explode("-",$days[$i]);
	$day=$day_parts[2];
	if ($days[$i] == $date) {
		echo(" $day");
	} else {
		echo(" <a href=\"$edit_page?c=$c&date=".$days[$i]."#content\">$day</a>");
	}
}
?>
</font></form></td>
</tr>
</table>
</body>
</html>