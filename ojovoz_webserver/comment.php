<?
//initialize
session_start();
include_once "includes/all.php";
$dbh=initDB();

$error_msg="";

if (isset($_GET['l'])) {
	$_SESSION['language']=$_GET['l'];
}

if (isset($_SESSION['language'])) {
	$language = $_SESSION['language'];
} else {
	$language = 0;
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
} else {
	$bgcolor="FFFFFF";
	$textcolor="000000";
	$datacolor="000000";
	$desccolor="000000";
	$tag_color="#000000";
	$descriptor_color="#000000";
}

if (isset($_GET['id'])) {
	$id=$_GET['id'];
	$c=$_GET['c'];
	$date=$_GET['date'];
	$from=$_GET['from'];
} else if (isset($_POST['id']) && isset($_POST['add'])) {
	$id=$_POST['id'];
	$alias=$_POST['alias'];
	$pass=$_POST['pass'];
	$comment=$_POST['comment'];
	$c=$_POST['c'];
	$date=$_POST['date'];
	$from=$_POST['from'];
	$ret=VerifyUserAccess($alias,$pass,$dbh);
	if ($ret) {
		if (trim($comment)!="") {
			$comment_date=GetChannelDate($time_zone);
			$query="INSERT INTO comment (message_id, user_id, comment_date, comment_text) VALUES ($id, $alias, '$comment_date', '$comment')";
			$result = mysql_query($query, $dbh);
			header("location: $main_page?c=$c&date=$date&from=$from#$id");
			break;
		}
	} else {
		$error_msg=$ov_comment_wrong_password[$language];
	}
}
?>
<html>
<head>
<title><? echo($global_channel_name.": ".$ov_comments_page_text[$language]); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" src="includes/swfobject.js" language="javascript" type="text/javascript"></script>
</head>

<body bgcolor="#<? echo($bgcolor); ?>" text="#<? echo($textcolor); ?>" link="#<? echo($textcolor); ?>" vlink="#<? echo($textcolor); ?>" alink="#<? echo($textcolor); ?>" leftmargin="50" marginwidth="50">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr> 
    <td width="42%"><font size="<? echo($ov_text_font_size); ?>" face="<? echo($ov_text_font); ?>"><strong><a href="<? echo($main_page."?c=$c&date=$date#$id"); ?>"><? echo($ov_comment_back_link[$language]) ?></a></strong>
        </font></td>
    <td width="42%"><div align="right"><font size="<? echo($ov_text_font_size); ?>" face="<? echo($ov_text_font); ?>">
      <?
$languages = ShowLanguageOptions("comment.php",$c,"",$ov_languages,$language,$date,$id);
echo($languages);
?>
      </font></div></td>
    <td width="12%"><div align="right"> </div></td>
  </tr>
</table>
<br>
<font size="<? echo($ov_text_font_size); ?>" face="<? echo($ov_text_font); ?>"><a name="parent"></a>
<?
$query = "SELECT message.message_text, message.message_date, message.message_sender, message.message_subject, message.message_id FROM message WHERE message_id = $id";
$result = mysql_query($query, $dbh);
if ($row = mysql_fetch_array($result, MYSQL_NUM)) {
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
	$data_string = $ov_message_sender_text[$language]." ";
	$data_string.=$ov_message_date_text[$language]." ".$this_date." ";
	$data_string.=$ov_message_time_text[$language]." ".$time;
	$message_tags = GetMessageTags($message_id,$c,$main_page,$tag_color,$dbh,$search,$language);
	if (strip_tags($message_tags)!="") {
		$image_title = $ov_image_title_text[$language]." ".strip_tags($message_tags);
	} else {
		$image_title = $ov_non_descripted_mesage_text[$language];
	}
	echo($data_string."</font><br>");
	$att_query = "SELECT filename, original_filename, image_width, image_height, content_type, attachment_id, latitude, longitude, map_filename, map_address FROM attachment WHERE message_id = ".$message_id." ORDER BY content_type, attachment_id";
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
		if (($type == 4) && ($discard_short_audio == true)) {
			if (filesize($filename) < $min_audio_size) {
				continue;
			}
		}
		
		if ($type == 1) {
			
			//show image
			if ($width>1) {
				if ($width > $max_image_width_1 && $width < $max_image_width_2) {
					$height = $height*($max_image_width_1/$width);
  					$width = $max_image_width_1;
				} else if ($width > $max_image_width_2) {
					$height = $height*($max_image_width_2/$width);
  					$width = $max_image_width_2;
				}
				$prev_type = 1;
			?><img src="<? echo($filename); ?>" width="<? echo($width); ?>" height="<? echo($height); ?>" alt="<? echo($image_title); ?>" border="0" title="<? echo($image_title); ?>" /><?
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
				echo("<font size=\"$ov_text_font_size\" face=\"$ov_text_font\">$image_title</font>");
			}
		} else {
			//show video or sound using quicktime
			if (strpos($filename,".mp3")>0) {
				if ($prev_type==1) {
					echo("<br>");
				} else if ($prev_type=="" && $message_tags!="") {
					echo("<font size=\"$ov_text_font_size\" face=\"$ov_text_font\">".$ov_image_title_text[$language]." ".strip_tags($message_tags)."</font><br>");
				}
				$prev_type=$type;
				if($ov_show_player) {
					$width = $audio_width;
					$height = $audio_height;
?><div id="<? echo($attachment_id); ?>"></div>
	<script type="text/javascript">
		// <![CDATA[
			var flashvars = {
      			'file':   '<? echo($filename); ?>',
      			'skin':   'includes/minima.zip',
				'icons':  'false'
   			};
			var params = {
      			'allowfullscreen':        'false',
      			'allowscriptaccess':      'always',
				'wmode':				  'opaque',
				'bgcolor':				  '#000000'
   			};
			var attributes = {
      			'id':                     '<? echo($attachment_id); ?>',
      			'name':                   '<? echo($attachment_id); ?>'
   			};
			swfobject.embedSWF('includes/player.swf', '<? echo($attachment_id); ?>', '<? echo($width); ?>', '<? echo($height); ?>', '9', false, flashvars, params, attributes);
		// ]]>
		</script>
<br><?
				} else {
					echo("<font size=\"$ov_text_font_size\" face=\"$ov_text_font\"><a href=\"$filename\">$ov_audio_link_text[$language]</a></font><br>");
				}
			} 
		}
	}
?><font size="<? echo($ov_text_font_size); ?>" face="<? echo($ov_text_font); ?>"><?
	//show message text
	if ($prev_type == 1) {
		echo("<br>");
  	}		
	if ($message_text != "") {
		print($message_text."<br>");
		if ($c==$media_channel_id) {
			echo("<font size=\"$ov_text_font_size\" face=\"$ov_text_font\">$message_sender</font><br>");
		}		
	}
}
?>
</font><br><font size="<? echo($ov_text_font_size); ?>" face="<? echo($ov_text_font); ?>">
<?
$query="SELECT user.user_alias, comment_date, comment_text FROM comment, user WHERE message_id = $id AND user.user_id = comment.user_id ORDER BY comment_date DESC";
$result = mysql_query($query, $dbh);
$nc=0;
while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
	if ($nc==0) {
?><font size="<? echo($ov_text_font_size); ?>" face="<? echo($ov_text_font); ?>"><? echo($ov_comments_list_text[$language]."<br><br>"); ?></font>
<?
		$nc++;
	}
	$user_alias=$row[0];
	$comment_date=$row[1];
	$date_parts = explode(' ', $comment_date);
	$this_date = $date_parts[0];
	$d=strtotime($this_date);
	setlocale(LC_TIME, $ov_locales[$language]);
	$this_date=strftime("%A %e",$d)." $ov_day_month_prep[$language] ".strftime("%B",$d)." $ov_month_year_prep[$language] ".date("Y",$d);
	$time = $date_parts[1];
	$comment_text = str_replace("\n","<br>",trim(urldecode($row[2])));
	if ($comment_text!="") {
		if((strrpos($comment_text,".")!=(strlen($comment_text)-1)) && (strrpos($comment_text,"!")!=(strlen($comment_text)-1)) && (strrpos($comment_text,"?")!=(strlen($comment_text)-1))) {
			$comment_text.=".";
		}
	}
	$data_string="";
	$data_string = $ov_comment_sender_text[$language]." ".$user_alias." ";
	$data_string.=$ov_message_date_text[$language]." ".$this_date." ";
	$data_string.=$ov_message_time_text[$language]." ".$time;
	echo($data_string."<br>");
	echo("<i>".$comment_text."</i><br><br>");
}
?>
</font>
<form action="comment.php" method="post">
<font size="<? echo($ov_text_font_size); ?>" face="<? echo($ov_text_font); ?>"><strong><a href="<? echo($main_page."?c=$c&date=$date&from=$from#$id"); ?>"><? echo($ov_comment_back_link[$language]) ?></a></strong>
<?
if ($error_msg!="") {
	echo("<br><br>".$error_msg."<br><br>");
}
?>
<p><hr>
<? echo($ov_comments_page_text[$language].":"); ?>
<input name="id" type="hidden" value="<? echo($id); ?>">
<input name="c" type="hidden" value="<? echo($c); ?>">
<input name="date" type="hidden" value="<? echo($date); ?>">
</p>
<p><? echo($ov_comment_alias[$language]); ?> 
<select name="alias" style="color: <? echo($textcolor); ?>; background-color: <? echo($bgcolor); ?>; font-size: <? echo($ov_form_font_size); ?>em;">
<?
if (isset($_POST['alias'])) {
	$current=$_POST['alias'];
} else {
	$current=-1;
}
$list=GetUsersComboList($dbh,$current);
for($i=0;$i<sizeof($list);$i++) {
	echo($list[$i]);
}
?>
</select>
</p>
<p><? echo($ov_comment_password[$language]); ?> 
<input name="pass" type="text" size="10" style="color: <? echo($textcolor); ?>; background-color: <? echo($bgcolor); ?>; font-size: <? echo($ov_form_font_size); ?>em;">
<p><? echo($ov_comment_text[$language]); ?>
<br>
<textarea name="comment" cols="30" rows="6" style="color: <? echo($textcolor); ?>; background-color: <? echo($bgcolor); ?>; font-size: <? echo($ov_form_font_size); ?>em;"><? 
if (isset($_POST['comment'])) {
	echo($_POST['comment']);
}
?></textarea>
<p>
<input type="submit" name="add" value="<? echo($ov_add_comment_button[$language]); ?>" style="color: <? echo($textcolor); ?>; background-color: <? echo($bgcolor); ?>; font-size: <? echo($ov_form_font_size); ?>em;">
 
</font>
</form>
</body>
</html>
