<?php
	/*
	* DBO for company office table
	*/
	
	class CompanyOffice extends OZ\DBO {
		/* put all fields from DB table here (except id field) */
		public $companyID;
		public $name;
		public $address;

		/* put other fields here */
		public $offices = array(); /* list of company offices from `company_office` database table */
		
		/* table definition*/
		public static $definition = array(
			'table' => 'company_office', 			/* table name */
			'id' => 'officeID',								/* id field name */
			'fields' => array(
				'companyID' => array('type' => 'numeric', 'required' => true),						/* name field definition: numeric and required field */
				'name' => array('type' => 'text', 'required' => true, 'unique' => true),	/* name field definition: text, required and unique field */
				'address' => array('type' => 'text')                                  		/* address field definition: text field */
			)
		);
		
		function __construct($id = 0) {
			parent::__construct($id);
		}
		
		/* if you are using cache for company data */
		function save() {
			/* clear cache for company with $this->companyID */
			parent::save();
		}
		
		/* if you are using cache for company data */
		function delete() {
			/* clear cache for company with $this->companyID */
			parent::delete();
		}
		
	}
