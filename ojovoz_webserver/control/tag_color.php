<?
include_once("./../includes/channel_vars.php");
include_once("./../includes/init_database.php");
$dbh = initDB();

if (isset($_GET['newcolor'])) {
	$newcolor = $_GET['newcolor'];
	$id = $_GET['id'];
	if ($id>0) {
		$query="UPDATE tag SET color_in_map = '$newcolor' WHERE tag_id=$id";
	} else {
		$query="UPDATE tag_group SET color_in_map = '$newcolor' WHERE tag_group_id=$id*-1";
	}
	$result = mysql_query($query, $dbh);
	header("Location: tags_map.php");
} else {
	$id = $_GET['id'];
	if ($id>0) {
		$query="SELECT tag_name, color_in_map FROM tag WHERE tag_id=$id";
	} else {
		$query="SELECT tag_group_name, color_in_map FROM tag_group WHERE tag_group_id=$id*-1";
	}
	$result = mysql_query($query, $dbh);
	$row = mysql_fetch_array($result, MYSQL_NUM);
?>
<html>
<head>
<title><? echo($global_channel_name); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<p><font size="2" face="Courier New, Courier, mono"><strong><? echo($row[0]); ?> <img src="../includes/images/marker<? echo($row[1]); ?>_ball.png" align="absmiddle" border="0"></strong></font></p>
<p><font size="2" face="Courier New, Courier, mono">New color:</font> </p>
<p><a href="tag_color.php?id=<? echo($id); ?>&newcolor=00"><img src="../includes/images/marker00_ball.png"  border="0"></a> 
  <a href="tag_color.php?id=<? echo($id); ?>&newcolor=01"><img src="../includes/images/marker01_ball.png"  border="0"></a> 
  <a href="tag_color.php?id=<? echo($id); ?>&newcolor=02"><img src="../includes/images/marker02_ball.png"  border="0"></a> 
  <a href="tag_color.php?id=<? echo($id); ?>&newcolor=03"><img src="../includes/images/marker03_ball.png"  border="0"></a> 
  <a href="tag_color.php?id=<? echo($id); ?>&newcolor=04"><img src="../includes/images/marker04_ball.png"  border="0"></a> 
  <a href="tag_color.php?id=<? echo($id); ?>&newcolor=05"><img src="../includes/images/marker05_ball.png"  border="0"></a> 
  <a href="tag_color.php?id=<? echo($id); ?>&newcolor=06"><img src="../includes/images/marker06_ball.png"  border="0"></a> 
  <a href="tag_color.php?id=<? echo($id); ?>&newcolor=07"><img src="../includes/images/marker07_ball.png"  border="0"></a> 
  <a href="tag_color.php?id=<? echo($id); ?>&newcolor=08"><img src="../includes/images/marker08_ball.png"  border="0"></a> 
  <a href="tag_color.php?id=<? echo($id); ?>&newcolor=09"><img src="../includes/images/marker09_ball.png"  border="0"></a><a href="tag_color.php?id=<? echo($id); ?>&newcolor=10"><img src="../includes/images/marker10_ball.png"  border="0"></a> 
</p>
<p><a href="tags_map.php"><font size="2" face="Courier New, Courier, mono">&lt;-- Back</font></a></p>
</body>
</html>
<?
}
?>