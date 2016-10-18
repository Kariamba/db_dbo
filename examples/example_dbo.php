<?php
	require_once('./../db.class.php');
	require_once('./../dbo.class.php');
	
	/* Mysql access */
	$sql_driver = 'mysql';
	$sql_host = 'localhost';
	$sql_name = 'opensource.my';
	$sql_user = 'root';
	$sql_pass = '';
	OZ\DB::init($sql_driver, $sql_host, $sql_name, $sql_user, $sql_pass);
	
	/* Load your DBO childs */
	require_once('./dbo_classes/company.dbo.class.php');
	require_once('./dbo_classes/company_office.dbo.class.php');
	
	
	/* Try to get company with ID 1 */
	$company = new Company(1);
	
	/* Check if DB entry exists. */
	/* You should check id field! */
	if(!empty($company->id)) {
		/* modify and update some data */
		$company->name = 'My new company name';
		$company->save();
	}
	
	/* Create new company office */
	$office = new CompanyOffice();
	$office->companyID = $company->id;
	$office->name = 'Office 2';
	$office->address = 'Office address 2';
	/* check data */
	$validation = $office->validate();
	if($validation['result']) {
		$office->save();
	}
	else {
		echo '<pre>';
		print_r($validation['errors']);
		echo '</pre>';
	}
	
	/* Validation error, create office without companyID (required) and with same name as previous one (unique) */
	$office = new CompanyOffice();
	$office->name = 'Office 2';
	$office->address = 'Office address 3';
	/* check data */
	$validation = $office->validate();
	if($validation['result']) {
		$office->save();
	}
	else {
		echo '<pre>';
		print_r($validation['errors']);
		echo '</pre>';
	}
	