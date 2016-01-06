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
* @subpackage Service
* @author Michelangelo Turillo <mturillo@shinesoftware.com>
* @copyright 2014 Michelangelo Turillo.
* @license http://www.opensource.org/licenses/bsd-license.php BSD License
* @link http://shinesoftware.com
* @version @@PACKAGE_VERSION@@
*/

namespace Cms\Service;

use Cms\Entity\Block;
use Zend\Db\TableGateway\TableGateway;
use Zend\Stdlib\Hydrator\ClassMethods;

class BlockService implements BlockServiceInterface
{
	protected $tableGateway;
	protected $translator;
	protected $settings;
	
	public function __construct(TableGateway $tableGateway,
								\Zend\Mvc\I18n\Translator $translator,
								\Base\Service\SettingsServiceInterface $settings
	){
		$this->tableGateway = $tableGateway;
		$this->translator = $translator;
		$this->settings = $settings;
	}
	
    /**
     * @inheritDoc
     */
    public function findAll()
    {
    	$records = $this->tableGateway->select();
        return $records;
    }

    /**
     * @inheritDoc
     */
    public function find($id)
    {
    	if(!is_numeric($id)){
    		return false;
    	}
    	$rowset = $this->tableGateway->select(array('id' => $id));
    	$row = $rowset->current();
    	
    	return $row;
    }

    /**
     * @inheritDoc
     */
    public function findByPlaceholder($placeholder, $locale = "en_US")
    {
		$myRecord = null;
    	$records = $this->tableGateway->select(function (\Zend\Db\Sql\Select $select) use ($placeholder, $locale){
    		$select->join('base_languages', 'language_id = base_languages.id', array ('locale', 'base', 'language'), 'left');
    		$select->where(array('placeholder' => $placeholder));
    	});

		$debug = $this->settings->getValueByParameter("Cms", "debug");

		if ($records->count()){
			foreach ($records as $record){
				if ($record->locale != $locale) {
					if($record->base == 1) {
						$myRecord = $record;
						$myContent = $myRecord->getContent();
						if ($debug) {
							$message = sprintf($this->translator->translate('The placeholder %s%s%s has not been found into the selected language. %s version is shown.'), "<strong>", $placeholder, "</strong>", $myRecord->language);
							$message = "\n<div class='text-center text-muted'>$message</div>";
							$myRecord->setContent($myContent . $message);
						} else {
							$myRecord->setContent($myContent);
						}
					}
				}else{
					return $record;
				}
			}
		}
    	return $myRecord;
    }

    /**
     * @inheritDoc
     */
    public function delete($id)
    {
    	$this->tableGateway->delete(array(
    			'id' => $id
    	));
    }

    /**
     * @inheritDoc
     */
    public function save(\Cms\Entity\Block $record)
    {
    	$hydrator = new ClassMethods(true);
        
        // extract the data from the object
        $data = $hydrator->extract($record);
        
        $id = (int) $record->getId();
    	    
    	if ($id == 0) {
    		unset($data['id']);
    		$data['createdat'] = date('Y-m-d H:i:s');
    		$data['updatedat'] = date('Y-m-d H:i:s');
    		$this->tableGateway->insert($data); // add the record
    		$id = $this->tableGateway->getLastInsertValue();
    	} else {
    		$rs = $this->find($id);
    		if (!empty($rs)) {
    			$data['updatedat'] = date('Y-m-d H:i:s');
    			unset( $data['createdat']);
    			$this->tableGateway->update($data, array (
    					'id' => $id
    			));
    		} else {
    			throw new \Exception('Record ID does not exist');
    		}
    	}
        
        $record = $this->find($id);
        return $record;
    }
}