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
<p><font size="2" face="Courier New, Courier, mono"><strong><? echo($row[0]); ?> <img src="../includes/images/marker<? echo($row[1]); ?>s.png" align="absmiddle" border="0"></strong></font></p>
<p><font size="2" face="Courier New, Courier, mono">New color:</font> </p>
<p><a href="tag_color.php?id=<? echo($id); ?>&newcolor=01"><img src="../includes/images/marker01s.png"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=02"><img src="../includes/images/marker02s.png"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=03"><img src="../includes/images/marker03s.png"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=04"><img src="../includes/images/marker04s.png"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=05"><img src="../includes/images/marker05s.png"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=06"><img src="../includes/images/marker06s.png"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=07"><img src="../includes/images/marker07s.png"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=08"><img src="../includes/images/marker08s.png"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=09"><img src="../includes/images/marker09s.png"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=10"><img src="../includes/images/marker10s.png"  border="0"></a><a href="tag_color.php?id=<? echo($id); ?>&newcolor=11"><img src="../includes/images/marker11s.png"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=12"><img src="../includes/images/marker12s.png"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=13"><img src="../includes/images/marker13s.png"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=14"><img src="../includes/images/marker14s.png"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=15"><img src="../includes/images/marker15s.png"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=16"><img src="../includes/images/marker16s.png"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=17"><img src="../includes/images/marker17s.png"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=18"><img src="../includes/images/marker18s.png"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=19"><img src="../includes/images/marker19s.png"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=20"><img src="../includes/images/marker20s.png"  border="0"></a><a href="tag_color.php?id=<? echo($id); ?>&newcolor=21"><img src="../includes/images/marker21s.png"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=22"><img src="../includes/images/marker22s.png"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=23"><img src="../includes/images/marker23s.png"  border="0"></a></p>
<p><a href="tags_map.php"><font size="2" face="Courier New, Courier, mono">&lt;-- Back</font></a></p>
</body>
</html>
<?
}
?>