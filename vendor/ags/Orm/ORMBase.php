<?php

namespace Ags\Orm;

use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorInterface;

class ORMBase {
	protected $dbAdapter;
	protected $tableGateway;	
	protected $table;
	protected $primaryKey;
	protected $serviceLocator;
	
	private $select;
	private $join;
	private $where;
	private $limit;
	private $order;
	private $having;
	private $group;
	private $query_statement;
	
	function __construct(ServiceLocatorInterface $serviceLocator) {
		$this->dbAdapter = $serviceLocator->get('Zend\Db\Adapter\Adapter');	
		$this->serviceLocator = $serviceLocator;	
	}
	
	public function load($id) {
		$sql = "SELECT * FROM $this->table WHERE $this->primaryKey = '$id'";
		$cur = $this->query($sql);
		if ($cur && count($cur)) {
			$this->resultset = $cur[0];
			// a copy of the resultset as member variables
			foreach ($cur[0] as $key => $value) {
	            $this->$key = $value;
	        }
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	public function loadAll($categories = array()) {
		$this->resetQuery();
		if (isset($categories['select'])) $this->select = implode(', ', $categories['select']); else $this->select = "*";
		if (isset($categories['join'])) {
			$join = array();
			foreach ($categories['join'] as $jo) {
				if (isset($jo['pos']) && $jo['pos'] != '') {
					$join[] = $jo['pos'].' JOIN '.$jo['table'].' ON '.$jo['fields'];
				} else {
					$join[] = 'INNER JOIN '.$jo['table'].' ON '.$jo['fields'];
				}
			}
			$this->join = implode(' ', $join);
		}
		if (isset($categories['where'])) {
			foreach ($categories['where'] as $key=>$value) {
				$value = addslashes($value);
				if (preg_match("/[a-zA-Z0-9]*(>|<|=)/", $key)) $where = "$key '$value'"; else $where = "$key = '$value'"; 
				if (isset($this->where) && $this->where != '') $this->where .= " AND $where"; else $this->where = $where;
			} 
		}
		if (isset($categories['or_where'])) {
			foreach ($categories['or_where'] as $key=>$value) {
				$value = addslashes($value);
				if (preg_match("/[a-zA-Z0-9]*(>|<|=)/", $key)) $where = "$key '$value'"; else $where = "$key = '$value'"; 
				if (isset($this->where) && $this->where != '') $this->where .= " OR $where"; else $this->where = $where;
			} 
		}
		if (isset($categories['order'])) {
			$order = array();
			foreach ($categories['order'] as $key=>$value) $order[] = "$key $value";
			$this->order = "ORDER BY ".implode(',', $order);
		}
		if (isset($categories['limit'])) {
			$this->limit = "LIMIT ".$categories['limit']['offset'].", ".$categories['limit']['length'];
		}
		if (isset($categories['having'])) {
			foreach ($categories['having'] as $key=>$value) {
				$value = addslashes($value);
				if (preg_match("/[a-zA-Z0-9]*(>|<|=)/", $key)) $having = "$key '$value'"; else $having = "$key = '$value'"; 
				if (isset($this->having) && $this->having != '') $this->having .= " AND $having"; else $this->having = $having;
			} 
		}
		if (isset($categories['group'])) {
			$this->group = "GROUP BY ".implode(',', $categories['group']);
		}
		
		// construire sql
		if (strlen($this->where) > 0) $this->where = "WHERE ".$this->where;
		if (strlen($this->having) > 0) $this->having = "HAVING ".$this->having;
		$this->query_statement = "SELECT $this->select FROM $this->table $this->join $this->where $this->having $this->group $this->order $this->limit";
		return $this->query();
	}
	
	function desc() {
		$sql = "DESC $this->table";
		$cur = mysql_query($sql);
		$champs = array();
		while ($row = mysql_fetch_assoc($cur)) {
			$champs[] = $row['Field'];
		} 
		
		return $champs;
	}

	function query($sql = '', $return_result = true) {
		if ($sql != '') $this->query_statement = $sql;
		
		$result = $this->dbAdapter->query($this->query_statement, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
		if ($result) {
			if ($return_result && $result instanceof ResultSet) {
				return $result->toArray();
			} else {
				return true;
			}
		} else {
			return FALSE;
		}
	}

	private function resetQuery() {
		$this->select = '';
		$this->join = '';
		$this->where = '';
		$this->having = '';
		$this->limit = '';
		$this->order = '';
		$this->group = '';
		$this->query_statement = '';
	} 
	
	public function debug($dieafter = TRUE) {
		echo "<pre>";
		print_r((array)$this);
		echo "</pre>";
		if ($dieafter) die();
	}

	public function delete($id) {
		$sql = "DELETE FROM $this->table WHERE $this->primaryKey = $id";
		$this->query($sql);
	}
	
	public function deleteAll($categories = array()) {
		$this->resetQuery();
		if (isset($categories['where'])) {
			foreach ($categories['where'] as $key=>$value) {
				$value = addslashes($value);
				if (preg_match("/[a-zA-Z0-9]*(>|<|=)/", $key)) $where = "$key '$value'"; else $where = "$key = '$value'"; 
				if (isset($this->where) && $this->where != '') $this->where .= " AND $where"; else $this->where = $where;
			} 
		}
		if (isset($categories['or_where'])) {
			foreach ($categories['or_where'] as $key=>$value) {
				$value = addslashes($value);
				if (preg_match("/[a-zA-Z0-9]*(>|<|=)/", $key)) $where = "$key '$value'"; else $where = "$key = '$value'"; 
				if (isset($this->where) && $this->where != '') $this->where .= " OR $where"; else $this->where = $where;
			} 
		}
		if (strlen($this->where) > 0) $this->where = "WHERE ".$this->where;
		$this->query_statement = "DELETE FROM $this->table $this->where";
		return $this->query();
	}
	
	public function init() {		
        $resultSetPrototype = new ResultSet();
        $this->tableGateway = new TableGateway($this->table, $this->dbAdapter, null, $resultSetPrototype);				
	}
	
	public function last_query() {
		return $this->query_statement;
	}
	
	public function save($data = array(), $pkinsert = FALSE, $update = FALSE) {
		$keys = array();
		$values = array();
		foreach ($data as $key=>$value) {
			if ($key != $this->primaryKey || $pkinsert) {
				$keys[] = $key;
				$values[] = "'".addslashes($value)."'";
			}
		}
		
		if (!$update) {
			$keys_str = implode(',', $keys);
			$values_str = implode(',', $values);
			$this->query_statement = "INSERT INTO $this->table ($keys_str) VALUES ($values_str)";
		} else {
			$updates = array();
			for ($i = 0; $i < count($keys); $i++) {
				$updates[] = $keys[$i]." = ".$values[$i];
			}
			$update_list = implode(',', $updates);
			$this->query_statement = "UPDATE $this->table SET $update_list WHERE $this->primaryKey = ".$data[$this->primaryKey];
		}
		$cur = $this->query($this->query_statement);
		return !$cur ? FALSE : ($update ? $cur : $this->dbAdapter->getDriver()->getLastGeneratedValue());
	}
	
	public function setPrimaryKey($pk) { $this->primaryKey = $pk; }
	public function setTable($table) { $this->table = $table; }
	public function setProperties($props) {
		if (!is_array($props)) return;
		foreach ($props as $key=>$value) $this->$key = $value;
	}
}
