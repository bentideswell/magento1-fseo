<?php
/**
 * @category    Fishpig
 * @package    Fishpig_FSeo
 * @license      http://fishpig.co.uk/license.txt
 * @author       Ben Tideswell <ben@fishpig.co.uk>
 */

class Fishpig_FSeo_Model_Catalog_Layer_Filter_Category extends Mage_Catalog_Model_Layer_Filter_Category
{
	/**
	 * Add the caegory tree to the state block
	 *
	 * @param Zend_Controller_Request_Abstract $request,
	 * @param Mage_Core_Block_Abstract $filterBlock
	 * @return $this
	 */
	public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
	{
		parent::apply($request, $filterBlock);
		
		$_helper = Mage::helper('fseo/layer');
		
		if (Mage::getStoreConfigFlag('fseo/layer/show_applied_category_filters')) {
			if ($_helper->getEntityType() === 'catalog_category') {
				$categoryTree = array_reverse($_helper->getEntityTree());
		
				if (count($categoryTree) > 1) {
					array_shift($categoryTree);
		
					foreach($categoryTree as $object) {
						$this->getLayer()->getState()->addFilter(
							$this->_createItem($object->getName(), $object->getId())
						);
					}
				}
			}
		}
		
		return $this;
	}

	/**
	 * @return array
	**/
	protected function _getItemsData()
	{
		$transportObject = new Varien_Object();
		
		Mage::dispatchEvent('fseo_catalog_layer_filter_category_get_items_data', array(
			'filter' => $this,
			'transport' => $transportObject
		));
		
		return $transportObject->hasItemsData()
			? $transportObject->getItemsData()
			: parent::_getItemsData();
	}
	
	/**
	 * @return string
	**/
    public function getName()
    {
	    if ($this->getCategory()->getSubcategoryFilterLabel()) {
		    return $this->getCategory()->getSubcategoryFilterLabel();
	    }

        return Mage::helper('catalog')->__('Category');
    }
}
