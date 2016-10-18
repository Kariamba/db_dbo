# db_dbo

Package contain 2 classes: 

- DBO is database tabels manager.
- DB is static wrapper for PDO (allow to use it in every contnent of your application). Required for DBO.

PHP Tested: 5.6.19, 7.0.11


## CONTENTS

	1. SHORT EXAMPLE
	2. DBO
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

