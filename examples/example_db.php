<?php
	require_once('./../db.class.php');
	require_once('./../dbo.class.php');
	
	/* make it short */
	use OZ\DB as DB;
	
	/* Mysql access */
	$sql_driver = 'mysql';
	$sql_host = 'localhost';
	$sql_name = 'opensource.my';
	$sql_user = 'root';
	$sql_pass = '';
	DB::init($sql_driver, $sql_host, $sql_name, $sql_user, $sql_pass);
	
	echo 'Query example: <br/>';
	$res = DB::query('SELECT * FROM `company` ORDER BY `companyID` DESC;');
	
	echo 'Num rows: ' . DB::rows($res) . '<br/>';
	
	while($lot = DB::fetch($res)) {
		echo '<pre>';
		print_r($lot);
		echo '</pre>';
	}
	
	echo 'Prepare + execute example: <br/>';
	$res = DB::prepare('SELECT * FROM `company` WHERE `companyID` = :id;');
	DB::execute($res, array('id' => 1));
	if($lot = DB::fetch($res)) {
		echo '<pre>';
		print_r($lot);
		echo '</pre>';
	}
	/* prepare statment already exists */
	DB::execute($res, array('id' => 2));
	if($lot = DB::fetch($res)) {
		echo '<pre>';
		print_r($lot);
		echo '</pre>';
	}
	
	echo 'Insert some data: <br/>';
	$res = DB::prepare('INSERT INTO `company` SET `name` = :name, `phone` = :phone, `mail` = :mail;');
	DB::execute($res, array('name' => 'My Company 3', 'phone' => '+7 (999) 000-00-03', 'mail' => 'somemail@company3.test'));
	echo 'Last ID: ' . DB::id() . '<br/>';
	