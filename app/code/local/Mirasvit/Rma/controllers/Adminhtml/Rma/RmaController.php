<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   RMA
 * @version   2.1.0-beta
 * @build     1359
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_Rma_Adminhtml_Rma_RmaController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()->_setActiveMenu('rma');

        return $this;
    }

    public function _redirect($route, $params = array())
    {
        if (Mage::getBaseUrl() != Mage::getModel('core/url')->getBaseUrl(array('_store' => 0))) {
            $params['_store'] = 0;
            $this->_redirectUrl(Mage::helper('adminhtml')->getUrl($route, $params));

            return;
        }
        parent::_redirect($route, $params);
    }

    public function indexAction()
    {
        $this->_title($this->__('RMA'));
        $this->_initAction();
        $this->_addContent($this->getLayout()
            ->createBlock('rma/adminhtml_rma'));
        $this->renderLayout();
    }

    public function addAction()
    {
        $this->_title($this->__('New RMA'));

        $rma = $this->_initRma();
        $ordersId = $this->getRequest()->getParam('orders_id');

        if ($ordersId) {
            if (!Mage::helper('rma')->isReturnAllowed($ordersId)) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('According to RMA rules, we should not allow RMA for this order'));
            }
        }

        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $rma->setData($data);
        }

        $this->_initAction();
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('RMA  Manager'),
                Mage::helper('adminhtml')->__('RMA Manager'), $this->getUrl('*/*/'));
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Add RMA '), Mage::helper('adminhtml')->__('Add RMA'));

        $this->getLayout()
            ->getBlock('head')
            ->setCanLoadExtJs(true);

        if ($ordersId) {
            $rma->initFromOrder($ordersId);
            $this->_addContent($this->getLayout()->createBlock('rma/adminhtml_rma_edit'));
        } else {
            $this->_addContent($this->getLayout()->getBlock('rma_adminhtml_rma_create'));
        }
        $this->renderLayout();
    }

    public function editAction()
    {
        $rma = $this->_initRma();

        if (!Mage::helper('rma/fedex')->isEnabled() && Mage::getSingleton('rma/config')->getFedexFedexEnable($rma->getStore())) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('FedEx is incorrectly set up. Check your key/password and account/meter credentials at Configuration -> Sales -> Shipping Methods -> FedEx.'));
        }

        if ($rma->getId()) {
            $this->_title($this->__('RMA #%s', $rma->getIncrementId()));
            $this->_initAction();
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('RMA'),
                    Mage::helper('adminhtml')->__('RMA'), $this->getUrl('*/*/'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Edit RMA '),
                    Mage::helper('adminhtml')->__('Edit RMA '));

            $this->getLayout()
                ->getBlock('head')
                ->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('rma/adminhtml_rma_edit'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('The rma does not exist.'));
            $this->_redirect('*/*/');
        }
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $items = $data['items'];
            unset($data['items']);
            unset($data['comment']);
            try {
                $isEmpty = true;
                foreach ($items as $itemList) {
                    foreach ($itemList as $item) {
                        if ((int) $item['qty_requested'] > 0) {
                            $isEmpty = false;
                            break;
                        }
                    }
                }
                if ($isEmpty) {
                    throw new Mage_Core_Exception("Please, add order items to the RMA (set 'Qty to Return')");
                }

                $rma = Mage::helper('rma/process')->createOrUpdateRmaFromPost($data, $items);

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('RMA was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $rma->getId()));

                    return;
                }
                $this->_redirect('*/*/');

                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                if ($this->getRequest()->getParam('id')) {
                    $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                } else {
                    $this->_redirect('*/*/add', array('orders_id' => $this->getRequest()->getParam('order_id')));
                }

                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Unable to find rma to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $rma = Mage::getModel('rma/rma');

                $rma->setId($this->getRequest()
                    ->getParam('id'))
                    ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__('RMA was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()
                    ->getParam('id'), ));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massSelectOrdersAction()
    {
        $ids = $this->getRequest()->getParam('selected_orders');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select order(s)'));
        } else {
            $this->_redirect('*/*/add', array('orders_id' => implode(',', $ids)));

            return;
        }
        // Proper redirect if mass action was conducted in Tab Mode - see in Mirasvit_Rma_Block_Adminhtml_Rma_Grid::_prepareMassaction()
        if ($this->getRequest()->getParam('back_url')) {
            $backUrl = base64_decode(strtr($this->getRequest()->getParam('back_url'), '-_,', '+/='));
            $this->_redirectUrl($backUrl);

            return;
        }
        $this->_redirect('*/*/add');
    }

    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam('rma_id');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select rma(s)'));
        } else {
            try {
                foreach ($ids as $id) {
                    /** @var Mirasvit_Rma_Model_Rma $rma */
                    $rma = Mage::getModel('rma/rma')
                        ->setIsMassDelete(true)
                        ->load($id);
                    $rma->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($ids)
                    )
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        // Proper redirect if mass action was conducted in Tab Mode - see in Mirasvit_Rma_Block_Adminhtml_Rma_Grid::_prepareMassaction()
        if ($this->getRequest()->getParam('back_url')) {
            $backUrl = base64_decode(strtr($this->getRequest()->getParam('back_url'), '-_,', '+/='));
            $this->_redirectUrl($backUrl);

            return;
        }
        $this->_redirect('*/*/index');
    }

    public function _initRma()
    {
        $rma = Mage::getModel('rma/rma');
        if ($this->getRequest()->getParam('id')) {
            $rma->load($this->getRequest()->getParam('id'));
        }
        if ($ticketId = (int) $this->getRequest()->getParam('ticket_id')) {
            $rma->setTicketId($ticketId);
        }

        Mage::register('current_rma', $rma);

        return $rma;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('rma/rma');
    }

    /************************/

    public function convertTicketAction()
    {
        $ticket = Mage::getModel('helpdesk/ticket')->load($this->getRequest()->getParam('id'));
        $this->_redirect('*/*/add', array('order_id' => $ticket->getOrderId(), 'ticket_id' => $ticket->getId()));
    }

    /**
     * Export rma grid to CSV format.
     */
    public function exportCsvAction()
    {
        $fileName = 'rma.csv';
        $content = $this->getLayout()->createBlock('rma/adminhtml_rma_grid')
            ->getCsvFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export rma grid to XML format.
     */
    public function exportXmlAction()
    {
        $fileName = 'rma.xml';
        $content = $this->getLayout()->createBlock('rma/adminhtml_rma_grid')
            ->getExcelFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function createReplacementAction()
    {
        //        $rma = Mage::getModel('rma/rma')->load((int) $this->getRequest()->getParam('rma_id'));
//        try {
//            Mage::helper('rma/order')->createReplacementOrder($rma);
//            Mage::getSingleton('adminhtml/session')->addSuccess(
//                Mage::helper('adminhtml')->__('Replacement Order is created')
//            );
//        } catch (Mage_Core_Exception $e) {
//            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
//        }
        $this->_redirect('*/*/edit', array('id' => $rma->getId()));
    }

//    public function creditmemoAction()
//    {
//        $rma = Mage::getModel('rma/rma')->load((int) $this->getRequest()->getParam('rma_id'));
//        try {
//            Mage::helper('rma/order')->createCreditMemo($rma);
//            Mage::getSingleton('adminhtml/session')->addSuccess(
//                Mage::helper('adminhtml')->__('Credit Memo is created')
//            );
//        } catch (Mage_Core_Exception $e) {
//            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
//        }
//        $this->_redirect('*/*/edit', array('id' => $rma->getId()));
//    }

    public function markReadAction()
    {
        $rma = Mage::getModel('rma/rma')->load((int) $this->getRequest()->getParam('rma_id'));
        try {
            $isRead = (int) $this->getRequest()->getParam('is_read');
            $rma->setIsAdminRead($isRead)->save();
            if ($isRead) {
                $message = Mage::helper('adminhtml')->__('Marked as read');
            } else {
                $message = Mage::helper('adminhtml')->__('Marked as unread');
            }
            Mage::getSingleton('adminhtml/session')->addSuccess($message);
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/edit', array('id' => $rma->getId()));
    }

    /**
     *  Controller function to allow users create FedEx Label from popup dialog.
     */
    public function createFedExLabelAction()
    {
        $rma = Mage::getModel('rma/rma')->load((int) $this->getRequest()->getParam('rma_id'));
        $data = json_decode($this->getRequest()->getParam('data'));
        $fedexParams = Mage::helper('rma/fedex')->jsonToArray($data);
        $result = Mage::helper('rma/fedex')->createFedexLabel($rma, $fedexParams);
        $this->getResponse()->setHeader('status', $result['status']);
        if ($result['status'] == 'success') {
            $this->_getSession()->addSuccess(Mage::helper('rma')->__('FedEx Label was successfully created!'));
            $this->getResponse()->setBody(Mage::helper('adminhtml')->getUrl('*/*/edit', array('id' => $rma->getId())));
        } else {
            $this->getResponse()->setBody(implode('<br>', $result['errata']));
        }
    }

    /**
     *  Controller function to allow users direct download FedEx Label after generation.
     */
    public function downloadFedExLabelAction()
    {
        $label = Mage::getModel('rma/fedex_label')->load($this->getRequest()->getParam('label_id'));
        if ($label) {
            $this->getResponse()->clearHeaders();
            $this->getResponse()
                ->setHttpResponseCode(200)
                ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
                ->setHeader('Pragma', 'public', true)
                ->setHeader('Content-Disposition', 'attachment; filename=fedexlabel_'.$label->getTrackNumber().'.pdf')
                ->setHeader('Content-type', 'application/x-pdf');
            $this->getResponse()->sendHeaders();
            $this->getResponse()->clearBody();
            echo $label->getLabelBody();
        }
    }
}
