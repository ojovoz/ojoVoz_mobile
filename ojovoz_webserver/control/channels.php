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
</head>
<body bgcolor="#FFFFFF" onLoad="Start()">
<font face="Courier New, Courier, mono" size="2"><? echo($global_channel_name); ?> --- Channels</font> 
  <p><font face="Courier New, Courier, mono" size="2"><b>Existing Channels: </b></font></p>
  <table border="0" width="80%" cellspacing="5" cellpadding="0">
<?
    $query = "SELECT channel_name,channel_id FROM channel WHERE 1 ORDER BY channel_name";
    $result = mysql_query($query, $dbh);
    while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
?>
	<tr>
	<td width="1%"><a href="edit_channel.php?id=<? echo($row[1]); ?>"><font face="Courier New, Courier, mono" size="2">edit</font></a></td>
	<td width="1%"><a href="delete_channel.php?id=<? echo($row[1]); ?>"><font face="Courier New, Courier, mono" size="2">delete</font></a></td>
	<td width="76%"><font face="Courier New, Courier, mono" size="2"><b><? echo($row[0]); ?></b></font></td>
	</tr>
<?
    }
?>
</font>
</table>
<form method="post" action="" name="form1">
  <p><font face="Courier New, Courier, mono" size="2"><b>Add Channel:</b></font></p>
  
  <table border="0" width="60%" cellspacing="0">
    <tr valign="middle"> 
      <td width="18%"><font face="Courier New, Courier, mono" size="2">Name </font></td>
      <td width="82%" valign="middle"> <input type="text" name="channel_name" size="30"> 
      </td>
    </tr>
    <tr valign="middle"> 
      <td><font size="2" face="Courier New, Courier, mono">Phone ID</font></td>
      <td valign="middle"><input name="phone_id" type="text" id="phone_id" size="30"></td>
    </tr>
    <tr valign="middle"> 
      <td width="18%"><font face="Courier New, Courier, mono" size="2">Description 
        </font></td>
      <td width="82%" valign="middle"> <textarea name="description" cols="30" rows="5" wrap="VIRTUAL"></textarea> 
      </td>
    </tr>
    <tr> 
      <td width="18%"><font face="Courier New, Courier, mono" size="2">Mail</font></td>
      <td width="82%"> <input type="text" name="channel_mail" size="30"> </td>
    </tr>
    <tr> 
      <td width="18%"><font face="Courier New, Courier, mono" size="2">Pass</font></td>
      <td width="82%"> <input type="text" name="channel_pass" size="30"> </td>
    </tr>
    <tr> 
      <td><font size="2" face="Courier New, Courier, mono">Pass (edit)</font></td>
      <td><input name="channel_pass_edit" type="text" id="channel_pass_edit" size="30"></td>
    </tr>
    <tr> 
      <td width="18%"><font face="Courier New, Courier, mono" size="2">Folder</font> 
      </td>
      <td width="82%"> <input type="text" name="channel_folder" size="30"> </td>
    </tr>
    <tr>
      <td><font size="2" face="Courier New, Courier, mono">Tag list</font></td>
      <td><input name="tag_list" type="text" id="tag_list" size="30">
        <font size="2" face="Courier New, Courier, mono">separated by &quot;;&quot;</font></td>
    </tr>
    <tr> 
      <td><font size="2" face="Courier New, Courier, mono">Per page </font></td>
      <td><input name="messages_per_page" type="text" id="messages_per_page" value="10" size="4"></td>
    </tr>
    <tr> 
      <td width="18%"><font face="Courier New, Courier, mono" size="2">Type</font> 
      </td>
      <td width="82%"> <select name="open_closed">
          <option value="0">closed</option>
          <option value="1">open</option>
        </select> </td>
    </tr>
    <tr> 
      <td width="18%"><font face="Courier New, Courier, mono" size="2">Show</font> 
      </td>
      <td width="82%"> <font face="Courier New, Courier, mono" size="2"> 
        <input type="checkbox" name="show_date" value="1" checked>
        date 
        <input type="checkbox" name="show_time" value="1" checked>
        time 
        <input type="checkbox" name="show_sender" value="1" checked>
        sender</font> </td>
    </tr>
    <tr> 
      <td><font size="2" face="Courier New, Courier, mono">Show map</font> </td>
      <td><select name="show_map" id="show_map">
          <option value="1">yes</option>
          <option value="0" selected>no</option>
        </select></td>
    </tr>
    <tr> 
      <td><font size="2" face="Courier New, Courier, mono">Show legend</font> 
      </td>
      <td><select name="show_legend" id="show_legend">
          <option value="1">yes</option>
          <option value="0" selected>no</option>
        </select></td>
    </tr>
    <tr> 
      <td width="18%"><font face="Courier New, Courier, mono" size="2">Colors:</font> 
      </td>
      <td><img src="colors.gif" usemap="#ColorMap" border="0"> </td>
    </tr>
    <tr> 
      <td width="18%"><font face="Courier New, Courier, mono" size="2">Background</font> 
      </td>
      <td width="82%"> <input type="text" name="back_color" size="21" value="FFFFFF" onFocus="Select(0)"> 
      </td>
    </tr>
    <tr> 
      <td width="18%"><font face="Courier New, Courier, mono" size="2">Description</font> 
      </td>
      <td width="82%"> <input type="text" name="desc_color" size="21" value="000000" onFocus="Select(1)"> 
      </td>
    </tr>
    <tr> 
      <td width="18%"><font face="Courier New, Courier, mono" size="2">Text</font> 
      </td>
      <td width="82%"> <input type="text" name="text_color" size="21" value="000000" onFocus="Select(2)"> 
      </td>
    </tr>
    <tr> 
      <td width="18%"><font face="Courier New, Courier, mono" size="2">Data</font> 
      </td>
      <td width="82%"> <input type="text" name="data_color" size="21" value="00FF00" onFocus="Select(3)"> 
      </td>
    </tr>
    <tr> 
      <td><font size="2" face="Courier New, Courier, mono">Tags</font></td>
      <td><input name="tag_color" type="text" value="0000FF" size="21" onFocus="Select(4)"></td>
    </tr>
    <tr> 
      <td><font size="2" face="Courier New, Courier, mono">Descriptors</font></td>
      <td><input name="descriptor_color" type="text" value="FF0000" size="21" onFocus="Select(5)"></td>
    </tr>
    <tr> 
      <td><font size="2" face="Courier New, Courier, mono">Map legend</font> </td>
      <td><input name="legend_color" type="text" onFocus="Select(6)" value="000000" size="21"></td>
    </tr>
    <tr> 
      <td width="18%"><font face="Courier New, Courier, mono" size="2">Ascending</font> 
      </td>
      <td width="82%"> <input type="checkbox" name="ascending" value="1"> </td>
    </tr>
    <tr> 
      <td width="18%"><font face="Courier New, Courier, mono" size="2">Thumbnails</font> 
      </td>
      <td width="82%"> <input type="checkbox" name="thumbnails" value="1"> </td>
    </tr>
    <tr> 
      <td width="18%"><font face="Courier New, Courier, mono" size="2">Visible</font> 
      </td>
      <td width="82%"> <input type="checkbox" name="visible" value="1" checked> 
      </td>
    </tr>
    <tr> 
      <td><font size="2" face="Courier New, Courier, mono">Active</font></td>
      <td><input name="active" type="checkbox" id="active" value="1" checked></td>
    </tr>
    <tr> 
      <td width="18%"><font face="Courier New, Courier, mono" size="2">Crono</font> 
      </td>
      <td width="82%"> <input type="checkbox" name="crono" value="1"> </td>
    </tr>
    <tr> 
      <td><font size="2" face="Courier New, Courier, mono">Study</font></td>
      <td><input type="checkbox" name="study" value="1"></td>
    </tr>
    <tr> 
      <td><font size="2" face="Courier New, Courier, mono">Show tags</font> </td>
      <td><input name="show_tags" type="checkbox" value="1" checked></td>
    </tr>
    <tr> 
      <td><font size="2" face="Courier New, Courier, mono">Show descriptors</font> 
      </td>
      <td><input name="show_descriptors" type="checkbox" value="1"></td>
    </tr>
    <tr> 
      <td><font size="2" face="Courier New, Courier, mono">Allow search</font></td>
      <td><input name="allow_search" type="checkbox" id="allow_search" value="1"></td>
    </tr>
    <tr> 
      <td><font size="2" face="Courier New, Courier, mono">RSS</font></td>
      <td><input name="has_rss" type="checkbox" id="has_rss" value="1"></td>
    </tr>
    <tr> 
      <td><font size="2" face="Courier New, Courier, mono">Publish by default</font></td>
      <td><input name="publish_default" type="checkbox" id="publish_default" value="1" checked></td>
    </tr>
    <tr> 
      <td><font size="2" face="Courier New, Courier, mono">Color combination</font></td>
      <td><select name="color_combination" id="color_combination">
          <option value="0" selected><? echo($ov_color_combination_titles[2][0]); ?></option>
          <option value="1"><? echo($ov_color_combination_titles[2][1]); ?></option>
          <option value="2"><? echo($ov_color_combination_titles[2][2]); ?></option>
          <option value="3"><? echo($ov_color_combination_titles[2][3]); ?></option>
        </select></td>
    </tr>
    <tr> 
      <td><font size="2" face="Courier New, Courier, mono">Tag mode</font> </td>
      <td><select name="tag_mode" id="tag_mode">
          <option value="0" selected>frequent</option>
          <option value="1">popular</option>
        </select></td>
    </tr>
  </table>
  <p> 
    <input type="submit" name="submit" value="Add">
  </p>
</form>
<p><font face="Courier New, Courier, mono" size="2"><a href="index.php"><--- 
  Control Panel</a> </font> </p>

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
	if (!isset($ascending)) {$ascending = "0";}
	if (!isset($thumbnails)) {$thumbnails = "0";}
	if (!isset($visible)) {$visible = "0";}
	if (!isset($active)) {$active = "0";}
	if (!isset($crono)) {$crono = "0";}
	if (!isset($study)) {$study = "0";}
	if (!isset($show_tags)) {$show_tags = "0";}
	if (!isset($show_descriptors)) {$show_descriptors = "0";}
	if (!isset($messages_per_page)) {$messages_per_page=10;}
	if (!isset($allow_search)) {$allow_search = "0";}
	if (!isset($has_rss)) {$has_rss = "0";}
	if (!isset($publish_default)) {$publish_default = "0";}
	$description=urlencode($description);
	$query="INSERT INTO channel (open_closed,channel_name,channel_folder,channel_mail,channel_pass,show_time,show_date,show_sender,background_color,text_color,channel_description,channel_description_color,data_color,tag_color,descriptor_color,is_crono,is_visible,is_ascending,messages_per_page,is_active,has_thumbnails,is_study,show_tags,show_descriptors,tag_mode,show_map,show_legend,legend_color,allow_search,has_rss,channel_pass_edit,publish_default,phone_id,tag_list) VALUES ($open_closed,'$channel_name','$channel_folder','$channel_mail','$channel_pass',$show_time,$show_date,$show_sender,'$back_color','$text_color','$description','$desc_color','$data_color','$tag_color','$descriptor_color',$crono,$visible,$ascending,$messages_per_page,$active,$thumbnails,$study,$show_tags,$show_descriptors,$tag_mode,$show_map,$show_legend,'$legend_color',$allow_search,$has_rss,'$channel_pass_edit',$publish_default,'$phone_id','$tag_list')";
	$result = mysql_query($query, $dbh);
	if ($channel_folder!="") {
	mkdir("../channels/".$channel_folder,0755);
	chmod("../channels/".$channel_folder,0777);
	mkdir("../channels/".$channel_folder."/image",0755);
	chmod("../channels/".$channel_folder."/image",0777);
	mkdir("../channels/".$channel_folder."/sound",0755);
	chmod("../channels/".$channel_folder."/sound",0777);
	mkdir("../channels/".$channel_folder."/video",0755);
	chmod("../channels/".$channel_folder."/video",0777);
	if ($auto_create_email) {
		shell_exec('sudo -S /usr/local/psa/bin/mail --create '.$channel_mail.' -mailbox true -cp_access true -passwd '.$channel_pass);
	}
}
header("Location: channels.php");
}
?>