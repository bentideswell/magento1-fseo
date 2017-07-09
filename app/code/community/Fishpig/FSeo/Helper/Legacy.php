<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_FSeo_Helper_Legacy extends Mage_Core_Helper_Abstract
{
	/**
	 * Flag that determines whether hacks have been applied
	 *
	 * @var bool
	 */
	protected $_hacksApplied = false;

	/**
	 * Classes to apply hacks for
	 *
	 * @param array
	 */
	protected $_classes = array(
		'Mage_Core_Model_Resource_Db_Abstract',
		'Mage_Core_Model_Resource_Db_Collection_Abstract',
		'Mage_Catalog_Model_Resource_Layer_Filter_Attribute',
		'Fishpig_FSeo_Model_Resource_Catalog_Eav_Mysql4_Layer_Filter_Attribute',
	);
	
	/**
	 * Apply the legacy hacks
	 *
	 * @return $this
	 */
	public function applyLegacyHacks()
	{
		if ($this->_hacksApplied) {
			return $this;
		}

		$this->_hacksApplied = true;

		$compilerPath = $this->_getCompilerPath();

		foreach($this->_classes as $class) {
			$newFile = Mage::getBaseDir() . DS . implode(DS, array('app', 'code', 'core')) . DS . str_replace('_', DS, $class) . '.php';
			
			if (!is_file($newFile)) {
				if ($compilerPath !== false) {
					$hackFile = $compilerPath . DS . 'Fishpig_FSeo_class_' . $class . '.php';
				}
				else {
					$hackFile = dirname(dirname(__FILE__)) . DS . 'class' . DS . str_replace('_', DS, $class) . '.php';
				}
				
				if (is_file($hackFile)) {
					require_once($hackFile);
				}
			}
		}
		
		return $this;
	}
	
	/**
	 * Get the compiler path
	 * If not compiled, return false
	 *
	 * @return false|string
	 */
	protected function _getCompilerPath()
	{
		return defined('COMPILER_INCLUDE_PATH') ? COMPILER_INCLUDE_PATH : false;
	}
}
