<?

include_once("misc_functions.php");

//functions for reading XML feeds XML
//2006 Eugenio Tisselli

function GetFeed($feed) {
//read xml feed
	$pgdata = "";
	$fd = @fopen($feed,"r"); 
	if ($fd) {
		while(!feof($fd)) {
			stream_set_timeout($fd, 20);
			$pgdata .= fread($fd, 5000) or die("");
		}
		fclose($fd);
	}
	return $pgdata;
}

function My_Strip_Tags($c) {
	$ret = "";
	$open = false;
	$c = html_entity_decode($c);
	for($i=0;$i<strlen($c);$i++) {
		if (substr($c,$i,1) == "<" || substr($c,$i,4) == "&lt;") {
			$open = true;
			$ret .= " ";
		}
		else if (substr($c,$i,1) == ">" || substr($c,$i,4) == "&gt;") {
			$open = false;
		}
		else {
			if ($open == false) {
				$ret .= substr($c,$i,1);
			}
		}
	}
	return trim($ret);
}

function GetEncoding($xml) {
	if (strpos($xml,"ISO-8859-1") > 0 || strpos($xml,"iso-8859-1") > 0) {
		$enc = "iso";
	} else {
		$enc = "utf";
	}
	return $enc;
}

function GetItems($xml) {
//returns a list with the positions in which
//the tag <item> was found
	$list = Multi_strpos("\<item\>",$xml);
	return $list;
}

function ItemAttributes($pos,$endpos,$xml) {
//returns attributes at position
	$strings[0] = "<title>";
	$strings[1] = "<link>";
	$strings[2] = "<description>";
	$strings[3] = "<pubDate>";
	for ($i=0;$i<=3;$i++) {
		$startpos = strpos($xml,$strings[$i],$pos)+strlen($strings[$i]);
		if ($startpos > $pos && $startpos < $endpos) {
			$end_pos = strpos($xml,"<",$startpos);
			$long_tag = $end_pos - $startpos;
			$c = substr($xml,$startpos,$long_tag);
			$c = My_Strip_Tags($c);
			if (GetEncoding($xml) == "iso") {
                $c = html_entity_decode($c);
			} else {
				$c = utf8_decode($c);
                $c = html_entity_decode($c);
			}
		} else {
			$c = "";
		}
		switch($i) {
			case 0:
				$title = $c;
				break;
			case 1:
				$link = $c;
				break;
			case 2:
				$description = $c;
				break;
			case 3:
				$date = $c;
				break;
		}
	}
	$item = array("title" => $title, "link" => $link, "description" => $description, "date" => $date);
	return $item;
}

function CreateItemList($xml) {
//returns an array in which every element is an xml item
	$array = array();
	$items = GetItems($xml);
	for($i=0;$i<sizeof($items);$i++) {
		if ($i == (sizeof($items) - 1)) {
			$endpos = strlen($xml);
		} else {
			$endpos = $items[$i+1];
		}
		$array[$i] = ItemAttributes($items[$i],$endpos,$xml);
	}
	return $array;
}

function Xml2Array($url) {
//gets a feed url and returns an array with xml items
	$xml = GetFeed($url);
	$array = CreateItemList($xml);
	return $array;
}

function WordInText($haystack,$needle) {
	$ret=false;
	if (strpos($needle," ") > 0 || strpos($needle,".") > 0) {
		if (strpos($haystack,$needle) > 0) {
			$ret=true;
		}
	} else {
		$haystack = trim(strtolower($haystack));
		$needle = trim(strtolower($needle));
		$punctuation = '/[^a-z0-9αινσϊρ-]/';
		$haystack_parts = preg_split($punctuation, $haystack, -1, PREG_SPLIT_NO_EMPTY);
		for($i=0;$i<count($haystack_parts);$i++) {
			$hay=trim($haystack_parts[$i]);
			if ($needle==$hay) {
				$ret=true;
				break;
			}
		}
	}
	return $ret;
}
?>