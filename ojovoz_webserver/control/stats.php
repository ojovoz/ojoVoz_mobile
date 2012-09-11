<?
include_once("./../includes/channel_vars.php");
include_once("./../includes/init_database.php");
$dbh = initDB();
?>
<html>
<head>
<title><? echo($global_channel_name); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body bgcolor="#FFFFFF">
<p><font face="Courier New, Courier, mono" size="2">Messages per participant:</font></p>
<table border="0" width="50%" cellspacing="5" cellpadding="0">
<?
function GetWeight($who,$dbh) {
	$query="SELECT filename FROM attachment, message WHERE message_sender='$who' AND message.message_id = attachment.message_id";
	$result = mysql_query($query, $dbh);
	$weight=0;
	while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
		$file="./../channels/".$row[0];
		$weight = $weight + round((filesize($file) / 1024),2); 
	}
	return $weight;
}

$query1 = "SELECT DISTINCT user_alias FROM user ORDER BY user_alias";
$result1 = mysql_query($query1, $dbh);
$i=0;

while ($row1 = mysql_fetch_array($result1, MYSQL_NUM)) {
    $user = $row1[0];
    $query2 = "SELECT count(message_id) AS n FROM message WHERE message_sender = '$user'";
    $result2 = mysql_query($query2, $dbh);
    if ($row2 = mysql_fetch_array($result2, MYSQL_NUM)) {
        $participant[$i]=$user;
        $messages[$i]=$row2[0];
        mysql_free_result($result2);
        $query3 = "SELECT count(attachment_id) AS c FROM message, attachment WHERE message_sender = '$user' AND attachment.message_id = message.message_id";
        $result3 = mysql_query($query3, $dbh);
        if ($row3 = mysql_fetch_array($result3, MYSQL_NUM)) {
            $files[$i]=$row3[0];
        }
        mysql_free_result($result3);
        $i++;
    }
}
mysql_free_result($result1);

for ($j=1;$j<$i;$j++) {   
	for ($k=0;$k<$i-1;$k++) {
		if ($messages[$k] > $messages[$k+1]) {	
			$temp = $files[$k];
			$files[$k] = $files[$k+1];
			$files[$k+1] = $temp;
			
			$temp = $messages[$k];
			$messages[$k] = $messages[$k+1];
			$messages[$k+1] = $temp;
			
			$temp = $participant[$k];
			$participant[$k] = $participant[$k+1];
			$participant[$k+1] = $temp;
		}
	}
}

?>
<tr bgcolor="#CCCCCC"><td width="20%"><font face="Courier New, Courier, mono" size="2"><b>Participant</b></font></td>
<td><font face="Courier New, Courier, mono" size="2"><b>Messages</b></font></td>
<td><font face="Courier New, Courier, mono" size="2"><b>Files</b></font></td>
<td><font face="Courier New, Courier, mono" size="2"><b>Weight</b></font></td></tr>
<?
$ct=0;
$total_messages=0;
$total_files=0;
$total_weight=0;

for ($j=0;$j<$i;$j++) {    
    $who = $participant[$j];
    $n = $messages[$j];
    $w = $files[$j];
    $weightk = GetWeight($who,$dbh);
    $weightm = round(($weightk/1024),2);
    $total_weight = $total_weight + $weightk;
    $total_messages = $total_messages + $n;
    $total_files = $total_files + $w;
    if ($ct==0) {
        echo("<tr bgcolor=\"#FFFFFF\"><td width=\"20%\">");
    } else {
        echo("<tr bgcolor=\"#CCCCCC\"><td width=\"10%\">");
    }

    echo("<font face=\"Courier New, Courier, mono\" size=\"2\">".$who."</font></td>");

    echo("<td><font face=\"Courier New, Courier, mono\" size=\"2\">".$n."</font></td>");

    echo("<td><font face=\"Courier New, Courier, mono\" size=\"2\">".$w."</font></td>");

    echo("<td><font face=\"Courier New, Courier, mono\" size=\"2\">".$weightk."Kb, ".$weightm."Mb</font></td></tr>");

    $ct = 1 - $ct;    

}

$total_weightm = round(($total_weight/1024),2);

echo("<tr bgcolor=\"#FFCCCC\"><td width=\"20%\"><font face=\"Courier New, Courier, mono\" size=\"2\">Total</font></td>");

echo("<td><font face=\"Courier New, Courier, mono\" size=\"2\">".$total_messages."</font></td>");

echo("<td><font face=\"Courier New, Courier, mono\" size=\"2\">".$total_files."</font></td>");

echo("<td><font face=\"Courier New, Courier, mono\" size=\"2\">".$total_weight."Kb, ".$total_weightm."Mb</font></td></tr>");

?>

</font>

</table>

<p><font face="Courier New, Courier, mono" size="2"><a href="index.php">&lt;--- 

  Control Panel</a> </font> </p>

</body>

</html>