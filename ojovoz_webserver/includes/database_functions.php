<?
//database functions

include_once("tagging_functions.php");
include_once("misc_functions.php");
include_once("channel_vars.php");

function GetUserNameFromPhoneNumber($dbh,$phone) {
	$query = "SELECT user_alias FROM user WHERE zexe_phone LIKE '%$phone'";
	$result = mysql_query($query, $dbh);
	if ($result) {
		$row = mysql_fetch_array($result, MYSQL_NUM);
		$user = $row[0];
	} else {
		$user = "";
	}
	return $user;
}

function showUserNames($dbh) {
	$ret="";
	$query = "SELECT DISTINCT user_alias FROM user,channel WHERE user.is_study = 0 AND is_megafone=0 AND user_alias = channel_name AND channel.is_visible = 1";
	$result = mysql_query($query, $dbh);
	while ($row=mysql_fetch_array($result,MYSQL_NUM)) {
		if ($ret=="") {
			$ret=$row[0];
		} else {
			$ret.=", ".$row[0];
		}
	}
	return htmlentities($ret);
}

function GetUserNameFromZexeEmail($dbh,$mail) {
	$query = "SELECT user_alias FROM user WHERE zexe_mail = '$mail'";
	$result = mysql_query($query, $dbh);
	if ($result) {
		$row = mysql_fetch_array($result, MYSQL_NUM);
		$user = $row[0];
	} else {
		$user = "";
	}
	return $user;
}

function GetUserNameFromPhone($dbh,$phone) {
	$query = "SELECT user_alias FROM user WHERE zexe_phone = '$phone'";
	$result = mysql_query($query, $dbh);
	if ($result) {
		$row = mysql_fetch_array($result, MYSQL_NUM);
		$user = $row[0];
	} else {
		$user = "";
	}
	return $user;
}

function GetThumbnails($dbh,$order,$crono,$c,$exc) {
	//returns an array of 3 strings
	//each one contains: "attachmentfilename,query_pointer" (use mysql_data_seek to move to pointer)
	$thumb[0]="";
	$thumb[1]="";
	$thumb[2]="";
	if ($crono==1) {
		$query="SELECT filename,message_date
		FROM attachment,message,channel
		WHERE content_type = '1' AND
		attachment.message_id = message.message_id AND
		channel.channel_id = message.channel_id AND
		channel.is_visible = 1 AND
		channel.channel_id NOT IN ($channels_excluded_from_crono)
		ORDER BY message_date ".$order;
	} else {
		$query="SELECT filename,message_order
		FROM attachment,message
		WHERE content_type = '1' AND
		attachment.message_id = message.message_id AND
		message.channel_id = $c
		ORDER BY message_order ".$order;
	}
	$result = mysql_query($query, $dbh);
	$n = mysql_num_rows($result);
	if ($n>0) {
		if ($n<=3) {
			for($i=0;$i<$n;$i++) {
				$row = mysql_fetch_array($result, MYSQL_NUM);
				$thumb[$i]="channels/".$row[0].",".$row[1];
			}
		} else {
			$row = mysql_fetch_array($result, MYSQL_NUM);
			$thumb[0]="channels/".$row[0].",".$row[1];
			$x = intval($n/2);
			mysql_data_seek($result,$x);
			$row = mysql_fetch_array($result, MYSQL_NUM);
			$thumb[1]="channels/".$row[0].",".$row[1];
			$n--;
			mysql_data_seek($result,$n);
			$row = mysql_fetch_array($result, MYSQL_NUM);
			$thumb[2]="channels/".$row[0].",".$row[1];
		}
	}
	return $thumb;
}

function PublishWebMessage($alias,$text,$tz,$c,$dbh) {
	$publish = false;
	$query = "SELECT message_text,message_order FROM message WHERE channel_id = ".$c. " ORDER BY message_id DESC LIMIT 0,1";
	$result = mysql_query($query, $dbh);
	if ($row=mysql_fetch_array($result,MYSQL_NUM)) {
		if (urldecode($row[0]) != $text) {
			$publish = true;
			$new_order = $row[1]+1;
		}
	} else {
		$publish=true;
		$new_order=1;
	}
	if ($publish) {
		$currentDate=GetChannelDate($tz);
		$text=urlencode($text);
		$alias=urlencode($alias);
		$query="INSERT INTO message (channel_id,message_text,message_date,message_sender,message_order) VALUES ($c,'$text','$currentDate','$alias',$new_order)";
		$result = mysql_query($query, $dbh);
	}
}

function GetMaxMessageOrder($c,$dbh) {
	$query = "SELECT MAX(message_order) AS max_order FROM message WHERE channel_id = $c";
	$result = mysql_query($query, $dbh);
	if ($row=mysql_fetch_array($result,MYSQL_NUM)) {
		return $row[0]+1;
	} else {
		return 1;
	}
}

function GetMaxFileIndex($c,$dbh) {
	$query = "SELECT MAX(file_index) AS i FROM channel WHERE channel_id = $c";
	$result = mysql_query($query, $dbh);
	if ($result) {
		$value = mysql_fetch_row($result);
		$i = $value[0];
		$i++;	
		mysql_free_result($result);				
	} else {
		$i = 0;
	}
	return $i;
}

function IncreaseFileIndex($c,$dbh) {
	$query = "UPDATE channel SET file_index = file_index + 1 WHERE channel_id = $c";
	$result = mysql_query($query, $dbh);
}

function GetTotalMessages($c,$crono,$qWhere,$exc,$dbh,$children="",$date="") {
	$r=0;
	if ($crono == 0) {
		if ($qWhere == "") {
		    $query = "SELECT DISTINCT message_id FROM message WHERE channel_id = ".$c." AND (DATE(message_date) = '$date')";
			$result = mysql_query($query, $dbh);
			$r = mysql_num_rows($result);
		} else if ($qWhere != "") {
			$query = "SELECT DISTINCT message_id FROM message WHERE message_id ".$qWhere." AND channel_id = ".$c."  AND (DATE(message_date) = '$date')";
			$result = mysql_query($query, $dbh);
			$r = mysql_num_rows($result);
		}
	} else {
		if ($qWhere == "") {
			if ($children=="") {
	    		$query = "SELECT DISTINCT message_id FROM message, channel WHERE message.channel_id = channel.channel_id AND channel.is_visible = 1 AND channel.channel_id NOT IN ($exc) AND (DATE(message_date) = '$date')";
			} else {
				$query = "SELECT DISTINCT message_id FROM message, channel WHERE message.channel_id IN (".$children.") AND message.channel_id = channel.channel_id AND channel.is_visible = 1 AND channel.channel_id NOT IN ($exc) AND (DATE(message_date) = '$date')";
			}
			$result = mysql_query($query, $dbh);
			$r = mysql_num_rows($result);
		} else if ($qWhere != "") {
			if ($children=="") {
				$query = "SELECT DISTINCT message_id FROM message, channel WHERE message.channel_id = channel.channel_id AND channel.is_visible = 1 AND channel.channel_id NOT IN ($exc) AND message_id ".$qWhere." AND (DATE(message_date) = '$date')";
			} else {
				$query = "SELECT DISTINCT message_id FROM message, channel WHERE message.channel_id = channel.channel_id AND channel.is_visible = 1 AND channel.channel_id NOT IN ($exc) AND channel.channel_id IN (".$children.") AND message_id ".$qWhere." AND (DATE(message_date) = '$date')";
			}
			$result = mysql_query($query, $dbh);
			$r = mysql_num_rows($result);
		} 
	}
	return $r;
}

function GetComboListStudy($x,$sc,$c,$sp,$parent,$dbh,$tag_channel=false,$tc_name="") {
	$ct=0;
	if ($parent==-1) {
		$id=$c;
	} else {
		$id=$parent;
	}
	if($x==0) {
		$query[0]="SELECT channel_name,channel_id FROM channel WHERE channel.is_study=1 ORDER BY channel_name";
	} else {
		$query[0]="SELECT channel_name,channel_id FROM channel WHERE channel_id=$sc";
		$query[1]="SELECT channel_name,channel_id FROM channel WHERE is_crono=1 AND is_visible=1";
		$query[2]="SELECT channel_name,channel_id FROM channel WHERE channel_id IN ($sp) AND is_visible=1 ORDER BY channel_name";
	}
	for($i=0;$i<sizeof($query);$i++) {
		if ($i==2) {
			if ($tag_channel) {
				if ($id==-4) {
					$lines[$ct] = "<option value=\"-4\" selected=\"selected\">$tc_name</option>\n";
					$ct++;
				} else {
					$lines[$ct] = "<option value=\"-4\">$tc_name</option>\n";
					$ct++;
				}
			}
		}
		$result = mysql_query($query[$i], $dbh);
		while($row = mysql_fetch_array($result, MYSQL_NUM)) {
			if ($x==1 && $row[1]==$id) {
				$lines[$ct] = "<option value=\"".$row[1]."\" selected=\"selected\">".$row[0]."</option>\n";
			} else {
				$lines[$ct] = "<option value=\"".$row[1]."\">".$row[0]."</option>\n";
			}
			$ct++;
		}
	}
	return $lines;
}

function GetComboListUpload($dbh) {
	$ct=0;
	$query="SELECT channel_name,channel_id FROM channel WHERE is_crono=0 AND is_visible=1 ORDER BY channel_name";
	$result = mysql_query($query, $dbh);
	while($row = mysql_fetch_array($result, MYSQL_NUM)) {
		$lines[$ct] = "<option value=\"".$row[1]."\">".$row[0]."</option>\n";
		$ct++;
	}
	return $lines;
}

function GetComboListEdit($c,$dbh) {
	$ct=0;
	$query="SELECT channel_name,channel_id FROM channel WHERE is_crono=0 AND is_visible=1 ORDER BY channel_name";
	$result = mysql_query($query, $dbh);
	while($row = mysql_fetch_array($result, MYSQL_NUM)) {
		if ($row[1]==$c) {
			$lines[$ct] = "<option value=\"".$row[1]."\" selected>".$row[0]."</option>\n";
		} else {
			$lines[$ct] = "<option value=\"".$row[1]."\">".$row[0]."</option>\n";
		}
		$ct++;
	}
	return $lines;
}

function GetComboList($c,$cn,$a,$sp,$k,$m,$mn,$dbh,$parent=-1,$channel_order="",$zexe=false,$tag_channel=false,$tc_name="") {
	if ($channel_order!="") {
		if ($parent==-1) {
			$id=$c;
		} else {
			$id=$parent;
		}
		$order=explode(",",$channel_order);
		$ct=0;
		for($i=0;$i<sizeof($order);$i++) {
			if ($order[$i]==-1) {
				if($c==-1) {
					$lines[$ct] = "<option value=\"-1\" selected>$cn</option>";
				} else {
					$lines[$ct] = "<option value=\"-1\">$cn</option>";
				}
			} else if($order[$i]==-3) {
				if($c==-3) {
					$lines[$ct] = "<option value=\"-3\" selected>$mn</option>";
				} else {
					$lines[$ct] = "<option value=\"-3\">$mn</option>";
				}
			} else if($order[$i]==-4) {
				if($c==-4) {
					$lines[$ct] = "<option value=\"-4\" selected>$tc_name</option>";
				} else {
					$lines[$ct] = "<option value=\"-4\">$tc_name</option>";
				}
			} else {
				$name=GetChannelName($dbh,$order[$i]);
				if ($name!="") {
					if($id==$order[$i]) {	
						$lines[$ct] = "<option value=\"".$order[$i]."\" selected>".$name."</option>";
					} else {
						$lines[$ct] = "<option value=\"".$order[$i]."\">".$name."</option>";
					}
				}
			}
			$ct++;
		}
		if (!$k && $zexe) {
			$lines[$ct] = "";
		}
	} else {
		$ct=0;
		if ($parent==-1) {
			$id=$c;
		} else {
			$id=$parent;
		}
		if($cn!='') {
			$lines[$ct] = "<option value=\"-1\">$cn</option>";
			$ct++;
			$query[0] = "SELECT channel_name,channel_id FROM channel WHERE channel_name LIKE '$a%' AND is_visible=1 AND parent_channel_id=-1 ORDER BY channel_name";
			$query[1] = "SELECT channel_name,channel_id FROM channel WHERE channel_name NOT LIKE '$a%' AND channel_id NOT IN ($sp) AND is_crono = 0 AND is_visible=1 AND parent_channel_id=-1 ORDER BY channel_name";
			$query[2] = "SELECT channel_name,channel_id FROM channel WHERE channel_name NOT LIKE '$a%' AND channel_id IN ($sp) AND is_visible=1 AND parent_channel_id=-1 ORDER BY channel_name DESC";
			//$query[3] = "SELECT channel_name,channel_id FROM channel WHERE is_crono=1 AND is_visible=1";
		} else {
			$query[0] = "SELECT channel_name,channel_id FROM channel WHERE channel_name LIKE '$a%' AND is_visible=1 ORDER BY channel_name";
			$query[1] = "SELECT channel_name,channel_id FROM channel WHERE channel_name NOT LIKE '$a%' AND channel_id NOT IN ($sp) AND is_crono = 0 AND is_visible=1 ORDER BY channel_name";
			$query[2] = "SELECT channel_name,channel_id FROM channel WHERE channel_id IN ($sp) AND is_visible=1 ORDER BY channel_name";
		}
		for ($i=0;$i<sizeof($query);$i++) {
			if ($i==1) {
				if ($tag_channel) {
					if ($id==-4) {
						$lines[$ct] = "<option value=\"-4\" selected=\"selected\">$tc_name</option>\n";
						$ct++;
					} else {
						$lines[$ct] = "<option value=\"-4\">$tc_name</option>\n";
						$ct++;
					}
				}
			}
			$result = mysql_query($query[$i], $dbh);
			while($row = mysql_fetch_array($result, MYSQL_NUM)) {
				if ($row[1] == $id) {
					$lines[$ct] = "<option value=\"".$row[1]."\" selected=\"selected\">".$row[0]."</option>\n";
					$ct++;		
				} else {
					$lines[$ct] = "<option value=\"".$row[1]."\">".$row[0]."</option>\n";
					$ct++;
				}
			}
			mysql_free_result($result);
		}
		if ($m==true) {
			if($c==-3) {
				$lines[$ct] = "<option value=\"-3\" selected>$mn</option>";
			} else {
				$lines[$ct] = "<option value=\"-3\">$mn</option>";
			}
			$ct++;
		}
		if (!$k && $zexe) {
			$lines[$ct] = "";
		}
	}
	return $lines;
}

function GetGroupComboList($c,$p,$dbh,$group_combo_title) {
	$ct=1;
	$lines=array();
	if ($p!=-1) {
		$id=$p;
	} else {
		$id=$c;
	}
	$query="SELECT channel_id,channel_name FROM channel WHERE parent_channel_id=$id AND is_visible=1 ORDER BY channel_name";
	$result = mysql_query($query, $dbh);
	while($row = mysql_fetch_array($result, MYSQL_NUM)) {
		if ($row[0]==$c) {
			$lines[$ct] = "<option value=\"".$row[0]."\" selected>".$row[1]."</option>\n";
		} else {
			$lines[$ct] = "<option value=\"".$row[0]."\">".$row[1]."</option>\n";
		}
		$ct++;
	}
	if (sizeof($lines)>0) {
		$lines[0]="<option value=\"$id\">$group_combo_title</option>\n";
	}
	return $lines;
}

function GetGroupComboListStudy($c,$p,$sc,$dbh,$group_combo_title) {
	$ct=1;
	$lines=array();
	if ($p!=-1) {
		$id=$p;
	} else {
		$id=$c;
	}
	$query="SELECT channel_id,channel_name FROM channel WHERE parent_channel_id=$id AND is_visible=1 AND channel_id<>$sc ORDER BY channel_name";
	$result = mysql_query($query, $dbh);
	while($row = mysql_fetch_array($result, MYSQL_NUM)) {
		if ($row[0]==$c) {
			$lines[$ct] = "<option value=\"".$row[0]."\" selected>".$row[1]."</option>\n";
		} else {
			$lines[$ct] = "<option value=\"".$row[0]."\">".$row[1]."</option>\n";
		}
		$ct++;
	}
	if (sizeof($lines)>0) {
		$lines[0]="<option value=\"$id\">$group_combo_title</option>\n";
	}
	return $lines;
}

function GetChannelName($dbh,$c) {
	$ret="";
	$query="SELECT channel_name FROM channel WHERE channel_id=$c and is_visible=1";
	$result = mysql_query($query, $dbh);
	if($row = mysql_fetch_array($result, MYSQL_NUM)) {
		$ret=$row[0];
	}
	return $ret;
}

function GetMessagesQueryPermalink($id) {
	$query="SELECT message.message_text, message.message_date, message.message_sender, message.message_subject, message.message_id FROM message WHERE message_id = $id";
	return $query;
}

function GetMessagesQuery($c,$from,$nmessages,$crono,$qWhere,$order,$exc,$children="") {
	if ($crono == 0) {
		if ($qWhere != "") {
			$query = "SELECT message.message_text, message.message_date, message.message_sender, message.message_subject, message.message_id FROM message WHERE channel_id = $c AND message_id ".$qWhere." ORDER BY message_order ".$order." LIMIT ".$from.",".$nmessages;
		} else {
			$query = "SELECT message.message_text, message.message_date, message.message_sender, message.message_subject, message.message_id FROM message WHERE channel_id = $c ORDER BY message_order ".$order." LIMIT ".$from.",".$nmessages;
		}
	} else {
		if ($qWhere != "") {
			if ($children=="") {
				$query = "SELECT message.message_text, message.message_date, message.message_sender, message.message_subject, message.message_id, channel.channel_name, channel.channel_id FROM message, channel WHERE channel.channel_id = message.channel_id AND channel.is_visible = 1 AND channel.channel_id NOT IN ($exc) AND message_id ".$qWhere." ORDER BY message_date ".$order." LIMIT ".$from.",".$nmessages;
			} else {
				$query = "SELECT message.message_text, message.message_date, message.message_sender, message.message_subject, message.message_id, channel.channel_name, channel.channel_id FROM message, channel WHERE channel.channel_id = message.channel_id AND channel.is_visible = 1 AND channel.channel_id NOT IN ($exc) AND channel.channel_id IN (".$children.") AND message_id ".$qWhere." ORDER BY message_date ".$order." LIMIT ".$from.",".$nmessages;
			}
		} else {
			if ($children=="") {
				$query = "SELECT message.message_text, message.message_date, message.message_sender, message.message_subject, message.message_id, channel.channel_name, channel.channel_id FROM message, channel WHERE channel.channel_id = message.channel_id AND channel.is_visible = 1 AND channel.channel_id NOT IN ($exc) ORDER BY message_date ".$order." LIMIT ".$from.",".$nmessages;
			} else {
				$query = "SELECT message.message_text, message.message_date, message.message_sender, message.message_subject, message.message_id, channel.channel_name, channel.channel_id FROM message, channel WHERE channel.channel_id = message.channel_id AND channel.is_visible = 1 AND channel.channel_id NOT IN ($exc) AND channel.channel_id IN (".$children.") ORDER BY message_date ".$order." LIMIT ".$from.",".$nmessages;
			}
		}
	}
	return $query;
}

function GetMessagesQueryStudy($c,$date,$nmessages,$crono,$qWhere,$order,$exc,$children="") {
	if ($crono == 0) {
		if ($qWhere != "") {
			$query = "SELECT message.message_text, message.message_date, message.message_sender, message.message_subject, message.message_id FROM message WHERE channel_id = $c AND message_id ".$qWhere." AND (DATE(message_date) = '$date') ORDER BY message_order ".$order;
		} else {
			$query = "SELECT message.message_text, message.message_date, message.message_sender, message.message_subject, message.message_id FROM message WHERE channel_id = $c AND (DATE(message_date) = '$date') ORDER BY message_order ".$order;
		}
	} else {
		if ($qWhere != "") {
			if ($children=="") {
				$query = "SELECT message.message_text, message.message_date, message.message_sender, message.message_subject, message.message_id, channel.channel_name, channel.channel_id FROM message, channel WHERE channel.channel_id = message.channel_id AND channel.is_visible = 1 AND channel.channel_id NOT IN ($exc) AND message_id ".$qWhere." AND (DATE(message_date) = '$date') ORDER BY message_date ".$order;
			} else {
				$query = "SELECT message.message_text, message.message_date, message.message_sender, message.message_subject, message.message_id, channel.channel_name, channel.channel_id FROM message, channel WHERE channel.channel_id = message.channel_id AND channel.is_visible = 1 AND channel.channel_id NOT IN ($exc) AND channel.channel_id IN (".$children.") AND message_id ".$qWhere." AND (DATE(message_date) = '$date') ORDER BY message_date ".$order;
			}
		} else {
			if ($children=="") {
				$query = "SELECT message.message_text, message.message_date, message.message_sender, message.message_subject, message.message_id, channel.channel_name, channel.channel_id FROM message, channel WHERE channel.channel_id = message.channel_id AND channel.is_visible = 1 AND channel.channel_id NOT IN ($exc) AND (DATE(message_date) = '$date') ORDER BY message_date ".$order;
			} else {
				$query = "SELECT message.message_text, message.message_date, message.message_sender, message.message_subject, message.message_id, channel.channel_name, channel.channel_id FROM message, channel WHERE channel.channel_id = message.channel_id AND channel.is_visible = 1 AND channel.channel_id NOT IN ($exc) AND channel.channel_id IN (".$children.") AND (DATE(message_date) = '$date') ORDER BY message_date ".$order;
			}
		}
	}
	return $query;
}

function GetMessagesQueryByDate($c,$date,$nmessages,$crono,$qWhere,$order,$exc,$children="",$from=0) {
	if ($crono == 0) {
		if ($qWhere != "") {
			$query = "SELECT message.message_text, message.message_date, message.message_sender, message.message_subject, message.message_id FROM message WHERE channel_id = $c AND message_id ".$qWhere." AND (DATE(message_date) = '$date') ORDER BY message_date ".$order. " LIMIT $from, $nmessages";
		} else {
			$query = "SELECT message.message_text, message.message_date, message.message_sender, message.message_subject, message.message_id FROM message WHERE channel_id = $c AND (DATE(message_date) = '$date') ORDER BY message_date ".$order. " LIMIT $from, $nmessages";
		}
	} else {
		if ($qWhere != "") {
			if ($children=="") {
				$query = "SELECT message.message_text, message.message_date, message.message_sender, message.message_subject, message.message_id, channel.channel_name, channel.channel_id FROM message, channel WHERE channel.channel_id = message.channel_id AND channel.is_visible = 1 AND channel.channel_id NOT IN ($exc) AND message_id ".$qWhere." AND (DATE(message_date) = '$date') ORDER BY message_date ".$order." LIMIT $from, $nmessages";
			} else {
				$query = "SELECT message.message_text, message.message_date, message.message_sender, message.message_subject, message.message_id, channel.channel_name, channel.channel_id FROM message, channel WHERE channel.channel_id = message.channel_id AND channel.is_visible = 1 AND channel.channel_id NOT IN ($exc) AND channel.channel_id IN (".$children.") AND message_id ".$qWhere." AND (DATE(message_date) = '$date') ORDER BY message_date ".$order." LIMIT $from, $nmessages";
			}
		} else {
			if ($children=="") {
				$query = "SELECT message.message_text, message.message_date, message.message_sender, message.message_subject, message.message_id, channel.channel_name, channel.channel_id FROM message, channel WHERE channel.channel_id = message.channel_id AND channel.is_visible = 1 AND channel.channel_id NOT IN ($exc) AND (DATE(message_date) = '$date') ORDER BY message_date ".$order." LIMIT $from, $nmessages";
			} else {
				$query = "SELECT message.message_text, message.message_date, message.message_sender, message.message_subject, message.message_id, channel.channel_name, channel.channel_id FROM message, channel WHERE channel.channel_id = message.channel_id AND channel.is_visible = 1 AND channel.channel_id NOT IN ($exc) AND channel.channel_id IN (".$children.") AND (DATE(message_date) = '$date') ORDER BY message_date ".$order." LIMIT $from, $nmessages";
			}
		}
	}
	return $query;
}

function MediaElementExists($dbh,$link,$media_channel) {
	$link = trim($link);
	$query = "SELECT message_id FROM message WHERE channel_id = $media_channel AND message_subject = '$link'";
	$result = mysql_query($query, $dbh);
	if (mysql_num_rows($result) > 0) {
		$ret = true;
	} else {
		$ret = false;
	}
	return $ret;
}

function GetTimesDescriptorUsed($d,$dbh) {
	$query = "SELECT COUNT(descriptor_id) FROM descriptor_x_attachment WHERE descriptor_id =".$d;
    $result = mysql_query($query, $dbh);
	$res = mysql_fetch_array($result);
	return $res[0];
}

function GetDescriptorCategories($dbh) {
	$query="SELECT DISTINCT descriptor_category FROM descriptor ORDER BY descriptor_category";
	$result = mysql_query($query, $dbh);
	$ct=0;
	while($row = mysql_fetch_array($result, MYSQL_NUM)) {
		$ret[$ct]=$row[0];
		$ct++;
	}
	return $ret;
}

function GetDescriptorNamesFromID($id,$dbh) {
	$query="SELECT descriptor_spanish,descriptor_english,descriptor_catalan,descriptor_portuguese FROM descriptor WHERE descriptor_id=".$id;
	$result = mysql_query($query, $dbh);
	$row = mysql_fetch_array($result, MYSQL_NUM);
	return $row;
}

function GetMessagesChannelMap($c,$from,$qWhere,$m,$crono,$order,$channels_excluded_from_crono,$dbh,$children="") {
	$ret="";
	$query=GetMessagesQuery($c,$from,$m,$crono,$qWhere,$order,$channels_excluded_from_crono,$children);
	$result=mysql_query($query,$dbh);
	$in="";
	while($row=mysql_fetch_array($result,MYSQL_NUM)) {
		if ($in=="") {
			$in=" IN(".$row[4];
		} else {
			$in.=",".$row[4];
		}
	}
	if ($in!="") {
		$in.=") ";
		if ($qWhere == "") {
			$ret = "SELECT message_date, message_sender, filename, latitude, longitude, content_type, image_width, image_height, message.message_id, attachment_id FROM message, attachment WHERE message.message_id ".$in." AND attachment.message_id = message.message_id AND latitude <> '' AND longitude <> '' ORDER BY message_date DESC";
		} else {
			$ret = "SELECT message_date, message_sender, filename, latitude, longitude, content_type, image_width, image_height, message.message_id, attachment_id FROM message, attachment WHERE attachment.message_id = message.message_id AND message.message_id ".$qWhere." AND message.message_id ".$in." AND latitude <> '' AND longitude <> '' ORDER BY message_date DESC";
		}
	}
	return $ret;
}

function GetMessageColor($dbh,$id) {
	$query="SELECT color_in_map, tag_group_id FROM tag, tag_x_message WHERE tag_x_message.message_id = $id AND tag.tag_id = tag_x_message.tag_id ORDER BY color_in_map DESC";
	$result=mysql_query($query,$dbh);
	if ($row=mysql_fetch_array($result,MYSQL_NUM)) {
		if ($row[1]==-1) {
			$ret=$row[0];
		} else {
			$id=$row[1];
			$query="SELECT color_in_map FROM tag_group WHERE tag_group_id=$id";
			$result=mysql_query($query,$dbh);
			if ($row=mysql_fetch_array($result,MYSQL_NUM)) {
				$ret=$row[0];
			} else {
				$ret="00";
			}
		}
	} else {
		$ret="00";
	}
	return $ret;
}

function GetTagsInMap($dbh) {
	$query="SELECT DISTINCT tag_x_message.message_id FROM tag_x_message,tag WHERE tag.in_map=1 AND tag_x_message.tag_id = tag.tag_id";
	$result=mysql_query($query,$dbh);
	$ret="";
	while($row=mysql_fetch_array($result,MYSQL_NUM)) {
		if ($ret=="") {
			$ret="AND message.message_id IN (".$row[0];
		} else {
			$ret.=",".$row[0];
		}
	}
	if ($ret!="") {
		$ret.=")";
	} else if ($ret=="") {
		$ret="AND message.message_id IN (-1)";
	}
	return $ret;
}

function IsPublished($dbh,$t) {
	$query = "SELECT attachment_id FROM attachment,tag_x_message WHERE tag_x_message.tag_id = $t AND attachment.message_id = tag_x_message.message_id AND is_published = 1";
	$result = mysql_query($query, $dbh);
	if (mysql_num_rows($result) > 0) {
		return true;
	} else {
		return false;
	}
}

function GetMessagesInMap($qWhere,$m,$dbh,$message=-1,$from="",$to="") {
	if($from!="" && $to!=""){
		if(strtotime($from)>strtotime($to)){
			$from=date("Y-m-t",strtotime($from));
			$to=date("Y-m",strtotime($to))."-01";
			$temp=$from;
			$from=$to;
			$to=$temp;
		}
		$date_filter=" AND DATE(message.message_date) BETWEEN '$from' AND '$to'";
	} else {
		$date_filter="";
	}
	if ($qWhere == "") {
		$filter = GetTagsInMap($dbh);		
		if ($message!=-1) {
			$query = "(SELECT DISTINCT message_text, message_date, message_sender, filename, latitude, longitude, content_type, channel.channel_id, channel_name, image_width, image_height, channel.is_ascending, channel.messages_per_page, message.message_order, message.message_id FROM channel, message, attachment WHERE channel.channel_id = message.channel_id AND channel.is_visible=1 AND attachment.message_id = message.message_id AND latitude <> '' AND longitude <> '' AND is_published = 1 AND content_type=1 ".$filter.$date_filter." ORDER BY message_date DESC LIMIT 0,$m) UNION (SELECT message_text, message_date, message_sender, filename, latitude, longitude, content_type, channel.channel_id, channel_name, image_width, image_height, channel.is_ascending, channel.messages_per_page, message.message_order, message.message_id FROM channel, message, attachment WHERE attachment.message_id = $message AND latitude <> '' AND longitude <> '' AND is_published = 1 AND content_type=1 AND message.message_id = attachment.message_id AND channel.channel_id = message.channel_id)";
		} else {
			$query = "SELECT DISTINCT message_text, message_date, message_sender, filename, latitude, longitude, content_type, channel.channel_id, channel_name, image_width, image_height, channel.is_ascending, channel.messages_per_page, message.message_order, message.message_id FROM channel, message, attachment WHERE channel.channel_id = message.channel_id AND channel.is_visible=1 AND attachment.message_id = message.message_id AND latitude <> '' AND longitude <> '' AND is_published = 1 AND content_type=1 ".$filter.$date_filter." ORDER BY message_date DESC LIMIT 0,$m";
		}
	} else {
		$query = "SELECT DISTINCT message_text, message_date, message_sender, filename, latitude, longitude, content_type, channel.channel_id, channel_name, image_width, image_height, channel.is_ascending, channel.messages_per_page, message.message_order, message.message_id FROM channel, message, attachment WHERE channel.channel_id = message.channel_id AND channel.is_visible=1 AND attachment.message_id = message.message_id AND message.message_id ".$qWhere." AND latitude <> '' AND longitude <> '' AND is_published = 1 AND content_type=1".$date_filter." ORDER BY message_date DESC LIMIT 0,$m";		
	}
	return $query;
}

function GetNMessagesInMap($qWhere,$dbh) {
	if ($qWhere == "") {
		$filter = GetTagsInMap($dbh);
		$query = "SELECT DISTINCT message_text, message_date, message_sender, filename, latitude, longitude, content_type, channel.channel_id, channel_name, image_width, image_height, channel.is_ascending, channel.messages_per_page, message.message_order, message.message_id FROM channel, message, attachment WHERE channel.channel_id = message.channel_id AND channel.is_visible=1 AND attachment.message_id = message.message_id AND latitude <> '' AND longitude <> '' ".$filter;
	} else {
		$query = "SELECT DISTINCT message_text, message_date, message_sender, filename, latitude, longitude, content_type, channel.channel_id, channel_name, image_width, image_height, channel.is_ascending, channel.messages_per_page, message.message_order, message.message_id FROM channel, message, attachment WHERE channel.channel_id = message.channel_id AND channel.is_visible=1 AND attachment.message_id = message.message_id AND message.message_id ".$qWhere." AND latitude <> '' AND longitude <> ''";
	}
	$result=mysql_query($query,$dbh);
	$ret=mysql_num_rows($result);
	return $ret;
}

function VerifyChannelAccess($dbh,$c,$p,$m) {
	$ret=false;
	if ($p==$m) {
		$ret=true;
	} else {
		$query = "SELECT channel_pass_edit FROM channel WHERE channel_id = $c";
		$result = mysql_query($query, $dbh);
		$row = mysql_fetch_array($result, MYSQL_NUM);
		if ($row[0]==$p) {
			$ret=true;
		}
	}
	return $ret;
}

function CopyMessageToDestination($dbh,$c,$d,$m) {
	$maxorder = GetMaxMessageOrder($d,$dbh);
	$query = "SELECT * FROM message WHERE message_id=$m";                 
	$result = mysql_query($query, $dbh);                 
	$row = mysql_fetch_array($result, MYSQL_NUM);                                 
	$query = "INSERT INTO message VALUES ('',$row[1],'$row[2]','$row[3]','$row[4]','$row[5]','$row[6]',$maxorder)";                  
	$ins = mysql_query($query, $dbh);                  
	$nm = mysql_insert_id();
	               
	$query = "SELECT * FROM attachment WHERE message_id=$m";                 
	$result = mysql_query($query, $dbh);                 
	while ($row = mysql_fetch_array($result, MYSQL_NUM)) {                         
		$query = "INSERT INTO attachment VALUES ('',$nm,'$row[1]',$row[2],'$row[3]',$row[4],$row[5],'$row[6]','$row[7]','$row[8]','$row[9]')";                         
		$ins = mysql_query($query, $dbh);                 
	}                  
}

function DeleteMessage($dbh,$m) {
	$query = "SELECT attachment_id FROM attachment WHERE message_id=$m";
	$result = mysql_query($query, $dbh);
	while ($row = mysql_fetch_array($result, MYSQL_NUM)) 
	{
		$query = "DELETE FROM descriptor_x_attachment WHERE attachment_id=".$row[0];
	    $del = mysql_query($query, $dbh);
	}
	$query = "DELETE FROM attachment WHERE message_id=$m";
	$del = mysql_query($query, $dbh);
	$query = "DELETE FROM tag_x_message WHERE message_id=$m";
	$del = mysql_query($query, $dbh);
	$query = "DELETE FROM message WHERE message_id=$m";
	$del = mysql_query($query, $dbh);
}

function ChangeMessageText($dbh,$m,$t) {
	$query = "UPDATE message SET message_text = '".urlencode($t)."' WHERE message_id = $m";
	$result = mysql_query($query, $dbh);
}

function UpdateTags($dbh,$m,$tags) {
	$query = "DELETE FROM tag_x_message WHERE message_id = $m";
	$result = mysql_query($query, $dbh);
	$tagList = explode(",",$tags);
	if (sizeof($tagList)>0) {
		for($i=0;$i<sizeof($tagList);$i++) {
			$newTag = trim(strtolower($tagList[$i]));
			$newTag = str_replace(".","",$newTag);
			if ($newTag != "") {
				$idTag = GetTagIDFromName($newTag,$dbh);
				if ($idTag == -1) {
					$query = "INSERT INTO tag (tag_name) VALUES ('$newTag')";
					$result = mysql_query($query, $dbh);
					$query = "SELECT LAST_INSERT_ID()";
					$result = mysql_query($query, $dbh);
					$row = mysql_fetch_array($result,MYSQL_NUM);
					$idTag = $row[0];
				} 
				$query = "INSERT INTO tag_x_message (tag_id, message_id) VALUES ($idTag,$m)";
				$result = mysql_query($query, $dbh);
			}
		}
	}
}

function GetChannelAscendDescend($dbh,$c) {
	$ret="";
	$query = "SELECT is_ascending FROM channel WHERE channel_id = $c";
	$result = mysql_query($query, $dbh);
	$row = mysql_fetch_array($result, MYSQL_NUM);
	$asc = $row[0];
	if ($asc==0) {
		$ret="DESC";
	}
	return $ret;
}

function MoveMessage($dbh,$c,$o,$move,$m,$t) {
	$o=$t-$o;
	$oo=GetChannelAscendDescend($dbh,$c);
	$query = "SELECT message_order FROM message WHERE channel_id = $c ORDER BY message_order $oo LIMIT $o,1";
	$result = mysql_query($query, $dbh);
	$row = mysql_fetch_array($result, MYSQL_NUM);
	$from = $row[0];
	
	$move=$t-$move;
	$query = "SELECT message_order FROM message WHERE channel_id = $c ORDER BY message_order $oo LIMIT $move,1";
	$result = mysql_query($query, $dbh);
	$row = mysql_fetch_array($result, MYSQL_NUM);
	$to = $row[0];
	if ($to > $from) {
		$start = $from;
		$end = $to;
		$query="UPDATE message SET message_order=message_order-1 WHERE message_order > $start AND message_order <= $end AND channel_id = $c ORDER BY message_order ASC";
	} else {
		$start = $to;
		$end = $from;
		$query="UPDATE message SET message_order=message_order+1 WHERE message_order >= $start AND message_order < $end AND channel_id = $c ORDER BY message_order DESC";
	}	
	$upd = mysql_query($query, $dbh);
	$query="UPDATE message SET message_order=$to WHERE message_id = $m";
	$upd = mysql_query($query, $dbh);
}

function GetMessagesPerPage($c,$dbh) {
	$query = "SELECT messages_per_page FROM channel WHERE channel_id = $c";
	$result = mysql_query($query, $dbh);
	$row = mysql_fetch_array($result, MYSQL_NUM);
	return $row[0];
}

function DeleteAttachments($delete_att,$dbh) {
	for ($i=0; $i < count($delete_att); $i++) {
    	if ($delete_att[$i] > 0) {
			$query = "DELETE FROM descriptor_x_attachment WHERE attachment_id = ".$delete_att[$i];
	  		$result = mysql_query($query, $dbh);
	  		$query = "DELETE FROM attachment WHERE attachment_id = ".$delete_att[$i];
	  		$result = mysql_query($query, $dbh);
		}
  	}
}

function ApproveMessage($m,$v,$dbh) {
	$query="UPDATE attachment SET is_published=$v WHERE message_id=$m";
	$result = mysql_query($query, $dbh);
}

function PublishAttachments($m,$publish_att,$dbh) {
	$query="UPDATE attachment SET is_published=0 WHERE message_id=$m AND content_type=1";
	$result = mysql_query($query, $dbh);
	if ($publish_att!="") {
		for($i=0;$i<count($publish_att);$i++) {
			$att=$publish_att[$i];
			$query="UPDATE attachment SET is_published=1 WHERE attachment_id=$att";
			$result = mysql_query($query, $dbh);
		}
	} 
}

function IsCrono($dbh,$c) {
	if ($c==-3) {
		return 1;
	} else {
		$query = "SELECT is_crono FROM channel WHERE channel_id = $c";
		$result = mysql_query($query, $dbh);
		$row = mysql_fetch_array($result, MYSQL_NUM);
		return $row[0];
	}
}

function GetFilterMapMessageList($qWhere,$dbh) {
	$ret="";
	if ($qWhere!="") {
		$query="SELECT message_id FROM attachment WHERE message_id ".$qWhere." AND latitude <> '' AND longitude <> ''";
		$result = mysql_query($query, $dbh);
		while($row=mysql_fetch_array($result,MYSQL_NUM)) {
			if ($ret=="") {
				$ret=" IN(".$row[0];
			} else {
				$ret.=",".$row[0];
			}
		}
		if ($ret!="") {
			$ret.=") ";
		}
	}
	return $ret;
}

function GetFilterMessageList($selection_list,$dbh) {
	$ret="";
	$query="";
	$a=explode(",",$selection_list);
	if (sizeof($a)>1) {
		for($i=1;$i<sizeof($a);$i++) {
			if ($a[$i]>0) {
				$b=explode("/",$a[$i]);
				if(sizeof($b)>1) {
					$cond="";
					for($j=0;$j<sizeof($b);$j++) {
						if ($cond=="") {
							$cond=$b[$j];
						} else {
							$cond.=" OR tag_x_message.tag_id=".$b[$j];
						}
					}
				} else {
					$cond=$a[$i];
				}
				if ($query=="") {
					$query="(SELECT DISTINCT message.message_id AS m FROM message,tag_x_message WHERE (tag_x_message.tag_id=".$cond.") AND message.message_id = tag_x_message.message_id)";
				} else {
					$query.=" UNION ALL (SELECT DISTINCT message.message_id AS m FROM message,tag_x_message WHERE (tag_x_message.tag_id=".$cond.") AND message.message_id = tag_x_message.message_id)";
				}
			} else {
				$n=-$a[$i];
				if ($query=="") {
					$query="(SELECT DISTINCT attachment.message_id AS m FROM attachment,descriptor_x_attachment WHERE descriptor_x_attachment.descriptor_id=".$n." AND attachment.attachment_id = descriptor_x_attachment.attachment_id)";
				} else {
					$query.=" UNION ALL (SELECT DISTINCT attachment.message_id AS m FROM attachment,descriptor_x_attachment WHERE descriptor_x_attachment.descriptor_id=".$n." AND attachment.attachment_id = descriptor_x_attachment.attachment_id)";
				}
			}
		}
		$query.=" ORDER BY m";
		$result=mysql_query($query,$dbh);
		$m_ant=-1;
		$n=0;
		while ($row=mysql_fetch_array($result,MYSQL_NUM)) {
			if ($row[0]!=$m_ant) {
				if ($n==(sizeof($a)-1)) {
					if ($ret=="") {
						$ret="IN (".$m_ant;
					} else {
						$ret.=",".$m_ant;
					}
				}
				$m_ant=$row[0];
				$n=1;
			} else {
				$n++;
			}
		}
		if ($n==(sizeof($a)-1)) {
			if ($ret=="") {
				$ret="IN (".$m_ant;
			} else {
				$ret.=",".$m_ant;
			}
		}
		if ($ret!="") {
			$ret.=")";
		} else {
			$ret="IN (-1)";
		}
	}
	return $ret;
}

function GetMapLegend($dbh,$font_size,$legend_color) {
	$color="";
	$tags="";
	$legend="<font face=\"Arial, Helvetica, sans-serif\" size=\"$font_size\" color=\"#".$legend_color."\">";
	$query="SELECT color_in_map, tag_name FROM tag WHERE in_map=1 ORDER BY color_in_map";
	$result=mysql_query($query,$dbh);
	while ($row=mysql_fetch_array($result,MYSQL_NUM)) {
		if ($row[0]<>$color) {
			if ($color<>"") {
				$legend.="<img src=\"includes/images/marker".$color."_small.png\" align=\"absbottom\"> ".$tags." ";
			}
			$color=$row[0];
			$tags=$row[1];
		} else {
			$tags.=", ".$row[1];
		}
	}
	$legend.="<img src=\"includes/images/marker".$color."_small.png\" align=\"absbottom\"> ".$tags." </font><br>";
	return $legend;
}

function GetAttachmentLocation($dbh,$id,$default_latitude,$default_longitude) {
	$ret=array("","","0");
	$query="SELECT latitude,longitude FROM attachment WHERE message_id = $id AND content_type=1";
	$result=mysql_query($query,$dbh);
	if($row=mysql_fetch_array($result,MYSQL_NUM)) {
		$ret[0]=$row[0];
		$ret[1]=$row[1];
		$ret[2]="1";
	} else {
		$ret[0]=$default_latitude;
		$ret[1]=$default_longitude;
	}
	if ($ret[0]=="" || $ret[1]=="") {
		$ret[0]=$default_latitude;
		$ret[1]=$default_longitude;
		$ret[2]="0";
	}
	return $ret;
}

function LocateAttachment($dbh,$id,$latitude,$longitude) {
	$query="UPDATE attachment SET latitude='$latitude', longitude='$longitude' WHERE message_id=$id";
	$result=mysql_query($query,$dbh);
}

function RefineChannelTagFilter($c,$dbh) {
	$tWhere="";
	$query="SELECT tag_id,tag_group_id FROM tag_x_channel WHERE channel_id=$c";
	$result=mysql_query($query,$dbh);
	if (mysql_num_rows($result)>0) {
		$i=0;
		$tags=array();
		while($row=mysql_fetch_array($result,MYSQL_NUM)) {
			if($row[0]>=0) {
				if(!in_array($row[0],$tags)) {
					$tags[$i]=$row[0];
					$i++;
				}
			} else if ($row[1]>=0) {
				$x=GetTagsInGroup($row[1],$dbh);
				for($j=0;$j<sizeof($x);$j++) {
					if(!in_array($x[$j],$tags)) {
						$tags[$i]=$x[$j];
						$i++;
					}
				}
			}
		}
		$tWhere=implode(",",$tags);
		$tWhere="IN(".$tWhere.")";
	}
	return $tWhere;
}

function RefineChannelFilter($qWhere,$c,$crono,$dbh,$children="") {
	$ret="";
	if ($crono==1 && $qWhere!="IN (-1)") {
		$query="SELECT tag_id,tag_group_id FROM tag_x_channel WHERE channel_id=$c";
		$result=mysql_query($query,$dbh);
		if (mysql_num_rows($result)>0) {
			$i=0;
			$tags=array();
			while($row=mysql_fetch_array($result,MYSQL_NUM)) {
				if($row[0]>=0) {
					if(!in_array($row[0],$tags)) {
						$tags[$i]=$row[0];
						$i++;
					}
				} else if ($row[1]>=0) {
					$x=GetTagsInGroup($row[1],$dbh);
					for($j=0;$j<sizeof($x);$j++) {
						if(!in_array($x[$j],$tags)) {
							$tags[$i]=$x[$j];
							$i++;
						}
					}
				}
			}
			$tWhere=implode(",",$tags);
			$tWhere="IN(".$tWhere.")";
			if ($children=="") {
				$query="SELECT DISTINCT message.message_id FROM message,tag_x_message WHERE message.message_id = tag_x_message.message_id AND tag_id ".$tWhere;
			} else {
				$query="SELECT DISTINCT message.message_id FROM message,tag_x_message WHERE message.channel_id IN (".$children.") AND message.message_id = tag_x_message.message_id AND tag_id ".$tWhere;
			}	
			$result=mysql_query($query,$dbh);
			if (mysql_num_rows($result)>0) {
				$i=0;
				while($row=mysql_fetch_array($result,MYSQL_NUM)) {
					$messages[$i]=$row[0];
					$i++;
				}
				if ($qWhere=="") {
					$ret="IN(".implode(",",$messages).")";
				} else {
					str_replace("IN","",$qWhere);
					str_replace("(","",$qWhere);
					str_replace(")","",$qWhere);
					$qWhere=trim($qWhere);
					$searched_messages=explode(",",$qWhere);
					$result_messages=array();
					$j=0;
					for($i=0;$i<sizeof($searched_messages);$i++) {
						if(in_array($searched_messages[$i],$messages)) {
							$result_messages[$j]=$searched_messages[$i];
							$j++;
						}
					}
					if (sizeof($result_messages)>0) {
						$ret="IN(".implode(",",$result_messages).")";
					}
				}
			} else {
				if ($qWhere=="") {
					$ret="IN (-1)";
				}
			}
		}
	}
	return $ret;
}

function GetGroupedChannels($dbh,$c) {
	$ret="=".$c;
	$query="SELECT channel_id FROM channel WHERE parent_channel_id=$c AND is_visible=1";
	$result=mysql_query($query,$dbh);
	if (mysql_num_rows($result)>0) {
		$ret="";
		while($row=mysql_fetch_array($result,MYSQL_NUM)) {
			if($ret=="") {
				$ret=$row[0];
			} else {
				$ret.=",".$row[0];
			}
		}
		$ret="IN(".$ret.")";
	} 
	return $ret;
}

function GetQMessageList($c,$q,$dbh) {
	$ret="";
	$query="SELECT DISTINCT message_id FROM message WHERE message_text LIKE '%$q%'";
	$result=mysql_query($query,$dbh);
	while($row=mysql_fetch_array($result,MYSQL_NUM)) {
		if ($ret=="") {
			$ret=$row[0];
		} else {
			$ret.=",".$row[0];
		}
	}
	if ($ret!="") {
		$ret="IN (".$ret.")";
	} else {
		$ret="IN (-1)";
	}
	return $ret;
}

/*
function GetQMessageList($selection_list,$dbh) {
	$cond="";
	$ret="";
	$selection_list=explode(",",$selection_list);
	for($i=1;$i<sizeof($selection_list);$i++) {
		if($cond=="") {
			$cond=$selection_list[$i];
		} else {
			$cond.=",".$selection_list[$i];
		}
	}
	if($cond!="") {
		$cond="IN (".$cond.")";
		$query="SELECT DISTINCT(message.message_id) FROM message,tag_x_message WHERE tag_x_message.tag_id ".$cond." AND message.message_id = tag_x_message.message_id";
		$result=mysql_query($query,$dbh);
		while($row=mysql_fetch_array($result,MYSQL_NUM)) {
			if ($ret=="") {
				$ret=$row[0];
			} else {
				$ret.=",".$row[0];
			}
		}
		if ($ret!="") {
			$ret="IN (".$ret.")";
		} else {
			$ret="IN (-1)";
		}
	} else {
		$ret="IN (-1)";
	}
	return $ret;
}
*/

function GetChannelFolder($c,$pass,$dbh) {
	$ret="";
	$query="SELECT channel_folder FROM channel WHERE channel_id=$c AND channel_pass_edit='$pass'";
	$result=mysql_query($query,$dbh);
	if ($row=mysql_fetch_array($result,MYSQL_NUM)) {
		$ret=$row[0];
	}
	return $ret;
}

function GetOtherChannelFolder($c,$dbh) {
	$ret="";
	$query="SELECT channel_folder FROM channel WHERE channel_id=$c";
	$result=mysql_query($query,$dbh);
	if ($row=mysql_fetch_array($result,MYSQL_NUM)) {
		$ret=$row[0];
	}
	return $ret;
}

function GetChildChannels($c,$dbh) {
	$ret="";
	$query="SELECT channel_id FROM channel WHERE parent_channel_id=$c";
	$result=mysql_query($query,$dbh);
	while($row=mysql_fetch_array($result,MYSQL_NUM)) {
		if ($ret=="") {
			$ret=$row[0];
		} else {
			$ret.=",".$row[0];
		}
	}
	return $ret;
}

function GetUsersComboList($dbh,$current=-1) {
	$ct=0;
	$query="SELECT user_id,user_alias FROM user WHERE is_megafone=0 ORDER BY user_alias";
	$result = mysql_query($query, $dbh);
	while($row = mysql_fetch_array($result, MYSQL_NUM)) {
		if ($current == $row[0]) {
			$lines[$ct] = "<option value=\"".$row[0]."\" selected>".$row[1]."</option>\n";
		} else {
			$lines[$ct] = "<option value=\"".$row[0]."\">".$row[1]."</option>\n";
		}
		$ct++;
	}
	return $lines;
}

function VerifyUserPass($dbh,$u,$p,$master_pass) {
	$ret=false;
	if ($p==$master_pass) {
		$ret=true;
	} else {
		$query="SELECT zexe_pass FROM user WHERE user_id=$u";
		$result = mysql_query($query, $dbh);
		if ($row = mysql_fetch_array($result, MYSQL_NUM)) {
			if ($row[0]==$p) {
				$ret=true;
			}
		}
	}
	return $ret;
}

function GetMessageDateFromExif($file,$m,$dbh) {
	$exif = exif_read_data($file, 0, true);
	if(isset($exif['IFD0']['DateTime'])) {
		$date=$exif['IFD0']['DateTime'];
		$parts = explode(" ",$date);
		$subparts = explode(":",$parts[0]);
		$parts[0] = $subparts[0]."-".$subparts[2]."-".$subparts[1];
		$date = implode(" ",$parts);
	} else if (isset($exif['EXIF']['DateTimeOriginal'])) {
		$date=$exif['EXIF']['DateTimeOriginal'];
	}
	$query = "UPDATE message SET message_date = '$date' WHERE message_id = $m";
	$result = mysql_query($query, $dbh);
}

function GetChannelFromUserName($user_name,$megafone_name,$dbh) {
	$ret=-1;
	$user_name=trim($user_name);
	$query="SELECT channel_id FROM channel WHERE channel_name='$user_name'";
	$result = mysql_query($query, $dbh);
	if ($row = mysql_fetch_array($result, MYSQL_NUM)) {
		$ret=$row[0];
	}
	if ($ret==-1) {
		$ret=CreateNewChannel($user_name,$megafone_name,$dbh);
	}
	return $ret;
}

function GetMegafoneChannel($id,$dbh) {
	$ret=-1;
	$query="SELECT channel_id FROM user_x_channel WHERE user_id = $id";
	$result=mysql_query($query,$dbh);
	if ($row=mysql_fetch_array($result,MYSQL_NUM)) {
		$ret=$row[0];
	}
	return $ret;
}

function GetMegafoneDefaultEmail($id,$dbh) {
	$ret="";
	$query="SELECT other_mail,zexe_pass FROM user WHERE user_id = $id";
	$result=mysql_query($query,$dbh);
	if ($row=mysql_fetch_array($result,MYSQL_NUM)) {
		if ($row[0] != "") {
			$ret=$row[0].",".$row[1];
		}
	}
	return $ret;
}

function CreateNewChannel($user_name,$megafone_name,$dbh) {

	$ret=-1;
	
	if ((trim($user_name) != "") && (trim($megafone_name) != ""))  {
	
		$query="SELECT user_id FROM user WHERE user_name='$megafone_name'";
		$result=mysql_query($query,$dbh);
		if ($row=mysql_fetch_array($result,MYSQL_NUM)) {
			$id=$row[0];
			$query="UPDATE user SET user_alias='$user_name' WHERE user_id=$id";
			$result=mysql_query($query,$dbh);
		}
	
		$user_alias_clean=RemoveBadChars($user_name);
	
		$mail=GetMegafoneDefaultEmail($id,$dbh);
			
		if ($mail=="") {
			$mail=$channel_mail_prefix."_".$user_alias_clean."@megafone.net";
			$pass=$channel_mail_prefix.substr($user_alias_clean,strlen($user_alias_clean)-1,1).substr($user_alias_clean,strlen($user_alias_clean)-2,1).substr($user_alias_clean,strlen($user_alias_clean)-3,1);
		} else {
			$parts=explode(",",$mail);
			$mail=$parts[0];
			$pass=$parts[1];
		}
		$folder=$user_alias_clean;
	
		$query="INSERT INTO user (user_alias,zexe_pass) VALUES ('$user_name','$pass')";
		$result=mysql_query($query,$dbh);
			
		$user_id = mysql_insert_id();
		$query="INSERT INTO user_x_megafone (user_id,megafone_id) VALUES ($user_id,$id)";
		$result=mysql_query($query,$dbh);
	
		$megafone_channel=GetMegafoneChannel($id,$dbh);
	
		$query="INSERT INTO channel (channel_name,channel_folder,channel_mail,channel_pass,is_crono,is_visible,is_active,parent_channel_id) VALUES ('$user_name','$folder','$mail','$pass',0,1,1,$megafone_channel)";
		$result=mysql_query($query,$dbh);
		$ret = mysql_insert_id();

		mkdir("./channels/".$folder,0755);
		chmod("./channels/".$folder,0777);
		mkdir("./channels/".$folder."/image",0755);
		chmod("./channels/".$folder."/image",0777);
		mkdir("./channels/".$folder."/sound",0755);
		chmod("./channels/".$folder."/sound",0777);
		mkdir("./channels/".$folder."/video",0755);
		chmod("./channels/".$folder."/video",0777);
			
		shell_exec('sudo -S /usr/local/psa/bin/mail --create '.$mail.' -mailbox true -cp_access true -passwd '.$pass);
	}
	
	return $ret;
}

function UpdateMegafoneUser($user_name,$megafone_name,$dbh) {
	$query="SELECT user_id FROM user WHERE user_name='$megafone_name'";
	$result=mysql_query($query,$dbh);
	if ($row=mysql_fetch_array($result,MYSQL_NUM)) {
		$id=$row[0];
		$query="UPDATE user SET user_alias='$user_name' WHERE user_id=$id";
		$result=mysql_query($query,$dbh);
	}
}

function VerifyChannelMail($user_name,$megafone_name,$dbh) {
	$query="SELECT channel_mail FROM channel WHERE channel_name='$user_name'";
	$result=mysql_query($query,$dbh);
	if ($row=mysql_fetch_array($result,MYSQL_NUM)) {
		$mail_channel=$row[0];
		$query="SELECT other_mail, zexe_pass FROM user WHERE user_name = '$megafone_name'";
		$result=mysql_query($query,$dbh);
		if ($row=mysql_fetch_array($result,MYSQL_NUM)) {
			$mail_megafone=$row[0];
			$pass_megafone=$row[1];
			if ($mail_channel != $mail_megafone) {
				$query="UPDATE channel SET channel_mail='$mail_megafone', channel_pass='$pass_megafone' WHERE channel_name='$user_name'";
				$result=mysql_query($query,$dbh);
			}
		}
	}
}

function GetParentChannels($dbh) {
	$ret="";
	$channels=array();
	$i=0;
	$query="SELECT DISTINCT parent_channel_id FROM channel WHERE parent_channel_id <> -1 AND is_visible = 1";
	$result=mysql_query($query,$dbh);
	while ($row=mysql_fetch_array($result,MYSQL_NUM)) {
		$id=$row[0];
		$query="SELECT channel_name FROM channel WHERE channel_id=$id AND is_visible = 1";
		$result2=mysql_query($query,$dbh);
		$row2=mysql_fetch_array($result2,MYSQL_NUM);
		$name=$row2[0];
		$channels[$i]=$id.",".$name;
		$i++;
	}
	if (sizeof($channels)>0) {
		return $channels;
	} else {
		return $ret;
	}
}

function GetChildChannelsName($c,$dbh) {
	$ret="";
	$channels=array();
	$i=0;
	$query="SELECT DISTINCT channel_id, channel_name FROM channel WHERE parent_channel_id = $c AND is_visible = 1 ORDER BY channel_name";
	$result=mysql_query($query,$dbh);
	while ($row=mysql_fetch_array($result,MYSQL_NUM)) {
		$id=$row[0];
		$name=$row[1];
		$channels[$i]=$id.",".$name;
		$i++;
	}
	if (sizeof($channels)>0) {
		return $channels;
	} else {
		return $ret;
	}
}

function GetTagNames($tag_list,$dbh,$l=0) {
	$ret="";
	$list=explode(",",$tag_list);
	if (sizeof($list)>1) {
		for($i=1;$i<sizeof($list);$i++) {
			$tag=$list[$i];
			$tag=str_replace("/",",",$tag);
			$query="SELECT tag_name FROM tag WHERE tag_id IN($tag) ORDER BY tag_name";
			$result=mysql_query($query,$dbh);
			while ($row=mysql_fetch_array($result,MYSQL_NUM)) {
				if($l>0) {
					$name=GetTagTranslationFromName($row[0],$l,$dbh);
				} else {
					$name=$row[0];
				}
				if ($ret=="") {
					$ret=$name;
				} else {
					$ret.=", ".$name;
				}
			}
		}
	}
	return $ret;
}

function GetTagTranslationFromName($t,$l,$dbh) {
	$ret=$t;
	$query="SELECT tag_id FROM tag WHERE tag_name='$t'";
	$result = mysql_query($query, $dbh);
	if($row=mysql_fetch_array($result,MYSQL_NUM)) {
		$id=$row[0];
		$query2="SELECT translation FROM tag_x_language WHERE tag_id = $id AND language_id = $l";
		$result2 = mysql_query($query2, $dbh);
		if($row2=mysql_fetch_array($result2,MYSQL_NUM)) {
			$ret=$row2[0];
		}
	}
	return $ret;
}

function GetLatestDate($c,$crono,$qWhere,$exc,$dbh,$children) {
	$r="";
	if ($crono == 0) {
		if ($qWhere == "") {
		    $query = "SELECT MAX(message_date) FROM message WHERE channel_id = ".$c;
			$result = mysql_query($query, $dbh);
			if($row=mysql_fetch_array($result,MYSQL_NUM)) {
				$r = $row[0];
			}
		} else if ($qWhere != "") {
			$query = "SELECT MAX(message_date) FROM message WHERE message_id ".$qWhere." AND channel_id = ".$c;
			$result = mysql_query($query, $dbh);
			if($row=mysql_fetch_array($result,MYSQL_NUM)) {
				$r = $row[0];
			}
		}
	} else {
		if ($qWhere == "") {
			if ($children=="") {
	    		$query = "SELECT MAX(message_date) FROM message, channel WHERE message.channel_id = channel.channel_id AND channel.is_visible = 1 AND channel.channel_id NOT IN ($exc)";
			} else {
				$query = "SELECT MAX(message_date) FROM message, channel WHERE message.channel_id IN (".$children.") AND message.channel_id = channel.channel_id AND channel.is_visible = 1 AND channel.channel_id NOT IN ($exc)";
			}
			$result = mysql_query($query, $dbh);
			if($row=mysql_fetch_array($result,MYSQL_NUM)) {
				$r = $row[0];
			}
		} else if ($qWhere != "") {
			if ($children=="") {
				$query = "SELECT MAX(message_date) FROM message, channel WHERE message.channel_id = channel.channel_id AND channel.is_visible = 1 AND channel.channel_id NOT IN ($exc) AND message_id ".$qWhere;
			} else {
				$query = "SELECT MAX(message_date) FROM message, channel WHERE message.channel_id = channel.channel_id AND channel.is_visible = 1 AND channel.channel_id NOT IN ($exc) AND channel.channel_id IN (".$children.") AND message_id ".$qWhere;
			}
			$result = mysql_query($query, $dbh);
			if($row=mysql_fetch_array($result,MYSQL_NUM)) {
				$r = $row[0];
			}
		} 
	}
	if ($r!="") {
		$parts=explode(" ",$r);
		$r=$parts[0];
	}
	return $r;
}


function CalculateChannelDates($c,$crono,$qWhere,$exc,$dbh,$children,$date,$loc) {
	$r="";
	if ($crono == 0) {
		if ($qWhere == "") {
		    $query = "SELECT DISTINCT(DATE(message_date)) AS d FROM message WHERE channel_id = ".$c." ORDER BY d DESC";
		} else if ($qWhere != "") {
			$query = "SELECT DISTINCT(DATE(message_date)) AS d FROM message WHERE message_id ".$qWhere." AND channel_id = ".$c." ORDER BY d DESC";
		}
	} else {
		if ($qWhere == "") {
			if ($children=="") {
	    		$query = "SELECT DISTINCT(DATE(message_date)) AS d FROM message, channel WHERE message.channel_id = channel.channel_id AND channel.is_visible = 1 AND channel.channel_id NOT IN ($exc) ORDER BY d DESC";
			} else {
				$query = "SELECT DISTINCT(DATE(message_date)) AS d FROM message, channel WHERE message.channel_id IN (".$children.") AND message.channel_id = channel.channel_id AND channel.is_visible = 1 AND channel.channel_id NOT IN ($exc) ORDER BY d DESC";
			}
		} else if ($qWhere != "") {
			if ($children=="") {
				$query = "SELECT DISTINCT(DATE(message_date)) AS d FROM message, channel WHERE message.channel_id = channel.channel_id AND channel.is_visible = 1 AND channel.channel_id NOT IN ($exc) AND message_id ".$qWhere." ORDER BY d DESC";
			} else {
				$query = "SELECT DISTINCT(DATE(message_date)) AS d FROM message, channel WHERE message.channel_id = channel.channel_id AND channel.is_visible = 1 AND channel.channel_id NOT IN ($exc) AND channel.channel_id IN (".$children.") AND message_id ".$qWhere." ORDER BY d DESC";
			}
		} 
	}
	$result = mysql_query($query, $dbh);
	$prev_month="";
	$prev_year="";
	$days="";
	$dates=array();
	$date_parts=explode("-",$date);
	$current_month=$date_parts[1];
	$current_year=$date_parts[0];
	$i=0;
	while($row=mysql_fetch_array($result,MYSQL_NUM)) {
		$this_date=explode("-",$row[0]);
		$this_day=$this_date[2];
		$this_month=$this_date[1];
		$this_year=$this_date[0];
		if($this_year!="0000") {
			if ($this_month != $prev_month || $this_year != $prev_year) {
				$prev_month = $this_month;
				$prev_year = $this_year;
				$d=strtotime($row[0]);
				setlocale(LC_TIME, $loc);
				//$dates[$i] = strftime("%B",$d)." de ".date("Y",$d).",".$row[0];
				$dates[$i] = date("Y",$d)." ".strftime("%B",$d).",".$row[0];
				if ($this_month == $current_month && $this_year == $current_year) {
					$dates[$i] = $dates[$i]."*";
				}
				$i++;
			}
			if ($this_month == $current_month && $this_year == $current_year) {
				if ($days=="") {
					$days=$row[0];
				} else {
					$days=$row[0].",".$days;
				}
			}
		}
	}
	return $r=array($dates,$days);
}

function CalculateMapDates($qWhere,$dbh,$loc) {
	$r="";
	$children=GetChildChannels($default_channel_id,$dbh);
	if ($qWhere == "") {
		if ($children=="") {
	    	$query = "SELECT DISTINCT(DATE(message_date)) AS d FROM message, channel WHERE message.channel_id = channel.channel_id AND channel.is_visible = 1 ORDER BY d DESC";
		} else {
			$query = "SELECT DISTINCT(DATE(message_date)) AS d FROM message, channel WHERE message.channel_id IN (".$children.") AND message.channel_id = channel.channel_id AND channel.is_visible = 1 ORDER BY d DESC";
		}
	} else if ($qWhere != "") {
		if ($children=="") {
			$query = "SELECT DISTINCT(DATE(message_date)) AS d FROM message, channel WHERE message.channel_id = channel.channel_id AND channel.is_visible = 1 AND message_id ".$qWhere." ORDER BY d DESC";
		} else {
			$query = "SELECT DISTINCT(DATE(message_date)) AS d FROM message, channel WHERE message.channel_id = channel.channel_id AND channel.is_visible = 1 AND channel.channel_id IN (".$children.") AND message_id ".$qWhere." ORDER BY d DESC";
		}
	}
	$result = mysql_query($query, $dbh);
	$prev_month="";
	$prev_year="";
	$dates_from=array();
	$dates_to=array();
	$i=0;
	while($row=mysql_fetch_array($result,MYSQL_NUM)) {
		$this_date=explode("-",$row[0]);
		$this_month=$this_date[1];
		$this_year=$this_date[0];
		if($this_year!="0000") {
			if ($this_month != $prev_month || $this_year != $prev_year) {
				$prev_month = $this_month;
				$prev_year = $this_year;
				$d=strtotime($row[0]);
				setlocale(LC_TIME, $loc);
				$dates_from[$i] = date("Y",$d)." ".strftime("%B",$d).",".date("Y-m",$d)."-01";
				$dates_to[$i] = date("Y",$d)." ".strftime("%B",$d).",".date("Y-m-t",$d);
				$i++;
			}
		}
	}
	return $r=array($dates_from,$dates_to);
}

function GetMegafoneLanguage($megafone,$dbh) {
	$ret=0;
	$query="SELECT user_alias FROM user WHERE user_name='$megafone'";
	$result = mysql_query($query, $dbh);
	if($row=mysql_fetch_array($result,MYSQL_NUM)) {
		$alias=$row[0];
		$query="SELECT prefered_language FROM user WHERE user_alias='$alias' AND is_megafone=0";
		$result = mysql_query($query, $dbh);
		if($row=mysql_fetch_array($result,MYSQL_NUM)) {
			$ret=$row[0];
		}
	}
	return $ret;
}

function GetUserLanguage($c,$dbh) {
	$ret=0;
	/*
	$query="SELECT channel_name FROM channel WHERE channel_id=$c";
	$result = mysql_query($query, $dbh);
	if($row=mysql_fetch_array($result,MYSQL_NUM)) {
		$alias=$row[0];
		$query="SELECT prefered_language FROM user WHERE user_alias='$alias' AND is_megafone=0";
		$result = mysql_query($query, $dbh);
		if($row=mysql_fetch_array($result,MYSQL_NUM)) {
			$ret=$row[0];
		}
	}
	*/
	return $ret;
}

function GetTagTranslation($id,$lang,$dbh) {
	$ret="";
	$query="SELECT translation FROM tag_x_language WHERE tag_id = $id AND language_id = $lang";
	$result = mysql_query($query, $dbh);
	if($row=mysql_fetch_array($result,MYSQL_NUM)) {
		$ret=$row[0];
	}
	return $ret;
}

function VerifyUserAccess($id,$pass,$dbh) {
	$ret=false;
	$query="SELECT channel_pass_edit FROM user, channel WHERE user.user_id = $id AND user.user_alias = channel.channel_name";
	$result = mysql_query($query, $dbh);
	if($row=mysql_fetch_array($result,MYSQL_NUM)) {
		if ($row[0]==$pass) {
			$ret=true;
		}
	} else {
		$query="SELECT zexe_pass FROM user WHERE user_id = $id";
		$result = mysql_query($query, $dbh);
		if($row=mysql_fetch_array($result,MYSQL_NUM)) {
			if ($row[0]==$pass) {
				$ret=true;
			}
		}
	}
	return $ret;
}

function GetNComments($id,$dbh) {
	$ret=0;
	$query="SELECT COUNT(comment_id) FROM comment WHERE message_id = $id";
	$result = mysql_query($query, $dbh);
	if($row=mysql_fetch_array($result,MYSQL_NUM)) {
		$ret=$row[0];
	}
	return $ret;
}

function GetPublishedDefault($c,$dbh) {
	$ret=1;
	$query="SELECT publish_default FROM channel WHERE channel_id=$c";
	$result = mysql_query($query, $dbh);
	if($row=mysql_fetch_array($result,MYSQL_NUM)) {
		$ret=$row[0];
	}
	return $ret;
}

function UpdateChannelUserPass($c,$dbh,$user,$pass) {
	$query="UPDATE channel SET user='$user' AND pass='$pass' WHERE channel_id=$c";
	$result = mysql_query($query, $dbh);
}

function GetMessageAddress($id,$dbh) {
	$ret="";
	$query="SELECT DISTINCT map_address FROM attachment WHERE message_id=$id AND map_address<>''";
	$result = mysql_query($query, $dbh);
	if($row=mysql_fetch_array($result,MYSQL_NUM)) {
		$parts=explode(",",$row[0]);
		for($i=0;$i<sizeof($parts);$i++) {
			if (trim($parts[$i])!="Espanya" && trim($parts[$i])!="España") {
				if ($ret=="") {
					$ret=$parts[$i];
				} else {
					$ret.=", ".$parts[$i];
				}
			}
		}
		$ret=utf8_decode($ret);
	}
	return $ret;
}

function GetColorCombination($c,$dbh) {
	$ret="0";
	$query="SELECT color_combination FROM channel WHERE channel_id = $c";
	$result = mysql_query($query, $dbh);
	if($row=mysql_fetch_array($result,MYSQL_NUM)) {
		$ret=$row[0];
	} 
	return $ret;
}

function GetAllTags($dbh) {
	$ret=array();
	$query="SELECT tag_name FROM tag WHERE in_megafone=1 ORDER BY tag_name";
	$result = mysql_query($query, $dbh);
	while($row=mysql_fetch_array($result,MYSQL_NUM)) {
		$ret[]=$row[0];
	}
	return $ret;
}

function GetMessageImageAttachment($id,$dbh) {
	$ret=-1;
	$query="SELECT attachment_id FROM attachment WHERE message_id=$id AND content_type=1";
	$result = mysql_query($query, $dbh);
	if($row=mysql_fetch_array($result,MYSQL_NUM)) {
		$ret=$row[0];
	} 
	return $ret;
}

function UpdateMapData($address,$lat,$lng,$attachment_id,$dbh) {
	$map_filename="map".$attachment_id.".gif";
	$query="UPDATE attachment SET map_address='$address', latitude='$lat', longitude='$lng', map_filename='$map_filename' WHERE attachment_id=$attachment_id";
	$result = mysql_query($query, $dbh);
}

function HasImage($id,$dbh) {
	$ret=false;
	$query="SELECT attachment_id FROM attachment WHERE message_id=$id AND content_type=1";
	$result = mysql_query($query, $dbh);
	if($row=mysql_fetch_array($result,MYSQL_NUM)) {
		$ret=true;
	} 
	return $ret;
}

function GetParticipantNames($dbh) {
	$ret="";
	$query="SELECT DISTINCT user_name FROM user, channel WHERE parent_channel_id <> -1 AND is_visible = 1 AND is_active = 1 AND is_megafone=0 AND user_alias = channel_name ORDER BY user_name";
	$result = mysql_query($query, $dbh);
	while($row=mysql_fetch_array($result,MYSQL_NUM)) {
		if ($ret=="") {
			$ret=$row[0];
		} else {
			$ret=$ret.", ".$row[0];
		}
	}
	return $ret;
}

function UpdateMessageAddress($dbh,$id,$address) {
	$ret=-1;
	$query="UPDATE attachment SET map_address='$address' WHERE message_id=$id";
	$result = mysql_query($query, $dbh);
	$query="SELECT attachment_id FROM attachment WHERE message_id=$id AND content_type=1";
	$result = mysql_query($query, $dbh);
	if($row=mysql_fetch_array($result,MYSQL_NUM)) {
		$ret=$row[0];
	}
	return $ret;
}

function GetDefaultImage($id,$dbh) {
	$ret="";
	$query="SELECT filename FROM attachment WHERE message_id=$id AND content_type=1";
	$result = mysql_query($query, $dbh);
	if($row=mysql_fetch_array($result,MYSQL_NUM)) {
		$ret="channels/".$row[0];
	}
	return $ret;
}

?>