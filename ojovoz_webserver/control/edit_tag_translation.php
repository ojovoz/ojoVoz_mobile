<?
include_once("./../includes/channel_vars.php");
include_once("./../includes/init_database.php");
$dbh = initDB();

$l=explode(",",$ov_languages);

if (isset($_POST['edit'])) {
	$id=$_POST['id'];
	$lang=$_POST['lang'];
	$trans=$_POST['trans'];
	$prev=$_POST['prev'];
	if ($prev==0) {
		$query="INSERT INTO tag_x_language VALUES (NULL, $id, $lang, '$trans')";
	} else {
		$query="UPDATE tag_x_language SET translation='$trans' WHERE tag_id=$id AND language_id=$lang";
	}
	$result = mysql_query($query, $dbh);
	header("Location: translate_tags.php");
} else {
	$id=$_GET['id'];
	$lang=$_GET['lang'];
	$prev=$_GET['prev'];
	$query="SELECT tag_name FROM tag WHERE tag_id = $id";
	$result = mysql_query($query, $dbh);
	$row = mysql_fetch_array($result, MYSQL_NUM);
	$name = $row[0];
	$query="SELECT translation FROM tag_x_language WHERE tag_id = $id AND language_id = $lang";
	$result = mysql_query($query, $dbh);
	if($row = mysql_fetch_array($result, MYSQL_NUM)) {
		$trans = $row[0];
	} else {
		$trans = "";
	}
}
?>
<html>
<head>
<title><? echo($global_channel_name); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<form name="form1" method="post" action="">
<font size="2" face="Courier New, Courier, mono">Edit <? echo($l[$lang]); ?> for tag <? echo($name); ?>: 
<input name="trans" type="text" id="trans" value="<? echo($trans); ?>">
<input type="hidden" name="id" value="<? echo($id); ?>">
<input type="hidden" name="prev" value="<? echo($prev); ?>">
<input type="hidden" name="lang" value="<? echo($lang); ?>">
<input type="submit" name="edit" value="Edit">
</font>
</form>
<p><font face="Courier New, Courier, mono", size="2"><a href="translate_tags.php"><font size="2" face="Courier New, Courier, mono">&lt;-- Translate tags</font></a></font></p>
</body>
</html>
