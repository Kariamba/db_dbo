# db_dbo

Package contain 2 classes: 

- DBO is database tabels manager.
- DB is static wrapper for PDO (allow to use it in every contnent of your application). Required for DBO.

PHP Tested: 5.6.19, 7.0.11


## CONTENTS

	1. SHORT EXAMPLE
	2. DBO
	  2.1. TABLE DEFINITION
	  2.2. FIELDS TYPES DEFINITION
	  2.3. DBO PUBLIC METHODS
	    2.3.1. __construct()
	    2.3.2. prepare()
	    2.3.3. save()
	    2.3.4. delete()
	    2.3.5. reload()
	    2.3.6. validate()
	    2.3.7. unique()
	    2.3.8. static getList()
	    2.3.9. static deleteList()
	3. DB

* * *

## 1. SHORT EXAMPLE

	<?php
	
	class User extends OZ\DBO {
	  public $name;
	  public $login;

	  public static $definition = array(
	    'table' => 'users',
	    'id' => 'userID',
	    'fields' => array(
	      'name' => array('type' => 'text', 'required' => true),
	      'login' => array('type' => 'text', 'required' => true, 'unique' => true)
	    )
	  );

	  function __construct($id = 0) {
	    parent::__construct($id);
	  }
	}

	/* Update name of user with ID 35 */
	$user = new User(35);
	$user->name = 'New name';
	$user->save();

	/* Delete user with ID 12 */
	$user = new User(12);
	$user->delete();

	/* Or simple use static deleteList() method */
	User::deleteList(array(12));

	/* Create new user and check unique login */
	$user = new User();
	$user->name = 'Name';
	$user->login = 'Login';
	$validation = $user->validate();
	if($validation['result']) {
	  $user->save();
	}
	else {
	  print_r($validation['errors']);
	}
	
	/* Get users list sorted by name */
	$result = User::GetList(array('order' => array('name' => 'asc')));
	if(!empty($result['list'])) {
	  print_r($result['list']);
	}
	
	/* Get users list with names John, sorted by ID, and use pagination */
	$result = User::GetList(
		array(
			'filter' => array('name' => 'John'),
			'order' => array('userID' => 'asc'),
			'pager' => array(
				'page' => 2,
				'onpage' => 10
			)
		)
	);
	if(!empty($result['list'])) {
	  print_r($result['list']);
	}
	
* * *

## 2.DBO

Each data base table should have its own DBO child class with its own table definition.

Simlest class for `users` table with fields `userID`, `name` and `login`:

	class User extends OZ\DBO {
	  public $name;
	  public $login;

	  public static $definition = array(
	    'table' => 'users',
	    'id' => 'userID', /* ID will store in $id field */
	    'fields' => array(
	      'name' => array('type' => 'text', 'required' => true),
	      'login' => array('type' => 'text', 'required' => true, 'unique' => true)
	    )
	  );

	  function __construct($id = 0) {
	    parent::__construct($id);
	  }
	}

### 2.1. TABLE DEFINITION

There are several steps to create the table definition in your DBO child:

1.  Define public fields of class equal to fields in database, except ID field.

    $name and $login fields in example above.
		
2.  Define any additional fields of class.

    It could be field that related on database fields such as $full_name field, which can be as a result of table fields $name_first and $name_last.
		
    Or it could contain list of data from other database tables (see company.dbo.class.php in example).
		
3.  Create $definition array.

    'table' - name of your database table
		
    'id' - id field in your database table
		
    'fields' - list of all other fields with some params.
		
    For each field in 'fields' section you should provide type of field. Also you can add required or unique field properties. See example above.
		
    Class provide several default types: numeric (numeric database fields), text, html, mail, password (all other database fields). You can define your own types such as 'date' for date or datetime fields (see section 2.2).
		
4.  Define class constructor and redefine other parent methods (if required).
    For example, you can skip parent construcor if you have data in cache. Or update you cache after calling parent save() or delete() methods.
5.  Define you own methods.

### 2.2. FIELDS TYPES DEFINITION

Actualy you can use any type you want. But several DBO parent methods use type for several purposes:

1.  Preparetion fields before SQL insert or update statment.
    DBO::prepare() has check all fields and try to convert them to proper format or set empty (zero, null) data.
    So, it's to modify this method for your field types
2.  Fields validation rules.
    DBO::validate() has check all fields according rules for field type.
    So, if you want some validation rules for your field types modify validate() method.

### 2.3. DBO PUBLIC METHODS

#### 2.3.1. __construct($id = 0)

Class constructor.

Create object and try to get database record with $id.

You can check data by checking id field:

	$user = new User(2);
	if(!empty($user->id)) {
	  /* data was load from DB */
	}
	else {
	  /* there is no such ID in DB */
	}

#### 2.3.2. prepare()

Check all fields and try to convert them to proper format based on field type definition.

#### 2.3.3. save()

Insert (if $id is empty) or update data in DB.

#### 2.3.4. delete()

Remove data with current $id from DB.

#### 2.3.5. reload()

Reload data from DB. Additional fields of class are skipped.

#### 2.3.6. validate()

Validate current object fields.

Returns array with keys:

- result boolean - result of validation
- errors array - list of errors for each fields (if validation fails)

#### 2.3.7. unique($field, $value)

Check if $value of $filed of current object is unique in database

Returns boolean result of checking.

#### 2.3.8. static getList($params = array())

Get list of data from database.

You can filter and sort your list. You can get single page of list (pagination) also.

Filer your result:

	$params = array(
		'filter' => array('<field1>' => '<value1>', '<field2>' => '<value2>', ...)
	)

	where field - name of field in database,
	      value - filterd value
				
Sort your result:

	$params = array(
		'sort' => array('<field1>' => '<direction1>', '<field2>' => '<direction2>', ...)
	)

	where field - name of field in database,
	      direction - sort direction: asc or desc

Pagination:

	$params = array(
		'pager' => array('page' => '<current_page>', 'onpage' => '<items_on_page>')
	)

	where page - current list page,
	      onpage - number of items per page

You can mix these params.

In result you'll get an array with keys:

- list - list of selected items
- filter - list of accepted filters (if provided in params)
- sort - list of accepted sorting params (if provided in params)
- pager - array with data fo pager (if provided in params):
    page - current list page
		onpage - number of items in page
		pages - total list pages
		record - total items number
		
####  2.3.9. static deleteList($list = array())
	
Removes DB entries according to $list of IDs.

* * *
