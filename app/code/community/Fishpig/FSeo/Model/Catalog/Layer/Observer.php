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

	/*
	 * Inject the canonical tag and remove any existing canonicals
	 *
	 *
	 * @param Varien_Event_Observer $observer
	 * @return $this
	 */
	public function injectCanonicalTagObserver(Varien_Event_Observer $observer)
	{
		if (!($page = Mage::helper('fseo/layer')->getAppliedPage())) {
			return $this;
		}

		if (!($canonicalUrl = $page->getCanonicalUrl())) {
			return $this;
		}
		
		$html = $observer->getEvent()->getFront()->getResponse()->getBody();
		
		if (strpos($html, 'canonical') !== false) {
			if (!preg_match_all('/<link[^>]+>/', $html, $matches)) {
				// Canonical exists but cannot match it
				exit('no match');
				return $this;
			}
			
			$actualMatches = array();
			
			foreach($matches[0] as $match) {
				if (strpos($match, 'canonical') !== false) {
					$actualMatches[] = $match;
				}
			}
			
			if (count($actualMatches) === 0) {
				return $this;
			}
			
			foreach($actualMatches as $existingCanonicalTag) {
				$html = str_replace($existingCanonicalTag, '', $html);
			}
		}
		
		$html = str_replace('</head>', '<link rel="canonical" href="' . $canonicalUrl . '"/></head>', $html);

		$observer->getEvent()->getFront()->getResponse()->setBody($html);
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
