<?

//initialize dbConnection
function initDB() {
	$host="localhost";
	$db="ovwebserver";
	$db_user="mysautiyaw";
	$db_pass="j8bA0y11";
	$dbh=mysql_connect ($host, $db_user, $db_pass);
	mysql_select_db ($db);
	return $dbh;
}

//
?>