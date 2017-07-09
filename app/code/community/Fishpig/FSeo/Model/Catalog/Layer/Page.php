<?php
/**
 * @category    Fishpig
 * @package    Fishpig_FSeo
 * @license      http://fishpig.co.uk/license.txt
 * @author       Ben Tideswell <ben@fishpig.co.uk>
 */

class Fishpig_FSeo_Model_Catalog_Layer_Page extends Mage_Core_Model_Abstract
{
	/**
	 * Page type
	 *
	 * @const int
	 */
	const TYPE_FIXED = 1;
	const TYPE_TEMPLATE = 2;

	/**
	 * Initialize the model type
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('fseo/catalog_layer_page');
	}
	
	public function getName()
	{
		return $this->processVariables($this->getAttributeValue('name'));
	}
	
	public function getDescription()
	{
		return $this->processVariables($this->getAttributeValue('description'));
	}
	
	public function getPageTitle()
	{
		return $this->processVariables($this->getAttributeValue('page_title'));
	}
	
	public function getMetaDescription()
	{
		return $this->processVariables($this->getAttributeValue('meta_description'));
	}
	
	public function getMetaKeywords()
	{
		return $this->processVariables($this->getAttributeValue('meta_keywords'));
	}
	
	public function getAttributeValue($attribute)
	{
		$attributes = $this->getAttributes();
		
		return is_array($attributes) && isset($attributes[$attribute]) 
			? $attributes[$attribute]
			: null;
	}
	
	/**
	 * Process the variables inside $string
	 *
	 * @param string $string
	 * @return string
	 */
	public function processVariables($string)
	{
		$string = str_replace('}}', ' }}', $string);
		
		if (preg_match_all('/\{\{([a-z0-9_]{1,})[ ]{0,}([^\}]{1,})\}\}/i', $string, $matches)) {
			$appliedFilters = Mage::helper('fseo/layer')->getAppliedFilters();
			$objectTree = array_values(Mage::helper('fseo/layer')->getEntityTree());

			foreach($matches[1] as $key => $attribute) {
				$variable = array(
					'attribute' => $attribute,
					'type' => 'label', // Default to label but can be overridden
					'original' => $matches[0][$key],
					'position' => '*',
					'join' => null,
					'remove' => null,
				);

				if (preg_match_all('/[ ]{0,}([a-z]{1,})="([^"]{1,})"/', " " . $matches[2][$key], $pmatches)) {
					foreach($pmatches[1] as $i => $a) {
						$variable[$a] = $pmatches[2][$i];
					}
				}
				
				$replace = '';

				if ($attribute === 'name') {
					$replace = $this->getName();					
				}
				else if ($attribute === 'object') {
					if ($variable['position'] === '*' && $this->getTargetObjectUrlKey() === '**') {
						$joinedValues = array();
						
						foreach($objectTree as $object) {
							$joinedValues[] = $object->getOriginalName() ? $object->getOriginalName() : $object->getName();	
						}

						$replace = implode($variable['join'], $joinedValues);
					}
					else {
						if ($variable['position'] === '*') {
							$variable['position'] = 1;
						}

						if (isset($objectTree[$variable['position']-1])) {
							$object = $objectTree[$variable['position']-1];
							$replace =  $variable['type'] === 'label' ? ($object->getOriginalName() ? $object->getOriginalName() : $object->getName()) : $object->getData($variable['type']);
						}
					}
				}
				else if (isset($appliedFilters[$attribute])) {
					if ($variable['position'] !== '*') {
						if (isset($appliedFilters[$attribute][$variable[$position]][$type])) {
							$replace = $appliedFilters[$attribute][$variable[$position]][$type];
						}
					}
					else {
						$replace = array();

						foreach($appliedFilters[$attribute] as $appliedFilter) {
							if (isset($appliedFilter[$variable['type']])) {
								$replace[] = $appliedFilter[$variable['type']];
							}
						}

						$replacements = count($replace);
						
						if ($replacements === 0) {
							$replace = '';
						}
						else if ($replacements === 1) {
							$replace = array_shift($replace);
						}
						else if ($replacements === 2) {
							$replace = implode(' & ', $replace);
						}
						else {
							$replace = implode(', ', array_splice($replace, 0, count($replace)-1)) . ' & ' . array_pop($replace);
						}
					}
					
					if (isset($appliedFilters[$attribute][$variable['type']])) {
						$replace = $appliedFilters[$attribute][$variable['type']];
					}
				}
				
				if ($replace !== false) {
					if (isset($variable['rtrim'])) {
						$replace = rtrim($replace, $variable['rtrim']);
					}
					
					if (isset($variable['ltrim'])) {
						$replace = ltrim($replace, $variable['ltrim']);
					}

					if ($variable['remove'] !== null) {
						$replace = str_replace($variable['remove'], '', $replace);
					}

					$string = str_replace($variable['original'], $replace, $string);
				}
			}
		}	

		return $string;
	}
	
	/**
	 * Determine whether the page can be displayed for the entity type
	 *
	 * @param string $type
	 * @return bool
	 */
	 public function canDisplayOn($type)
	 {
		 $applyTo = $this->getApplyTo();
		 
		 return !$applyTo 
		 	|| in_array($type, $applyTo);
	 }
}
