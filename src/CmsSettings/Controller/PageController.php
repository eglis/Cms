<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace CmsSettings\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class PageController extends AbstractActionController
{
	protected $recordService;
    protected $pageform;
    protected $translator;

	public function __construct(\Base\Service\SettingsServiceInterface $recordService, \CmsSettings\Form\PageForm $pageform, $translator)
	{
        $this->recordService = $recordService;
        $this->pageform = $pageform;
        $this->translator = $translator;
	}
	
    public function indexAction ()
    {
    	$formData = array();

		// Get the custom settings of this module: "Cms"
		$records = $this->recordService->findByModule('Cms');
		
		if(!empty($records)){
			foreach ($records as $record){
				$formData[$record->getParameter()] = $record->getValue(); 
			}
		}
		
		// Fill the form with the data
		$this->pageform->setData($formData);
		
    	$viewModel = new ViewModel(array (
    			'form' => $this->pageform,
    	));
    
    	$viewModel->setTemplate('cms-settings/page/index');
    	return $viewModel;
    }
	
    public function processAction ()
    {
    	
    	if (! $this->request->isPost()) {
    		return $this->redirect()->toRoute('zfcadmin/cmspages/settings');
    	}
    	
    	try{
	    	$settingsEntity = new \Base\Entity\Settings();
	    	
	    	$post = $this->request->getPost();
            $this->pageform->setData($post);
	    	
	    	if (!$this->pageform->isValid()) {
	    	
	    		// Get the record by its id
	    		$viewModel = new ViewModel(array (
	    				'error' => true,
	    				'form' => $this->pageform,
	    		));
	    		$viewModel->setTemplate('cms-settings/page/index');
	    		return $viewModel;
	    	}
	    	
	    	$data = $this->pageform->getData();
	    	
	    	// Cleanup the custom settings
	   		$this->recordService->cleanup('Cms');
	    	
	    	foreach ($data as $parameter => $value){
	    		if($parameter == "submit"){
	    			continue;
	    		}
	
	    		$settingsEntity->setModule('Cms');
	    		$settingsEntity->setParameter($parameter);
	    		$settingsEntity->setValue($value);
	    		$this->recordService->save($settingsEntity); // Save the data in the database
	    		
	    	}
	    	
	    	$this->flashMessenger()->setNamespace('success')->addMessage($this->translator->translate('The information have been saved.'));
    		
    	}catch(\Exception $e){
    		$this->flashMessenger()->setNamespace('error')->addMessage($e->getMessage());
    	}
    	
    	return $this->redirect()->toRoute('zfcadmin/cmspages/settings');
    }
}
