<?

//initialize dbConnection
function initDB() {
	$host="localhost";
	$db="xxx";
	$db_user="xxx";
	$db_pass="xxx";
	$dbh=mysql_connect ($host, $db_user, $db_pass);
	mysql_select_db ($db);
	return $dbh;
}

//
?>