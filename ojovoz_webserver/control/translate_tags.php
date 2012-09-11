<?
include_once("./../includes/channel_vars.php");
include_once("./../includes/init_database.php");
include_once("./../includes/tagging_functions.php");
$dbh = initDB();

$l=explode(",",$ov_languages);
$w=intval(90/(sizeof($l)+1));

if (isset($_POST['add'])) {
	$id=$_POST['tag'];
	if ($id>0) {
		$query="SELECT tag_name FROM tag WHERE tag_id = $id";
	} else {
		$query="SELECT tag_group_name FROM tag_group WHERE tag_group_id = ($id*-1)";
	}
	$result = mysql_query($query, $dbh);
	$row = mysql_fetch_array($result, MYSQL_NUM);
	$name = $row[0];
	$query="INSERT INTO tag_x_language VALUES (NULL,$id,0,'$name')";
	$result = mysql_query($query, $dbh);
} else if (isset($_POST['add_new'])) {
	$tag=$_POST['new_tag'];
	$id=GetTagIDFromName($tag,$dbh);
	if ($id==-1) {
		$query="INSERT INTO tag (tag_name) VALUES ('$tag')";
		$result = mysql_query($query, $dbh);
		$id=mysql_insert_id();
		$query="INSERT INTO tag_x_language VALUES (NULL,$id,0,'$tag')";
		$result = mysql_query($query, $dbh);
	} else {
		$query="SELECT tag_id FROM tag_x_language WHERE tag_id = $id";
		$result = mysql_query($query, $dbh);
		if (mysql_num_rows($result)>0) {
		} else {
			$query="INSERT INTO tag_x_language VALUES (NULL,$id,0,'$tag')";
			$result = mysql_query($query, $dbh);
		}
	}
}
?>

<html>
<head>
<title><? echo($global_channel_name); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body><font size="2" face="Courier New, Courier, mono">
<table width="90%" border="1" cellspacing="0" cellpadding="0">
<tr>
<td bgcolor="#CCCCCC" width="<? echo($w); ?>%" align="left">Original tag</td>
<?
for($i=0;$i<sizeof($l);$i++) {
?>
<td bgcolor="#CCCCCC" width="<? echo($w); ?>%" align="left"><? echo($l[$i]); ?></td>
<?
}
?>
</tr>
<?	
$queryt[0]="SELECT DISTINCT tag_x_language.tag_id, tag.tag_name FROM tag, tag_x_language WHERE tag.tag_id = tag_x_language.tag_id ORDER BY tag.tag_name";
$queryt[1]="SELECT DISTINCT tag_x_language.tag_id, tag_group.tag_group_name FROM tag_group, tag_x_language WHERE tag_x_language.tag_id < 0 AND tag_group.tag_group_id = tag_x_language.tag_id*-1 ORDER BY tag_group.tag_group_name";
for($j=0;$j<sizeof($queryt);$j++) {
	$result = mysql_query($queryt[$j], $dbh);
	$lang=0;
	while($row = mysql_fetch_array($result, MYSQL_NUM)) {
		echo("<tr>");
		$id=$row[0];
		echo("<td bgcolor=\"#FFFFFF\" width=\"$w%\" align=\"left\">$row[1]</td>");
		for($i=0;$i<sizeof($l);$i++) {
			$query2="SELECT translation FROM tag_x_language WHERE tag_id=$id AND language_id=$i";
			$result2 = mysql_query($query2, $dbh);
			if ($row2 = mysql_fetch_array($result2, MYSQL_NUM)) {
				echo("<td bgcolor=\"#FFFFFF\" width=\"$w%\" align=\"left\"><a href=\"edit_tag_translation.php?id=$id&lang=$i&prev=1\">$row2[0]</a></td>");
			} else {
				echo("<td bgcolor=\"#FFFFFF\" width=\"$w%\" align=\"left\"><a href=\"edit_tag_translation.php?id=$id&lang=$i&prev=0\">???</a></td>");
			}
		}
	}
	echo("</tr>");
}
?>
</table>
</font><br>
<form name="form1" method="post" action="">
  <p><font size="2" face="Courier New, Courier, mono">Add tag / tag group: 
    <select name="tag">
<?
$query="SELECT tag_id, tag_name FROM tag WHERE tag_id NOT IN (SELECT DISTINCT tag_id FROM tag_x_language) ORDER BY tag_name";
$result = mysql_query($query, $dbh);
while($row = mysql_fetch_array($result, MYSQL_NUM)) {
	echo("<option value=\"$row[0]\">$row[1]</option>");
}
echo('<option value="">---Tag groups:</option>');
$query="SELECT tag_group_id, tag_group_name FROM tag_group WHERE (tag_group_id*-1) NOT IN (SELECT DISTINCT tag_id FROM tag_x_language) ORDER BY tag_group_name";
$result = mysql_query($query, $dbh);
while($row = mysql_fetch_array($result, MYSQL_NUM)) {
	echo("<option value=\"".($row[0]*-1)."\">$row[1]</option>");
}

?>
</select>
<input type="submit" name="add" value="Add">
</font></p>
<p><font size="2" face="Courier New, Courier, mono">Add new tag: 
<input name="new_tag" type="text" id="new_tag">
<input name="add_new" type="submit" id="add_new" value="Add">
</font>
</p>
</form>
<p><font face="Courier New, Courier, mono", size="2"><a href="index.php"><font size="2" face="Courier New, Courier, mono">&lt;-- Control Panel</font></a></font></p>
</body>
</html>
