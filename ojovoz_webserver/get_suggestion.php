<?
include_once("includes/init_database.php");
$dbh = initDB();
$q = trim($_GET['q']);
if ($q != "") {
	$query = "SELECT COUNT(tag_x_message.tag_id) AS n, tag_name FROM tag, tag_x_message WHERE tag.tag_id = tag_x_message.tag_id AND tag.tag_name LIKE '$q%' GROUP BY tag_name ORDER BY n DESC LIMIT 0 , 2";
	$result = mysql_query($query, $dbh);
	while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
		echo(htmlentities($row[1])." ");
	}
}
?>
