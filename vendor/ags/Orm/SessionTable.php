<?php

namespace Ags\Orm;


use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorInterface;

class SessionTable extends ORMBase {
	function __construct(ServiceLocatorInterface $serviceLocator) {
		parent::__construct($serviceLocator);
		
		$this->table = 'session';
		$this->primaryKey = 'sid';
        $resultSetPrototype = new ResultSet();
        $this->tableGateway = new TableGateway($this->table, $this->dbAdapter, null, $resultSetPrototype);				
	}

	function mostActiveChildren($parentid = null) {
		$category = '';
		if ($parentid !== null) {
			$tuser = new UserTable($this->serviceLocator);
			if ($tuser->load($parentid)) {
				$parentrole = $tuser->usergroupinfo['label'];
				$parentemail = $tuser->email; 
				// add query category based on parent info 
				if ($parentrole == 'Account Executive') $category = "AND s.username IN (SELECT username FROM user WHERE email_ae = '$parentemail')";
				elseif ($parentrole == 'Company Administrator') $category = "AND s.username IN (SELECT username FROM user WHERE email = '$parentemail')";
			}
		}
		$aweekago = date('Y-m-d', strtotime('-1 WEEK'));		
		$sql = "SELECT s.username, COUNT(*) AS cnt FROM session s WHERE s.stime > '$aweekago' AND s.action = 'login' $category			 
			GROUP BY s.username ORDER BY cnt DESC LIMIT 10";
		return $this->query($sql);
	}
}
