<?
include_once("./../includes/channel_vars.php");
include_once("./../includes/init_database.php");
$dbh = initDB();

if (isset($_POST['add'])) {
	$tag_group_name = $_POST['tag_group_name'];
	$query = "SELECT tag_group_id FROM tag_group WHERE tag_group_name = '$tag_group_name'";
	$result = mysql_query($query, $dbh);
	if ($row = mysql_fetch_array($result, MYSQL_NUM)) {
	} else {
		$query = "INSERT INTO tag_group (tag_group_name) VALUES ('$tag_group_name')";
		$result = mysql_query($query, $dbh);
	}
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><? echo($global_channel_name); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<p><strong><font size="2" face="Courier New, Courier, mono">Tag groups:</font></strong>
</p>
<p><font size="2" face="Courier New, Courier, mono">
<?
$query = "SELECT tag_group_id, tag_group_name FROM tag_group ORDER BY tag_group_name";
$result = mysql_query($query, $dbh);
while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
?>
<a href="edit_tag_group.php?id=<? echo($row[0]); ?>">edit</a> <a href="delete_tag_group.php?id=<? echo($row[0]); ?>">delete</a> <? echo($row[1]."<br>"); 
}
?>
</font></p>
<p><font size="2" face="Courier New, Courier, mono">Add tag group:</font></p>
<form name="form1" method="post" action="">
<p>
  <font size="2">
  <font face="Courier New, Courier, mono">Tag group:</font>
  <input name="tag_group_name" type="text" id="tag_group_name" size="30">  
  <font face="Courier New, Courier, mono"><br>
  <br>
  </font></font>
  <input name="add" type="submit" id="add" value="Add">
</p>
</form>
<p><a href="index.php"><font size="2" face="Courier New, Courier, mono">&lt;-- Control Panel </font></a></p>
</body>
</html>
