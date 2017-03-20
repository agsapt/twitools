<?php

namespace Ags\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;

class AgsController extends AbstractActionController {
	private $application_config;
	private $userscripts = array();
	private $userstyles = array();
	
	protected $userinfo;
	protected $logger;

	function __construct() {
		$this->userinfo = $this->getUserSessionInfo();
		// application logger
		$logwriter = new \Zend\Log\Writer\Stream(getcwd() . '/log/log-' . date('Y-m-d') . '.log');
		$this->logger = new \Zend\Log\Logger();
		$this->logger->addWriter($logwriter);
		// log PHP errors and exceptions too
		\Zend\Log\Logger::registerErrorHandler($this->logger);
		\Zend\Log\Logger::registerExceptionHandler($this->logger);
	}

	public function addScripts($scripts) {
		if (is_array($scripts)) $this->userscripts = array_merge($this->userscripts, $scripts);
		else $this->userscripts[] = $scripts;
	}

	public function addStyles($styles) {
		if (is_array($styles)) $this->userstyles = array_merge($this->userstyles, $styles);
		else $this->userstyles[] = $styles;
	}
	
	public function getAppConfig($key = null) {
		if (!isset($this->application_config)) {
			$config = $this->getServiceLocator()->get('Config');
			$this->application_config = $config['appconfig'];
			// get params from hol_option table
			$toption = new \Ags\Orm\ORMBase($this->getServiceLocator());
			$toption->setProperties(array('table'=>'option', 'primaryKey'=>'paramname'));
			$this->application_config['params'] = $toption->loadAll();
			// get params from user options
			$tuseropt = new \Ags\Orm\ORMBase($this->getServiceLocator());
			$tuseropt->setProperties(array('table'=>'user_option'));
			$useroptions = $tuseropt->loadAll(array(
				'select'=>array('paramname', 'paramvalue'),
				'where'=>array('userid'=>$this->userinfo['id'])
			));
			if (count($useroptions)) $this->application_config['params'] = array_merge($this->application_config['params'], $useroptions);
		}
		return ($key == null) ? $this->application_config : $this->application_config[$key];
	}

	public function getAppParam($key) {
		$result = null;
		foreach ($this->getAppConfig('params') as $param) {
			if ($key == $param['paramname']) {
				$result = $param['paramvalue'];
				break;
			}
		}
		return $result;
	}

	private function getBreadcrumb() {
		$breadcrumb = array();
		$route = $this->params()->fromRoute();
		if ($route['controller'] != 'Application\Controller\Index') {
			$controllerparts = explode('\\', strtolower($route['controller']));
			$breadcrumb[] = array('title'=>$controllerparts[0], 'icon'=>'icon-laptop', 'url'=>"/{$controllerparts[0]}");
			if ($controllerparts[2] != 'index') $breadcrumb[] = array('title'=>$controllerparts[2], 'icon'=>'icon-keyboard-o', 'url'=>"/{$controllerparts[2]}");
			$breadcrumb[] = array('title'=>$route['action'], 'icon'=>'icon-laptop', 'url'=>'javascript:;', 'class'=>'current');
		}

		return $breadcrumb;
	}

	public function getLoadedModules() {
		$modules = array();
		$resources = $this->getServiceLocator()->get('ModuleManager')->getLoadedModules();
        foreach (array_keys($resources) as $res) $modules[] = strtolower($res);
		return $modules;
	}
	
	private function getSideMenu() {
		
	}
	
	private function getNotifications() {
		
	}
	
	private function getMessages() {
		
	}
	
	private function getTasks() {
		
	}

	public function getUserSessionInfo() {
		$usersession = new Container('user');
		return $usersession->userinfo;
	}

	public function log($level, $message) {
		$message = "[".session_id()."][".$this->params('controller')."-".$this->params('action')."] $message";
		$this->logger->$level($message);
	}

	public function prepareScripts() { /* to override by the child object */ }
	public function prepareStyles() { /* to override by the child object */ }

	public function reloadUserSessionInfo() {
		$user_obj = new \Ags\Orm\UserTable($this->getServiceLocator());
		if ($user_obj->load($this->userinfo['id'])) {
			$usersession = new Container('user');
			$userdata = $this->Utility()->object2array($user_obj);
			$usersession->offsetSet('userinfo', $userdata);
		}
		$this->userinfo = $this->getUserSessionInfo();
	}
	
	public function setupLayout($template = null, $options = array()) {
		if ($template !== null) $this->layout()->setTemplate($template);
		$this->layout()->currentpages = array();
		
		foreach ($options as $key=>$value) $this->layout()->$key = $value;
		// set user scripts and styles 
		$this->layout()->userscripts = $this->userscripts;
		$this->layout()->userstyles = $this->userstyles;
		// application configuration
		$this->layout()->appconfig = $this->getAppConfig();
		// user info 
		$this->layout()->userinfo = $this->getUserSessionInfo();
		// side menu 
		$usersession = $this->getUserSessionInfo();
		$userlevel = $usersession['usergroup'];
		// other parts of the layout
		$this->layout()->breadcrumb = $this->getBreadcrumb();
		$this->layout()->notifications = $this->getNotifications();
		$this->layout()->messages = $this->getMessages();
		$this->layout()->tasks = $this->getTasks();		
	}
}
