<?
//mail functions

include_once("tagging_functions.php");
include_once("database_functions.php");
include_once("misc_functions.php");
include_once("geo_functions.php");
include_once("channel_vars.php");

function decode_ISO88591($string) {               
	$string=str_replace("=?iso-8859-1?q?","",$string);
  	$string=str_replace("=?iso-8859-1?Q?","",$string);
  	$string=str_replace("?=","",$string);

  	$charHex=array("0","1","2","3","4","5","6","7","8","9","A","B","C","D","E","F");
       
	for($z=0;$z<sizeof($charHex);$z++) {
		for($i=0;$i<sizeof($charHex);$i++) {
      		$string=str_replace(("=".($charHex[$z].$charHex[$i])),chr(hexdec($charHex[$z].$charHex[$i])),$string);
    	}
  	}
  	return($string);
}

// check if email address is valid
function validate_email($val) {
	if($val != "") {
		$pattern = "/^([a-zA-Z0-9])+([\.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)+/";
		if(preg_match($pattern, $val)) {
			return true;
		}
		else {
			return false;
		}
	}
	else {
		return false;
	}
}

// parse message body
function parse($structure) {
	$type = array("text", "multipart", "message", "application", "audio", "image", "video", "other");
	$encoding = array("7bit", "8bit", "binary", "base64", "quoted-printable", "other");
	// create an array to hold message sections
	$ret = array();
	// split structure into parts
	$parts = $structure->parts;
	/*
	iterate through parts
	and create an array whose every element
	represents one part
	each element is itself an associative array 
	with keys representing the 
		- part number
		- part type 
		- encoding
		- disposition
		- size
		- filename
	*/
	for($x=0; $x<sizeof($parts); $x++) {
		$ret[$x]["pid"] = ($x+1);	
		$this_part = $parts[$x];
		// default to text
		if ($this_part->type == "") { $this_part->type = 0; }
		$ret[$x]["type"] = $type[$this_part->type] . "/" . strtolower($this_part->subtype);	
		// default to 7bit
		if ($this_part->encoding == "") { $this_part->encoding = 0; }
		$ret[$x]["encoding"] = $encoding[$this_part->encoding];	
		$ret[$x]["size"] = strtolower($this_part->bytes);	
		if ($this_part->ifdisposition) {
			$ret[$x]["disposition"] = strtolower($this_part->disposition);	
			if (strtolower($this_part->disposition) == "attachment" || strtolower($this_part->disposition) == "inline") {
				$params = $this_part->dparameters;
				if (is_null($params)) {
					$params = $this_part->parameters;
				}
				if (!is_null($params)) {
					foreach ($params as $p) {
						if($p->attribute == "FILENAME" || $p->attribute == "NAME") {
							$ret[$x]["name"] = $p->value;	
							break;			
						}
					}
				}
			}
		} 
	}
	return $ret;
}

// iterate through object returned by parse()
// create a new array holding information only on message attachments
function get_attachments($arr) {
	for($x=0; $x<sizeof($arr); $x++) {
		if($arr[$x]["disposition"] == "attachment" || $arr[$x]["disposition"] == "inline") {
			$ret[] = $arr[$x];
		}
	}
	return $ret;
}

// remove extraneous stuff from email addresses
// returns an email address stripped of everything but the address itself
function clean_address(&$val, $index) {
	// clean out whitespace
	$val = trim($val);
	// look for angle braces
	$begin = strrpos($val, "<");
	$end = strrpos($val, ">");
	if ($begin !== false) {
		// return whatever is between the angle braces
		$val = substr($val, ($begin+1), $end-$begin-1);
	}
}

function CheckMessagesRandomChannel($get_tags_from_subject,$server,$dbh,$tz,$get_user_from_message_subject,$get_date_from_exif,$convert_to_mp3,$servpath,$sample_rate,$channel_folder,$static_map_width,$static_map_height,$api_key,$get_reverse_geocoding,$ffmpeg_path,$max_messages_from_inbox) {
	$query="SELECT channel_mail,channel_pass,channel_id,channel_folder FROM channel WHERE is_active=1 AND is_crono=0 AND is_visible=1 AND channel_mail<>'' ORDER BY RAND() LIMIT 0,1";
	$result=mysql_query($query,$dbh);
	if ($row=mysql_fetch_array($result,MYSQL_NUM)) {
		CheckMessages($row[0],$row[1],$row[2],$row[3],$get_tags_from_subject,$server,$dbh,$tz,$get_user_from_message_subject,$get_date_from_exif,$convert_to_mp3,$servpath,$sample_rate,$channel_folder,$static_map_width,$static_map_height,$api_key,$get_reverse_geocoding,$ffmpeg_path,$max_messages_from_inbox);
	}
}

function DecodeSubject($s) {
	$ret=$s;
	$elements=imap_mime_header_decode($s);
	if (sizeof($elements)>0) {
		if($elements[0]->charset=="utf-8") {
			$ret=utf8_decode($elements[0]->text);
		} else if ($elements[0]->charset="ISO-8859-1") {
			$ret=decode_ISO88591($elements[0]->text);
		}
	}
	return $ret;
}

function CheckMessages($user,$pass,$c,$folder,$get_tags_from_subject,$server,$dbh,$tz,$get_user_from_message_subject,$get_date_from_exif,$convert_to_mp3,$servpath,$sample_rate,$channel_folder,$static_map_width,$static_map_height,$api_key,$get_reverse_geocoding,$ffmpeg_path,$max_messages_from_inbox) {
	if ($inbox = imap_open ($server, $user, $pass)) {
		$total = imap_num_msg($inbox);
		if($total>$max_messages_from_inbox){
			$total=$max_messages_from_inbox;
		}
		for($x=1; $x<=$total; $x++) {
			$headers = imap_header($inbox, $x);
			$structure = imap_fetchstructure($inbox, $x);
			$sections = parse($structure);
			if (isset($headers->subject)) {
				$subject = DecodeSubject($headers->subject);
			} else {
				$subject = "";
			}
			if ($subject=="ojovoz" && is_array($sections) && sizeof($sections)>0) {
				$maxorder = GetMaxMessageOrder($c,$dbh);
				$query = "INSERT INTO message (channel_id, message_order) VALUES ($c,$maxorder)";
				$result = mysql_query($query, $dbh);
				$current_message = mysql_insert_id();
				$lat="";
				$long="";
				for($y=0; $y<sizeof($sections); $y++) {	
					$type = $sections[$y]["type"];
					$encoding = $sections[$y]["encoding"];
					$filename = substr($complete_filename,0,strrpos($complete_filename,"."));
					$pid = $sections[$y]["pid"];
					$attachment = imap_fetchbody($inbox,$x,$pid);
					if (strpos($sections[$y]["name"],".jpg") > 0 && $type=="image/jpeg") {
						$subfolder = "/image/image";
						$extension = ".jpg";
						$att_type = 1;
					} else if (strpos($sections[$y]["name"],".wav") > 0 && $type=="application/octet-stream") {
						$subfolder = "/sound/sound";
						$extension = ".wav";
						$att_type = 2;
					} else if (strpos($sections[$y]["name"],".amr") > 0 && $type=="application/octet-stream") {
						$subfolder = "/sound/sound";
						$extension = ".amr";
						$att_type = 2;
					} else if (strpos($sections[$y]["name"],".mp3") > 0 && $type=="application/octet-stream") {
						$subfolder = "/sound/sound";
						$extension = ".mp3";
						$att_type = 2;
					} else if (strpos($sections[$y]["name"],".3gp") > 0 && $type=="application/octet-stream") {
						$subfolder = "/sound/sound";
						$extension = ".3gp";
						$att_type = 2;
					} else if (strpos($sections[$y]["name"],".mp4") > 0 && $type=="application/octet-stream") {
						$subfolder = "/video/video";
						$extension = ".mp4";
						$att_type = 3;
					} else if (strpos($sections[$y]["name"],".avi") > 0 && $type=="application/octet-stream") {
						$subfolder = "/video/video";
						$extension = ".avi";
						$att_type = 3;
				    } else if ($type=="text/plain" || $type=="text/html") {
						if ($encoding == "base64") {
							$text = trim(utf8_decode(imap_base64($attachment)));
						} else {
							$text = trim(utf8_decode(decode_ISO88591($attachment)));
						}
						//remove new lines!!
						$text=str_replace(array("\n\r", "\n", "\r"),'',$text);
						$parts=explode(";",$text);
						$sender=$parts[0];
						$date=formatDate($parts[1]);
						$lat=$parts[2];
						if($lat=="-1.0"){
							$lat="";
						}
						$long=$parts[3];
						if($long=="-1.0"){
							$long="";
						}
						$tags=$parts[4];
						if($tags=="null"){
							$tags="";
						}
						$query = "UPDATE message SET message_date='$date', message_sender='$sender' WHERE message_id = $current_message";
						$result = mysql_query($query, $dbh);
						$att_type = 0;
					} 
					if ($att_type > 0) {
						$index=GetMaxFileIndex($c,$dbh);
						$file = $folder.$subfolder.$index.$extension;
						$handle = fopen("channels/".$file, "wb");
						$ok = fwrite($handle, imap_base64($attachment));
						fclose($handle);
						if ($extension == ".jpg") {
							$sz = getimagesize("channels/".$file);
							$w = $sz[0];
							$h = $sz[1];
							/*
							$newfile="";
							$newfile = CheckOrientation($folder,$subfolder,$index,$extension);
							if($newfile!=$file && $newfile!=""){
								$tempw=$w;
								$w=$h;
								$h=$tempw;
								$file=$newfile;
							}
							if ($w > 640) {
								$h_dest = $h*(640/$w);
  								$w_dest = 640;
								$file = ScaleDown($folder,$subfolder,$index,$extension,$w,$h,$w_dest,$h_dest);
								$w = $w_dest;
								$h = $h_dest;
							}
							*/
						} else if (($extension == ".amr" || $extension == ".3gp") && $convert_to_mp3 == true) {
							$file1=$subfolder.$index.$extension;
							$file2=$subfolder.$index.".mp3";
							ConvertAMRToMP3($file1,$file2,$servpath,$ffmpeg_path,$channel_folder,$folder,$sample_rate);
							$w = 0;
							$h = 0;
							$file=$folder.$file2;
						} else {
							$w = 0;
							$h = 0;
						}
						$is_published=GetPublishedDefault($c,$dbh);
						$query = "INSERT INTO attachment (message_id, filename, content_type,image_width,image_height,is_published) VALUES ($current_message,'$file','$att_type',$w,$h,$is_published)";
						$result = mysql_query($query, $dbh);
						IncreaseFileIndex($c,$dbh);
					}
				}
				if($lat!="" && $long!="") {
					$query = "UPDATE attachment SET latitude='$lat', longitude='$long' WHERE message_id=$current_message";
					$result = mysql_query($query, $dbh);
				}
				$sections=NULL;
				ProcessMessageTags($tags,$current_message,$dbh);
			}
			imap_delete($inbox,$x);
		}
		imap_close($inbox, CL_EXPUNGE);
	}
}

function ParseMultipart($type,$m,$a,$c,$folder,$tags,$dbh,$convert_to_mp3,$servpath,$sample_rate,$channel_folder,$static_map_width,$static_map_height,$api_key,$get_reverse_geocoding) {
	if(strpos($type,"mixed")>0){
		$multipart = GetMultipart($a);
	} elseif(strpos($type,"alternative")>0) {
		$multipart = GetMultipartAlt($a);
	}
	if (sizeof($multipart)>0) {
		$date_done=false;
		for ($mi=0;$mi<sizeof($multipart);$mi++) {
    		$mtype = $multipart[$mi]["type"];
    		$mattach = $multipart[$mi]["attachment"];
			$original_filename = $multipart[$mi]["name"];
    		if ($mtype == "jpg" || $mtype == "jpe") {
      			$subfolder = "/image/image";
      			$extension = ".jpg";
      			$att_type = 1;
			} elseif ($mtype == "wav") {
      			$subfolder = "/sound/sound";
      			$extension = ".wav";
      			$att_type = 2;
			} elseif ($mtype == "amr") {
      			$subfolder = "/sound/sound";
      			$extension = ".amr";
      			$att_type = 2;
			} elseif ($mtype == "mp3") {
      			$subfolder = "/sound/sound";
      			$extension = ".mp3";
      			$att_type = 2;
			} elseif ($mtype == "3gp") {
      			$subfolder = "/video/video";
      			$extension = ".3gp";
      			$att_type = 3;
			} elseif ($mtype == "mp4") {
      			$subfolder = "/video/video";
      			$extension = ".mp4";
      			$att_type = 3;
			} elseif ($mtype == "avi") {
      			$subfolder = "/video/video";
      			$extension = ".avi";
      			$att_type = 3;
    		} elseif ($mtype == "txt" || $mtype == "text/plain") {
	      		$att_type = 0;
				$text = trim(utf8_decode(decode_ISO88591($mattach)));
	  			//tagging from mobile
				if ($tags=="") {
	  				$parts = ExtractTagsFromText($text);
					$text = $parts[0];
					$tags = $parts[1];
				}
	  			//
      			$text = urlencode($text);
      			$query = "UPDATE message SET message_text = '$text' WHERE message_id = $m";
      			$result = mysql_query($query, $dbh);
    		} else {
      			$att_type = 0;
    		}
    		if ($att_type > 0) {
      			$index=GetMaxFileIndex($c,$dbh);
				$file = $folder.$subfolder.$index.$extension;
				$handle = fopen("channels/".$file, "wb");
				$ok = fwrite($handle, imap_base64($mattach));
				fclose($handle);
				if ($extension == ".jpg") {
					$sz = getimagesize("channels/".$file);
					$w = $sz[0];
					$h = $sz[1];
					if ($get_date_from_exif && !$date_done) {
						GetMessageDateFromExif("channels/".$file,$m,$dbh);
						$date_done=true;
					}
				} else if ($extension == ".amr" && $convert_to_mp3 == true) {
					$file1=$subfolder.$index.$extension;
					$file2=$subfolder.$index.".mp3";
					ConvertAMRToMP3($file1,$file2,$servpath,$channel_folder,$folder,$sample_rate);
					$w = 0;
					$h = 0;
					$file=$folder.$file2;
				} else {
					$w = 0;
					$h = 0;
				}
				//GeoZexe: from filename or exif
				$lat="";
				$long="";
				$datetime="";
				/*
				$coord=GetCoordinatesFromFilename($original_filename);
				*/
				//if (sizeof($coord)==0) {
				if ($att_type==1) {
					$coord=GetCoordinatesFromExif("channels/".$file);
					//}
					if (sizeof($coord)==1) {
						$datetime=$coord[0];
					} else if (sizeof($coord)==2 && $coord[0]!=0 && $coord[1]!=0) {
						$lat=$coord[0];
						$long=$coord[1];
					} else if (sizeof($coord)==3 && $coord[0]!=0 && $coord[1]!=0) {
						$lat=$coord[0];
						$long=$coord[1];
						$datetime=$coord[2];
					}
					if ($lat!="" && $long!="" && $get_reverse_geocoding==true) {
						$address=GetReverseGeocoding($lat,$long);
					}
					$tags_exif=GetTagsFromExif("channels/".$file);
					if ($tags=="") {
						$tags=$tags_exif;
					} else {
						$tags.=",".$tags_exif;
					}
				}
				$is_published=GetPublishedDefault($c,$dbh);
				$query = "INSERT INTO attachment (message_id, filename, content_type, original_filename,image_width,image_height,latitude,longitude,date_time,map_address,is_published) VALUES ($m,'$file','$att_type','$original_filename',$w,$h,'$lat','$long','$datetime','$address',$is_published)";
				$result = mysql_query($query, $dbh);
				/*
				if ($lat!="" && $long!="") {
					$attachment_id=mysql_insert_id();
					$map_filename=GrabMapImage($lat,$long,$attachment_id,$static_map_width,$api_key);
					if ($map_filename!="") {
						$query="UPDATE attachment SET map_filename = '$map_filename' WHERE attachment_id=$attachment_id";
						$result = mysql_query($query, $dbh);
					}
				}*/
				IncreaseFileIndex($c,$dbh);
    		}
  		}
	}
	return $tags;
}

function GetMultipart($a) {
	$order  = array("\r\n", "\n", "\r");
  	$replace = ' ';
  	$a = str_replace($order, $replace, $a);
  	$parts = Multi_strpos("Content-Location",$a);
  	for($i=0;$i<sizeof($parts);$i++) {
		$name = $parts[$i]+17;
    	$point = strpos($a,".",$parts[$i]);
		$ret[$i]["name"] = trim(substr($a,$name,($point-$name)));
	    $pointend = strpos($a," ",$point);
	    $pointsz = 3;
	    $ret[$i]["type"] = trim(substr($a,$point+1,$pointsz));
    	$aend = strpos($a,"------=_Part_",$pointend)-1;
    	$asz = $aend-$pointend;
    	$ret[$i]["attachment"] = trim(substr($a,$pointend+1,$asz));
	}
	return $ret;
}

function GetMultipartAlt($a) {
	$order  = array("\r\n", "\n", "\r");
  	$replace = ' ';
  	$a = str_replace($order, $replace, $a);
  	$parts = Multi_strpos("Content-Type",$a);
  	for($i=0;$i<sizeof($parts);$i++) {
		$ret[$i]["name"] = "";
		$type_start=$parts[$i]+13;
		$type_end=strpos($a,";",$type_start);
		$typesz=$type_end-$type_start;
	    $ret[$i]["type"] = trim(substr($a,$type_start,$typesz));
    	$aend = strpos($a,"--",$type_end)-1;
    	$asz = $aend-$type_end;
    	$ret[$i]["attachment"] = trim(substr($a,$type_end+1,$asz));
		//Mumbai
		$ret[$i]["attachment"] = trim(substr($ret[$i]["attachment"],strpos($ret[$i]["attachment"],"to view the message.")+20));
	}
	return $ret;
}
?>
