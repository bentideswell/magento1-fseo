<?php
/**
 * @category    Fishpig
 * @package     Fishpig_FSeo
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_FSeo_Block_Adminhtml_Catalog_Layer_Page_Edit_Tab_General extends Fishpig_FSeo_Block_Adminhtml_Catalog_Layer_Page_Edit_Tab_Abstract
{
	/**
	 * Generate the form object
	 *
	 * @return $this
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();

		$fieldset = $this->getForm()->addFieldset('fseo_catalog_layer_page_general', array(
			'legend'=> $this->__('Page Information'),
			'class' => 'fieldset-wide'
		));
		
		$fieldset->addField('page_name', 'text', array(
			'name' 		=> 'page_name',
			'label' 	=> $this->__('Name'),
			'title' 	=> $this->__('Name'),
			'required'	=> true,
			'class'		=> 'required-entry',
			'note' => 'This is an internal name and not the category name',
		));
		
		$fieldset->addField('target_object_url_key', 'text', array(
			'name' 		=> 'target_object_url_key',
			'label' 	=> $this->__('Target Object URL Key'),
			'title' 	=> $this->__('Target Object URL Key'),
			'required'	=> true,
			'class'		=> 'required-entry',
			'note' => 'This can include variables in the format: {{attribute_code}}',
		));
		
		$fieldset->addField('target_filter_url_key', 'text', array(
			'name' 		=> 'target_filter_url_key',
			'label' 	=> $this->__('Target Filter URL Key'),
			'title' 	=> $this->__('Target Filter URL Key'),
			'required'	=> true,
			'class'		=> 'required-entry',
			'note' => 'This can include variables in the format: {{attribute_code}}',
		));

		/*
		$field = $fieldset->addField('type_id', 'select', array(
			'name' => 'type_id',
			'label' => Mage::helper('cms')->__('Type'),
			'title' => Mage::helper('cms')->__('Type'),
			'required' => true,
			'values' => Mage::getSingleton('fseo/system_config_source_catalog_layer_page_type')->toOptionArray(),
		));
		*/
		
		if (!Mage::app()->isSingleStoreMode()) {
			$field = $fieldset->addField('store_ids', 'multiselect', array(
				'name' => 'store_ids[]',
				'label' => Mage::helper('cms')->__('Store View'),
				'title' => Mage::helper('cms')->__('Store View'),
				'required' => true,
				'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
			));

			$renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
			
			if ($renderer) {
				$field->setRenderer($renderer);
			}
		}
		else {
			if (($page = Mage::registry('fseo_catalog_layer_page')) !== null) {
				$page->setStoreId(Mage::app()->getStore()->getId());
			}
		}

		$fieldset->addField('apply_to', 'multiselect', array(
			'name' => 'apply_to[]',
			'label' => Mage::helper('cms')->__('Apply To'),
			'title' => Mage::helper('cms')->__('Apply To'),
			'required' => true,
			'values' => array_merge(
				array(array('value' => '', 'label' => Mage::helper('adminhtml')->__('All'))), 
				Mage::getSingleton('fseo/system_config_source_catalog_layer_entity')->toOptionArray()),
		));
		
		$fieldset->addField('is_enabled', 'select', array(
			'name' => 'is_enabled',
			'title' => $this->__('Enabled'),
			'label' => $this->__('Enabled'),
			'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
		));

		$this->getForm()->setValues($this->_getFormData());
		
		return $this;
	}
}
