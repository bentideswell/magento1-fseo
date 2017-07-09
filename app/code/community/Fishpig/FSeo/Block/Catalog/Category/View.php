<?php
/**
 * @Author FishPig
**/

class Fishpig_FSeo_Block_Catalog_Category_View extends Mage_Catalog_Block_Category_View
{
    /**
     * Retrieve Clear Filters URL
     *
     * @return string
     */
    public function isContentMode()
    {
	    if (Mage::registry('fseo_layer_applied_filter_data')) {
		    return false;
	    }
	    
		return parent::isContentMode();
    }
}
