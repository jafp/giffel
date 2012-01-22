<?php

/** 
 * Copyright 2011 by Jacob Pedersen <jacob@zafri.dk>
 * All rights reserved.
**/

class Migrator {
	private $_dir;
	private $_migrations;
	
	public function __construct($dir) {
		
		try {
			
			$this->_db = new PDO("mysql:host=" . DBHOST . ";dbname=" . DBNAME, DBUSER, DBPASS);
			$this->_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
		} catch (PDOException $e) {
			die($e->getMessage());
		}
		
		$this->_dir = $dir;
		$this->_migrations = array();

		$d = dir($this->_dir);		
		
		while (($entry = $d->read()) !== false) {
			if ($entry != '.' && $entry != '..') {
				
				$parts = explode('.', $entry);
				
				$number = (int) $parts[0];
				$name = (string) $parts[1];
				
				$name = ucwords(str_replace('_', ' ', $name));
				$name = str_replace(' ', '', $name);
				
				$path = $this->_dir . '/' . $entry;
				
				echo "$number $name <br/>";
				
				$this->_migrations[$number] = array($number, $name, $path);	
			}
		}
		
		$this->initSchemaVersionTable();
	}
	
	public function migrate() {
		$version = $this->readSchemaVersion();
		
		// return when there is no migrations
		if (count($this->_migrations) <= 0) return;
		
		$last_migration = $this->_migrations[count($this->_migrations)];
		
		echo "Schema version: $version - Last migration: $last_migration[0] <br/>";
		
		for ($i = $version + 1; $i <= $last_migration[0]; $i++) {
			if (isset($this->_migrations[$i])) {
				$mgr = $this->_migrations[$i];
				
				if (is_file($mgr[2])) {
					$sql = file_get_contents($mgr[2]);
					
					try {
						echo 'MIGRATING: ' . $mgr[2] . ': ';
						
						$this->_db->exec($sql);
						$this->writeSchemaVersion($mgr[0]);
						
						echo 'OK';

					} catch (PDOException $e) {
						die('Migration error [<b>'.$mgr[2].'</b>]:<br/><br/>' . $e->getMessage());
						echo 'ERROR';
					}
					echo '<br/>';
				}
			}
		}
		
		if ($this->readSchemaVersion() == $last_migration[0]) {
			return true;
		}
		return false;
	}
	
	private function initSchemaVersionTable() {
		try {
			$this->_db->query("SELECT * FROM `schema`");
		} catch (PDOException $e) {
			$this->_db->exec("CREATE TABLE `schema` (`version` INT NOT NULL, `timestamp` 
				TIMESTAMP DEFAULT CURRENT_TIMESTAMP);");
		}
	}
	
	private function readSchemaVersion() {
		try {
			$stmt = $this->_db->query("SELECT `version` FROM `schema` 
				ORDER BY `timestamp` DESC, `version` DESC");
				
			if ($stmt != null) {
				return $stmt->fetchColumn();
			}
			
		} catch (PDOException $e) {
			die('READ ERROR: ' .$e->getMessage());
		}
		return 0;
	}

	private function writeSchemaVersion($version) {
		$stmt = $this->_db->prepare("INSERT INTO `schema` (version) VALUES (:version)");
		return $stmt->execute(array('version' => $version));
	}
}

?>