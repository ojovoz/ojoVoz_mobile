<?
include_once("./../includes/channel_vars.php");
include_once("./../includes/init_database.php");
$dbh = initDB();
if (!isset($_POST['submit'])) {
	$id = $_GET['id'];
	$c = $_GET['c'];
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
$query="SELECT user_alias FROM user WHERE user_id=".$id;
$result = mysql_query($query, $dbh);
$row = mysql_fetch_array($result, MYSQL_NUM);
$user_alias = $row[0];

$query="SELECT channel_name FROM channel WHERE channel_id=".$c;
$result = mysql_query($query, $dbh);
$row = mysql_fetch_array($result, MYSQL_NUM);
$channel_name = $row[0];

?>
<form method="post" action="" name="form1">
<p><font face="Courier New, Courier, mono" size="2"><b>Delete Participant: <? echo($user_alias); ?> from channel <? echo($channel_name); ?> ?</b></font></p>
<input type="hidden" name="id" value="<? echo($id); ?>" size="30">
<input type="hidden" name="c" value="<? echo($c); ?>" size="30">
<input type="submit" name="submit" value="Delete">

</form>
<br>
<a href="add_user.php?id=<? echo($c); ?>">&lt;---  Participants</a>
</body>
</html>
<?
} else {
	$query = "DELETE FROM user_x_channel WHERE user_id = $id AND channel_id = $c";
	$result = mysql_query($query, $dbh);
	header("Location: add_user.php?id=$c");
}

?>