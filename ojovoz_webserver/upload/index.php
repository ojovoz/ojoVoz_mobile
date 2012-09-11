<?
include_once("./../includes/all.php");
$dbh = initDB();

if (isset($_POST['upload']) && isset($_POST['pass'])) {
	$msg="File uploader ... files not uploaded.";
	$c=$_POST['c'];
	$pass=$_POST['pass'];
	$alias=$_POST['participant'];
	$folder=GetChannelFolder($c,$pass,$dbh);
	for($i=1;$i<=5;$i++) {
		$fieldname='file'.$i;
		if (isset($_FILES[$fieldname]['name']) && ($folder!="")) {
			$file=$_FILES[$fieldname]['name'];
			if ($file!="") {
				$path = pathinfo($file);
				$ext = $path['extension'];
				$valid=false;
				if ($ext=="jpg") {
					$subfolder = "/image/image";
					$extension = ".jpg";
					$att_type = 1;
					$valid=true;
				} else if ($ext=="amr" || $ext=="wav" || $ext=="mp3") {
					$subfolder = "/sound/sound";
					$extension = ".".$ext;
					$att_type = 2;
					$valid=true;
				} else if ($ext=="3gp" || $ext=="mp4") {
					$subfolder = "/video/video";
					$extension = ".".$ext;
					$att_type = 3;
					$valid=true;
				}
				if ($valid) {
					$index=GetMaxFileIndex($c,$dbh);
					$filename = $folder.$subfolder.$index.$extension;
					$upload = "./../channels/".$filename;
					if(is_uploaded_file($_FILES[$fieldname]['tmp_name'])) {
						move_uploaded_file($_FILES[$fieldname]['tmp_name'],$upload);
					}
					$lat="";
					$long="";
					$datetime="";
					if ($att_type == 1) {
						$sz = getimagesize($upload);
						$w = $sz[0];
						$h = $sz[1];
						$coord=GetCoordinatesFromExif($upload);
						if (sizeof($coord)==1) {
							$datetime=$coord[0];
						} else if (sizeof($coord)==2 && $coord[0]!=0 && $coord[1]!=0) {
							$lat=$coord[0];
							$long=$coord[1];
						} else if (sizeof($coord)==3 && $coord[0]!=0 && $coord[1]!=0) {
							$lat=$coord[0];
							$long=$coord[1];
							$datetime=$coord[2];
						}
						$tags=GetTagsFromExif($upload);
					} else {
						$w = 0;
						$h = 0;
					}
					$message_date = date("Y-m-d H:i:s");
					$maxorder = GetMaxMessageOrder($c,$dbh);
					$query = "INSERT INTO message (channel_id, message_date, message_sender, message_subject, message_order) VALUES ($c,'".$message_date."','".$alias."','',$maxorder)";
					$result = mysql_query($query, $dbh);
					$current_message = mysql_insert_id();
					$query = "INSERT INTO attachment (message_id, filename, content_type, original_filename,image_width,image_height,latitude,longitude,date_time) VALUES ($current_message,'$filename','$att_type','$filename',$w,$h,'$lat','$long','$datetime')";
					$result = mysql_query($query, $dbh);
					IncreaseFileIndex($c,$dbh);
					ProcessMessageTags($tags,$current_message,$dbh);
					$tags="";
					$msg="File uploader ... file upload OK.";
				}
			}
		}
	}
	
} else {
	$c=-1;
	$alias="";
	$msg="File uploader";
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title><? echo($global_channel_name); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<form action="index.php" method="post" enctype="multipart/form-data" name="form1">
  <p><font face="Courier New, Courier, mono" size="2"><b><? echo($msg); ?></b></font></p>
  <p><font face="Courier New, Courier, mono" size="2">Upload to channel: </font>
      <select name="c">
        <?
//get combo lines
$lines = GetComboListUpload($dbh);
for ($i=0;$i<sizeof($lines);$i++) {
	echo($lines[$i]);
}
?> 
    </select>
</p>
  <p><font size="2" face="Courier New, Courier, mono">Password:</font>    
    <input name="pass" type="password" id="pass">
</p>
  <p><font size="2" face="Courier New, Courier, mono">Participant: 
    <input name="participant" type="text" id="participant">
    </font></p>
  <p><font size="2" face="Courier New, Courier, mono">File 1: 
      <input name="file1" type="file" id="file1">
</font></p>
  <p><font size="2" face="Courier New, Courier, mono">File 2:
      <input name="file2" type="file" id="file2">
  </font></p>
  <p><font size="2" face="Courier New, Courier, mono">File 3:
      <input name="file3" type="file" id="file3">
  </font></p>
  <p><font size="2" face="Courier New, Courier, mono">File 4:
      <input name="file4" type="file" id="file4">
  </font></p>
  <p><font size="2" face="Courier New, Courier, mono">File 5:
      <input name="file5" type="file" id="file5">
  </font></p>
  <p>
    <input name="upload" type="submit" id="upload" value="Upload"> 
  </p>
</form>
</body>
</html>
