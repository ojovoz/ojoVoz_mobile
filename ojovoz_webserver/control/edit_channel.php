<?
include_once("./../includes/channel_vars.php");
include_once("./../includes/init_database.php");
$dbh = initDB();
if (!isset($_POST['submit'])) {
?>
<html>
<head>
<title><? echo($global_channel_name); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" src="./../includes/color_picker.js" language="javascript" type="text/javascript"></script>
<script language="JavaScript">
<!--
function EditCronoTags(c) {
	s=document.forms[0].is_crono.checked;
	if (s==true) {
		document.getElementById("crono_tags").innerHTML=' <a href="edit_channel_tags.php?c=<? echo($id); ?>">filter tags</a> <a href="edit_channel_group.php?c=<? echo($id); ?>">group</a>';
	} else {
		document.getElementById("crono_tags").innerHTML='';
	}
}

function ChangeMail() {
	document.forms[0].change_mail.value="yes"
}
//-->
</script>
</head>
<body bgcolor="#FFFFFF" onLoad="Start()">
<font face="Courier New, Courier, mono" size="2">
<p>
<?
$id = $_GET['id'];
$query="SELECT channel_name,channel_folder,channel_mail,channel_pass,open_closed,show_date,show_time,show_sender,background_color,text_color,channel_description,channel_description_color,data_color, tag_color, descriptor_color, is_visible, is_ascending, messages_per_page, is_active, has_thumbnails, is_crono, is_study, show_tags, show_descriptors, tag_mode, show_map, show_legend, legend_color, allow_search, tag_minimum_date, has_rss, channel_pass_edit, publish_default, color_combination, phone_id, tag_list FROM channel WHERE channel_id=".$id;
$result = mysql_query($query, $dbh);
$row = mysql_fetch_array($result, MYSQL_NUM);
$channel_name = $row[0];
$channel_folder = $row[1];
$channel_mail = $row[2];
$channel_pass = $row[3];
$open_closed = $row[4];
$show_date = $row[5];
$show_time = $row[6];
$show_sender = $row[7];
$background_color = $row[8];
$text_color = $row[9];
$channel_description = urldecode($row[10]);
$channel_description_color = $row[11];
$data_color = $row[12];
$tag_color = $row[13];
$descriptor_color = $row[14];
$is_visible = $row[15];
$is_ascending = $row[16];
$messages_per_page = $row[17];
$is_active = $row[18];
$has_thumbnails = $row[19];
$is_crono = $row[20];
$is_study = $row[21];
$show_tags=$row[22];
$show_descriptors=$row[23];
$tag_mode=$row[24];
$show_map=$row[25];
$show_legend=$row[26];
$legend_color=$row[27];
$allow_search=$row[28];
$tag_minimum_date=$row[29];
$has_rss=$row[30];
$channel_pass_edit=$row[31];
$publish_default=$row[32];
$color_combination=$row[33];
$phone_id=$row[34];
$tag_list=$row[35];
mysql_free_result($result);

?>
<form method="post" action="" name="form1">
<input type="hidden" name="id" value="<? echo($id); ?>">
  <p><font face="Courier New, Courier, mono" size="2"><b>Edit Channel:</b></font></p>
  
  <table border="0" width="60%" cellspacing="0">
    <tr valign="middle"> 
      <td width="20%"><font face="Courier New, Courier, mono" size="2">Name</font></td>
      <td colspan="2"> <input type="text" name="channel_name" value="<? echo($channel_name); ?>" size="30"> 
      </td>
    </tr>
    <tr valign="middle"> 
      <td><font size="2" face="Courier New, Courier, mono">Phone ID</font></td>
      <td colspan="2"><input name="phone_id" type="text" id="phone_id" value="<? echo($phone_id); ?>" size="30"></td>
    </tr>
    <tr valign="middle"> 
      <td width="20%"><font face="Courier New, Courier, mono" size="2">Description 
        </font></td>
      <td colspan="2"> <textarea name="channel_description" cols="30" rows="5" wrap="VIRTUAL"><? echo($channel_description); ?></textarea> 
      </td>
    </tr>
    <tr> 
      <td width="20%"><font face="Courier New, Courier, mono" size="2">Mail</font></td>
      <td colspan="2"> <input type="text" name="channel_mail" value="<? echo($channel_mail); ?>" size="30" onChange="ChangeMail()"> 
        <input name="change_mail" type="hidden" id="change_mail" value="no"> </td>
    </tr>
    <tr> 
      <td width="20%"><font face="Courier New, Courier, mono" size="2">Pass</font></td>
      <td colspan="2"> <input type="text" name="channel_pass" value="<? echo($channel_pass); ?>" size="30"> 
      </td>
    </tr>
    <tr> 
      <td><font size="2" face="Courier New, Courier, mono">Pass (edit)</font></td>
      <td colspan="2"><input type="text" name="channel_pass_edit" value="<? echo($channel_pass_edit); ?>" size="30"></td>
    </tr>
    <tr> 
      <td width="20%"><font face="Courier New, Courier, mono" size="2">Folder</font></td>
      <td colspan="2"> <font face="Courier New, Courier, mono" size="2"><? echo($channel_folder); ?></font> 
      </td>
    </tr>
    <tr>
      <td><font size="2">Tag list</font></td>
      <td colspan="2"><input name="tag_list" type="text" id="tag_list" value="<? echo($tag_list); ?>" size="30">
        <font size="2"> separated by &quot;;&quot;</font></td>
    </tr>
    <tr> 
      <td width="20%"><font face="Courier New, Courier, mono" size="2">Type</font> 
      </td>
      <td colspan="2"> <select name="open_closed">
          <?
if ($open_closed == 1) {
?>
          <option value="0">closed</option>
          <option value="1" selected="selected">open</option>
          <?
} else {
?>
          <option value="0" selected="selected">closed</option>
          <option value="1">open</option>
          <?
}
?>
        </select> </td>
    </tr>
    <tr> 
      <td width="20%"><font face="Courier New, Courier, mono" size="2">Show</font> 
      </td>
      <td colspan="2"><font face="Courier New, Courier, mono" size="2"> 
        <?
if ($show_date)
{
	echo("<input type=\"checkbox\" name=\"show_date\" value=\"1\" checked>date ");
} else {
	echo("<input type=\"checkbox\" name=\"show_date\" value=\"1\">date ");
}
if ($show_time)
{
	echo("<input type=\"checkbox\" name=\"show_time\" value=\"1\" checked>time ");
} else {
	echo("<input type=\"checkbox\" name=\"show_time\" value=\"1\">time ");
}
if ($show_sender)
{
	echo("<input type=\"checkbox\" name=\"show_sender\" value=\"1\" checked>sender ");
} else {
	echo("<input type=\"checkbox\" name=\"show_sender\" value=\"1\">sender ");
}
?>
        </font></td>
    </tr>
    <tr> 
      <td><font size="2">Show map</font> </td>
      <td colspan="2"><select name="show_map" id="show_map">
          <? if ($show_map==0) { ?>
          <option value="1">yes</option>
          <option value="0" selected>no</option>
          <? } else { ?>
          <option value="1" selected>yes</option>
          <option value="0">no</option>
          <? }  ?>
        </select></td>
    </tr>
    <tr> 
      <td><font size="2">Show legend</font> </td>
      <td colspan="2"><select name="show_legend" id="show_legend">
          <? if ($show_legend==0) { ?>
          <option value="1">yes</option>
          <option value="0" selected>no</option>
          <? } else { ?>
          <option value="1" selected>yes</option>
          <option value="0">no</option>
          <? }  ?>
        </select></td>
    </tr>
    <tr> 
      <td><font face="Courier New, Courier, mono" size="2">Per page</font></td>
      <td colspan="2"><input type="text" name="messages_per_page" value="<? echo($messages_per_page); ?>" size="21"></td>
    </tr>
    <tr> 
      <td width="20%"><font face="Courier New, Courier, mono" size="2">Colors:</font></td>
      <td colspan="2"><img src="colors.gif" usemap="#ColorMap" border="0"></td>
    </tr>
    <tr> 
      <td width="20%"><font face="Courier New, Courier, mono" size="2">Background</font></td>
      <td colspan="2"> <input type="text" name="back_color" value="<? echo($background_color); ?>" size="21" onFocus="Select(0)"> 
      </td>
    </tr>
    <tr> 
      <td width="20%"><font face="Courier New, Courier, mono" size="2">Description</font></td>
      <td colspan="2"> <input type="text" name="desc_color" value="<? echo($channel_description_color); ?>" size="21" onFocus="Select(1)"> 
      </td>
    </tr>
    <tr> 
      <td width="20%"><font face="Courier New, Courier, mono" size="2">Text</font></td>
      <td colspan="2"> <input type="text" name="text_color" value="<? echo($text_color); ?>" size="21" onFocus="Select(2)"> 
      </td>
    </tr>
    <tr> 
      <td width="20%"><font face="Courier New, Courier, mono" size="2">Data</font></td>
      <td colspan="2"> <input type="text" name="data_color" value="<? echo($data_color); ?>" size="21" onFocus="Select(3)"> 
      </td>
    </tr>
    <tr> 
      <td width="20%"><font size="2">Tags</font></td>
      <td colspan="2"><input name="tag_color" value="<? echo($tag_color); ?>" type="text" size="21" onFocus="Select(4)"> 
      </td>
    </tr>
    <tr> 
      <td><font size="2">Descriptors</font></td>
      <td colspan="2"><input name="descriptor_color" value="<? echo($descriptor_color); ?>" type="text" size="21" onFocus="Select(5)"></td>
    </tr>
    <tr> 
      <td><font size="2">Legend</font></td>
      <td colspan="2"><input name="legend_color" type="text" onFocus="Select(6)" value="<? echo($legend_color); ?>" size="21"></td>
    </tr>
    <tr> 
      <td width="20%"><font face="Courier New, Courier, mono" size="2">Ascending</font> 
      </td>
      <td colspan="2"> 
        <?
        if ($is_ascending)
        {
            echo("<input type=\"checkbox\" name=\"is_ascending\" value=\"1\" checked>");
        } else {
            echo("<input type=\"checkbox\" name=\"is_ascending\" value=\"1\">");
        }
?>
      </td>
    </tr>
    <tr> 
      <td width="20%"><font face="Courier New, Courier, mono" size="2">Thumbnails</font> 
      </td>
      <td colspan="2"> 
        <?
        if ($has_thumbnails)
        {
            echo("<input type=\"checkbox\" name=\"has_thumbnails\" value=\"1\" checked>");
        } else {
            echo("<input type=\"checkbox\" name=\"has_thumbnails\" value=\"1\">");
        }
?>
      </td>
    </tr>
    <tr> 
      <td width="20%"><font face="Courier New, Courier, mono" size="2">Visible</font> 
      </td>
      <td colspan="2"> 
        <?
        if ($is_visible)
        {
            echo("<input type=\"checkbox\" name=\"is_visible\" value=\"1\" checked>");
        } else {
            echo("<input type=\"checkbox\" name=\"is_visible\" value=\"1\">");
        }
?>
      </td>
    </tr>
    <tr> 
      <td width="20%"><font face="Courier New, Courier, mono" size="2">Crono</font> 
      </td>
      <td width="2%"> 
        <?
        if ($is_crono)
        {
            echo("<input type=\"checkbox\" name=\"is_crono\" value=\"1\" checked onClick=\"EditCronoTags($id)\">");
        } else {
            echo("<input type=\"checkbox\" name=\"is_crono\" value=\"1\" onClick=\"EditCronoTags($id)\">");
        }
?>
      </td>
      <td width="84%"><font face="Courier New, Courier, mono" size="2"> 
        <div id="crono_tags"> 
          <? if ($is_crono) { ?>
          <a href="edit_channel_tags.php?c=<? echo($id); ?>">filter tags</a> <a href="edit_channel_group.php?c=<? echo($id); ?>">group</a> 
          <? } ?>
        </div>
        </font> </td>
    </tr>
    <tr> 
      <td width="20%"><font face="Courier New, Courier, mono" size="2">Active</font> 
      </td>
      <td colspan="2"> 
        <?
        if ($is_active)
        {
            echo("<input type=\"checkbox\" name=\"is_active\" value=\"1\" checked>");
        } else {
            echo("<input type=\"checkbox\" name=\"is_active\" value=\"1\">");
        }
?>
      </td>
    </tr>
    <tr> 
      <td width="20%"><font face="Courier New, Courier, mono" size="2">Study</font> 
      </td>
      <td colspan="2"> 
        <?
        if ($is_study)
        {
            echo("<input type=\"checkbox\" name=\"is_study\" value=\"1\" checked>");
        } else {
            echo("<input type=\"checkbox\" name=\"is_study\" value=\"1\">");
        }
?>
      </td>
    </tr>
    <tr> 
      <td width="20%"><font face="Courier New, Courier, mono" size="2">Show tags</font> 
      </td>
      <td colspan="2"> 
        <?
        if ($show_tags)
        {
            echo("<input type=\"checkbox\" name=\"show_tags\" value=\"1\" checked>");
        } else {
            echo("<input type=\"checkbox\" name=\"show_tags\" value=\"1\">");
        }
?>
      </td>
    </tr>
    <tr> 
      <td><font size="2">Show descriptors</font></td>
      <td colspan="2"> 
        <?
        if ($show_descriptors)
        {
            echo("<input type=\"checkbox\" name=\"show_descriptors\" value=\"1\" checked>");
        } else {
            echo("<input type=\"checkbox\" name=\"show_descriptors\" value=\"1\">");
        }
?>
      </td>
    </tr>
    <tr> 
      <td><font size="2">Allow search</font> </td>
      <td colspan="2"> 
        <?
        if ($allow_search)
        {
            echo("<input type=\"checkbox\" name=\"allow_search\" value=\"1\" checked>");
        } else {
            echo("<input type=\"checkbox\" name=\"allow_search\" value=\"1\">");
        }
?>
      </td>
    </tr>
    <tr> 
      <td><font size="2">RSS</font></td>
      <td colspan="2"> 
        <?
        if ($has_rss)
        {
            echo("<input type=\"checkbox\" name=\"has_rss\" value=\"1\" checked>");
        } else {
            echo("<input type=\"checkbox\" name=\"has_rss\" value=\"1\">");
        }
?>
      </td>
    </tr>
    <tr> 
      <td><font size="2">Publish by default</font></td>
      <td colspan="2"> 
        <?
        if ($publish_default)
        {
            echo("<input type=\"checkbox\" name=\"publish_default\" value=\"1\" checked>");
        } else {
            echo("<input type=\"checkbox\" name=\"publish_default\" value=\"1\">");
        }
?>
      </td>
    </tr>
    <tr> 
      <td><font size="2">Color combination</font></td>
      <td colspan="2"><select name="color_combination" id="color_combination">
          <option value="0" <? if ($color_combination == 0) { echo("selected"); } ?>><? echo($ov_color_combination_titles[2][0]); ?></option>
          <option value="1" <? if ($color_combination == 1) { echo("selected"); } ?>><? echo($ov_color_combination_titles[2][1]); ?></option>
          <option value="2" <? if ($color_combination == 2) { echo("selected"); } ?>><? echo($ov_color_combination_titles[2][2]); ?></option>
          <option value="3" <? if ($color_combination == 3) { echo("selected"); } ?>><? echo($ov_color_combination_titles[2][3]); ?></option>
        </select></td>
    </tr>
    <tr> 
      <td><font size="2">Tag mode</font> </td>
      <td colspan="2"><select name="tag_mode">
          <?
if ($tag_mode == 1) {
?>
          <option value="0">frequent</option>
          <option value="1" selected="selected">popular</option>
          <?
} else {
?>
          <option value="0" selected="selected">frequent</option>
          <option value="1">popular</option>
          <?
}
?>
        </select></td>
    </tr>
    <tr> 
      <td><font size="2">Show tags<font face="Courier New, Courier, mono"> after 
        </font></font></td>
      <td colspan="2"><input name="tag_minimum_date" type="text" id="tag_minimum_date" value="<? echo($tag_minimum_date); ?>" size="21"></td>
    </tr>
  </table>
  <p> 
    <input type="submit" name="submit" value="Edit">
  </p>
</form>
</p>
<a href="channels.php"><--- 
  Channels</a>

</font>

<map name="ColorMap">
  <area shape="rect" coords="0,0,20,21" href="Javascript:Color()" onClick="PutColor('000000')">
  <area shape="rect" coords="21,0,40,21" href="Javascript:Color()" onClick="PutColor('333333')">
  <area shape="rect" coords="41,0,60,21" href="Javascript:Color()" onClick="PutColor('808080')">
  <area shape="rect" coords="61,0,80,21" href="Javascript:Color()" onClick="PutColor('C0C0C0')">
  <area shape="rect" coords="81,0,100,21" href="Javascript:Color()" onClick="PutColor('FFFFFF')">
  <area shape="rect" coords="101,0,120,21" href="Javascript:Color()" onClick="PutColor('FFC0CB')">
  <area shape="rect" coords="121,0,140,21" href="Javascript:Color()" onClick="PutColor('FA8072')">
  <area shape="rect" coords="141,0,160,21" href="Javascript:Color()" onClick="PutColor('FF00FF')">
  <area shape="rect" coords="161,0,180,21" href="Javascript:Color()" onClick="PutColor('800080')">
  <area shape="rect" coords="181,0,200,21" href="Javascript:Color()" onClick="PutColor('800000')">
  <area shape="rect" coords="201,0,220,21" href="Javascript:Color()" onClick="PutColor('CC0000')">
  <area shape="rect" coords="221,0,240,21" href="Javascript:Color()" onClick="PutColor('FF0000')">
  <area shape="rect" coords="241,0,260,21" href="Javascript:Color()" onClick="PutColor('FFA500')">
  <area shape="rect" coords="261,0,280,21" href="Javascript:Color()" onClick="PutColor('FFFF00')">
  <area shape="rect" coords="281,0,300,21" href="Javascript:Color()" onClick="PutColor('ADFF2F')">
  <area shape="rect" coords="301,0,320,21" href="Javascript:Color()" onClick="PutColor('00FF00')">
  <area shape="rect" coords="321,0,340,21" href="Javascript:Color()" onClick="PutColor('008000')">
  <area shape="rect" coords="341,0,360,21" href="Javascript:Color()" onClick="PutColor('006400')">
  <area shape="rect" coords="361,0,380,21" href="Javascript:Color()" onClick="PutColor('808000')">
  <area shape="rect" coords="381,0,400,21" href="Javascript:Color()" onClick="PutColor('008080')">
  <area shape="rect" coords="401,0,420,21" href="Javascript:Color()" onClick="PutColor('000080')">
  <area shape="rect" coords="421,0,440,21" href="Javascript:Color()" onClick="PutColor('0000FF')">
  <area shape="rect" coords="441,0,460,21" href="Javascript:Color()" onClick="PutColor('00FFFF')">
</map>

</body>
</html>

<?
} else {
	import_request_variables("gp");
	if (!isset($show_date)) {$show_date = "0";}
	if (!isset($show_time)) {$show_time = "0";}
	if (!isset($show_sender)) {$show_sender = "0";}
	if (!isset($is_visible)) {$is_visible = "0";}
	if (!isset($is_ascending)) {$is_ascending = "0";}
	if (!isset($is_active)) {$is_active = "0";}
	if (!isset($has_thumbnails)) {$has_thumbnails = "0";}
	if (!isset($is_crono)) {$is_crono = "0";}
	if (!isset($is_study)) {$is_study = "0";}
	if (!isset($show_tags)) {$show_tags = "0";}
	if (!isset($show_descriptors)) {$show_descriptors = "0";}
	if (!isset($allow_search)) {$allow_search = "0";}
	if (!isset($has_rss)) {$has_rss = "0";}
	if (!isset($publish_default)) {$publish_default = "0";}
	$channel_description = urlencode($channel_description);
	$query = "UPDATE channel SET channel_mail='$channel_mail', channel_name='$channel_name', channel_pass='$channel_pass', open_closed='$open_closed', show_date=$show_date, show_time=$show_time, show_sender=$show_sender, background_color='$back_color', text_color='$text_color', channel_description='$channel_description', channel_description_color='$desc_color', data_color='$data_color', tag_color='$tag_color', descriptor_color='$descriptor_color', is_visible=$is_visible, is_ascending=$is_ascending, messages_per_page=$messages_per_page, is_active=$is_active, has_thumbnails=$has_thumbnails, is_crono=$is_crono, is_study=$is_study, show_tags=$show_tags, show_descriptors=$show_descriptors, tag_mode=$tag_mode, show_map=$show_map, show_legend=$show_legend, legend_color='$legend_color', allow_search=$allow_search, tag_minimum_date='$tag_minimum_date', has_rss=$has_rss, channel_pass_edit='$channel_pass_edit', publish_default=$publish_default, color_combination=$color_combination, phone_id='$phone_id', tag_list='$tag_list' WHERE channel_id = ".$id;
	$result = mysql_query($query, $dbh);
	if($auto_create_email && $change_mail=="yes") {
		shell_exec('sudo -S /usr/local/psa/bin/mail --create '.$channel_mail.' -mailbox true -cp_access true -passwd '.$channel_pass);
	}
	header("Location: channels.php");
}
?>