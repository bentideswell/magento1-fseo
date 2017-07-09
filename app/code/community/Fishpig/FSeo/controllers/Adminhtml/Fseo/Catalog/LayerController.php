<?php
/**
 * @category    Fishpig
 * @package    Fishpig_FSeo
 * @license      http://fishpig.co.uk/license.txt
 * @author       Ben Tideswell <ben@fishpig.co.uk>
 */

class Fishpig_FSeo_Adminhtml_Fseo_Catalog_LayerController extends Mage_Adminhtml_Controller_Action
{
	/**
	 *
	 * @return void
	 */
	public function indexAction()
	{
		return $this->_redirect('*/fseo');
	}
	
	/**
	 * This is just a wrapper for the edit action
	 *
	 * @return void
	 */
	public function newAction()
	{
		$this->_forward('edit');
	}
	
	/**
	 * Edit an existing page
	 *
	 * @return void
	 */
	public function editAction()
	{
		$titles = array(
			'FishPig',
			'FSeo',
		);

		if (($page = $this->_getPage()) !== false) {
			$titles[] = $this->_title($page->getPageName());
		}
		else {
			$titles[] = Mage::helper('cms')->__('New Page');
		}

		$this->loadLayout();
		
		foreach($titles as $title) {
			$this->_title($title);
		}
			
		$this->renderLayout();
	}

	/**
	 * Save a page
	 *
	 * @return void
	 */
	public function saveAction()
	{
		if ($data = $this->getRequest()->getPost('page')) {
			$page = Mage::getModel('fseo/catalog_layer_page')
				->setData($data)
				->setId($this->getRequest()->getParam('id', null));

			try {
				$page->save();

				$this->_getSession()->addSuccess(Mage::helper('cms')->__('The page has been saved.'));
			}
			catch (Exception $e) {
				$this->_getSession()->addError($this->__($e->getMessage()));
			}
				
			if ($page->getId() && $this->getRequest()->getParam('back', false)) {
				$this->_redirect('*/*/edit', array('id' => $page->getId()));
				return;
			}
		}
		else {
			$this->_getSession()->addError($this->__('There was no data to save.'));
		}

		$this->_redirect('*/fseo');
	}
	
	/**
	 * Delete the selected spash page
	 *
	 * @return void
	 */
	public function deleteAction()
	{
		if ($objectId = $this->getRequest()->getParam('id')) {
			$object = Mage::getModel('fseo/catalog_layerpage')->load($objectId);
			
			if ($object->getId()) {
				try {
					$object->delete();
					$this->_getSession()->addSuccess($this->__('The page was deleted.'));
				}
				catch (Exception $e) {
					$this->_getSession()->addError($e->getMessage());
				}
			}
		}

		$this->_redirect('*/fseo');
	}
	
	/**
	 * Retrieve the current page
	 *
	 * @return false|Fishpig_FSeo_Model_Catalog_Layer_Page
	 */
	protected function _getPage()
	{
		if (($page = Mage::registry('fseo_catalog_layer_page')) !== null) {
			return $page;
		}

		$page = Mage::getModel('fseo/catalog_layer_page')->load($this->getRequest()->getParam('id', 0));

		if ($page->getId()) {
			Mage::register('fseo_catalog_layer_page', $page);
			return $page;
		}
		
		return false;
	}
	
	public function massDeleteAction()
	{
		$pageIds = $this->getRequest()->getParam('page');

		if (!is_array($pageIds)) {
			$this->_getSession()->addError($this->__('Please select template(s).'));
		}
		else {
			if (!empty($pageIds)) {
				try {
					foreach ($pageIds as $pageId) {
						$page = Mage::getSingleton('fseo/catalog_layer_page')->load($pageId);
						
						if ($page->getId()) {
							$page->delete();
						}
					}
					
					$this->_getSession()->addSuccess($this->__('Total of %d record(s) have been deleted.', count($pageIds)));
				}
				catch (Exception $e) {
					$this->_getSession()->addError($e->getMessage());
				}
			}
		}
		
		$this->_redirect('*/fseo');
	}
	
	/**
	 * Ajax action for template grid
	 *
	 * @return void
	 */
	public function gridAction()
	{
		$this->getResponse()
			->setBody(
				$this->getLayout()->createBlock('fseo/adminhtml_catalog_layer_page_grid')->toHtml()
			);
	}
}