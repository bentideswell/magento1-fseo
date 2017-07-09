<?php
/**
 * @category    Fishpig
 * @package    Fishpig_FSeo
 * @license      http://fishpig.co.uk/license.txt
 * @author       Ben Tideswell <ben@fishpig.co.uk>
 */

class Fishpig_FSeo_Model_Catalog_Layer_Observer
{
	/**
	 * Correctly set the canonical URL with applied filters for the category
	 * Set any other custom data to category
	 *
	 * @param Varien_Event_Observer $observer
	 * @return $this
	 */
	public function catalogControllerCategoryInitAfterObserver(Varien_Event_Observer $observer)
	{
		if (Mage::getStoreConfigFlag('fseo/layer/try_to_redirect')) {
			if ($newUrl = $this->_getHelper()->getNewUrlForCurrentUrl()) {
				header('Location: ' . $newUrl, true, 301);
				exit;
			}
		}

		$_category = $observer->getEvent()->getCategory();
		$_category->setOriginalUrl($_category->getUrl());
		$_category->setUrl($this->_getHelper()->getUrl());

		return $this;
	}
	
	/**
	 * Retrieve the helper object
	 *
	 * @return Fishpig_FSeo_Helper_Layer
	 */
	protected function _getHelper()
	{
		return Mage::helper('fseo/layer');
	}
}
