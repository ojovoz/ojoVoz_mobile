<?
include_once("./../includes/channel_vars.php");
include_once("./../includes/init_database.php");
$dbh = initDB();

if (isset($_POST['change'])) {
	$id=$_POST['channel'];
	$query="UPDATE global SET value='$id' WHERE global_variable='default_channel_id'";
	$result = mysql_query($query, $dbh);
	header("Location: index.php");
} else {
	$query="SELECT value FROM global WHERE global_variable = 'default_channel_id'";
	$result = mysql_query($query, $dbh);
	$row = mysql_fetch_array($result, MYSQL_NUM);
	$selected = intval($row[0]);
	$query="SELECT channel_id, channel_name FROM channel WHERE is_visible = 1 ORDER BY channel_name";
	$result = mysql_query($query, $dbh);
	$i=0;
	while($row = mysql_fetch_array($result, MYSQL_NUM)) {
		if ($row[0]==$selected) {
			$lineas_combo[$i]="<option value=".$row[0]." selected>".$row[1]."</option>";
		} else {
			$lineas_combo[$i]="<option value=".$row[0].">".$row[1]."</option>";
		}
		$i++;
	}
}
?>
<html>
<head>
<title><? echo($global_channel_name); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<form name="form1" method="post" action="">
  <font size="2" face="Courier New, Courier, mono">Default channel: 
  <select name="channel" id="channel">
<?
for ($j=0;$j<$i;$j++) {
	echo($lineas_combo[$j]);
}
?>
  </select>
  <input name="change" type="submit" id="change" value="Change">
  </font> 
</form>
<p><font face="Courier New, Courier, mono", size="2"><a href="index.php"><font size="2" face="Courier New, Courier, mono">&lt;-- 
  Control panel</font></a></font></p>
</body>
</html>
