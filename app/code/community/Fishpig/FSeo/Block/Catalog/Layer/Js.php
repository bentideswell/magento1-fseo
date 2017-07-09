<?php
/**
 * @category    Fishpig
 * @package    Fishpig_FSeo
 * @license      http://fishpig.co.uk/license.txt
 * @author       Ben Tideswell <ben@fishpig.co.uk>
 */

class Fishpig_FSeo_Block_Catalog_Layer_Js extends Mage_Core_Block_Text
{
	protected function _beforeToHtml()
	{
		if (!$this->isEnabled()) {
			return null;
		}

		$output = array(
			'<script type="text/javascript">',
			'//<![CDATA[',
			sprintf("var FSeoCatalogLayer = new FishPig.FSeo.Catalog.Layer('%s');", $this->getLayeredNavigationHtmlId()),
		);
		
		
		if ($filters = Mage::helper('fseo/layer')->getLayer()->getState()->getFilters()) {
			foreach($filters as $filter) {
				if ($filter->getValue()) {
					$output[] = sprintf("FSeoCatalogLayer.addActiveUrl('%s');", $filter->getRemoveUrl());
				}
			}
		}
		
		$output[] = 'FSeoCatalogLayer.run();';
		$output[] = '//]]>';
		$output[] = '</script>';
		
		$this->setText(implode("\n", $output));

		return parent::_beforeToHtml();
	}

	public function isEnabled()
	{
		$_helper = Mage::helper('fseo/layer');

		return $_helper->isEntityTypeEnabled($_helper->getEntityType());
	}
}
