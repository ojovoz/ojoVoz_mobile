<?
include_once "includes/channel_vars.php";
include_once "includes/init_database.php";

$dbh=initDB();

$c=$_GET['c'];
$date=$_GET['date'];
$id=$_GET['id'];

$query="SELECT messages_per_page,is_ascending FROM channel WHERE channel_id=$c";
$result = mysql_query($query, $dbh);
$row=mysql_fetch_array($result, MYSQL_NUM);
$nm=$row[0];
if ($row[1] != 0) {
	$order = "";
} else {
  	$order = "DESC";
}

$query="SELECT message_id FROM message WHERE DATE(message_date)='$date' AND channel_id=$c ORDER BY message_date $order";
$result = mysql_query($query, $dbh);
if(mysql_num_rows($result)<=$nm) {
	header("Location: $main_page?c=$c&date=$date#$id");
} else {
	$i=0;
	$from=0;
	while($row=mysql_fetch_array($result, MYSQL_NUM)) {
		if($row[0]==$id) {
			break;
		} else {
			$i++;
			if($i==$nm) {
				$i=0;
				$from=$from+$nm;
			}
		}
	}
	header("Location: $main_page?c=$c&date=$date&from=$from#$id");
}
?>




