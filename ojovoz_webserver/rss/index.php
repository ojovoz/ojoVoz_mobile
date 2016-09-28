<?
header("Content-Type: application/xml; charset=ISO-8859-1");
include_once("./../includes/init_database.php");
include_once("./../includes/tagging_functions.php");
include_once("./../includes/channel_vars.php");
$dbh = initDB();

function messageApproved($id,$dbh){
	$ret=false;
	$query="SELECT is_published FROM attachment WHERE message_id=$id";
	$result = mysql_query($query, $dbh);
	while($row = mysql_fetch_array($result, MYSQL_NUM)) {
		if($row[0]==1){
			$ret=true;
			break;
		}
	}
	return $ret;
}

$protocol=explode('/',$_SERVER['SERVER_PROTOCOL']);
$server=strtolower($protocol[0]).'://'.$_SERVER['SERVER_NAME'];
$project=$server.'/'.$channel_folder;

echo('<?xml version="1.0" encoding="ISO-8859-1" ?>
	<rss version="2.0">
		<channel>
			<title>'.$global_channel_name.'</title>
			<link>'.$project.'</link>
			<description>'.$rss_description.'</description>
			<language>'.$rss_language.'</language>');

$query="SELECT message_text, message_date, message_sender, message_id, channel_id FROM message ORDER BY message_date DESC LIMIT 0,$rss_max_messages";
$result = mysql_query($query, $dbh);
while($row = mysql_fetch_array($result, MYSQL_NUM)) {
	if(messageApproved($row[3],$dbh)){
		$message_text = str_replace("\n","<br>",trim(urldecode($row[0])));
		if ($message_text!="") {
			if((strrpos($message_text,".")!=(strlen($message_text)-1)) && (strrpos($message_text,"!")!=(strlen($message_text)-1)) && (strrpos($message_text,"?")!=(strlen($message_text)-1))) {
				$message_text.=".";
			}
		}
		$message_text=stripslashes($message_text);
	
		$message_tags=GetMessageTagsCSV($row[3],$dbh);
		$categories=explode(",",$message_tags);
	
		$date_parts=explode(" ",$row[1]);
	
		echo('			
						<item>
							<title>Message sent by '.$row[2].' with tags: '.$message_tags.'</title>
							<description>'.$message_text.'</description>
							<link>'.$project.'/calc.php?c='.$row[4].'&amp;date='.$date_parts[0].'&amp;id='.$row[3].'</link>
							<pubDate>'.date(DATE_RSS, strtotime($row[1])).'</pubDate>');
					
		for($i=0;$i<sizeof($categories);$i++){
			echo('						
							<category>'.$categories[$i].'</category>');
		}
	
		$query2="SELECT filename, content_type FROM attachment WHERE message_id=".$row[3]." ORDER BY content_type";
		$result2 = mysql_query($query2, $dbh);
		while($row2 = mysql_fetch_array($result2, MYSQL_NUM)) {
			$filename=$project."/channels/".$row2[0];
			$filesize=filesize("./../channels/".$row2[0]);
			if($row2[1]==1) {
				$mime_content_type="image/jpeg";
			} else {
				$mime_content_type="audio/mpeg3";
			}
			echo('				
							<enclosure url="'.$filename.'" length="'.$filesize.'" type="'.$mime_content_type.'" />');
		}
	
		echo('			
						</item>');
	}				
}
echo('		
		</channel>
	</rss>');	
?>