<?php
/**
 * @category    Fishpig
 * @package     Fishpig_FSeo
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_FSeo_Block_Adminhtml_Catalog_Layer_Page_Edit_Tab_Attributes extends Fishpig_FSeo_Block_Adminhtml_Catalog_Layer_Page_Edit_Tab_Abstract
{
	/**
	 * Generate the form object
	 *
	 * @return $this
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();

        $this->getForm()->setHtmlIdPrefix('fseo_page_attribute');
        $this->getForm()->setFieldNameSuffix('page[attributes]');
        
		$fieldset = $this->getForm()->addFieldset('fseo_catalog_layer_page_attributes', array(
			'legend'=> $this->__('Attributes'),
			'class' => 'fieldset-wide'
		));
		
		$fieldset->addField('name', 'text', array(
			'name' 		=> 'name',
			'label' 	=> $this->__('Name'),
			'title' 	=> $this->__('Name'),
		));
		
		$htmlConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(array(
			'add_widgets' => true,
			'add_variables' => true,
			'add_image' => true,
			'files_browser_window_url' => $this->getUrl('adminhtml/cms_wysiwyg_images/index')
		));

		$fieldset->addField('description', 'editor', array(
			'name' => 'description',
			'label' => $this->helper('adminhtml')->__('Description'),
			'title' => $this->helper('adminhtml')->__('Description'),
			'style' => 'width:100%; height:400px;',
			'config' => $htmlConfig,
		));
		
		$fieldset = $this->getForm()->addFieldset('fseo_catalog_layer_page_meta', array(
			'legend'=> $this->__('Meta Information'),
			'class' => 'fieldset-wide'
		));
		
		$fieldset->addField('page_title', 'text', array(
			'name' 		=> 'page_title',
			'label' 	=> $this->__('Page Title'),
			'title' 	=> $this->__('Page Title'),
		));
		
		$fieldset->addField('meta_description', 'editor', array(
			'name' 		=> 'meta_description',
			'label' 	=> $this->__('Description'),
			'title' 	=> $this->__('Description'),
		));
		
		$fieldset->addField('meta_keywords', 'editor', array(
			'name' 		=> 'meta_keywords',
			'label' 	=> $this->__('Meta Keywords'),
			'title' 	=> $this->__('Meta Keywords'),
		));

		$this->getForm()->setValues($this->_getFormData());
		
		return $this;
	}
}
