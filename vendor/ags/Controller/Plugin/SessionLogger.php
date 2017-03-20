<?php

namespace Ags\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class SessionLogger extends AbstractPlugin {    
	private function write($userdata) {
		$usersession_obj = new \Ags\Orm\SessionTable($this->getController()->getServiceLocator());
		$usersession_obj->save($userdata);
	}

	function log($username, $action, $data = null) {
		$userdata = array(
			'username'=>$username, 
			'php_session_id'=>session_id(), 
			'stime'=>date('Y-m-d H:i:s'), 
			'action'=>$action,
			'url'=>$this->getController()->getRequest()->getUriString()
		);
		if ($data != null) $userdata['data'] = json_encode($data);
		$this->write($userdata);
	}

	function logArray($sessiondata) {
		if (empty($sessiondata['php_session_id'])) $sessiondata['php_session_id'] = session_id();
		if (empty($sessiondata['stime'])) $sessiondata['stime'] = date('Y-m-d H:i:s');
		$this->write($sessiondata);
	}
}