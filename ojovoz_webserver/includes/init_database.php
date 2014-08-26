<?

//initialize dbConnection
function initDB() {
	$host="localhost";
	$db="mydatabase";
	$db_user="db_user";
	$db_pass="db_pass";
	$dbh=mysql_connect ($host, $db_user, $db_pass);
	mysql_select_db ($db);
	return $dbh;
}

//
?>
