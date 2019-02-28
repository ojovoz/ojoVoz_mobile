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
		$email_pass_parts=explode(";",$email_pass);
		$df = fopen("php://output", 'w');
		$row=array($email_pass_parts[0],$email_pass_parts[1],$multimedia_subject,$smtp_server,$smtp_server_port);
		fputcsv($df, $row);
		fclose($df);
	}
}
?>
