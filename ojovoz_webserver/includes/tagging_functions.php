<?
//tagging functions

include_once("channel_vars.php");
include_once("database_functions.php");

function SelectTags($dbh,$t) {
	$a=explode(",",$t);
	for($i=0;$i<sizeof($a);$i++) {
		$x=$a[$i];
		$query="UPDATE tag SET times_clicked = times_clicked + 1 WHERE tag_id = $x";
		$result = mysql_query($query, $dbh);
	}
}

function ExtractTagsFromText($t) {
	//array with [0] tags and [1] text is returned
	$p=array();
	$point = strpos($t,".");
	if ($point === false) {
		$test = explode(",",$t);
		if (sizeof($test) > 1) {
			$p[0] = "";
			$p[1] = $t;
		} else {
			$p[0] = $t;
			$p[1] = "";
		}
	} else {
		$p[0] = substr($t,$point+1);		
		$p[1] = substr($t,0,$point);
	}
	return $p;
}

function GetTagIDFromName($t,$dbh) {
	$query = "SELECT tag_id FROM tag WHERE tag_name = '".$t."'";
	$result = mysql_query($query, $dbh);
	if ($row = mysql_fetch_array($result, MYSQL_NUM)) {
		return $row[0];
	} else {
		$query="SELECT tag_id FROM tag_x_language WHERE translation = '$t'";
		$result = mysql_query($query, $dbh);
		if ($row = mysql_fetch_array($result, MYSQL_NUM)) {
			return $row[0];
		} else {
			return -1;
		}
	}
}

function GetTagNameFromID($t,$dbh) {
	$query = "SELECT tag_name FROM tag WHERE tag_id = $t";
	$result = mysql_query($query, $dbh);
	if ($row = mysql_fetch_array($result, MYSQL_NUM)) {
		return $row[0];
	} else {
		return -1;
	}
}

function GetTagNamesFromIDs($t,$dbh) {
	$a = explode(",",$t);
	$n = "";
	for($i=0;$i<sizeof($a);$i++) {
		if ($n == "") {
			$n = GetTagNameFromID($a[$i],$dbh);
		} else {
			$n = $n.",".GetTagNameFromID($a[$i],$dbh);
		}
	}
	return $n;
}

function ProcessMessageTags($t,$m,$dbh) {
	//updates tags for a given message
	//$t = comma separated list of tags
	//$m = message id
	//$dbh = database connection
	if ($t != "") {		
		$tagList = array();
		$tagList = explode(",",$t);
		if (sizeof($tagList)>0) {
			for($i=0;$i<sizeof($tagList);$i++) {
				$newTag = trim(strtolower($tagList[$i]));
				$newTag = str_replace(".","",$newTag);
				if ($newTag != "") {
					$tagId = GetTagIDFromName($newTag,$dbh);
					if ($tagId == -1) {
						$query = "INSERT INTO tag (tag_name) VALUES ('".$newTag."')";
						$result = mysql_query($query, $dbh);
						$query = "SELECT LAST_INSERT_ID()";
						$result = mysql_query($query, $dbh);
						$row = mysql_fetch_array($result,MYSQL_NUM);
						$tagId = $row[0];
						$query = "INSERT INTO tag_x_message (tag_id, message_id, from_mobile) VALUES ($tagId,$m,1)";
						$result = mysql_query($query, $dbh);
					} else {
						if (!TagExistsInMessage($tagId,$m,$dbh)) {
							$query = "INSERT INTO tag_x_message (tag_id, message_id, from_mobile) VALUES ($tagId,$m,1)";
							$result = mysql_query($query, $dbh);
						}
					}	
				}
			}
		}
	}
}

function TagExistsInMessage($t,$m,$dbh) {
	$found=false;
	$query="SELECT tag_id FROM tag_x_message WHERE message_id = $m AND tag_id = $t";
	$result = mysql_query($query, $dbh);
	if ($row = mysql_fetch_array($result,MYSQL_NUM)) {
		$found=true;
	}
	return $found;
}

function GetSearchTagsIDs($dbh,$t) {
	$query = "SELECT tag_id FROM tag WHERE tag_name LIKE '$t%'";
	$result = mysql_query($query, $dbh);
	$count = 0;
	$a=array();
	while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
		$a[$count] = $row[0];
		$count++;
	}
	return $a;
}

function GetTagFilter($dbh,$q) {
	//receives a comma separated list of tags
	$where = "";
	$n = 0;
	$qList = explode(" ",$q);
	for ($i=0;$i<sizeof($qList);$i++) {
		$tags=GetSearchTagsIDs($dbh,trim($qList[$i]));
		for ($j=0;$j<sizeof($tags);$j++) {
			if ($where == "") {
				$where = $tags[$j];
			} else {
				$where .= ",".$tags[$j];
			}
			$n++;
		}
	}
	if ($n == 1) {
		$where = " = ".$where;
		return $where;
	} else {
		if ($where != "") {
			$where = " IN (".$where.")";
		} else {
			$where = " = -1";
		}
		return $where;
	}
}

function GetTagsInChannel($c,$dbh) {
	$ret=array();
	$query="SELECT tag_id,tag_group_id FROM tag_x_channel WHERE channel_id=$c";
	$result=mysql_query($query,$dbh);
	$i=0;
	while($row=mysql_fetch_array($result,MYSQL_NUM)) {
		if($row[0]>=0) {
			if(!in_array($row[0],$ret)) {
				$ret[$i]=$row[0];
				$i++;
			}
		} else if ($row[1]>=0) {
			$x=GetTagsInGroup($row[1],$dbh);
			for($j=0;$j<sizeof($x);$j++) {
				if(!in_array($x[$j],$ret)) {
					$ret[$i]=$x[$j];
					$i++;
				}
			}
		}
	}
	return $ret;
}

function CompressMessageTags($message_tag_array,$dbh,$language=0) {
	$rt=array();
	$ct=0;
	for($i=0;$i<sizeof($message_tag_array);$i++) {
		if($message_tag_array[$i]["group"]==-1) {
			$rt[$ct]["id"]=$message_tag_array[$i]["id"];
			$rt[$ct]["name"]=$message_tag_array[$i]["name"];
			$rt[$ct]["group"]=-1;
			$ct++;
		} else {
			$found=false;
			for($j=0;$j<sizeof($rt);$j++) {
				if($message_tag_array[$i]["group"]==$rt[$j]["group"]) {
					$found=true;
					$rt[$j]["id"]=$rt[$j]["id"].",".$message_tag_array[$i]["id"];
					break;
				}
			}
			if (!$found) {
				$name=GetGroupNameFromID($message_tag_array[$i]["group"],$dbh);
				$n=GetTagTranslation($message_tag_array[$i]["group"]*-1,$language,$dbh);
				if ($n!="") {
					$name=$n;
				}
				$rt[$ct]["id"]=$message_tag_array[$i]["id"];
				$rt[$ct]["name"]=$name;
				$rt[$ct]["group"]=$message_tag_array[$i]["group"];
				$ct++;
			}
		}
	}
	return $rt;
}

function GetMessageTags($m,$c,$mp,$tag_color,$dbh,$search=false,$language=0) {
	//$m=message
	//$c=channel
	$tags = "";
	$tag_array=array();
	$message_tag_array=array();
	$ct=0;
	$tag_array = GetTagsInChannel($c,$dbh);
	$query = "SELECT tag.tag_id, tag.tag_name, tag.tag_group_id FROM tag,tag_x_message WHERE tag.tag_id = tag_x_message.tag_id AND tag_x_message.message_id = $m ORDER BY tag_x_message.tag_x_message_id";
	$result = mysql_query($query, $dbh);
	while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
		$name=GetTagTranslation($row[0],$language,$dbh);
		if ($name=="") {
			$name=$row[1];
		} 
		$message_tag_array[$ct]["id"]=$row[0];
		$message_tag_array[$ct]["name"]=$name;
		$message_tag_array[$ct]["group"]=$row[2];
		$ct++;
	}
	$message_tag_array=CompressMessageTags($message_tag_array,$dbh,$language);
	for($i=0;$i<sizeof($message_tag_array);$i++) {
		if ($search==false) {
			if ((sizeof($tag_array)>0 && in_array($message_tag_array[$i]["id"],$tag_array)) || (sizeof($tag_array)==0)) {
				$tags = $tags."<a href=\"".$mp."?c=$c&tag=".$message_tag_array[$i]["id"]."\" style=\"color: ".$tag_color.";\">".$message_tag_array[$i]["name"]."</a> ";
			} else {
				$tags = $tags."<font style=\"color: ".$tag_color.";\">".$message_tag_array[$i]["name"]."</font> ";
			}
		} else {
			$tags = $tags."<font style=\"color: ".$tag_color.";\">".$message_tag_array[$i]["name"]."</font> ";
		}
	}
	return $tags;
}

function GetMessageTagsCSV($m,$dbh) {
	//$m=message
	//$c=channel
	$tags = "";
	$query = "SELECT tag.tag_id, tag.tag_name FROM tag,tag_x_message WHERE tag.tag_id = tag_x_message.tag_id AND tag_x_message.message_id = $m ORDER BY tag_x_message.tag_x_message_id";
	$result = mysql_query($query, $dbh);
	while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
		if($tags=="") {
			$tags=$row[1];
		} else {
			$tags .= ",".$row[1];
		}
	}
	return $tags;
}

function GetTotalTagsFromGroup($g,$c,$crono,$dbh) {
	if ($crono==1) {
		$query="SELECT COUNT(tag_x_message.tag_id) AS c, tag.tag_id FROM tag,tag_x_message WHERE tag_x_message.tag_id = tag.tag_id AND tag.tag_group_id = $g GROUP BY tag.tag_id";
	} else {
		$query="SELECT COUNT(tag_x_message.tag_id) AS c, tag.tag_id FROM tag,tag_x_message,message WHERE tag_x_message.tag_id = tag.tag_id AND tag.tag_group_id = $g AND message.message_id = tag_x_message.message_id AND channel_id = $c GROUP BY tag.tag_id";
	}
	$result = mysql_query($query, $dbh);
	$t=0;
	$p="";
	while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
		$t = $t + $row[0];
		if($p=="") {
			$p = $row[1];
		} else {
			$p .= ",".$row[1];
		}
	}
	$r[0] = $t;
	$r[1] = $p;
	return $r;
}

function GetGroupNameFromID($g,$dbh) {
	$query="SELECT tag_group_name FROM tag_group WHERE tag_group_id = $g";
	$result = mysql_query($query, $dbh);
	$row = mysql_fetch_array($result, MYSQL_NUM);
	return $row[0];
}

function CompressTags($tags,$tag_mode,$c,$crono,$dbh,$language=0) {
	$ct=0;
	for($i=0;$i<sizeof($tags);$i++) {
		if ($tags[$i]["id"] >= 0) {
			$g = $tags[$i]["group"];
			if($g == -1) {
				$p[$ct] = array("id" => $tags[$i]["id"], "name" => $tags[$i]["name"], "times" => $tags[$i]["times"]);
				$ct++;
			} else {
				$r = GetTotalTagsFromGroup($g,$c,$crono,$dbh);
				$ids = $r[1];
				$n = GetGroupNameFromID($g,$dbh);
				$t = GetTagTranslation($g*-1,$language,$dbh);
				if ($t!="") {
					$n = $t;
				}
				if ($tag_mode==0) {
					$t = $r[0];
				} else {
					$t=$tags[$i]["times"];
					for($j=$i+1;$j<sizeof($tags);$j++) {
						if (($tags[$j]["group"] == $g) && ($tags[$j]["times"] > $t)) {
							$t=$tags[$j]["times"];
						}
					}
				}
				$p[$ct] = array("id" => $ids, "name" => $n, "times" => $t);
				for($j=$i+1;$j<sizeof($tags);$j++) {
					if ($tags[$j]["group"] == $g) {
						$tags[$j]["id"] = -1;
					}
				}
				$ct++;
			}
		}
	}
	return $p;
}

function TagCloud($c,$crono,$m,$e,$tb1,$tbn,$page,$tag_color,$bgcolor,$minSize,$maxSize,$dbh,$selection_list,$tag_cloud_title,$correlated,$tag_mode,$legend=false,$search=false,$tag_minimum_date='0000-00-00',$children="",$tag_hilite_color="",$hilite_size=0,$main_crono=-1,$language=0,$locales="") {
	if ($crono == 0) {
		if ($tag_mode==0) {
			$query = "SELECT COUNT(tag_x_message.tag_id) AS r, tag.tag_id, tag.tag_name, tag.tag_group_id, tag.is_study FROM tag,tag_x_message,message WHERE tag.tag_id = tag_x_message.tag_id AND tag_x_message.message_id = message.message_id AND message.channel_id = $c AND DATE(message.message_date) > '$tag_minimum_date' GROUP BY tag.tag_name ORDER BY r DESC LIMIT 0,$m";
		} else {
			$query = "SELECT COUNT(DISTINCT(message.message_sender)) AS r, tag.tag_id, tag.tag_name, tag.tag_group_id, tag.is_study FROM tag,tag_x_message,message WHERE tag.tag_id = tag_x_message.tag_id AND tag_x_message.message_id = message.message_id AND message.channel_id = $c AND DATE(message.message_date) > '$tag_minimum_date' GROUP BY tag.tag_name ORDER BY r DESC LIMIT 0,$m";
		}
	} else {
		if ($c==-3) {
			$filter = "AND tag.in_map = 1";
		} else {
			//$filter=RefineChannelFilter("",$c,1,$dbh);
			$filter=RefineChannelTagFilter($c,$dbh);
			if ($filter!="") {
				//$filter="AND message.message_id ".$filter;
				$filter="AND tag.tag_id ".$filter;
			}
		}
		if ($tag_mode==0) {
			if ($children=="") {
				$query = "SELECT COUNT(tag_x_message.tag_id) AS r, tag.tag_id, tag.tag_name, tag.tag_group_id, tag.is_study FROM tag,tag_x_message,message,channel WHERE tag.tag_id = tag_x_message.tag_id AND message.message_id = tag_x_message.message_id AND DATE(message.message_date) > '$tag_minimum_date' AND channel.channel_id = message.channel_id AND channel.is_visible = 1 AND channel.is_study = 0 AND channel.channel_id NOT IN ($e) ".$filter." GROUP BY tag.tag_name ORDER BY r DESC LIMIT 0,$m";
			} else {
				$query = "SELECT COUNT(tag_x_message.tag_id) AS r, tag.tag_id, tag.tag_name, tag.tag_group_id, tag.is_study FROM tag,tag_x_message,message,channel WHERE tag.tag_id = tag_x_message.tag_id AND message.message_id = tag_x_message.message_id AND DATE(message.message_date) > '$tag_minimum_date' AND channel.channel_id = message.channel_id AND channel.is_visible = 1 AND channel.is_study = 0 AND channel.channel_id NOT IN ($e) AND channel.channel_id IN (".$children.") ".$filter." GROUP BY tag.tag_name ORDER BY r DESC LIMIT 0,$m";
			}
		} else {
			if ($children=="") {
				$query = "SELECT COUNT(DISTINCT(message.message_sender)) AS r, tag.tag_id, tag.tag_name, tag.tag_group_id, tag.is_study FROM tag,tag_x_message,message,channel WHERE tag.tag_id = tag_x_message.tag_id AND message.message_id = tag_x_message.message_id AND DATE(message.message_date) > '$tag_minimum_date' AND channel.channel_id = message.channel_id AND channel.is_visible = 1 AND channel.is_study = 0 AND channel.channel_id NOT IN ($e) ".$filter." GROUP BY tag.tag_name ORDER BY r DESC LIMIT 0,$m";
			} else {
				$query = "SELECT COUNT(DISTINCT(message.message_sender)) AS r, tag.tag_id, tag.tag_name, tag.tag_group_id, tag.is_study FROM tag,tag_x_message,message,channel WHERE tag.tag_id = tag_x_message.tag_id AND message.message_id = tag_x_message.message_id AND DATE(message.message_date) > '$tag_minimum_date' AND channel.channel_id = message.channel_id AND channel.is_visible = 1 AND channel.is_study = 0 AND channel.channel_id NOT IN ($e) AND channel.channel_id IN (".$children.") ".$filter." GROUP BY tag.tag_name ORDER BY r DESC LIMIT 0,$m";
			}
		}
	}
	if ($correlated=="-1" && $search==false) {
		$deactivate=false;
		$x="";
	} else {
		$deactivate=true;
		$x=explode(",",$correlated);
	}
	$result = mysql_query($query, $dbh);
	$ct = 0;
	$max=0;
	$min=9999;
	$cloud="";
	$title="";
	while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
		$name = GetTagTranslation($row[1],$language,$dbh);
		if ($name=="") {
			$name=$row[2];
		}
		if ($row[4]==1) {
			$name=$name."*";
		}
		if($c==-3) {
			$gt=(IsGeoTagged($dbh,$row[1]) && IsPublished($dbh,$row[1]));
		} else {
			$gt=true;
		}
		if ($row[0] > 0 && $gt) {
			if ($ct < 10) {
				$title .= $name." ";
			}
			$tags[$ct] = array("id" => $row[1], "name" => $name, "times" => $row[0], "group" => $row[3]);
			$ct++;
		}
	}
	if ($ct>0) {
		$tags = CompressTags($tags,$tag_mode,$c,$crono,$dbh,$language);
		for($i=0;$i<sizeof($tags);$i++) {
			if($tags[$i]["times"] > $max) {
				$max = $tags[$i]["times"];
			}
			if($tags[$i]["times"] < $min) {
				$min = $tags[$i]["times"];
			}
		}
		if ($locales!="") {
			$locale=$locales[$language];
		} else {
			$locale="";
		}
		$tags = BubbleSort($tags,$locale);
		$diff = $max-$min;
		if ($diff>0) {
			$step = ($maxSize-$minSize)/$diff;
		} else {
			$step=0;
		}
		$selection_list=str_replace("/",",",$selection_list);
		$a=explode(",",$selection_list);
		for($i=0;$i<sizeof($tags);$i++) {
			$size = $minSize + (($tags[$i]["times"] - $min) * $step);		
			if ($tags[$i]["times"] == 1) {
				$toolbox = $tb1[$tag_mode];
			} else {
				$toolbox = $tags[$i]["times"].$tbn[$tag_mode];
			}
			if(strpos($tags[$i]["name"],"*")>0) {
				$tags[$i]["name"]=str_replace("*","",$tags[$i]["name"]);
				$tags[$i]["name"]="<i>".$tags[$i]["name"]."</i>";
			}
			$color=$tag_color;
			$backcolor=$bgcolor;
			if($search==false) {
				for ($j=1;$j<sizeof($a);$j++) {
					$b=explode(",",$tags[$i]["id"]);
					for ($k=0;$k<sizeof($b);$k++) {
						if ($a[$j]==$b[$k]) {
							$color=$bgcolor;
							$backcolor=$tag_color;
							break;
						}
					}	 
				}
			}
			if ($deactivate==false) {
				if ($legend==false) {
					if ($c==-4) {
						if ($size>=$hilite_size) {
							$cloud = $cloud."<li style=\"display: inline\"><a href=\"".$page."?c=$main_crono&tag=".$tags[$i]["id"]."\" style=\"font-size: ".$size."em; font-family: Arial,Helvetica; color: ".$tag_hilite_color."; background-color: ".$backcolor."\" title=\"".$toolbox."\">".$tags[$i]["name"]."</a></li> ";
						} else {
							$cloud = $cloud."<li style=\"display: inline\"><a href=\"".$page."?c=$main_crono&tag=".$tags[$i]["id"]."\" style=\"font-size: ".$size."em; font-family: Arial,Helvetica; color: ".$color."; background-color: ".$backcolor."\" title=\"".$toolbox."\">".$tags[$i]["name"]."</a></li> ";
						}
					} else {
						$cloud = $cloud."<li style=\"display: inline\"><a href=\"".$page."?c=$c&tag=".$tags[$i]["id"]."\" style=\"font-size: ".$size."em; font-family: Arial,Helvetica; color: ".$color."; background-color: ".$backcolor."\" title=\"".$toolbox."\">".$tags[$i]["name"]."</a></li> ";
					}
				} else {
					$legend_tag_color=GetTagColor($dbh,$tags[$i]["id"]);
					$cloud = $cloud."<li style=\"display: inline\"><img src=\"includes/images/marker".$legend_tag_color."s.png\" align=\"absbottom\"><a href=\"".$page."?c=$c&tag=".$tags[$i]["id"]."\" style=\"font-size: ".$size."em; font-family: Arial,Helvetica; color: ".$color."; background-color: ".$backcolor."\" title=\"".$toolbox."\">".$tags[$i]["name"]."</a></li> ";
				}
			} else {
				$found=false;
				if ($search==false) {
					for($k=0;$k<sizeof($x);$k++) {
						$y=explode(",",$tags[$i]["id"]);
						for($l=0;$l<sizeof($y);$l++) {
							if($x[$k]==$y[$l]) {
								$found=true;
								break;
							}
						}
						if ($found==true) {
							break;
						}
					}
				}
				if ($found==true) {
					if ($legend==false) {
						$cloud = $cloud."<li style=\"display: inline\"><a href=\"".$page."?c=$c&tag=".$tags[$i]["id"]."\" style=\"font-size: ".$size."em; font-family: Arial,Helvetica; color: ".$color."; background-color: ".$backcolor."\" title=\"".$toolbox."\">".$tags[$i]["name"]."</a></li> ";
					} else {
						$legend_tag_color=GetTagColor($dbh,$tags[$i]["id"]);
						$cloud = $cloud."<li style=\"display: inline\"><img src=\"includes/images/marker".$legend_tag_color."s.png\" align=\"absbottom\"><a href=\"".$page."?c=$c&tag=".$tags[$i]["id"]."\" style=\"font-size: ".$size."em; font-family: Arial,Helvetica; color: ".$color."; background-color: ".$backcolor."\" title=\"".$toolbox."\">".$tags[$i]["name"]."</a></li> ";
					}
				} else {
					if ($legend==false) {
						$cloud = $cloud."<li style=\"display: inline\"><font style=\"font-size: ".$size."em; font-family: Arial,Helvetica; color: ".$color."; background-color: ".$backcolor."\">".$tags[$i]["name"]."</font></li> ";
					} else {
						$legend_tag_color=GetTagColor($dbh,$tags[$i]["id"]);
						$cloud = $cloud."<li style=\"display: inline\"><img src=\"includes/images/marker".$legend_tag_color."s.png\" align=\"absbottom\"><font style=\"font-size: ".$size."em; font-family: Arial,Helvetica; color: ".$color."; background-color: ".$backcolor."\">".$tags[$i]["name"]."</font></li> ";
					}
				}
			}
		}
	}
	if ($cloud!="") {
		$cloud="<li style=\"display: inline\"><font style=\"font-size:".$maxSize."em; color:".$bgcolor."; background-color:".$tag_color.";\">$tag_cloud_title</font></li> ".$cloud;
	}
	$tc[0] = $cloud;
	$tc[1] = $title;
	return $tc;
}

function GetTagGroup($id,$dbh) {
	$query="SELECT tag_group_id FROM tag WHERE tag_id=$id";
	$result = mysql_query($query, $dbh);
	$row = mysql_fetch_array($result, MYSQL_NUM);
	return $row[0];
}

function GetTagColor($dbh,$id) {
	if(strpos($id,",")>0) {
		$tags=explode(",",$id);
		$id=GetTagGroup($tags[0],$dbh);
		$query="SELECT color_in_map FROM tag_group WHERE tag_group_id = $id";
	} else {
		$query="SELECT color_in_map FROM tag WHERE tag_id IN($id)";
	}
	$result = mysql_query($query, $dbh);
	$row = mysql_fetch_array($result, MYSQL_NUM);
	return $row[0];
}

function BubbleSort($a,$locale="") {
	for($i=0;$i<sizeof($a);$i++) {
		for($j=0;$j<sizeof($a);$j++) {
			if ($locale!="") {
				setlocale(LC_COLLATE,$locale);
			}
			if (strcmp($a[$i]["name"],$a[$j]["name"]) < 0) {
				$b = array("id" => $a[$i]["id"], "name" => $a[$i]["name"], "times" => $a[$i]["times"]);
				$a[$i] = array("id" => $a[$j]["id"], "name" => $a[$j]["name"], "times" => $a[$j]["times"]);
				$a[$j] = $b;
			}
		}
	}
	return $a;
}

function IsGeoTagged($dbh,$t) {
	$query = "SELECT attachment_id FROM attachment,tag_x_message WHERE tag_x_message.tag_id = $t AND attachment.message_id = tag_x_message.message_id AND latitude <> '' AND longitude <> ''";
	$result = mysql_query($query, $dbh);
	if (mysql_num_rows($result) > 0) {
		return true;
	} else {
		return false;
	}
}

//descriptor functions
function GetDescriptorFilter($dbh,$d,$c) {
	$crono=IsCrono($dbh,$c);
	if ($crono==1) {
		if ($c==-3) {
			$query="SELECT DISTINCT message.message_id FROM message,attachment,descriptor_x_attachment WHERE descriptor_x_attachment.descriptor_id=$d AND attachment.attachment_id = descriptor_x_attachment.attachment_id AND message.message_id = attachment.message_id AND latitude <> '' AND longitude <> ''";
		} else {
			$query="SELECT DISTINCT message.message_id FROM message,attachment,descriptor_x_attachment WHERE descriptor_x_attachment.descriptor_id=$d AND attachment.attachment_id = descriptor_x_attachment.attachment_id AND message.message_id = attachment.message_id";
		}
	} else {
		$query="SELECT DISTINCT message.message_id FROM message,attachment,descriptor_x_attachment WHERE descriptor_x_attachment.descriptor_id=$d AND attachment.attachment_id = descriptor_x_attachment.attachment_id AND message.message_id = attachment.message_id AND message.channel_id = $c";
	}
	$result=mysql_query($query,$dbh);
	$ret="";
	while($row=mysql_fetch_array($result,MYSQL_NUM)) {
		if ($ret=="") {
			$ret = "IN (".$row[0];
		} else {
			$ret .= ",".$row[0];
		}
	}
	$ret .= ")";
	return $ret;
}

function GetMessageDescriptors($id,$c,$mp,$descriptor_color,$descriptor_language,$dbh) {
	$query="SELECT DISTINCT descriptor_category,$descriptor_language,descriptor.descriptor_id FROM descriptor,descriptor_x_attachment,attachment WHERE attachment.message_id=$id AND descriptor_x_attachment.attachment_id = attachment.attachment_id AND descriptor.descriptor_id = descriptor_x_attachment.descriptor_id ORDER BY descriptor_category,$descriptor_language";
	$result=mysql_query($query,$dbh);
	$ret="";
	while($row=mysql_fetch_array($result,MYSQL_NUM)) {
		$ret.="<font face=\"Arial, Helvetica, sans-serif\"><a href=\"$mp?c=$c&d=".$row[2]."\" style=\"color:$descriptor_color;\">".$row[1]."</a> ";
	}
	return $ret;
}

function GetStoredDescriptorCloud($dbh,$c,$descriptor_cloud_refresh) {
	$ret="";
	$seconds=$descriptor_cloud_refresh*24*60*60;
	$query="SELECT date,cloud FROM cloud WHERE channel_id=$c AND cloud_type=0";
	$result=mysql_query($query,$dbh);
	if ($row=mysql_fetch_array($result,MYSQL_NUM)) {
		$date = $row[0];
		$time = time() - $date;
		if ($time <= $seconds) {
			$ret = $row[1];
		}
	}
	return $ret;
}
	
function DescriptorCloud($dbh,$c,$crono,$descriptor_color,$background_color,$descriptor_language,$descriptor_toolbox_1_time,$descriptor_toolbox_n_times,$page,$descriptor_category,$descriptor_cloud_refresh,$selection_list,$descriptor_cloud_title,$minSize,$maxSize,$correlated) {
	$ret=GetStoredDescriptorCloud($dbh,$c,$descriptor_cloud_refresh);
	if ($correlated=="-1") {
		$deactivate=false;
		$x="";
	} else {
		$deactivate=true;
		$x=explode(",",$correlated);
	}
	if ($ret=="") {
		$query="SELECT descriptor_category, $descriptor_language, descriptor_id FROM descriptor ORDER BY descriptor_category='".$descriptor_category[0]."' DESC, descriptor_category='".$descriptor_category[1]."' DESC, descriptor_category='".$descriptor_category[2]."' DESC, descriptor_category='".$descriptor_category[3]."' DESC, descriptor_category='".$descriptor_category[4]."' DESC,$descriptor_language";
		$result=mysql_query($query,$dbh);
		$cat="";
		$ret="";
		$max=0;
		$min=9999;
		$count=0;
		while($row=mysql_fetch_array($result,MYSQL_NUM)) {
			if ($crono==1) {
				if ($c==-3) {
					$query2="SELECT COUNT(attachment.attachment_id) FROM descriptor_x_attachment, attachment WHERE descriptor_x_attachment.descriptor_id = ".$row[2]." AND attachment.attachment_id = descriptor_x_attachment.attachment_id AND attachment.latitude<>'' AND attachment.longitude<>''";
				} else {
					$query2="SELECT COUNT(attachment.attachment_id) FROM descriptor_x_attachment, attachment WHERE descriptor_x_attachment.descriptor_id = ".$row[2]." AND attachment.attachment_id = descriptor_x_attachment.attachment_id";
				}
			} else {
				$query2="SELECT COUNT(attachment.attachment_id) FROM descriptor_x_attachment, attachment, message WHERE descriptor_x_attachment.descriptor_id = ".$row[2]." AND attachment.attachment_id = descriptor_x_attachment.attachment_id AND attachment.message_id = message.message_id AND message.channel_id = $c";
			}
			$result2=mysql_query($query2,$dbh);
			if ($row2=mysql_fetch_array($result2,MYSQL_NUM)) {
				$ct=$row2[0];
			} else {
				$ct=0;
			}
			if ($ct>0) {
				$desc[$count]=array("category"=>$row[0],"descriptor"=>$row[1],"times"=>$ct,"id"=>$row[2]);
				$count++;
				if ($ct>$max) {
					$max=$ct;
				}
				if ($ct<$min) {
					$min=$ct;
				}
			}
		}
		$diff=$max-$min;
		$step=($maxSize-$minSize)/$diff;
		$store="";
		for ($i=0;$i<$count;$i++) {
			if($cat!=$desc[$i]["category"]) {
				$ret.="<li style=\"display: inline;\"><font face=\"Arial, Helvetica, sans-serif\" style=\"color:$descriptor_color;\"><strong><i>".$desc[$i]["category"].":</i></strong></font></li> ";
				$cat=$desc[$i]["category"];
				if ($store!="") {
					$store.=",";
				}
				$store.=$desc[$i]["category"]."/0/0/0";
			}
			$size = $minSize + (($desc[$i]["times"] - $min) * $step);
			if ($desc[$i]["times"]==1) {
				$title=$descriptor_toolbox_1_time;
			} else {
				$title=$desc[$i]["times"].$descriptor_toolbox_n_times;
			}
			if ($c==-3) {
				$params="?d=".$desc[$i]["id"];
			} else {
				$params="?c=$c&d=".$desc[$i]["id"];
			}
			if ($deactivate==false) {
				$ret.="<li style=\"display: inline;\"><font face=\"Arial, Helvetica, sans-serif\" style=\"color:$descriptor_color;\"><a href=\"$page$params\" title=\"".$title."\" style=\"color:$descriptor_color; font-size:".$size."em\">".$desc[$i]["descriptor"]."</a></font></li> ";
			} else {
				$found=false;
				for($j=0;$j<sizeof($x);$j++) {
					if($desc[$i]["id"]==$x[$j]) {
						$found=true;
						break;
					}
				}
				if ($found) {
					$ret.="<li style=\"display: inline;\"><font face=\"Arial, Helvetica, sans-serif\" style=\"color:$descriptor_color;\"><a href=\"$page$params\" title=\"".$title."\" style=\"color:$descriptor_color; font-size:".$size."em\">".$desc[$i]["descriptor"]."</a></font></li> ";
				} else {
					$ret.="<li style=\"display: inline;\"><font face=\"Arial, Helvetica, sans-serif\" style=\"color:$descriptor_color; font-size:".$size."em\">".$desc[$i]["descriptor"]."</font></li> ";
				}
			}
			$store.=",".$desc[$i]["descriptor"]."/".$desc[$i]["id"]."/".$desc[$i]["times"]."/".$size;
		}
		$query="DELETE FROM cloud WHERE channel_id=$c";
		$result=mysql_query($query,$dbh);
		$now=time();
		$query="INSERT INTO cloud (channel_id,cloud_type,cloud,date) VALUES ($c,0,'$store','".$now."')";
		$result=mysql_query($query,$dbh);
		return "<li style=\"display: inline\"><font face=\"Arial, Helvetica, sans-serif\" style=\"font-size:".$maxSize."em; color:".$background_color."; background-color:".$descriptor_color.";\">$descriptor_cloud_title</font></li> ".$ret;
	} else {
		$a=explode(",",$ret);
		$ret="";
		$e=explode(",",$selection_list);
		for($i=0;$i<sizeof($a);$i++) {
			$b=explode("/",$a[$i]);
			if ($b[1]==0) {
				$ret.="<li style=\"display: inline;\"><font face=\"Arial, Helvetica, sans-serif\" style=\"color:#$background_color; background-color:$descriptor_color;\"><i><strong>".$b[0]."</strong></i></font></li> ";
			} else {
				$color=$descriptor_color;
				$backcolor="#".$background_color;
				for($j=1;$j<sizeof($e);$j++) {
					if ($b[1]==-$e[$j]) {
						$backcolor=$descriptor_color;
						$color="#".$background_color;
						break;
					}
				}
				if ($b[2]==1) {
					$title=$descriptor_toolbox_1_time;
				} else {
					$title=$b[2].$descriptor_toolbox_n_times;
				}
				if ($c==-3) {
					$params="?d=".$b[1];
				} else {
					$params="?c=$c&d=".$b[1];
				}
				if ($deactivate==false) {
					$ret.="<li style=\"display: inline;\"><font face=\"Arial, Helvetica, sans-serif\"><a href=\"$page$params\" title=\"".$title."\" style=\"color:".$color."; background-color:".$backcolor."; font-size:".$b[3]."em\">".$b[0]."</a></font></li> ";
				} else {
					$found=false;
					for($j=0;$j<sizeof($x);$j++) {
						if($b[1]==$x[$j]) {
							$found=true;
							break;
						}
					}
					if ($found) {
						$ret.="<li style=\"display: inline;\"><font face=\"Arial, Helvetica, sans-serif\"><a href=\"$page$params\" title=\"".$title."\" style=\"color:".$color."; background-color:".$backcolor."; font-size:".$b[3]."em\">".$b[0]."</a></font></li> ";
					} else {
						$ret.="<li style=\"display: inline;\"><font face=\"Arial, Helvetica, sans-serif\" style=\"color:".$color."; background-color:".$backcolor."; font-size:".$b[3]."em\">".$b[0]."</font></li> ";
					}
				}
			}
		}
		return "<li style=\"display: inline\"><font face=\"Arial, Helvetica, sans-serif\" style=\"font-size:".$maxSize."em; color:".$background_color."; background-color:".$descriptor_color.";\">$descriptor_cloud_title</font></li> ".$ret;
	}
}
//end descriptor functions

function GetCorrelated($qWhere,$crono,$c,$dbh) {
	$ret[0]="";
	$ret[1]="";
	if ($crono==1) {
		$query[0]="SELECT DISTINCT tag_id FROM tag_x_message WHERE message_id ".$qWhere;
		$query[1]="SELECT DISTINCT descriptor_id FROM descriptor_x_attachment,attachment WHERE attachment.message_id ".$qWhere." AND descriptor_x_attachment.attachment_id = attachment.attachment_id";
	} else {
		$query[0]="SELECT DISTINCT tag_id FROM tag_x_message,message WHERE tag_x_message.message_id ".$qWhere." AND message.message_id = tag_x_message.message_id AND channel_id = $c";
		$query[1]="SELECT DISTINCT descriptor_id FROM descriptor_x_attachment,attachment WHERE attachment.message_id ".$qWhere." AND descriptor_x_attachment.attachment_id = attachment.attachment_id";
	}
	//for ($i=0;$i<2;$i++) {
		$result=mysql_query($query[0],$dbh);
		$x="";
		while($row=mysql_fetch_array($result,MYSQL_NUM)) {
			if ($x=="") {
				$x=$row[0];
			} else {
				$x.=",".$row[0];
			}
		}
		if ($x!="") {
			$ret[0]=$x;
		}
	//}
	return $ret[0];
}

function GetTagsInGroup($id,$dbh) {
	$ret=array();
	$query="SELECT tag_id FROM tag WHERE tag_group_id=$id";
	$result=mysql_query($query,$dbh);
	$i=0;
	while($row=mysql_fetch_array($result,MYSQL_NUM)) {
		$ret[$i]=$row[0];
		$i++;
	}
	return $ret;
}

function GetQTags($dbh,$c,$q) {
	$ret=array();
	$c=GetGroupedChannels($dbh,$c);
	$tag_list = explode(",",$q);
	$like_tag="";
	$like_group="";
	for($i=0;$i<sizeof($tag_list);$i++) {
		if ($like_tag=="") {
			$like_tag="(tag_name LIKE '".$tag_list[$i]."%'";
			$like_group="(tag_group_name LIKE '".$tag_list[$i]."%'";
		} else {
			$like_tag=$like_tag." OR tag_name LIKE '".$tag_list[$i]."%'";
			$like_group=$like_group." OR tag_group_name LIKE '".$tag_list[$i]."%'";
		}
	}
	if ($like_tag!="") {
		$like_tag=$like_tag.")";
		$like_group=$like_group.")";
	} else {
		$like_tag=" tag_name LIKE ''";
		$like_group=" tag_group_name LIKE ''";
	}
	$query="SELECT DISTINCT(tag.tag_id) FROM tag,tag_group,tag_x_message,message WHERE $like_group AND tag.tag_group_id=tag_group.tag_group_id AND tag_x_message.tag_id=tag.tag_id AND message.message_id=tag_x_message.message_id AND message.channel_id $c";
	$result=mysql_query($query,$dbh);
	$i=0;
	while($row=mysql_fetch_array($result,MYSQL_NUM)) {
		$ret[$i]=$row[0];
		$i++;
	}
	$query="SELECT DISTINCT(tag.tag_id) FROM tag,tag_x_message,message WHERE $like_tag AND tag_x_message.tag_id=tag.tag_id AND message.message_id=tag_x_message.message_id AND message.channel_id $c";
	$result=mysql_query($query,$dbh);
	while($row=mysql_fetch_array($result,MYSQL_NUM)) {
		if(!in_array($row[0],$ret)) {
			$ret[$i]=$row[0];
			$i++;
		}
	}
	$query="SELECT DISTINCT(tag.tag_id) FROM tag,tag_group,tag_x_channel WHERE $like_group AND tag.tag_group_id=tag_group.tag_group_id AND tag_x_channel.tag_id=tag.tag_id AND tag_x_channel.channel_id $c";
	$result=mysql_query($query,$dbh);
	$i=0;
	while($row=mysql_fetch_array($result,MYSQL_NUM)) {
		if(!in_array($row[0],$ret)) {
			$ret[$i]=$row[0];
			$i++;
		}
	}
	$query="SELECT DISTINCT(tag.tag_id) FROM tag,tag_x_channel WHERE $like_tag AND tag_x_channel.tag_id=tag.tag_id AND tag_x_channel.channel_id $c";
	$result=mysql_query($query,$dbh);
	while($row=mysql_fetch_array($result,MYSQL_NUM)) {
		if(!in_array($row[0],$ret)) {
			$ret[$i]=$row[0];
			$i++;
		}
	}
	$list=",".implode(",",$ret);
	return $list;
}

function GetTagsFromExif($img) {
	$ret="";
	$exif = exif_read_data($img, 0, true);
	if(isset($exif['IFD0']['ImageDescription'])) {
		$ret=$exif['IFD0']['ImageDescription'];
	}
	return $ret;
}
?>