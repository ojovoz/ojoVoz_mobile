<?

//initialize dbConnection
function initDB() {
	$host="localhost";
	$db="my_database";
	$db_user="my_user";
	$db_pass="my_pass";
	$dbh=mysql_connect ($host, $db_user, $db_pass);
	mysql_select_db ($db);
	return $dbh;
}

//
?>