<?
include_once("./../includes/channel_vars.php");
include_once("./../includes/init_database.php");
$dbh = initDB();

function getEmailPass($id,$dbh) {
	$ret="";
	$query="SELECT channel_mail,channel_pass FROM channel WHERE phone_id='$id'";
	$result = mysql_query($query, $dbh);
	if($row = mysql_fetch_array($result, MYSQL_NUM)) {
		$ret = $row[0].";".$row[1];
	}
	return $ret;
}

$id="";
if(isset($_GET['id'])) {
	$id=$_GET['id'];
	$email_pass=getEmailPass($id,$dbh);
	if($email_pass!="") {
		echo($email_pass.";".$smtp_server.";".$smtp_server_port);
	}
}
?>
