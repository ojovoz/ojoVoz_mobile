<?
$id=$_GET['id'];
$c=$_GET['c'];
$date=$_GET['date'];
$mid=$_GET['mid'];

include_once("./../includes/init_database.php");
$dbh = initDB();

$query="SELECT filename, image_width, image_height FROM attachment WHERE attachment_id=$id";
$result=mysql_query($query,$dbh);
$row=mysql_fetch_array($result,MYSQL_NUM);

$w=$row[1];
$h=$row[2];

$filename="./../channels/".$row[0];
$pathsize=strpos($row[0],"image");
$newfilename=substr($row[0],0,$pathsize)."image/image".mktime().".jpg";

$img = imagecreatefromjpeg($filename);

$img = imagerotate($img,-90,0);

unlink($filename);

imagejpeg($img,"./../channels/".$newfilename,100);
imagedestroy($img);



$query="UPDATE attachment SET image_width=$h, image_height=$w, filename='$newfilename' WHERE attachment_id=$id";
$result=mysql_query($query,$dbh);

header("Location: edit_channel.php?c=$c&date=$date#$mid");

?>