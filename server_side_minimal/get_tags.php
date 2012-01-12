<?
//this script returns a list of tags for a specific phone ID
//tags are separated by ';'
if(isset($_GET['id'])) {
	if ($_GET['id'] == 'test') {
		echo('tag1;tag2;tag3');
	}
}
?>
