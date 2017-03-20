<?php

namespace Ags\Controller\Plugin;
 
use Zend\Mvc\Controller\Plugin\AbstractPlugin,
    Zend\Session\Container as SessionContainer,
    Zend\Permissions\Acl\Acl,
    Zend\Permissions\Acl\Role\GenericRole as Role,
    Zend\Permissions\Acl\Resource\GenericResource as Resource;
    
class AclPlugin extends AbstractPlugin
{
    protected $sesscontainer;
    
    public function doAuthorization($e)
    {
        // set ACL
        $acl = new Acl();
        $acl->deny(); // by default deny all access to any controllers action

		$controller = $e->getTarget();
        $controllerClass = get_class($controller);
        $moduleName = strtolower(substr($controllerClass, 0, strpos($controllerClass, '\\')));

		if (!$this->sesscontainer) $this->sesscontainer = $controller->getUserSessionInfo();             
                
        # ROLES ############################################
		$tbl_usergroup = new \Ags\Orm\ORMBase($controller->getServiceLocator());
		$tbl_usergroup->setProperties(array('table'=>'ref_usergroup', 'primaryKey'=>'id'));
		$roles = $tbl_usergroup->loadAll();
		foreach ($roles as $r) $acl->addRole(new Role($r['label']));

		
        # RESOURCES ########################################
        $resources = $controller->getServiceLocator()->get('ModuleManager')->getLoadedModules();
        foreach (array_keys($resources) as $res) $acl->addResource(strtolower($res));
        
        # WHITELIST ########################################
                
        # PERMISSIONS ######################################
        $accessright = new \Ags\Orm\AccessRightTable($controller->getServiceLocator());
        $all_rights = $accessright->loadAll();
        foreach ($all_rights as $r) $acl->allow($r['role'], $r['module'], $r['action']);

        $role = (!$this->sesscontainer['usergroupinfo']['label']) ? 'Everyone' : $this->sesscontainer['usergroupinfo']['label'];
        $routeMatch = $e->getRouteMatch();
                
        $actionName = strtolower($routeMatch->getParam('action', 'not-found')); // get the action name  
        $controllerName = $routeMatch->getParam('controller', 'not-found');     // get the controller name  
        $controllerNameArray = explode('\\', $controllerName);    
        $controllerName = strtolower(array_pop($controllerNameArray));
                
        #################### Check Access ########################
		if (!$acl->isAllowed($role, $moduleName, $controllerName.':'.$actionName) && !$acl->isAllowed('Everyone', $moduleName, $controllerName.':'.$actionName)) {
            $router = $e->getRouter();
            $url = (empty($this->sesscontainer)) ? $router->assemble(array(), array('name' => 'auth')) : $router->assemble(array(), array('name' => 'application'));
            $response = $e->getResponse();
            $response->setStatusCode(302);
            // notify the user 
            if (!empty($this->sesscontainer)) $controller->flashMessenger()->addMessage(array('error'=>"You do not have permission to access <b>$moduleName/$controllerName:$actionName</b> please contact system administrator"));
            // redirect to login page or other page.
            $response->getHeaders()->addHeaderLine('Location', $url);
            $e->stopPropagation();
        }             
    }

    function hasRole($roles) {
        if (is_array($roles)) {
            return in_array($this->sesscontainer['usergroupinfo']['label'], $roles);
        } else {
            return $this->sesscontainer['usergroupinfo']['label'] == $roles;
        }
    }

    function initSession($e) {
        $controller = $e->getTarget();
        if (!$this->sesscontainer) $this->sesscontainer = $controller->getUserSessionInfo();
    }
}