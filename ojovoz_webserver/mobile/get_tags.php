<?
include_once("./../includes/init_database.php");
$dbh = initDB();

function getTags($id,$dbh) {
	$query="SELECT tag_list FROM channel WHERE phone_id='$id'";
	$result = mysql_query($query, $dbh);
	$row = mysql_fetch_array($result, MYSQL_NUM);
	return $row[0];
}

$id="";
if(isset($_GET['id'])) {
	$id=$_GET['id'];
	$tags=getTags($id,$dbh);
	echo($tags);
}
?>
