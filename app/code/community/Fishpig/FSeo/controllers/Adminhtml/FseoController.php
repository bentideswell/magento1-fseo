<?php
/**
 * @category    Fishpig
 * @package    Fishpig_FSeo
 * @license      http://fishpig.co.uk/license.txt
 * @author       Ben Tideswell <ben@fishpig.co.uk>
 */

class Fishpig_FSeo_Adminhtml_FseoController extends Mage_Adminhtml_Controller_Action
{
	/**
	 * Display the list of all splash pages
	 *
	 * @return void
	 */
	public function indexAction()
	{
		$this->loadLayout();
		$this->_title('FSeo');
		$this->_title('FishPig');

		$this->renderLayout();
	}
	
	/**
	 *
	 */
	public function editAction()
	{
		return $this->_redirect('*/fseo_catalog_layer/edit', array('id' => $this->getRequest()->getParam('id')));
	}
	
	/**
	 *
	 */
	 public function massDeleteAction()
	 {
		 return $this->_forward('massDelete', 'fseo_catalog_layer');
	 }
}