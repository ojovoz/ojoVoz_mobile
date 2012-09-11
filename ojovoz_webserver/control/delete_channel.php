<?
include_once("./../includes/channel_vars.php");
include_once("./../includes/init_database.php");
$dbh = initDB();
if (!isset($_POST['submit'])) {
$id=$_GET['id'];
?>
<html>
<head>
<title><? echo($global_channel_name); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body bgcolor="#FFFFFF">
<font face="Courier New, Courier, mono" size="2">
<p>
<?
$query="SELECT channel_name FROM channel WHERE channel_id=".$id;
$result = mysql_query($query, $dbh);
$row = mysql_fetch_array($result, MYSQL_NUM);
$channel_name = $row[0];
?>
<form method="post" action="" name="form1">
<p><font face="Courier New, Courier, mono" size="2"><b>Delete Channel: <? echo($channel_name); ?> ?</b></font></p>
<input type="hidden" name="id" value="<? echo($id); ?>" size="30">
<input type="submit" name="submit" value="Delete">
</form>
<p>
<a href="channels.php">&lt;--- 
  Channels</a>
</p>
</body>
</html>
<?
} else {
$id = $_POST['id'];
$query = "DELETE FROM attachment USING attachment, message WHERE attachment.message_id = message.message_id AND message.channel_id = ".$id;
$result = mysql_query($query, $dbh);

$query = "DELETE FROM tag_x_message USING tag_x_message, message WHERE tag_x_message.message_id = message.message_id AND message.channel_id = ".$id;
$result = mysql_query($query, $dbh);

$query = "DELETE FROM descriptor_x_message USING descriptor_x_message, message WHERE descriptor_x_message.message_id = message.message_id AND message.channel_id = ".$id;
$result = mysql_query($query, $dbh);

$query = "DELETE FROM message WHERE channel_id = ".$id;
$result = mysql_query($query, $dbh);

$query = "DELETE FROM user_x_channel WHERE channel_id = ".$id;
$result = mysql_query($query, $dbh);

$query = "DELETE FROM channel WHERE channel_id = ".$id;
$result = mysql_query($query, $dbh);

header("Location: channels.php");
}
?>