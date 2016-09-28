<?
//miscellaneous functions
function Multi_strpos($pattern, $sequence) {
	$position=array();
	$n = -1;
	while (ereg($pattern, $sequence)) {
		$n++;
	   	$fragment = split($pattern, $sequence);
    	$trimsize = (strlen($fragment[0]))+1;
	    $sequence = "*".substr($sequence, $trimsize);
		if ($n>0) {
    		$position[$n] = (strlen($fragment[0]) + $position[($n-1)]);
		} else {
			$position[$n] = strlen($fragment[0]);
		}
	}
	return $position;
}

function getChannelDate($tz) {
	$TimeDiff = date('I')+$tz;
	$TimeZoneEpoc = time() + ($TimeDiff*60*60);
	$date = date('Y-m-d H:i:s',$TimeZoneEpoc);
	return $date;
}

function formatDate($d) {
	$parts=explode("_",$d);
	return $parts[2]."-".$parts[1]."-".$parts[0]." ".$parts[3].":".$parts[4].":".$parts[5];
}

/*
function CalculateChannelSections($f,$t,$n) {
	$nsections=intval($t/$n);
	$from = 1;
	$to = $n;
	for($i=0;$i<$nsections;$i++) {
		if($f == ($n*$i)) {
			$sections[$i]="<option value=\"$f\" selected=\"selected\">$from - $to</option>\n";
		} else {
			$x=$n*$i;
			$sections[$i]="<option value=\"$x\">$from - $to</option>\n";
		}
		$from += $n;
		$to += $n;
		if ($to > $t) {
			$to = $t;
		}
	}
	if (($t%$n)>0) {
		$x = ($n*($nsections));
		if ($from == $t) {
			$s = $from;
		} else {
			$s = $from." - ".$t;
		}
		if($f == ($x)) {
			$sections[$nsections]="<option value=\"$f\" selected=\"selected\">$s</option>\n";
		} else {
			$sections[$nsections]="<option value=\"$x\">$s</option>\n";
		}
	}
	return $sections;
}
*/

function CalculateChannelSections($f,$t,$n,$ov_page_combo_prefix,$ov_page_combo_separator) {
	$nsections=intval($t/$n);
	$from = $t;
	$to = $t-$n+1;
	for($i=0;$i<$nsections;$i++) {
		if ($from==$to) {
			$s=$ov_page_combo_prefix." ".$from;
		} else {
			$s=$ov_page_combo_prefix." ".$from." ".$ov_page_combo_separator." ".$to;
		}
		if($f == ($n*$i)) {
			$sections[$i]="<option value=\"$f\" selected=\"selected\">$s</option>\n";
		} else {
			$x=$n*$i;
			$sections[$i]="<option value=\"$x\">$s</option>\n";
		}
		$from -= $n;
		$to -= $n;
		if ($to < 1) {
			$to = 1;
		}
	}
	if (($t%$n)>0) {
		$x = ($n*($nsections));
		if ($from == 1) {
			$s = $ov_page_combo_prefix." ".$from;
		} else {
			$s = $ov_page_combo_prefix." ".$from." $ov_page_combo_separator 1";
		}
		if($f == ($x)) {
			$sections[$nsections]="<option value=\"$f\" selected=\"selected\">$s</option>\n";
		} else {
			$sections[$nsections]="<option value=\"$x\">$s</option>\n";
		}
	}
	return $sections;
}


function TrimFileExtension($f) {
	$point=strpos($f,".");
	if ($point>0) {
		$f=substr($f,0,$point);
	}
	return $f;
}

function readSourcePage($s) {
	$pgdata="";
	$fd = @fopen($s,"r"); 
	if ($fd) {
		while(!feof($fd)) {
			stream_set_timeout($fd, 20);
			$pgdata .= fread($fd, 5000);
		}
		fclose($fd);
	} 
	$bgcolor=strpos($pgdata,"background-color");
	if ($bgcolor===false) {
		$bgcolor=strpos($pgdata,"bgcolor")+9;
		$end_bgcolor=strpos($pgdata,'"',$bgcolor);
		$size_bgcolor=$end_bgcolor-$bgcolor;
		$color=trim(substr($pgdata,$bgcolor,$size_bgcolor));
	} else {
		$bgcolor+=18;
		$end_bgcolor=strpos($pgdata,";",$bgcolor);
		$size_bgcolor=$end_bgcolor-$bgcolor;
		$color=trim(substr($pgdata,$bgcolor,$size_bgcolor));
	}
	$body=strpos($pgdata,"<body");
	$body_init_end=strpos($pgdata,">",$body)+1;
	$body_end=strpos($pgdata,"</body>",$body_init_end);
	$body_size=$body_end-$body_init_end;
	$ret[0]=substr($pgdata,$body_init_end,$body_size);
	$style=strpos($pgdata,"<style");
	if ($style===false) {
		$ret[1]="";
	} else {
		$style_end=strpos($pgdata,"</style>",$style)+8;
		$style_size=$style_end-$style;
		$ret[1]=substr($pgdata,$style,$style_size);
	}
	$ret[2]=$color;
	return $ret;
}

function UpdateSelectionList($selection_list,$n) {
	$ret="";
	$a=explode(",",$selection_list);
	$b=explode(",",$n);
	$n=implode("/",$b);
	$ret=$a[0];
	$found=false;
	for($i=1;$i<sizeof($a);$i++) {
		if($n==$a[$i]) {
			$found=true;
		} else {
			$ret.=",".$a[$i];
		}
	}
	if (!$found) {
		$ret.=",".$n;
	}
	return $ret;
}

function GetPage($query) {
	$pgdata = "";
	$fd = @fopen($query,"r"); 
	if ($fd) {
		while(!feof($fd)) {
			stream_set_timeout($fd, 20);
			$pgdata .= fread($fd, 5000);
		}
		fclose($fd);
	}
	return $pgdata;
}

function RemoveBadChars($q) {
	$ret="";
	$ok="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890_";
	$n=strlen($q);
	for($i=0;$i<$n;$i++) {
		$c=substr($q,$i,1);
		if(strpos($ok,$c)===false) {
		} else {
			$ret.=$c;
		}
	}
	return $ret;
}

function runExternal ($cmd) {
	$descriptorspec = array(
		0 => array("pipe", "r"), // stdin is a pipe that the child will read from
		1 => array("pipe", "w"), // stdout is a pipe that the child will write to
		2 => array("pipe", "w") // stderr is a file to write to
	);

	$pipes= array();
	$process = proc_open($cmd, $descriptorspec, $pipes);

	$output= "";

	if (!is_resource($process)) return false;

	#close child's input imidiately
	fclose($pipes[0]);

	stream_set_blocking($pipes[1],false);
	stream_set_blocking($pipes[2],false);

	$todo= array($pipes[1],$pipes[2]);

	while( true ) {
		$read= array();
		if(!feof($pipes[1])) $read[]= $pipes[1];
		if(!feof($pipes[2])) $read[]= $pipes[2];

		if (!$read) break;

		$ready= stream_select($read, $write=NULL, $ex= NULL, 2);

		if ($ready === false) {
			break; #should never happen - something died
		}

		foreach ($read as $r) {
			$s= fread($r,1024);
			$output.= $s;
		}
	}

	fclose($pipes[1]);
	fclose($pipes[2]);

	$code= proc_close($process);

	return $output;
}

function ConvertAMRToMP3($file1, $file2, $servpath, $ffmpeg_path, $channel_folder, $channel_name, $sample_rate) {
	//$output = runExternal( "ffmpeg -i ".$servpath.$channel_folder."/channels/".$channel_name.$file1." -ar ".$sample_rate." ".$servpath.$channel_folder."/channels/".$channel_name.$file2);
	//echo($ffmpeg_path."ffmpeg -i ".$servpath.$channel_folder."/channels/".$channel_name.$file1." -ar ".$sample_rate." ".$servpath.$channel_folder."/channels/".$channel_name.$file2);
	exec($ffmpeg_path."ffmpeg -i ".$servpath.$channel_folder."/channels/".$channel_name.$file1." -ar ".$sample_rate." ".$servpath.$channel_folder."/channels/".$channel_name.$file2,$output);
}

function ShowLanguageOptions($page,$c,$date,$languages,$lang,$from="",$id="") {
	$ret="";
	$l=explode(",",$languages);
	for($i=0;$i<sizeof($l);$i++) {
		if ($i!=$lang) {
			if ($page=="tags.php") {
				$ret.="<a href=\"".$page."?t=$c&l=$i\">".$l[$i]."</a> ";
			} else if ($page=="about.php") {
				$ret.="<a href=\"".$page."?l=$i\">".$l[$i]."</a> ";
			} else if ($page=="comment.php") {
				$ret.="<a href=\"".$page."?c=$c&id=$id&l=$i&date=$from\">".$l[$i]."</a> ";
			} else if ($page=="map.php") {
				$ret.="<a href=\"".$page."?l=$i\">".$l[$i]."</a> ";	
			} else {
				$ret.="<a href=\"".$page."?c=$c&date=$date&from=$from&l=$i\">".$l[$i]."</a> ";
			}
		} else {
			$ret.=$l[$i]." ";
		}
	}
	return $ret;
}

function CheckOrientation($folder,$subfolder,$index,$extension) {
	$filename=$folder.$subfolder.$index.$extension;
	$exif=exif_read_data("channels/".$filename);
	$orientation=$exif['Orientation'];
	if($orientation!=1 && $orientation!="" && $orientation!="1"){
		$new_filename=$folder.$subfolder.mktime().$extension;
		$img=imagecreatefromjpeg("channels/".$filename);
		$img=imagerotate($img,-90,0);
		unlink("channels/".$filename);
		imagejpeg($img,"channels/".$new_filename,100);
		imagedestroy($img);
		$filename=$new_filename;
	}
	return $filename;
}

function ScaleDown($folder,$subfolder,$index,$extension,$w_src,$h_src,$w_dest,$h_dest) {
	$w_dest=round($w_dest);
	$h_dest=round($h_dest);
	$filename=$folder.$subfolder.$index.$extension;
	$new_filename=$folder.$subfolder.mktime().$extension;
	$img=imagecreatefromjpeg("channels/".$filename);
	$new_img=imagecreatetruecolor($w_dest,$h_dest);
	imagecopyresized($new_img,$img,0,0,0,0,$w_dest,$h_dest,$w_src,$h_src);
	unlink("channels/".$filename);
	imagejpeg($new_img,"channels/".$new_filename,100);
	imagedestroy($img);
	imagedestroy($new_img);
	$filename=$new_filename;
	return $filename;
}
?>
