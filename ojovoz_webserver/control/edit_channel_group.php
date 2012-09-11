<?
include_once("./../includes/channel_vars.php");
include_once("./../includes/init_database.php");
$dbh = initDB();

if (isset($_POST['add'])) {
	$channel_id = $_POST['channel_id'];
	$c = $_POST['c'];
	$query = "UPDATE channel SET parent_channel_id = $c WHERE channel_id = $channel_id";
	$result = mysql_query($query, $dbh);
} else {
	$c = $_GET['c'];
}
$query = "SELECT channel_name FROM channel WHERE channel_id = $c";
$result = mysql_query($query, $dbh);
$row = mysql_fetch_array($result, MYSQL_NUM);
$channel_name = $row[0];
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title><? echo($global_channel_name); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<p><font face="Courier New, Courier, mono", size="2">
  <strong>Channels in group: <? echo($channel_name); ?></strong></font></p>
<p>
  <? 
$query = "SELECT channel_id, channel_name FROM channel WHERE parent_channel_id = $c ORDER BY channel_name";
$result = mysql_query($query, $dbh);
while($row = mysql_fetch_array($result, MYSQL_NUM)) {
?>
  <font face="Courier New, Courier, mono", size="2"><? echo($row[1]); ?> <a href="delete_channel_from_group.php?id=<? echo($row[0]); ?>&c=<? echo($c); ?>">delete</a></font><br>
  <?
}
?>
  <font face="Courier New, Courier, mono", size="2">
  </font></p>
<form name="form1" method="post" action="">
  <input type="hidden" name="c" id="c" value="<? echo($c); ?>">
  <p><font face="Courier New, Courier, mono", size="2"><strong>Add channel to group:</strong> </font></p>
  <p><font size="2" face="Courier New, Courier, mono">
    <select name="channel_id" id="channel_id">
<?
$query = "SELECT channel_id, channel_name FROM channel WHERE parent_channel_id = -1 AND is_crono=0 AND is_visible=1 ORDER BY channel_name";
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
</form>
<p><font face="Courier New, Courier, mono", size="2"><a href="edit_channel.php?id=<? echo($c); ?>"><font size="2" face="Courier New, Courier, mono">&lt;-- Edit channel </font></a></font></p>
</body>
</html>
