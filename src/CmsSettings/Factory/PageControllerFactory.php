<?php
namespace CmsSettings\Factory;

use CmsSettings\Controller\PageController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PageControllerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $realServiceLocator = $serviceLocator->getServiceLocator();
        $service = $realServiceLocator->get('SettingsService');
        $translator = $realServiceLocator->get('translator');
        $pageform = $realServiceLocator->get('FormElementManager')->get('CmsSettings\Form\PageForm');

        return new PageController($service, $pageform, $translator);
    }
}