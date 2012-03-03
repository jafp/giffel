<?php

/** 
 * Copyright 2011 by Jacob Pedersen <jacob@zafri.dk>
 * All rights reserved.
**/

abstract class DbObject {
	const CLASS_NAME = "DbObject";
	
	public static $table = 'DbObjects';
	public static $fields = array();
	public static $validated = array();
	public static $relations = array();
	public static $order_by = '';
	
	public static function getRelations() {}
	public static function getOrderBy() { return ''; }
	protected function beforeSave() {}
	//public function validate() { return array(); }

	private $_db;
	public 	$_fields;
	private $_user_data;
	private $_table_name;
	private $_table_fields;
	private $_relations;
	private $_called_class;
	private $_cached_relations;
	private $_field_types;
	private $_include_empty = false;

	/**
	 * Statements executed.
	 * Simple logging utility.
	 */
	private static $_log_statements = array();
	
	public function __construct() 
	{

		$this->_db = Util::getDb();
		$this->_fields = array();
		$this->_user_data = array();
		$class = get_called_class();
		
		$this->_called_class = $this->className();

		$this->_table_name = $class::$table;
		$this->_table_fields = $class::$fields;
		$this->_relations = $class::$relations;
		
		$this->_field_types = array();
		
		foreach ($this->_table_fields as $field) {
			$this->_field_types[$field[0]] = $field[1];
			$this->_fields[$field[0]] = null;
		}
	}
	
	public static function find($where = null, $params = array(), $limit = null) {
		$class = get_called_class();
		$order_by = $class::$order_by;
 		$table_name = self::getTableName();
		
		$stmt = "SELECT SQL_NO_CACHE * FROM `{$table_name}` {$where} {$order_by} {$limit}";
		if (Util::isDebug())
		{
			self::$_log_statements[] = $stmt;	
		}
		
		try {
			$stmt = Util::getDb()->prepare($stmt);
			$stmt->execute($params);
			
			$results = array();
			
			foreach ($stmt->fetchAll() as $result) {
				$obj = new $class();
				foreach ($result as $key => $value) {
					$obj->$key = $value;
				}

				$obj->_callback('afterFind');
				$results[] = $obj;
			}
			
			return $results;
			
		} catch (PDOException $e) {
			die($e->getmessage());
		}
	}

	public static function findBySQL($stmt, $params) {
		$class = get_called_class();
		$order_by = $class::$order_by;
 		$table_name = self::getTableName();

 		try {
			$stmt = Util::getDb()->prepare($stmt);
			$stmt->execute($params);
			
			$results = array();
			
			foreach ($stmt->fetchAll() as $result) {
				$obj = new $class();
				foreach ($result as $key => $value) {
					$obj->$key = $value;
				}

				$obj->_callback('afterFind');
				$results[] = $obj;
			}
			
			return $results;
			
		} catch (PDOException $e) {
			die($e->getmessage());
		}
	}
	
	public static function findOne($where = null, $params = array()) {
		$class = get_called_class();
		$order_by = $class::$order_by;
		$table_name = self::getTableName();

		$stmt = "SELECT SQL_NO_CACHE * FROM `{$table_name}` {$where} {$order_by} LIMIT 1";

		if (Util::isDebug())
		{
			$backtrace = debug_backtrace();
			self::$_log_statements[] = array($class, $backtrace[0]['function'], $where, $params, $stmt);
		}
		
		try {
			$stmt = Util::getDb()->prepare($stmt);
			$stmt->execute($params);
			$result = $stmt->fetch();
			if ($result != null) {
				$obj = new $class();
				foreach ($result as $key => $value) {
					$obj->$key = $value;
				}
			
				$obj->_callback('afterFind');
				return $obj;
			}
		} catch (PDOException $e) {
			die($e->getmessage());
		}
		return null;
	}
	
	public static function findById($id) {
		return self::findOne("where `id`=?", array($id));
	}
	
	public function reload()
	{
		return self::findById($this->id);
	}

	public function save() {
		$this->_callback('beforeSave');
		$is_new = true;
		$res = null;
		if ($this->_fields['id'] != null) {
			$res = $this->_makeUpdateStatement();
			$is_new = false;
		} else {
			$res = $this->_makeInsertStatement();
			$is_new = true;
		}
		try {
			if (Util::isDebug())
			{
				self::$_log_statements[] = $res[0];
			}

			$stmt = $this->_db->prepare($res[0]);
			$fields = array();
			
			foreach ($res[1] as $field) {
				$var = "{$field}";
				$value = $this->_fields[$field];
				$fields[$var] = $value;
			}

			$retval = $stmt->execute($fields);
			
			if ($is_new)
			{
				$this->id = $this->_db->lastInsertId();
			}
			
			$this->_callback('afterSave');
			return $retval;
			
		} catch (PDOException $e) {
			die($e->getMessage());
		}
	}
	
	public function validate() {
		$class = get_called_class();
		$errors = array();
		$all = array();
		
		foreach ($class::$validated as $field) {
			$field_name = $field[0];
			$validators = explode('|', $field[1]);
			
			foreach ($validators as $validator_name) {
				$class_name = null;
				$params = null;
				
				if (preg_match("/([a-zA-Z0-9-_]+)\[([a-zA-Z0-9-_]+)\]/", $validator_name, $matches)) {
					$class_name = $matches[1] . 'Validator';
					$params = array($matches[2]);
				} else {
					$class_name = $validator_name . 'Validator';
				}
				
				$res = call_user_func("{$class_name}::validate", $this, $field, 
					$this->$field_name, $params);
					
				if ($res != null) {
					$errors[$field_name] = $res;
					$all[] = $res[1];
				}
			}
		}
		//$errors['all'] = $all;
		return $all;
	}
	
	public function delete() {
		if ($this->id != null) {
			try {
				$q = "DELETE FROM `{$this->_table_name}` WHERE `id`=?";
				$stmt = $this->_db->prepare($q);
				
				if (Util::isDebug())
				{
					self::$_log_statements[] = $q;
				}

				$stmt->execute(array($this->id));
				
			} catch (PDOException $e) {
				// handle exception
			}
		}
	}
	
	public function relation($name, $reload = false) {
		if (!isset($this->_relations[$name])) return null;
		
		if (!$reload && isset($this->_cached_relations[$name])) {
			return $this->_cached_relations[$name];
		}
		
		$relation = $this->_relations[$name];
		$type = $relation[0];
		$result = null;
		
		if ($type == 'one-to-many') {
			$result = $this->_oneToManyRelation($name, $relation);
		} else if ($type == 'many-to-one') {
			$result = $this->_manyToOneRelation($name, $relation);
		} else if ($type == 'many-to-many') {
			$result = $this->_manyToManyRelation($name, $relation);
		}
		
		if ($result != null) {
			$this->_cached_relations[$name] = $result;
			return $result;
		}
		
		return NULL;
		// todo: implement 'many-to-many' relations
	}
	
	public function set($data, $include_empty = false) {
		$this->_include_empty = $include_empty;
		foreach ($data as $key => $value) {
			$this->$key = $value;
		}
	}
	
	public function className() {
		return "DbObject";
	}

	public function isNew()
	{
		return $this->id == 0;
	}
	
	public function __set($name, $value) {
		if ($this->_hasField($name)) {
			$this->_fields[$name] = $value;
		} else {
			$this->_user_data[$name] = $value;
		}
	}
	
	public function __get($name) {
		if (isset($this->_relations[$name])) {
			return $this->relation($name);
		}
		if (isset($this->_fields[$name])) {
			return $this->_fields[$name];
		} else if (isset($this->_user_data[$name])) {
			return $this->_user_data[$name];
		}
		return NULL;
	}
	
	private function _oneToManyRelation($name, $relation) {
		$class = $relation[1];
		$key = $relation[2];
		
		return call_user_func("{$class}::findOne", "where id=?", array($this->$key));
	}
	
	private function _manyToOneRelation($name, $relation) {
		$class = $relation[1];
		$key = $relation[2];
		
		return call_user_func("{$class}::find", "where `{$key}`=?", array($this->id));
	}

	public function _manyToManyRelation($name, $relation) {
		list($type, $target_class, $target_table, $target_id, $rel_table, $rel_target_id, $rel_id) = $relation;
		
		return $target_class::findBySQL("SELECT `{$target_table}`.* FROM `{$target_table}` LEFT JOIN `{$rel_table}` ON `{$rel_table}`.`{$rel_target_id}` = `{$target_table}`.`{$target_id}` WHERE `{$rel_table}`.`{$rel_id}` = ?", array($this->id));
	}
	
	private function _hasField($name) {
		foreach ($this->_table_fields as $field) {
			if ($field[0] == $name) {
				return true;
			}
		}
		return false;
	}
	
	private function _makeInsertStatement() {
		$s = "INSERT INTO `{$this->_table_name}` (";
		
		$bound_vars = array();
		
		$c = 1; 
		$l = count($this->_fields);
		foreach ($this->_fields as $name => $value) {
			if ($name == 'id') { $l--; continue; } 
			
			$s .= "`{$name}`";
			
			if ($c < $l) {
				$s .= ',';
			}
			
			$bound_vars[] = $name;
			
			$c++;
		}
		
		$s .= ') VALUES (';
		
		$c = 1; 
		foreach ($this->_fields as $name => $value) {
			if ($name == 'id') { continue; }
			if ($this->_field_types[$name] == 'timestamp')
			{
				$s .= "TIMESTAMP(:$name)";
			}
			else
			{
				$s .= ":$name";
			}
			
			if ($c < $l) {
				$s .= ',';
			}
			
			$c++;
		}
		
		$s .= ')';
		
		return array($s, $bound_vars);
	}
	
	private function _makeUpdateStatement() {
			$s = "UPDATE `{$this->_table_name}` SET ";

			$bound_vars = array();
			$filtered = array();
			
			foreach ($this->_fields as $name => $value)
			{
				$is_integer = $this->_field_types[$name] == 'integer';
				if ($is_integer || $this->_include_empty || $name != 'id' && $value != null)
				{
					$filtered[$name] = $value;
				}
			}
			$len = count($filtered);
			$index = 1; 

			foreach ($filtered as $name => $value) {
				if ($this->_field_types[$name] == 'timestamp')
				{
					$s .= "`{$name}` = :{$name}";
				}
				else
				{
					$s .= "`{$name}` = :{$name}";
				}
				
				if ($index < $len) {
					$s .= ',';
				}
				
				$bound_vars[] = $name;
				$index++;
			}

			$bound_vars[] = 'id';
			$s .= " WHERE `id` = :id";

			return array($s, $bound_vars);
	}
	
	private function _callback($name) {
		if (method_exists($this, $name)) {
			call_user_func(array($this, $name));
		}
	}
	
	public function toArray()
	{
		$res = array();
		foreach ($this->_table_fields as $f)
		{
			$name = $f[0];
			$res[$name] = $this->$name;
		}
		
		return $res;
	}

	public static function getTableName()
	{
		$class = get_called_class();
		return (isset($class::$table_name) ? $class::$table_name : $class::$table); 
	}

	public static function dumpStats()
	{
		var_dump(self::$_log_statements);
	}
}

?>