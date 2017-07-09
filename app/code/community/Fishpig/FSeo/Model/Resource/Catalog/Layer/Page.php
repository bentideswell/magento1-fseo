<?php
/**
 * @category    Fishpig
 * @package    Fishpig_FSeo
 * @license      http://fishpig.co.uk/license.txt
 * @author       Ben Tideswell <ben@fishpig.co.uk>
 */

class Fishpig_FSeo_Model_Resource_Catalog_Layer_Page extends Mage_Core_Model_Resource_Db_Abstract
{
	/**
	 * If true, output from loading applied page will be loaded
	 * This is for debugging purposes
	 *
	 * @var bool
	 */
	protected $_debugAppliedPage = false;
	
	/**
	 * Initialize the model type
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('fseo/catalog_layer_page', 'page_id');
	}
	
	/**
	 * Get the currently applied page
	 *
	 * @return false|Fishpig_FSeo_Model_Resource_Catalog_Layer_Page
	 */
	public function getAppliedPage()
	{
		$_helper = Mage::helper('fseo/layer');

		$entityUrlKeyParts = explode('/', trim($_helper->getEntityUrlKey(), '/'));
		$objectTargetUrlKey = array();

		foreach($entityUrlKeyParts as $entityUrlKeyPart) {
			$objectTargetUrlKey[] = '(' . $entityUrlKeyPart . '|\*)';
		}
		
		$objectTargetUrlKey = '^' . implode('\/', $objectTargetUrlKey) . '$';

		$filterTargetUrlKey = array();
		$filterTargetUrlKey = false;
		$targetFilterUrlKeyPlaceholdersSql = array();
		
		if ($appliedFilters = $_helper->getAppliedFilters()) {
			foreach($appliedFilters as $appliedFilter) {
				$filterTargetUrlKey[] = '(' . implode('|', $appliedFilter->getSlugs()) . '|\*|\{\{' . $appliedFilter->getAttributeCode() . '\}\})';
			}
			
			$filterTargetUrlKey = '^' . implode('\/', $filterTargetUrlKey) . '$';
		}
		
		if ($filterTargetUrlKey !== false) {
			$targetFilterUrlKeyPlaceholdersSql = array(
				$this->_substrCountMysql('target_filter_url_key', '{{'),
				$this->_substrCountMysql('target_filter_url_key', '*')
			);
		}

		$targetObjectUrlKeyPlaceholdersSql = array(
			$this->_substrCountMysql('target_object_url_key', '{{'),
			$this->_substrCountMysql('target_object_url_key', '*')
		);
		
		$select = $this->_getReadAdapter()
			->select()
				->from($this->getMainTable(), 'page_id')
				->columns(array('target_filter_url_key_placeholders' => implode('+', $targetFilterUrlKeyPlaceholdersSql) . '+' . implode('+', $targetObjectUrlKeyPlaceholdersSql)))
				->where("target_object_url_key REGEXP ? OR target_object_url_key = '**'", $objectTargetUrlKey)
				->where('is_enabled = ?', 1)
				->columns(array('has_match_all' => new Zend_Db_Expr("IF (target_object_url_key = '**', 1, 0)")))
				->order('has_match_all ASC')
				->order('target_filter_url_key_placeholders ASC')
				->limit(1);
			
		if ($filterTargetUrlKey !== false) {
			$select->where('target_filter_url_key REGEXP ?', $filterTargetUrlKey);
		}
		else {
			$select->where('target_filter_url_key = ?', '');
		}
		
		if ($this->_debugAppliedPage) {
			echo sprintf('<p>%s</p><pre>%s</pre>', (string)$select->limit(null), print_r($this->_getReadAdapter()->fetchAll($select), true));
			exit;
		}
		
		if ($pageId =$this->_getReadAdapter()->fetchOne($select)) {
			$page = Mage::getModel('fseo/catalog_layer_page')->load($pageId);
			
			if ($page->getId()) {
				return $page;
			}
		}
		
		return false;
	}
	
	protected function _substrCountMysql($field, $target)
	{
		return new Zend_Db_Expr(
			sprintf("((LENGTH(%s) - LENGTH(REPLACE(%s, '%s', ''))) / LENGTH('%s'))", $field, $field, $target, $target)
		);
	}
	
	protected function _beforeSave(Mage_Core_Model_Abstract $object)
	{
		if (is_array($object->getStoreIds())) {
			$object->setStoreIds(implode(',', $object->getStoreIds()));
		}
		
		$object->setValues(
			serialize($object->getAttributes())
		);
		
		$object->unsAttributes();
		
		if (!$object->getPageId()) {
			$object->unsPageId();
		}
		
		if (is_array($object->getData('apply_to'))) {
			$object->setData('apply_to', implode(',', $object->getData('apply_to')));
		}
		
		return parent::_beforeSave($object);
	}


	protected function _afterLoad(Mage_Core_Model_Abstract $object)
	{
		if (!is_array($object->getStoreIds())) {
			$object->setStoreIds(explode(',', $object->getStoreIds()));
		}
		
		$object->setAttributes(
			@unserialize($object->getValues())
		);
		
		$object->unsValues();
		
		if ($object->getData('apply_to')) {
			$object->setData('apply_to', explode(',', $object->getData('apply_to')));
		}
		else {
			$object->setData('apply_to', array());
		}
		
		return parent::_afterLoad($object);
	}	
	
	public function createTemplateForRequest(Varien_Object $result)
	{
		$pageId = (int)$this->_getReadAdapter()->fetchOne(
			$this->_getReadAdapter()
				->select()
					->from($this->getMainTable(), 'page_id')
					->where('target_object_url_key=?', $result->getEntityUrlKey())
					->where('target_filter_url_key=?', $result->getFilterUrlKey())
					->where('FIND_IN_SET(?, apply_to)', $result->getEntityType())
					->where('FIND_IN_SET(?, store_ids) ', (int)Mage::app()->getStore()->getId())
					->limit(1)
		);
		
		if ($pageId === 0) {
			return Mage::getModel('fseo/catalog_layer_page')
				->setPageName($result->getUrlKey() . $result->getUrlSuffix())
				->setTargetObjectUrlKey($result->getEntityUrlKey())
				->setTargetFilterUrlKey($result->getFilterUrlKey())
				->setApplyTo(array($result->getEntityType()))
				->setIsEnabled(0)
				->setStoreIds(array((int)Mage::app()->getStore()->getId()))
				->save();
		}
		
		return false;
	}
}
