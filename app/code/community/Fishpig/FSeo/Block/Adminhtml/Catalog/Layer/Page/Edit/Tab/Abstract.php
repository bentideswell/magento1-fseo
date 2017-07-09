<?php
/**
 * @category    Fishpig
 * @package     Fishpig_FSeo
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

abstract class Fishpig_FSeo_Block_Adminhtml_Catalog_Layer_Page_Edit_Tab_Abstract extends Mage_Adminhtml_Block_Widget_Form
{
	/**
	 * Generate the form object
	 *
	 * @return $this
	 */
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('fseo_page_');
        $form->setFieldNameSuffix('page');
        
		$this->setForm($form);
		
		return parent::_prepareForm();
	}
	
	/**
	 * Retrieve the data used for the form
	 *
	 * @return array
	 */
	protected function _getFormData()
	{
		if ($page = Mage::registry('fseo_catalog_layer_page')) {
			$data = $page->getData();
			
			if (isset($data['attributes']) && is_array($data['attributes'])) {
				return array_merge($data, $data['attributes']);
			}
			
			return $data;
		}
		
		return array(
			'is_enabled' => 1,
			'store_ids' => array(0),
			'type' => Fishpig_FSeo_Model_Catalog_Layer_Page::TYPE_FIXED,
		);
	}
}
