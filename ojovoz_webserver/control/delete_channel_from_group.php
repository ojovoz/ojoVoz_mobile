<?
include_once("./../includes/channel_vars.php");
include_once("./../includes/init_database.php");
$dbh = initDB();

if (isset($_POST['yes'])) {
	$id = $_POST['id'];
	$c = $_POST['c'];
	$query="UPDATE channel SET parent_channel_id = -1 WHERE channel_id=$id";
	$result = mysql_query($query, $dbh);
	header("Location: edit_channel_group.php?c=$c");
} else if (isset($_POST['no'])) {
	$c = $_POST['c'];
	header("Location: edit_channel_group.php?c=$c");
} else {
	$id = $_GET['id'];
	$c = $_GET['c'];
	$query="SELECT channel_name FROM channel WHERE channel_id=$id";
	$result = mysql_query($query, $dbh);
	$row = mysql_fetch_array($result, MYSQL_NUM);
?>
<html>
<head>
<title><? echo($global_channel_name); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<p><font size="2" face="Courier New, Courier, mono"><strong>Delete channel '<? echo($row[0]); ?>' from group?</strong></font></p>
<form name="form1" method="post" action="">
  <input name="yes" type="submit" id="yes" value="yes">
  <input name="no" type="submit" id="no" value="no">
  <input name="id" type="hidden" id="id" value="<? echo($id); ?>">
  <input name="c" type="hidden" id="c" value="<? echo($c); ?>">
</form>
<p>&nbsp; </p>
</body>
</html>
<?
}
?>