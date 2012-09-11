<?
include_once("./../includes/channel_vars.php");
include_once("./../includes/init_database.php");
$dbh = initDB();

if (isset($_POST['yes'])) {
	$id = $_POST['id'];
	$gid = $_POST['gid'];
	$query="UPDATE tag SET tag_group_id = -1 WHERE tag_id=$id";
	$result = mysql_query($query, $dbh);
	header("Location: edit_tag_group.php?id=$gid");
} else if (isset($_POST['no'])) {
	$gid = $_POST['gid'];
	header("Location: edit_tag_group.php?id=$gid");
} else {
	$id = $_GET['id'];
	$gid = $_GET['gid'];
	$query="SELECT tag_name FROM tag WHERE tag_id=$id";
	$result = mysql_query($query, $dbh);
	$row = mysql_fetch_array($result, MYSQL_NUM);
?>
<html>
<head>
<title><? echo($global_channel_name); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<p><font size="2" face="Courier New, Courier, mono"><strong>Delete tag <? echo($row[0]); ?> from group?</strong></font></p>
<form name="form1" method="post" action="">
  <input name="yes" type="submit" id="yes" value="yes">
  <input name="no" type="submit" id="no" value="no">
  <input name="id" type="hidden" id="id" value="<? echo($id); ?>">
  <input name="gid" type="hidden" id="gid" value="<? echo($gid); ?>">
</form>
<p>&nbsp; </p>
</body>
</html>
<?
}
?>