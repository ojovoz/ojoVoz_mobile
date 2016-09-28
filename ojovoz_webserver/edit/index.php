<?
include_once("./../includes/channel_vars.php");
include_once("./../includes/init_database.php");
include_once("./../includes/database_functions.php");
$dbh = initDB();
if (isset($_POST['submit'])) {
	$c=$_POST['c'];
	$p=$_POST['p'];
	if ($p!="") {
		$v=VerifyChannelAccess($dbh,$c,$p,$master_pass);
		if ($v) {
			header("Location: edit_channel.php?c=$c");
			exit;
		}
	}
}
?>
<html>
<head>
<title><? echo($global_channel_name); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
</head>
<body bgcolor="#FFFFFF" text="#000000" alink="#000000" vlink="#000000" link="#000000">
<font face="<? echo($bv_text_font); ?>" size="<?  echo($bv_text_font_size); ?>"><b>Edit<br>
</b></font>
<form method="post" action="index.php">
<blockquote>
  <p>
    <select name="c" style="font-face: <? echo($bv_text_font); ?>; font-size: <? echo($font_size); ?>em;">
	<option value="-1" selected>Choose your user name:</option>
<?
//get combo lines
$lines = GetComboListUpload($dbh);
for ($i=0;$i<sizeof($lines);$i++) {
	echo($lines[$i]);
}
?> 
</select>
</p>
  <p><font face="<? echo($bv_text_font); ?>" size="<?  echo($bv_text_font_size); ?>"><b>Pass: </b></font> 
    <input type="password" name="p" maxlength="15" style="font-face: <? echo($bv_text_font); ?>; font-size: <? echo($font_size); ?>em;">
</p>
    <p align="left"> 
      <input type="submit" name="submit" value="Start" style="font-face: <? echo($bv_text_font); ?>; font-size: <? echo($font_size); ?>em;">
    </p>
    <p align="left"><font face="<? echo($bv_text_font); ?>" size="<?  echo($bv_text_font_size); ?>"><? if (isset($_GET['prev'])) { echo("<a href=\"javascript:history.go(-1)\">$bv_go_to_previous_page_text[0]</a>"); } ?></font></p>
</blockquote>
</form>
</html>
