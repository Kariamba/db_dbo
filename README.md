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

1.	Define public fields of class equal to fields in database, except ID field.
	$name and $login fields in example above.
2.	Define any addition fields of class.
	It could be field that related on database fields such as $full_name field, which can be as a result of table fields $name_first and $name_last.
	Or it could contain list of data from other database tables (see company.dbo.class.php in example).
3.	Create $definition array.
	'table' - name of your database table
	'id' - id field in your database table
	'fields' - list of all other fields with some params.
	For each field in 'fields' section you should provide type of field. Also you can add required or unique field properties. See example above.
	Class provide several default types: numeric (numeric database fields), text, html, mail, password (all other database fields). You can define your own types such as 'date' for date or datetime fields (see section 2.2).
4.	Define class constructor and redefine other parent methods (if required).
	For example, you can skip parent construcor if you have data in cache. Or update you cache after calling parent save() or delete() methods.
5.	Define you own methods.

### 2.2. FIELDS TYPES DEFINITION

Actualy you can use any type you want. But several DBO parent methods use type for several purposes:

1.	Preparetion fields before SQL insert or update statment.
	DBO::prepare() has check all fields and try to convert them to proper format or set empty (zero, null) data.
	So, it's to modify this method for your field types
2.	Fields validation rules.
	DBO::validate() has check all fields according rules for field type.
	So, if you want some validation rules for your field types modify validate() method.
	
### 2.3. DBO PUBLIC METHODS

