<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright  Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Layered navigation state
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Fishpig_FSeo_Block_Catalog_Layer_State extends Mage_Catalog_Block_Layer_State
{
    /**
     * Retrieve Clear Filters URL
     *
     * @return string
     */
    public function getClearUrl()
    {
		$_helper = Mage::helper('fseo/layer');

		if ($_helper->isEntityTypeEnabled()) {
			// If category, get parent category URL for remove
			if ($_helper->getEntityType() === 'catalog_category') {
				if ($_entity = $_helper->getEntity()) {
					while($_entity && $_entity->getLevel() > 2) {
						$_entity = $_entity->getParentCategory();
					}
					
					if ($_entity) {
						return $_entity->getUrl();
					}
				}
			}
			
			return $_helper->getUrl(
				$_helper->getEntityUrlKey() . $_helper->getUrlSuffix()
			);
		}
		
		return parent::getClearUrl();
    }
}
