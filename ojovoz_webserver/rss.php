<?
include_once "includes/all.php";
$dbh=initDB();

$c=$_GET['c'];

$query="SELECT messages_per_page,is_ascending,show_tags,show_map,channel_name,channel_description FROM channel WHERE channel_id=$c";
$result = mysql_query($query, $dbh);
$row = mysql_fetch_array($result, MYSQL_NUM);

$n_messages=$row[0];
$ascending=$row[1];
$show_tags=$row[2];
$show_map=$row[3];
$channel_name=$row[4];
$channel_description=$row[5];

if ($ascending != 0) {
	$order = "";
} else {
  	$order = "DESC";
}

header("Content-Type: application/xml; charset=ISO-8859-1");
echo("<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?>\n");
echo("<rss version=\"2.0\">\n");
echo(" <channel>\n");
echo("  <title>".$channel_name."</title>\n");
echo("  <description><![CDATA[".$channel_description."]]></description>\n");

$query="SELECT message_id,message_text,message_date,message_sender FROM message WHERE channel_id=$c ORDER BY message_order $order LIMIT 0,$n_messages";
$result = mysql_query($query, $dbh);
while($row = mysql_fetch_array($result, MYSQL_NUM)) {
	$message_id=$row[0];
	$message_text=$row[1];
	$message_date=$row[2];
	$message_sender=$row[3];
	echo("   <item>\n");
	echo("    <pubDate>".$message_date."</pubDate>\n");
	echo("    <link>http://www.myserver.net/".$channel_folder."/".$main_page."?c=".$c."#".$message_id."</link>\n");
	echo("    <author>".$message_sender."</author>\n");
	$message_tags = GetMessageTags($message_id,$c,$main_page,"",$dbh,"");
	$message_tags = " Message Tags: ".trim(strip_tags($message_tags));
	echo("    <description><![CDATA[".urldecode($message_text).$message_tags."]]></description>\n");
	echo("   </item>\n");
}

echo(" </channel>\n");
echo("</rss>\n");
?>