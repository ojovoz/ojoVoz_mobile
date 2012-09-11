<?
session_start();
$_SESSION['kiosk']=false;
header("Cache-Control: no-store, no-cache, must-revalidate");
include_once "includes/channel_vars.php";
?>
<html>
<head>
<title><? echo($channel_name); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body bgcolor="#FFFFFF">
	<script type="text/javascript">
        //<![CDATA[
		parent.location="<? echo($init_page); ?>"
		//]]>
    </script>
</body>
</html>

