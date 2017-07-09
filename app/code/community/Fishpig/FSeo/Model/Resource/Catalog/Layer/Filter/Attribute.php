<?php
/**
 * @category    Fishpig
 * @package    Fishpig_FSeo
 * @license      http://fishpig.co.uk/license.txt
 * @author       Ben Tideswell <ben@fishpig.co.uk>
 */

class Fishpig_FSeo_Model_Resource_Catalog_Layer_Filter_Attribute extends Mage_Catalog_Model_Resource_Layer_Filter_Attribute
{
	public function applyFilterToCollection($filter, $value)
	{
		$collection = $filter->getLayer()->getProductCollection();
		$attribute  = $filter->getAttributeModel();
		$connection = $this->_getReadAdapter();
		$tableAlias = $attribute->getAttributeCode() . '_idxx';

		$conditions = array(
			"{$tableAlias}.entity_id = e.entity_id",
			$connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
			$connection->quoteInto("{$tableAlias}.store_id = ?", $collection->getStoreId())
		);
			
		if (count($value) === 1) {
			$conditions[] = $connection->quoteInto("{$tableAlias}.value = ?", $value[0]);
		}
		else {
			$conditions[] = $connection->quoteInto("{$tableAlias}.value IN (?)", $value);
		}

		$collection->getSelect()->distinct()->join(
			array($tableAlias => $this->getMainTable()),
			implode(' AND ', $conditions),
			array()
		);

		return $this;
	}
}
