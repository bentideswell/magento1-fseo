<?php
/**
 * @category    Fishpig
 * @package    Fishpig_FSeo
 * @license      http://fishpig.co.uk/license.txt
 * @author       Ben Tideswell <ben@fishpig.co.uk>
 */

class Fishpig_FSeo_Block_Layout extends Mage_Core_Block_Abstract
{
	/**
	 *
	 *
	 * @return $this
	 */
	protected function _prepareLayout()
	{
		if (!($entity = Mage::helper('fseo/layer')->getEntity())) {
			return $this;
		}

		if (!Mage::registry('fseo_layer_applied_filter_data')) {
			return $this;
		}

		$_helper = Mage::helper('fseo/layer');
		
		if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) {
			if ($_helper->getEntityType() === 'catalog_category') {
				$breadcrumbs->addCrumb('category' . $entity->getId(), array(
					'link' => $entity->getOriginalUrl() ? $entity->getOriginalUrl() : $entity->getUrl(), 
					'label' => $entity->getName(),
				));
			}
			else if ($_helper->getEntityType() === 'attributeSplash_page') {
				// Stop PageController.php for Attribute Splash Pages including the page breadcrumb
				$breadcrumbs->setSkipSplashPageCrumb(true);
				
				// Add placeholders for home and Splash Group breadcrumb
				$breadcrumbs->addCrumb('home', array());
				$breadcrumbs->addCrumb('splash_group', array());
				
				$breadcrumbs->addCrumb('splash_page', array(
					'link' => $entity->getUrl(), 
					'label' => $entity->getName(),
					'title' => $entity->getName(),
				));
			}

			if ($appliedFilters = $_helper->getAppliedFilters()) {
				$count = count($appliedFilters);
	
				foreach($appliedFilters as $appliedFilter) {
					$breadcrumbs->addCrumb('fseo' . $appliedFilter->getSlug(), array(
						'link' => --$count > 0 ? $appliedFilter->getUrl() : null, 
						'label' => $this->escapeHtml($appliedFilter->getLabel()),
					));
				}
			}
		}

		$entity->setOriginalName($entity->getDisplayName() ? $entity->getDisplayName() : $entity->getName());
			
		if ($rootBlock = $this->getLayout()->getBlock('root')) {
			$rootBlock->addBodyClass('fseo');
		}
		
		if ($appliedFilters = $_helper->getAppliedFilters()) {
			$filters = array();

			foreach($appliedFilters as $appliedFilter) {
				$filters[] = $appliedFilter->getLabel();
			}
			
			$toReplace = array(
				implode($this->getNameGlue(), $filters),
				$entity->getDisplayName() ? $entity->getDisplayName() : $entity->getName(),
			);
			
			$toFind = array(
				'{filters}',
				'{entity}',
			);
			
			$defaultTitle = str_replace($toFind, $toReplace, Mage::getStoreConfig('fseo/layer/default_title'));
			$defaultMetaDescription = str_replace($toFind, $toReplace, Mage::getStoreConfig('fseo/layer/default_meta_description'));
			$defaultEntityName = str_replace($toFind, $toReplace, Mage::getStoreConfig('fseo/layer/default_entity_name'));
			$defaultEntityDescription = str_replace($toFind, $toReplace, Mage::getStoreConfig('fseo/layer/default_entity_description'));
			
			$entity->setName($defaultTitle);
			$entity->setDisplayName($defaultTitle); // Attribute Splash Page legacy
			$entity->setDescription($defaultEntityDescription);

			if ($headBlock = Mage::getSingleton('core/layout')->getBlock('head')) {
				if ($defaultTitle) {
					$headBlock->setTitle($defaultTitle);
				}
				
				if ($defaultMetaDescription) {
					$headBlock->setDescription($defaultMetaDescription);
				}
			}
		}
	
		if ($page = $_helper->getAppliedPage()) {
			if ($rootBlock) {
				$rootBlock->addBodyClass('fseo-page-' . $page->getId());
			}

			if ($page->getName()) {
				$entity->setName($page->getName());
				$entity->setDisplayName($page->getName()); // Attribute Splash Page legacy
			}
			
			if ($page->getDescription()) {
				$entity->setDescription($page->getDescription());
			}
			
			if ($headBlock = Mage::getSingleton('core/layout')->getBlock('head')) {
				if ($page->getPageTitle()) {
					$headBlock->setTitle(strip_tags($page->getPageTitle()));
				}
				
				if ($page->getMetaDescription()) {
					$headBlock->setDescription(strip_tags($page->getMetaDescription()));
				}
				
				if ($page->getMetaKeywords()) {
					$headBlock->setKeywords(strip_tags($page->getMetaKeywords()));
				}
			}
		}
				
		return $this;
	}
	
	/**
	 * @return string
	**/
	public function getNameGlue()
	{
		return !$this->hasNameGlue() ? ' & ' : $this->_getData('name_glue');
	}
}
