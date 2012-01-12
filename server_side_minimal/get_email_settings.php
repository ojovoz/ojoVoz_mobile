<?
//this script returns the following values for a phone id:
//myemail@myserver.net : an email address on your server, to which the messages will be sent
//mypassw0rd : the email password (TODO: encrypt)
//smtp.myserver.net : the address of your smtp (email) server
//port : the smtp port (usually 578)
if(isset($_GET['id'])) {
	if($_GET['id']== 'test') {
		echo('myemail@myserver.net;mypassw0rd;smtp.myserver.net;578');
	}
}
?>
