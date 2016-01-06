<?php
/**
* Copyright (c) 2014 Shine Software.
* All rights reserved.
*
* Redistribution and use in source and binary forms, with or without
* modification, are permitted provided that the following conditions
* are met:
*
* * Redistributions of source code must retain the above copyright
* notice, this list of conditions and the following disclaimer.
*
* * Redistributions in binary form must reproduce the above copyright
* notice, this list of conditions and the following disclaimer in
* the documentation and/or other materials provided with the
* distribution.
*
* * Neither the names of the copyright holders nor the names of the
* contributors may be used to endorse or promote products derived
* from this software without specific prior written permission.
*
* THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
* "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
* LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
* FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
* COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
* INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
* BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
* LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
* CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
* LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
* ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
* POSSIBILITY OF SUCH DAMAGE.
*
* @package Cms
* @subpackage Entity
* @author Michelangelo Turillo <mturillo@shinesoftware.com>
* @copyright 2014 Michelangelo Turillo.
* @license http://www.opensource.org/licenses/bsd-license.php BSD License
* @link http://shinesoftware.com
* @version @@PACKAGE_VERSION@@
*/


namespace Cms;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;
use Cms\Service\PageService;
use Cms\Entity\Page;
use Cms\Entity\Block;
use Cms\Entity\PageCategory;
use Zend\ModuleManager\Feature\DependencyIndicatorInterface;

class Module implements DependencyIndicatorInterface{
	
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }
    
    /**
     * Check the dependency of the module
     * (non-PHPdoc)
     * @see Zend\ModuleManager\Feature.DependencyIndicatorInterface::getModuleDependencies()
     */
    public function getModuleDependencies()
    {
    	return array('Base');
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    
    /**
     * Set the Services Manager items
     */
    public function getServiceConfig ()
    {
    	return array(
    			'factories' => array(
    					'PageService' => function  ($sm)
    					{
    						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    						$translator = $sm->get('translator');
							$SettingsService = $sm->get('SettingsService');
    						$resultSetPrototype = new ResultSet();
    						$resultSetPrototype->setArrayObjectPrototype(new Page());
    						$tableGateway = new TableGateway('cms_page', $dbAdapter, null, $resultSetPrototype);
    						$service = new \Cms\Service\PageService($tableGateway, $translator, $SettingsService);
    						return $service;
    					},
    					'PageCategoryService' => function  ($sm)
    					{
    						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    						$resultSetPrototype = new ResultSet();
    						$resultSetPrototype->setArrayObjectPrototype(new PageCategory());
    						$tableGateway = new TableGateway('cms_page_category', $dbAdapter, null, $resultSetPrototype);
    						$service = new \Cms\Service\PageCategoryService($tableGateway);
    						return $service;
    					},
    					'BlockService' => function  ($sm)
    					{
    						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
							$translator = $sm->get('translator');
							$SettingsService = $sm->get('SettingsService');
    						$resultSetPrototype = new ResultSet();
    						$resultSetPrototype->setArrayObjectPrototype(new Block());
    						$tableGateway = new TableGateway('cms_block', $dbAdapter, null, $resultSetPrototype);
    						$service = new \Cms\Service\BlockService($tableGateway, $translator, $SettingsService);
    						return $service;
    					},
    					
    					'PageForm' => function  ($sm)
    					{
    						$form = new \Cms\Form\PageForm();
    						$form->setInputFilter($sm->get('PageFilter'));
    						return $form;
    					},
    					'PageFilter' => function  ($sm)
    					{
    						return new \Cms\Form\PageFilter();
    					},
    					
    					'PageSettingsForm' => function  ($sm)
    					{
    						$form = new \CmsSettings\Form\PageForm();
    						$form->setInputFilter($sm->get('PageSettingsFilter'));
    						return $form;
    					},
    					'PageSettingsFilter' => function  ($sm)
    					{
    						return new \CmsSettings\Form\PageFilter();
    					},
    					
    					'BlockForm' => function  ($sm)
    					{
    						$form = new \Cms\Form\BlockForm();
    						$form->setInputFilter($sm->get('BlockFilter'));
    						return $form;
    					},
    					'BlockFilter' => function  ($sm)
    					{
    						return new \Cms\Form\BlockFilter();
    					},
    					
    					'PageCategoryForm' => function  ($sm)
    					{
    						$form = new \Cms\Form\PageCategoryForm();
    						$form->setInputFilter($sm->get('PageCategoryFilter'));
    						return $form;
    					},
    					'PageCategoryFilter' => function  ($sm)
    					{
    						return new \Cms\Form\PageCategoryFilter();
    					}
    					
    				),
    			);
    }
    
    
    /**
     * Get the form elements
     */
    public function getFormElementConfig ()
    {
    	return array (
    			'factories' => array (
    					'Cms\Form\Element\PageCategories' => function  ($sm)
		    					{
		    						$serviceLocator = $sm->getServiceLocator();
		    						$translator = $sm->getServiceLocator()->get('translator');
		    						$pagecategoryService = $serviceLocator->get('PageCategoryService');
		    						$element = new \Cms\Form\Element\PageCategories($pagecategoryService, $translator);
		    						return $element;
		    					},
    					'Cms\Form\Element\ParentPages' => function  ($sm)
		    					{
		    						$serviceLocator = $sm->getServiceLocator();
		    						$translator = $sm->getServiceLocator()->get('translator');
		    						$PageService = $serviceLocator->get('PageService');
		    						$element = new \Cms\Form\Element\ParentPages($PageService, $translator);
		    						return $element;
		    					},
    						),
    					);
    }
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                    __NAMESPACE__ . "Admin" => __DIR__ . '/src/' . __NAMESPACE__ . "Admin",
                    __NAMESPACE__ . "Settings" => __DIR__ . '/src/' . __NAMESPACE__ . "Settings",
                ),
            ),
        );
    }
}
