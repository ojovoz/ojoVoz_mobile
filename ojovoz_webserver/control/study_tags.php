<?
include_once("./../includes/channel_vars.php");
include_once("./../includes/init_database.php");
$dbh = initDB();

if (isset($_POST['add'])) {
	$tag = $_POST['tag'];
	if ($tag>0) {
		$query = "UPDATE tag SET is_study = 1 WHERE tag_id = $tag";
	} else {
		$tag=$tag*-1;
		$query = "UPDATE tag_group SET is_study = 1 WHERE tag_group_id = $tag";
	}
	$result = mysql_query($query, $dbh);
} else if (isset($_POST['remove_all'])) {
	$query = "UPDATE tag SET is_study = 0 WHERE 1";
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
<p><font size="2" face="Courier New, Courier, mono"><strong>Study tags:</strong></font>
</p>
<p><font size="2" face="Courier New, Courier, mono"><?
$query="SELECT tag_id, tag_name FROM tag WHERE is_study=1 ORDER BY tag_name";
$result=mysql_query($query,$dbh);
while($row=mysql_fetch_array($result,MYSQL_NUM)) {
	?><a href="remove_tag_study.php?id=<? echo($row[0]); ?>">remove</a> <? echo($row[1]); ?><br><?
}
?></font></p>
<p><font size="2" face="Courier New, Courier, mono"><strong>Add to study: </strong></font></p>
<form name="form1" method="post" action="">
  <p><font size="2" face="Courier New, Courier, mono">Tag / tag group:</font> 
    <select name="tag" id="tag">
	  <? $query="SELECT tag_id,tag_name FROM tag WHERE is_study=0 ORDER BY tag_name";
	  	$result=mysql_query($query,$dbh);
	  	while($row=mysql_fetch_array($result,MYSQL_NUM)) {
		?>
	  <option value="<? echo($row[0]); ?>"><? echo($row[1]); ?></option><?
		}?><option value="">---Tag groups:</option><?
		$query="SELECT tag_group_id,tag_group_name FROM tag_group WHERE is_study=0 ORDER BY tag_group_name";
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
    <input name="remove_all" type="submit" id="remove_all" value="Remove all"> 
  </p>
</form>
<p><font size="2" face="Courier New, Courier, mono"><a href="index.php">&lt;-- Control Panel</a></font> </p>
</body>
</html>
