<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Session\Config\SessionConfig;
use Zend\Session\SessionManager;
use Zend\Session\Container;
use Zend\Authentication\Adapter\DbTable as DbAuthAdapter;

class Module
{
	public function afterDispatch(MvcEvent $event) {
		//print "Called after any controller action called. Do any operation.";
	}
	
	public function beforeDispatch(MvcEvent $event) {
        $application   = $event->getApplication();
        $sm            = $application->getServiceManager();
        $sharedManager = $application->getEventManager()->getSharedManager();
        
   		$router = $sm->get('router');
        $request = $sm->get('request');
        
        $matchedRoute = $router->match($request);
        if (null !== $matchedRoute) {
            /** attach the ACL plugin */
            $sharedManager->attach('Zend\Mvc\Controller\AbstractActionController','dispatch', function($e) use ($sm) {
			   $sm->get('ControllerPluginManager')->get('AclPlugin')->doAuthorization($e); //pass to the plugin...    
            }, 2);
            /** attach other events */
			$sharedManager->attach('Zend\Mvc\Controller\AbstractActionController','dispatch', function($e) use ($sm, $request) {
				$controller = $e->getTarget();
				$session = new Container('user');

				if (!$session->offsetExists('userinfo') && !$request instanceof \Zend\Console\Request) {
					$controller->SessionLogger()->logArray(array(
						'action'=>'visit',
						'remote_addr' => $request->getServer('REMOTE_ADDR'),
					));
				}
            }, 
			100);
        }
    }

    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
		// initialize session manager
		$this->initSession(array('remember_me_seconds'=> time()+(3 * 24 * 60 * 60), 'use_cookies'=>true, 'cookie_httponly'=>true, 'name'=>'twitools'));
		// add action to dispatched events
		$eventManager->attach(MvcEvent::EVENT_DISPATCH, array($this, 'beforeDispatch'), 100);
		$eventManager->attach(MvcEvent::EVENT_DISPATCH, array($this, 'afterDispatch'), -100);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
					'Ags'=>__DIR__ . '/../../vendor/ags',
                ),
            ),
        );
    }

	public function initSession($config) {
		$sessionConfig = new SessionConfig();
		$sessionConfig->setOptions($config);
		$sessionManager = new SessionManager($sessionConfig);
		$sessionManager->start();
		Container::setDefaultManager($sessionManager);
	}
}
