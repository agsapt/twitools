<?php

namespace Ags\View\Helper;

use Zend\View\Helper\AbstractHelper;

class ConfigHelper extends AbstractHelper
{	
    protected $serviceLocator;
    protected $appconfig;

    /**
     * the helper uses this function when it's called from Module::getViewHelperConfig() function
     */
    public function setConfig($config) {
        $this->appconfig = $config;
    }

    private function init() {
        if (!isset($this->serviceLocator)) $this->serviceLocator =$this->getView()->getHelperPluginManager()->getServiceLocator(); 
        if (!isset($this->appconfig)) $this->appconfig = $this->serviceLocator->get('Config')['app'];
    }

    public function __invoke($key)
    {
        $this->init();
        return $this->appconfig[$key];
    }
}