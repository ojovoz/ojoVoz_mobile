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
		<channel>');

$query="SELECT message_text, message_date, message_sender, message_id, channel_id FROM message ORDER BY message_date DESC LIMIT 0,$rss_max_messages";
$result = mysql_query($query, $dbh);
while($row = mysql_fetch_array($result, MYSQL_NUM)) {
	if(messageApproved($row[3],$dbh)){
		/*
		$message_text = str_replace("\n","<br>",trim(urldecode($row[0])));
		if ($message_text!="") {
			if((strrpos($message_text,".")!=(strlen($message_text)-1)) && (strrpos($message_text,"!")!=(strlen($message_text)-1)) && (strrpos($message_text,"?")!=(strlen($message_text)-1))) {
				$message_text.=".";
			}
		}
		$message_text=stripslashes($message_text);
		*/
	
		$message_tags=GetMessageTagsCSV($row[3],$dbh);
		//$categories=explode(",",$message_tags);
	
		//$date_parts=explode(" ",$row[1]);
	
		
					
		$query2="SELECT filename, content_type FROM attachment WHERE message_id=".$row[3]." ORDER BY content_type";
		$result2 = mysql_query($query2, $dbh);
		while($row2 = mysql_fetch_array($result2, MYSQL_NUM)) {
			$filename=$project."/channels/".$row2[0];
			//$filesize=filesize("./../channels/".$row2[0]);
			if($row2[1]==1) {
				//$mime_content_type="image/jpeg";
				$image=$filename;
			} else {
				//$mime_content_type="audio/mpeg3";
				$audio=$filename;
			}
			
		}
		
		$date = date("Y-m",strtotime($row[1]));
		$date_parts = explode("-",$date);
		$year = $date_parts[0];
		$month = $date_parts[1];
		echo('			
				<item id="'.$row[3].'" date="'.date("Y-m-d H:i:s", strtotime($row[1])).'" image="'.$image.'" audio="'.$audio.'" month="'.$month.'" year="'.$year.'">Sent by '.$row[2].'</item>');
		
	}				
}
echo('		
		</channel>');	
?>