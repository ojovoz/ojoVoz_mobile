<?
include_once("./../includes/channel_vars.php");
include_once("./../includes/init_database.php");
$dbh = initDB();

if (isset($_POST['add'])) {
	$tag = $_POST['tag'];
	if ($tag>0) {
		$query = "UPDATE tag SET in_map = 1 WHERE tag_id = $tag";
	} else {
		$tag=$tag*-1;
		$query = "UPDATE tag_group SET in_map = 1, color_in_map = '00' WHERE tag_group_id = $tag";
	}
	$result = mysql_query($query, $dbh);
} else if (isset($_POST['add_all'])) {
	$query = "UPDATE tag SET in_map = 1 WHERE 1";
	$result = mysql_query($query, $dbh);
} else if (isset($_POST['remove_all'])) {
	$query = "UPDATE tag SET in_map = 0 WHERE 1";
	$result = mysql_query($query, $dbh);
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title><? echo($global_channel_name); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<p><font size="2" face="Courier New, Courier, mono"><strong>Tags in map:</strong></font>
</p>
<p><font size="2" face="Courier New, Courier, mono"><?
$queryt[0]="SELECT tag_id, tag_name, color_in_map FROM tag WHERE in_map=1 ORDER BY tag_name";
$queryt[1]="SELECT tag_group_id*-1, tag_group_name, color_in_map FROM tag_group WHERE in_map=1 ORDER BY tag_group_name";
for($i=0;$i<sizeof($queryt);$i++) {
	$result=mysql_query($queryt[$i],$dbh);
	while($row=mysql_fetch_array($result,MYSQL_NUM)) {
		?><a href="remove_tag_map.php?id=<? echo($row[0]); ?>">remove</a> <? echo($row[1]); ?> <a href="tag_color.php?id=<? echo($row[0]); ?>"><img src="../includes/images/marker<? echo($row[2]); ?>s.png" align="absmiddle" border="0" title="click to change..."></a><br><?
	}
}
?></font></p>
<p><font size="2" face="Courier New, Courier, mono"><strong>Add to map: </strong></font></p>
<form name="form1" method="post" action="">
  <p><font size="2" face="Courier New, Courier, mono">Tag / tag group:</font> 
    <select name="tag" id="tag">
	  <? $query="SELECT tag_id,tag_name FROM tag WHERE in_map=0 ORDER BY tag_name";
	  	$result=mysql_query($query,$dbh);
	  	while($row=mysql_fetch_array($result,MYSQL_NUM)) {
		?>
	  <option value="<? echo($row[0]); ?>"><? echo($row[1]); ?></option><?
		}?><option value="">---Tag groups:</option><?
		$query="SELECT tag_group_id,tag_group_name FROM tag_group WHERE in_map=0 ORDER BY tag_group_name";
	  	$result=mysql_query($query,$dbh);
	  	while($row=mysql_fetch_array($result,MYSQL_NUM)) {
		?>
	  <option value="<? echo($row[0]*-1); ?>"><? echo($row[1]); ?></option><?
		}
	  ?>
      </select>
</p>
  <p>
    <input name="add" type="submit" id="add" value="Add">
    <input name="add_all" type="submit" id="add_all" value="Add all">
    <input name="remove_all" type="submit" id="remove_all" value="Remove all"> 
  </p>
</form>
<p><font size="2" face="Courier New, Courier, mono"><a href="index.php">&lt;-- Control Panel</a></font> </p>
</body>
</html>
