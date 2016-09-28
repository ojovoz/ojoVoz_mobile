<?
include_once("./../includes/all.php");
$dbh = initDB();

if (isset($_POST['upload']) && isset($_POST['pass'])) {
	$msg="Files were not uploaded. Please try again.";
	$c=$_POST['c'];
	$pass=$_POST['pass'];
	$alias=$_POST['participant'];
	$yyyy=$_POST['yyyy'];
	$mm=$_POST['mm'];
	$dd=$_POST['dd'];
	if(is_numeric($yyyy) && is_numeric($mm) && is_numeric($dd)){
		$date=$yyyy."-".$mm."-".$dd;
	} else {
		$date=date("Y-m-d");
	}
	$hh=$_POST['hh'];
	$min=$_POST['min'];
	$ss=$_POST['ss'];
	if(is_numeric($hh) && is_numeric($min) && is_numeric($ss)){
		$time=$hh.":".$min.":".$ss;
	} else {
		$time=date("H:i:s");
	}
	$folder=GetChannelFolder($c,$pass,$dbh);
	if(isset($_FILES['file1']['name']) && ($folder!="")){
		$img=$_FILES['file1']['name'];
		$path=pathinfo($img);
		$ext=$path['extension'];
		if(strtolower($ext)=="jpg"){
			$subfolder = "/image/image";
			$extension = ".jpg";
			$att_type = 1;
			$index=GetMaxFileIndex($c,$dbh);
			$filename = $folder.$subfolder.$index.$extension;
			$upload = "./../channels/".$filename;
			if(is_uploaded_file($_FILES['file1']['tmp_name'])) {
				move_uploaded_file($_FILES['file1']['tmp_name'],$upload);
			}
			$lat="";
			$long="";
			$sz = getimagesize($upload);
			$w=$sz[0];
			$h=$sz[1];
			$maxorder=GetMaxMessageOrder($c,$dbh);
			$message_date=$date." ".$time;
			$query = "INSERT INTO message (channel_id, message_date, message_sender, message_subject, message_order) VALUES ($c,'".$message_date."','".$alias."','',$maxorder)";
			$result = mysql_query($query, $dbh);
			$current_message = mysql_insert_id();
			$query = "INSERT INTO attachment (message_id, filename, content_type, original_filename,image_width,image_height,latitude,longitude) VALUES ($current_message,'$filename','$att_type','$filename',$w,$h,'$lat','$long')";
			$result = mysql_query($query, $dbh);
			IncreaseFileIndex($c,$dbh);
			$msg="Success. Upload a new message.";
			if(isset($_FILES['file2']['name'])){
				$snd=$_FILES['file2']['name'];
				$path=pathinfo($snd);
				$ext=$path['extension'];
				if(strtolower($ext)=="mp3"){
					$subfolder = "/sound/sound";
					$extension = ".mp3";
					$att_type = 2;
					$w=0;
					$h=0;
					$index=GetMaxFileIndex($c,$dbh);
					$filename = $folder.$subfolder.$index.$extension;
					$upload = "./../channels/".$filename;
					if(is_uploaded_file($_FILES['file2']['tmp_name'])) {
						move_uploaded_file($_FILES['file2']['tmp_name'],$upload);
					}
					$query = "INSERT INTO attachment (message_id, filename, content_type, original_filename,image_width,image_height,latitude,longitude) VALUES ($current_message,'$filename','$att_type','$filename',$w,$h,'$lat','$long')";
					$result = mysql_query($query, $dbh);
					IncreaseFileIndex($c,$dbh);
				}
			}
		}
	}
	$folder="";
} else {
	$c=-1;
	$alias="";
	$msg="Upload message";
}
?>

<!DOCTYPE html>
<html>
<head>
<title><? echo($global_channel_name); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="css/w3.css">
</head>

<body>
<form class="w3-container w3-card-4" method="post" enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
  <h2 class="w3-green"><? echo($msg); ?></h2>
  <p>
  <label class="w3-text-green">Choose group:</label>
    <select class="w3-select w3-text-green" name="c">
        <?
//get combo lines
$lines = GetComboListUpload($dbh);
for ($i=0;$i<sizeof($lines);$i++) {
	echo($lines[$i]);
}
?> 
    </select></p>
	
  <p><label class="w3-text-green">Password: </label> 
    <input class="w3-input w3-border-green w3-text-green" name="pass" type="password" id="pass">
</p>
  <p><label class="w3-text-green">Sender: </label>
    <input class="w3-input w3-border-green w3-text-green" name="participant" type="text" id="participant">
</p>

  <p><label class="w3-text-green">Date:</label></p>
  <div class="w3-row-padding">
  <div class="w3-third">
    <select class="w3-select w3-text-green" name="dd">
		<option value="" disabled selected>Day</option>
		<?php
		for($i=1;$i<=31;$i++){
			if($i<10){
				$n="0".$i;
			} else {
				$n=$i;
			}
			echo('<option value="'.$n.'">'.$n.'</option>');
		}
		?>
	</select>
  </div>
  <div class="w3-third">
    <select class="w3-select w3-text-green" name="mm">
		<option value="" disabled selected>Month</option>
		<?php
		for($i=1;$i<=12;$i++){
			if($i<10){
				$n="0".$i;
			} else {
				$n=$i;
			}
			echo('<option value="'.$n.'">'.$n.'</option>');
		}
		?>
	</select>
  </div>
  <div class="w3-third">
    <input class="w3-input w3-border-green w3-text-green" type="text" name="yyyy" value="<?php echo(date('Y')); ?>">
  </div>
</div>
    
  <p><label class="w3-text-green">Time:</label></p> 
  <div class="w3-row-padding">
  <div class="w3-third">
    <select class="w3-select w3-text-green" name="hh">
		<option value="" disabled selected>Hour</option>
		<?php
		for($i=0;$i<=23;$i++){
			if($i<10){
				$n="0".$i;
			} else {
				$n=$i;
			}
			echo('<option value="'.$n.'">'.$n.'</option>');
		}
		?>
	</select>
  </div>
  <div class="w3-third">
    <select class="w3-select w3-text-green" name="min">
		<option value="" disabled selected>Minute</option>
		<?php
		for($i=0;$i<=59;$i++){
			if($i<10){
				$n="0".$i;
			} else {
				$n=$i;
			}
			echo('<option value="'.$n.'">'.$n.'</option>');
		}
		?>
	</select>
  </div>
  <div class="w3-third">
    <select class="w3-select w3-text-green" name="ss">
		<option value="" disabled selected>Second</option>
		<?php
		for($i=0;$i<=59;$i++){
			if($i<10){
				$n="0".$i;
			} else {
				$n=$i;
			}
			echo('<option value="'.$n.'">'.$n.'</option>');
		}
		?>
	</select>
  </div>
</div>  
	
  <p><label class="w3-text-green">Image (JPG): </label>
    <input class="w3-input w3-border-green w3-text-green" name="file1" type="file" id="file1"></p>
  <p><label class="w3-text-green">Audio (MP3): </label>
    <input class="w3-input w3-border-green w3-text-green" name="file2" type="file" id="file2">
    </p>
  <p>
  <button class="w3-button w3-padding-large w3-red w3-round w3-border w3-border-red" id="upload" name="upload">Upload</button>
  </p>
</form>
</body>
</html>
