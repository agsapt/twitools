<?php

namespace Ags\View\Helper;

use Zend\View\Helper\AbstractHelper;

class UtilityHelper extends AbstractHelper {

	function __invoke() {
		return $this->getView()->getHelperPluginManager()->getServiceLocator()->get('ControllerPluginManager')->get('Utility');
	}

}