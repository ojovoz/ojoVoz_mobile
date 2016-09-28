<?

//initialize dbConnection
function initDB() {
	$host="localhost";
	$db="your database";
	$db_user="database user";
	$db_pass="password";
	$dbh=mysql_connect ($host, $db_user, $db_pass);
	mysql_select_db ($db);
	return $dbh;
}

//
?>