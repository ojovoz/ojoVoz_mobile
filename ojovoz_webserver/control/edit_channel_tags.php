<?
include_once("./../includes/channel_vars.php");
include_once("./../includes/init_database.php");
$dbh = initDB();

if (isset($_GET['c'])) {
	$c=$_GET['c'];
} else if (isset($_POST['c'])) {
	$c=$_POST['c'];
}

if(isset($_POST['add_tag'])) {
	$tag=$_POST['tag'];
	$query="INSERT INTO tag_x_channel (channel_id,tag_id,tag_group_id) VALUES ($c,$tag,-1)";
	$result = mysql_query($query, $dbh);
}

if(isset($_POST['add_group'])) {
	$tag_group=$_POST['tag_group'];
	$query="INSERT INTO tag_x_channel (channel_id,tag_id,tag_group_id) VALUES ($c,-1,$tag_group)";
	$result = mysql_query($query, $dbh);
}
?>
<html>
<head>
<title><? echo($global_channel_name); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<p><strong><font size="2" face="Courier New, Courier, mono">Filter tags and tag groups in channel:</font></strong>
</p>
<p><font size="2" face="Courier New, Courier, mono">
<?
$query = "(SELECT tag_x_channel_id,CONCAT(CAST(tag_group_name AS char),' (group)'),tag_x_channel.tag_id,tag_x_channel.tag_group_id FROM tag_x_channel,tag_group WHERE channel_id=$c AND tag_group.tag_group_id=tag_x_channel.tag_group_id ORDER BY tag_group_name) UNION (SELECT tag_x_channel_id,CONCAT(CAST(tag_name AS char),' (tag)'),tag_x_channel.tag_id,tag_x_channel.tag_group_id FROM tag_x_channel,tag WHERE channel_id=$c AND tag.tag_id=tag_x_channel.tag_id ORDER BY tag_name)";
$result = mysql_query($query, $dbh);
$group_list="";
$tag_list="";
while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
?>
<a href="delete_channel_tag.php?c=<? echo($c); ?>&id=<? echo($row[0]); ?>">delete</a> <? echo($row[1]."<br>"); 
	if (strpos($row[1],"group")!==false) {
		if ($group_list=="") {
			$group_list=$row[3];
		} else {
			$group_list.=",".$row[3];
		}
	} else {
		if ($tag_list=="") {
			$tag_list=$row[2];
		} else {
			$tag_list.=",".$row[2];
		}
	}
}
if ($group_list!="") {
	$group_list="WHERE tag_group_id NOT IN (".$group_list.")";
}
if ($tag_list!="") {
	$tag_list="AND tag_id NOT IN (".$tag_list.")";
}
?>
</font></p>
<p><font size="2" face="Courier New, Courier, mono">Add tag or group to channel filter:</font></p>
<form name="form1" method="post" action="">
  <p>
    <select name="tag" id="tag">
	<?
	$query="SELECT tag_id, tag_name FROM tag WHERE tag_group_id=-1 ".$tag_list." ORDER BY tag_name";
	$result = mysql_query($query, $dbh);
	while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
	?>
	  <option value="<? echo($row[0]); ?>"><? echo($row[1]); ?></option>
	<? 
	}
	?>  
    </select>
    <input name="add_tag" type="submit" id="add_tag" value="Add tag">
</p>
  <p>
    <select name="tag_group" id="tag_group">
	<?
	$query="SELECT tag_group_id, tag_group_name FROM tag_group ".$group_list." ORDER BY tag_group_name";
	$result = mysql_query($query, $dbh);
	while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
	?>
	  <option value="<? echo($row[0]); ?>"><? echo($row[1]); ?></option>
	<? 
	}
	?> 
    </select>
    <input name="add_group" type="submit" id="add_group" value="Add group">
    <input name="c" type="hidden" id="c" value="<? echo($c); ?>">
</p>
</form>
<p><font size="2" face="Courier New, Courier, mono"><a href="edit_channel.php?id=<? echo($c); ?>">&lt;-- Edit channel</a> </font> </p>
</body>
</html>
