<?php

class Sh_Partnerinc_Model_Partnerinc extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('partnerinc/partnerinc');
    }

    public function invoiceSplit($order)
    {
        if ($order->canInvoice()) {
            $totalItems = count($order->getAllVisibleItems());
            $firstHalf =  (int)($totalItems/2);
            $counter = 1;
            if($totalItems==1)
            {
                return;
            }
            $firstInvoiceItems = array();
            $secondInvoiceItems = array();
            foreach ($order->getAllVisibleItems() as $orderItem) {
                $item = Mage::getModel('sales/convert_order')->itemToInvoiceItem($orderItem);
                if ($counter <= $firstHalf) {
                    $firstInvoiceItems[$item->getOrderItemId()] = $orderItem->getQtyOrdered();
                    $secondInvoiceItems[$item->getOrderItemId()] = 0;
                }
                elseif ($counter>$firstHalf)
                {
                    $secondInvoiceItems[$item->getOrderItemId()] = $orderItem->getQtyOrdered();
                    $firstInvoiceItems[$item->getOrderItemId()] = 0;
                }

                $counter++;
            }

            
            try {
                $firstInvoice = Mage::getModel('sales/service_order', $order)->prepareInvoice($firstInvoiceItems);
                $firstInvoice->register();
                $firstInvoice->setEmailSent(true);
                $firstInvoice->getOrder()->setCustomerNoteNotify(true);
                $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($firstInvoice)
                    ->addObject($order)
                    ->save();
            } catch(Exception $e) {
                die($e->getMessage());
            }

            $firstInvoice->clearInstance();

            try {
                $secondInvoice = Mage::getModel('sales/service_order', $order)->prepareInvoice($secondInvoiceItems);
                $secondInvoice->register();
                $secondInvoice->setEmailSent(true);
                $secondInvoice->getOrder()->setCustomerNoteNotify(true);

                $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($secondInvoice)
                    ->addObject($order)
                    ->save();
            } catch(Exception $e) {
                die($e->getMessage());
            }


            $secondInvoice->clearInstance();
        } else{
            Mage::log('Cannot create an invoice for order ' . $order->getIncrementId(), null, 'partnerinc.log', true);
        }
    }



}