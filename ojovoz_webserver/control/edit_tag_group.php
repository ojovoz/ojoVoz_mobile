<?
include_once("./../includes/channel_vars.php");
include_once("./../includes/init_database.php");
$dbh = initDB();

if (isset($_POST['add'])) {
	$tag_id = $_POST['tag_id'];
	$id = $_POST['id'];
	$query = "UPDATE tag SET tag_group_id = $id WHERE tag_id = $tag_id";
	$result = mysql_query($query, $dbh);
} else if (isset($_POST['edit'])) {
	$tag_group_name = $_POST['tag_group_name'];
	$id = $_POST['id'];
	$query = "UPDATE tag_group SET tag_group_name = '$tag_group_name' WHERE tag_group_id = $id";
	$result = mysql_query($query, $dbh);
} else {
	$id = $_GET['id'];
}
$query = "SELECT tag_group_name FROM tag_group WHERE tag_group_id = $id";
$result = mysql_query($query, $dbh);
$row = mysql_fetch_array($result, MYSQL_NUM);
$tag_group_name = $row[0];
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title><? echo($global_channel_name); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<p><font face="Courier New, Courier, mono", size="2">
  <strong>Tags in group: <? echo($tag_group_name); ?></strong></font></p>
<p>
  <? 
$query = "SELECT tag_id, tag_name FROM tag WHERE tag_group_id = $id ORDER BY tag_name";
$result = mysql_query($query, $dbh);
while($row = mysql_fetch_array($result, MYSQL_NUM)) {
?>
  <font face="Courier New, Courier, mono", size="2"><? echo($row[1]); ?> <a href="delete_tag_from_group.php?id=<? echo($row[0]); ?>&gid=<? echo($id); ?>">delete</a></font><br>
  <?
}
?>
  <font face="Courier New, Courier, mono", size="2">
  </font></p>
<form name="form1" method="post" action="">
  <input type="hidden" name="id" id="id" value="<? echo($id); ?>">
  <p><font face="Courier New, Courier, mono", size="2"><strong>Add tag to group:</strong> </font></p>
  <p><font size="2" face="Courier New, Courier, mono">
    <select name="tag_id" id="tag_id">
<?
$query = "SELECT tag_id, tag_name FROM tag WHERE tag_group_id = -1 ORDER BY tag_name";
$result = mysql_query($query, $dbh);
while($row = mysql_fetch_array($result, MYSQL_NUM)) {
?>
  <option value="<? echo($row[0]); ?>"><? echo($row[1]); ?></option>
<?
}
?>
    </select>
    <input name="add" type="submit" id="add" value="Add">
</font></p>
  <p><font size="2" face="Courier New, Courier, mono"><strong>Edit group name:</strong> </font></p>
  <p><font size="2" face="Courier New, Courier, mono">
    <input name="tag_group_name" type="text" id="tag_group_name" size="30" value="<? echo($tag_group_name); ?>">
    <input name="edit" type="submit" id="edit" value="Edit">
</font></p>
</form>
<p><font face="Courier New, Courier, mono", size="2"><a href="tag_groups.php"><font size="2" face="Courier New, Courier, mono">&lt;-- Tag groups </font></a></font></p>
</body>
</html>
