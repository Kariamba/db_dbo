<?php
	/**
  * DataBase Object (DBO).
  *
  * This class provides basic DB tables managment.
  * You sholdn't modify table definition in this class ($definition). Make it in child class (see examples and README.md).
  *
  * @author		Oleg Zorin <zorinoa@yandex.ru>
	* @link			http://oleg.zorin.ru homepage
	*
	* @license https://opensource.org/licenses/GPL-3.0 GNU Public License, version 3
	*
	* @package	OZ\
  * @version	1.0
	*/
	namespace OZ;
	
	abstract class DBO {
		/* common field for every DBO child */
		public $id;

		/* table definition, here sould be empty, but each DBO child mast have (see example) */
		public static $definition = array(
			'table' => '',					/* name of DB table */
			'id' => '',							/* name of ID field */
			'fields' => array()			/* fields definition */
		);
		
		/* copy of $definition for object usage */
		protected $_def;
		
		/* validation consts */
		const VALIDATOR_ERROR_FORMAT = 'Invalid field format!';
		const VALIDATOR_ERROR_REQUIRED = 'Field required!';
		const VALIDATOR_ERROR_UNIQUE = 'Duplication of field value (value already exists)!';
		
		/**
		* Object constructor. Read entry from DB table with ID.
		*
		* @param string $ID		ID of DB entry.
		*/		
		function __construct($ID = null) {
			/* get definition for object purpose */
			$this->_def = self::_getDefinition($this);
			/* prepare and check table and id definitions */
			$this->_def['table'] = preg_replace('/[^a-zA-Z0-9_-]/', '', trim($this->_def['table']));
			$this->_def['id'] = preg_replace('/[^a-zA-Z0-9_-]/', '', trim($this->_def['id']));
			if(!empty($this->_def['table'])) {
				$this->id = 0;
				if(!is_null($ID)) {
					/* read database table */
					$res = DB::prepare('SELECT *
						FROM `' . $this->_def['table'] . '`
						WHERE `' . $this->_def['id'] . '` = :id;');
					DB::execute($res, array('id' => (int)$ID));
					if($lot = DB::fetch($res)) {
						foreach($lot as $key => $value) {
							if($key == $this->_def['id']) {
								/* id fill ID field */
								$this->id = $value;
							}
							else if(array_key_exists($key, $this)) {
								/* fill data from DB in defined object fields */
								$this->{$key} = $value;
							}
						}
					}
				}
			}
			else {
				throw new Exception('Empty table field');
			}
		}

		
		/**
		* Prepare fields - try to convert to defined type or set default (empty) value.
		*
		* @return array Returns array of prepared fields (just in case).
		*/
		function prepare() {
			$fields = array(
				'id' => $this->id
			);
			foreach($this->_def['fields'] as $key => $value) {
				if(array_key_exists($key, $this)) {
					switch($value['type']) {
						case 'numeric': {
							if(!empty($this->{$key})) {
								if(preg_match('/\./', $this->{$key})) {
									$this->{$key} = (float)$this->{$key};
								}
								else {
									$this->{$key} = (int)$this->{$key};
								}
							}
							else {
								$this->{$key} = 0;
							}
						} break;
						
						case 'mail': {
							if(!empty($this->{$key})) {
								$this->{$key} = strip_tags($this->{$key});
							}
							else {
								$this->{$key} = '';
							}
						} break;
						
						case 'password':
						case 'text': {
							if(!empty($this->{$key}) || $this->{$key} == '0') {
								$this->{$key} = strip_tags($this->{$key});
							}
							else {
								$this->{$key} = '';
							}
						} break;
						
						case 'html': {
							if(empty($this->{$key}) && $this->{$key} != '0') {
								$this->{$key} = '';
							}
						} break;
					}
					$fields[$key] = $this->{$key};
				}
			}
			return $fields;
		}
		
		
		/**
		* INSERT or UPDATE database entry.
		*
		* @return bool Returns true on success or false on failure.
		*/
		function save() {
			$this->prepare();
			$result = false;
			$sql_set = array();					/* array for sql SET */
			$fields_values = array();		/* array for placeholders */
			foreach($this->_def['fields'] as $key => $value) {
				if(array_key_exists($key, $this)) {
					$sql_set[] = '`' . $key . '` = :' . $key;
					$fields_values[$key] = $this->{$key};
				}
			}
			if(!empty($sql_set)) {
				$sql_set = implode(',', $sql_set);
				if(!empty($this->id)) {
					/* update */
					$fields_values['id'] = $this->id;
					$res = DB::prepare('UPDATE `' . $this->_def['table'] . '`
						SET ' . $sql_set . '
						WHERE `' . $this->_def['id'] . '` = :id
						LIMIT 1;');
					DB::execute($res, $fields_values);
				}
				else {
					/* insert */
					$res = DB::prepare('INSERT INTO `' . $this->_def['table'] . '`
						SET ' . $sql_set . ';');
					DB::execute($res, $fields_values);
					$this->id = DB::id();
				}
				$result = true;
			}
			return $result;
		}

		
		/**
		* DELETE database entry.
		*
		* @return bool Returns true on success or false on failure.
		*/
		function delete() {
			$result = false;
			if($this->id > 0) {
				$res = DB::prepare('DELETE FROM `' . $this->_def['table'] . '`
					WHERE `' . $this->_def['id'] . '` = :id
					LIMIT 1;');
				DB::execute($res, array('id' => $this->id));
				/* set null for objecy fields */
				$this->id = null;
				foreach($this->_def['fields'] as $key => $value) {
					if(array_key_exists($key, $this)) {
						$this->{$key} = null;
					}
				}
				$result = true;
			}
			return $result;
		}

		
		/**
		* Reload fields from database.
		*/
		function reload() {
			$fields = array(
				'id' => $this->id
			);
			if(!empty($this->id)) {
				$res = DB::prepare('SELECT *
					FROM `' . $this->_def['table'] . '`
					WHERE `' . $this->_def['id'] . '` = :id;');
				DB::execute($res, array('id' => $this->id));
				if($lot = DB::fetch($res)) {
					foreach($lot as $key => $value) {
						if($key != $this->_def['id'] && array_key_exists($key, $this)) {
							$this->{$key} = $value;
							$fields[$key] = $this->{$key};
						}
					}
				}
			}
		}
		
		
		/**
		* Validation of fields values.
		*
		* @return array Returns result of validation.
		*		- result boolean	result of validation (true|false)
		*		- errors array		list of errors (if validation fails)
		*/
		function validate() {
			$result = array(
				'result' => true,
				'errors' => array()
			);
			if(!empty($this->_def['fields']) && is_array($this->_def['fields'])) {
				foreach($this->_def['fields'] as $key => $condition) {
					if(array_key_exists($key, $this) && !empty($condition['type'])) {
						$value = $this->{$key};
						/* type validation */
						switch($condition['type']) {
							case 'numeric': {
								if(!empty($value) && !is_numeric($value)) {
									$result['result'] = false;
									$result['errors'][$key][] = self::VALIDATOR_ERROR_FORMAT;
								}
							} break;
							
							case 'text':
							case 'html': {
								/*
								if(!empty($value) && !is_string($value)) {
									$result['result'] = false;
									$result['errors'][$key][] = self::VALIDATOR_ERROR_FORMAT;
								}
								*/
							} break;
							
							case 'mail': {
								if(!empty($value) && !preg_match('/^[^@]+@[^@]+\..+$/', $value)) {
									$result['result'] = false;
									$result['errors'][$key][] = self::VALIDATOR_ERROR_FORMAT;
								}
							} break;
							
							case 'password': {
								if(!empty($value) && !preg_match('/^[a-f0-9A-F]{64}$/', $value)) {
									$result['result'] = false;
									$result['errors'][$key][] = self::VALIDATOR_ERROR_FORMAT;
								}
							} break;
						}
						/* requried fields validation */
						if(isset($condition['required']) && $condition['required'] && empty($this->{$key})) {
							$result['result'] = false;
							$result['errors'][$key][] = self::VALIDATOR_ERROR_REQUIRED;
						}
						/* unique fields validation */
						if(isset($condition['unique']) && $condition['unique'] && !$this->unique($key, $this->{$key})) {
							$result['result'] = false;
							$result['errors'][$key][] = self::VALIDATOR_ERROR_UNIQUE;
						}
					}
				}
			}
			return $result;
		}
		
		
		/**
		* Check field value for unique property.
		*
		* @return bolean Returns true for unique value or false if such value exists in DB.
		*/
		function unique($field, $value) {
			$result = true;
			if(array_key_exists($field, $this)) {
				$res = DB::prepare('SELECT *
					FROM `' . $this->_def['table'] . '`
					WHERE `' . $field . '` = :field
						AND `' . $this->_def['id'] . '` <> :id;');
				DB::execute($res, array('id' => $this->id, 'field' => $value));
				if(DB::rows($res) > 0) {
					$result = false;
				}
			}
			return $result;
		}
		
		/* ---<====>--- */
	
		/**
		* Get list of entries.
		*
		* @param array $params		Array of params for select statment:
		*  	- filter	Array of filters (WHERE satatment)
		*  	- order		Order of enties (ORDER statment)
		*  	- pager		Pagination of entries (LIMIT statment)
		* 	See example or README.md for more details.
		*
		* @return array Returns result in array:
		*		- list 		Array of selected items
		*		- filter	Accepted where statments
		*		- order		Accepted order statments
		*		- pager		Pagination data (if pager params provided)
		*		- query		SQL query string (for debuging)
		*/
		static function getList($params = array()) {
			$result = array(
				'list' => array(),
				'filter' => array(),
				'order' => array(),
				'pager' => array(),
				'query' => ''
			);
			
			/* WHERE statment */
			$sql_where = '';
			$plh_where = array();
			if(!empty($params['filter']) && is_array($params['filter'])) {
				$tmp_where = array();
				foreach($params['filter'] as $key => $value) {
					$key = preg_replace('/[^a-zA-Z0-9_-]/', '', trim($key));
					if(isset(static::$definition['fields'][$key]) || $key == 'id') {
						if($key == 'id') {
							$key = static::$definition['id'];
						}
						if(is_array($value)) {
							/* `field` IN () */
							$tmp_in = array();
							foreach($value as $k => $v) {
								$tmp_in[] = ':' . $key . '_' . $k;
								$plh_where[$key . '_' . $k] = $v;
							}
							$tmp_where[] = '`' . $key . '` IN (' . implode(', ', $tmp_in) . ')';
						}
						else {
							/* `field` = */
							$plh_where[$key] = $value;
							$tmp_where[] = '`' . $key . '` = :' . $key;
							
						}
						$result['filter'][$key] = $value;						
					}
				}
				if(!empty($tmp_where)) {
					$sql_where = 'WHERE ' . implode(' AND ', $tmp_where);
				}
			}
			
			/* ORDER statment */
			$sql_order = '';
			if(!empty($params['order']) && is_array($params['order'])) {
				foreach($params['order'] as $key => $value) {
					if(!empty($key) && is_array($value)) {
						if(isset(static::$definition['fields'][$key])) {
							$field = preg_replace('/[^a-zA-Z0-9_-]/', '', trim($key));
							$direction = (strtolower($value) == 'asc') ? 'asc' : 'desc';
							$result['order'][] = '`' . $field . '` ' . strtoupper($direction);
						}
					}
				}
				if(!empty($result['order'])) {
					$sql_order = 'ORDER BY ' . implode(', ', $result['order']);
				}
			}
			
			/* LIMIT statment */
			$sql_limit = '';
			if(!empty($params['pager'])) {
				if(isset($params['pager']['onpage']) && (int)$params['pager']['onpage'] > 0) {
					$sql_query = 'SELECT * FROM `' . static::$definition['table'] . '` ' . $sql_where . ';';
					$res = DB::prepare($sql_query);
					DB::execute($res, $plh_where);
					$records = DB::rows($res);
					$page = (isset($params['pager']['page']) && (int)$params['pager']['page'] > 0) ? (int)$params['pager']['page'] : 1;
					$pages = ceil($records / (int)$params['pager']['onpage']);
					$result['pager'] = array(
						'page' => (int)$page,
						'onpage' => (int)$params['pager']['onpage'],
						'pages' => (int)$pages,
						'records' => (int)$records
					);
					$sql_limit = 'LIMIT ' . (((int)$page - 1) * (int)$params['pager']['onpage']) . ', ' . ((int)$params['pager']['onpage']);
				}
			}
			
			$sql_query = 'SELECT * FROM `' . static::$definition['table'] . '` ' . $sql_where . ' ' . $sql_order . ' ' . $sql_limit . ';';
			$res = DB::prepare($sql_query);
			DB::execute($res, $plh_where);
			while($lot = DB::fetch($res)) {
				$result['list'][] = $lot;
			}
			$result['query'] = $sql_query;
			return $result;
		}

		
		/**
		* Delete entries from database by list of IDs.
		*
		* @param array $list		List of IDs.
		*
		* @return true.
		*/
		static function deleteList($list = array()) {
			if(!empty($list) && is_array($list)) {
				$plh_where = array();
				$sql_where = array();
				foreach($list as $k => $v) {
					if((int)$v > 0) {
						$plh_where[] = (int)$v;
						$sql_where[] = '?';
					}
				}
				if(!empty($sql_where)) {
					if(count($sql_where) == 1) {
						$sql_where = implode(', ', $sql_where);
						$sql_query = 'DELETE FROM `' . static::$definition['table'] . '` WHERE `' . static::$definition['id'] . '` = ' . $sql_where . ';';
					}
					else {
						$sql_where = implode(', ', $sql_where);
						$sql_query = 'DELETE FROM `' . static::$definition['table'] . '` WHERE `' . static::$definition['id'] . '` IN (' . $sql_where . ');';
					}
					$res = DB::prepare($sql_query);
					DB::execute($res, $plh_where);
				}
			}
			return true;
		}
	
	
		/**
		* Get definition for object instance.
		*
		* @return array Returns definition of database table.
		*/
		static protected function _getDefinition($class) {
			if(is_object($class)) {
				$class = get_class($class);
			}
			$reflection = new \ReflectionClass($class);
			$definition = $reflection->getStaticPropertyValue('definition');
			return $definition;
		}

	}
	