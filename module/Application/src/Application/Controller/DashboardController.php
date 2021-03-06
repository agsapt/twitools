<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ZendService\Twitter\Twitter;
use Ags\Controller\AgsController;

class DashboardController extends AgsController
{
    public function indexAction()
    {
        $config = $this->getServiceLocator()->get('Config');
        $twitter = new Twitter($config['twitter']);
        $response = $twitter->account->verifyCredentials();
        if (!$response->isSuccess()) {
            die('Something is wrong with my credentials!');
        }

        // Search for something:
        $response = $twitter->search->tweets('823830117482954752');
        $this->setupLayout('layout/landing');
        return new ViewModel(array('tweets'=>$response->toValue()));
    }
}
