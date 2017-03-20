<?php

namespace Ags\Orm;


use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorInterface;

class MenuTable extends ORMBase {
	function __construct(ServiceLocatorInterface $serviceLocator) {
		parent::__construct($serviceLocator);
		
		$this->table = 'menu';
		$this->primaryKey = 'mid';
		$this->init();				
	}

	function loadAll($category = array()) {
		$topcategory = array('where'=>array('parent'=>0), 'order'=>array('morder'=>'ASC'));
		$topmenus = parent::loadAll(array_merge($topcategory, $category));
		$usermenu = array();
		foreach ($topmenus as $m) {
			$usermenu[] = $m;
			$children = parent::loadAll(array('where'=>array('parent'=>$m['mid']), 'order'=>array('label'=>'ASC')));
			foreach ($children as $child) {
				$usermenu[] = $child;
			}
		}
		
		return $usermenu;
	}

	function loadParents() {
		return parent::loadAll(array('where'=>array('parent'=>0), 'order'=>array('morder'=>'ASC')));
	}
	
	function loadSideMenu($userlevel = 0) {
		$topmenus = parent::loadAll(array(
			'join'=>array(
				array('table'=>'group_menu gm', 'fields'=>'menu.mid = gm.menuid', 'LEFT'),
			),
			'where'=>array('parent'=>0, 'position'=>1, 'gm.groupid'=>$userlevel),
			'order'=>array('morder'=>'ASC')));
		$usermenu = array();
		foreach ($topmenus as $m) {
			$m['children'] = parent::loadAll(array(
				'join'=>array(
					array('table'=>'group_menu gm', 'fields'=>'menu.mid = gm.menuid', 'LEFT'),
				),
				'where'=>array('parent'=>$m['mid'], 'gm.groupid'=>$userlevel),
				'order'=>array('label'=>'ASC')));
			$usermenu[] = $m;
		}
		
		return $usermenu;
	}
}
