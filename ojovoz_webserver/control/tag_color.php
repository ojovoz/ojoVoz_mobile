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
<p><font size="2" face="Courier New, Courier, mono"><strong><? echo($row[0]); ?> <img src="../includes/images/marker<? echo($row[1]); ?>.gif" align="absmiddle" border="0"></strong></font></p>
<p><font size="2" face="Courier New, Courier, mono">New color:</font> </p>
<p><a href="tag_color.php?id=<? echo($id); ?>&newcolor=01"><img src="../includes/images/marker01.gif"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=02"><img src="../includes/images/marker02.gif"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=03"><img src="../includes/images/marker03.gif"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=04"><img src="../includes/images/marker04.gif"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=05"><img src="../includes/images/marker05.gif"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=06"><img src="../includes/images/marker06.gif"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=07"><img src="../includes/images/marker07.gif"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=08"><img src="../includes/images/marker08.gif"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=09"><img src="../includes/images/marker09.gif"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=10"><img src="../includes/images/marker10.gif"  border="0"></a><a href="tag_color.php?id=<? echo($id); ?>&newcolor=11"><img src="../includes/images/marker11.gif"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=12"><img src="../includes/images/marker12.gif"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=13"><img src="../includes/images/marker13.gif"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=14"><img src="../includes/images/marker14.gif"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=15"><img src="../includes/images/marker15.gif"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=16"><img src="../includes/images/marker16.gif"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=17"><img src="../includes/images/marker17.gif"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=18"><img src="../includes/images/marker18.gif"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=19"><img src="../includes/images/marker19.gif"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=20"><img src="../includes/images/marker20.gif"  border="0"></a><a href="tag_color.php?id=<? echo($id); ?>&newcolor=21"><img src="../includes/images/marker21.gif"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=22"><img src="../includes/images/marker22.gif"  border="0"></a> <a href="tag_color.php?id=<? echo($id); ?>&newcolor=23"><img src="../includes/images/marker23.gif"  border="0"></a></p>
<p><a href="tags_map.php"><font size="2" face="Courier New, Courier, mono">&lt;-- Back</font></a></p>
</body>
</html>
<?
}
?>