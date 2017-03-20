<?php

namespace Ags\View\Helper;

use Zend\View\Helper\AbstractHelper;

class AclRole extends AbstractHelper {

	function __invoke($roles) {
		return $this->getView()->getHelperPluginManager()->getServiceLocator()->get('ControllerPluginManager')->get('AclPlugin')->hasRole($roles);
	}

}
		