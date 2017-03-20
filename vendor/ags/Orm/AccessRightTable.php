<?php

namespace Ags\Orm;


use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorInterface;

class AccessRightTable extends ORMBase {
	function __construct(ServiceLocatorInterface $serviceLocator) {
		parent::__construct($serviceLocator);
		
		$this->table = 'accessright';
		$this->primaryKey = 'id';
        $resultSetPrototype = new ResultSet();
        $this->tableGateway = new TableGateway($this->table, $this->dbAdapter, null, $resultSetPrototype);				
	}
}
