<?php
	/*
	* DBO for company table
	*/
	
	class Company extends OZ\DBO {
		/* put all fields from DB table here (except id field) */
		public $name;
		public $phone;
		public $mail;

		/* put other fields here */
		public $offices = array(); /* list of company offices from `company_office` database table */
		
		/** 
		* Table definition 
		*		- table string 		table name
		*		-	id string				id field name
		*		- fields array		other field definition:
		*			key - field name (same as in database table)
		*			value - array:
		*				type - type of field (numeric, text, html, mail, password). Required param. You can add any of your types (see README.md for more detail).
		*				required - true|false, required field flag. Default false.
		*				unique - true|false, unique field flag. Default false.
		**/
		public static $definition = array(
			'table' => 'company',
			'id' => 'companyID',
			'fields' => array(
				'name' => array('type' => 'text', 'required' => true, 'unique' => true),	/* name field definition: text, required and unique field */
				'phone' => array('type' => 'text'),																				/* phone field definition: text field */
				'mail' => array('type' => 'text')																					/* mail field definition: text field */
			)
		);
		
		function __construct($id = 0) {
			/* you can use cache here */
			parent::__construct($id);
			
			/* find all company offices */
			$params = array(
				'filter' => array('companyID' => $this->id)
			);
			$list = CompanyOffice::getList($params);
			$this->offices = $list['list'];
		}
		
		/* if you are using cache for company data */
		function save() {
			/* clear cache for company with $this->id */
			parent::save();
		}
		
		/* if you are using cache for company data */
		function delete() {
			/* clear cache for company with $this->id */
			parent::delete();
		}
		
	}
