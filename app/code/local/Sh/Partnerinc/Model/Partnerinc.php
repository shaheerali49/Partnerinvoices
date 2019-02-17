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
        // echo "here"; exit;

        $totalItems = count($order->getAllVisibleItems());
        $firstHalf = (int)($totalItems / 2);
        $counter = 1;
        if ($totalItems == 1) {
            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
            $invoice->register();
            $invoice->setEmailSent(true);
            $invoice->getOrder()->setCustomerNoteNotify(true);
            $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($order)
                ->save();
            return;
        }
        $firstInvoiceItems = array();
        $secondInvoiceItems = array();
        foreach ($order->getAllVisibleItems() as $orderItem) {
            $item = Mage::getModel('sales/convert_order')->itemToInvoiceItem($orderItem);
            if ($counter <= $firstHalf) {
                $firstInvoiceItems[$item->getOrderItemId()] = $orderItem->getQtyOrdered();
                $secondInvoiceItems[$item->getOrderItemId()] = 0;
            } elseif ($counter > $firstHalf) {
                $secondInvoiceItems[$item->getOrderItemId()] = $orderItem->getQtyOrdered();
                $firstInvoiceItems[$item->getOrderItemId()] = 0;
            }

            $counter++;
        }

        /*Split Invoices Start*/
        try {
            $firstInvoice = Mage::getModel('sales/service_order', $order)->prepareInvoice($firstInvoiceItems);
            $firstInvoice->register();
            $firstInvoice->setEmailSent(true);
            $firstInvoice->getOrder()->setCustomerNoteNotify(true);
            $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($firstInvoice)
                ->addObject($order)
                ->save();
        } catch (Exception $e) {
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
        } catch (Exception $e) {
            die($e->getMessage());
        }


        $secondInvoice->clearInstance();

        /*Split Invoices End*/

        /*Start Split Shipment*/

        try {
            $firstShipment = Mage::getModel('sales/service_order', $order)->prepareShipment($firstInvoiceItems);
            $firstShipment->register();
            $firstShipment->setEmailSent(true);
            $firstShipment->getOrder()->setCustomerNoteNotify(true);
            $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($firstShipment)
                ->addObject($order)
                ->save();
        } catch (Exception $e) {
            die($e->getMessage());
        }

        $firstShipment->clearInstance();

        try {
            $secondShipment = Mage::getModel('sales/service_order', $order)->prepareShipment($secondInvoiceItems);
            $secondShipment->register();
            $secondShipment->setEmailSent(true);
            $secondShipment->getOrder()->setCustomerNoteNotify(true);

            $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($secondShipment)
                ->addObject($order)
                ->save();
        } catch (Exception $e) {
            die($e->getMessage());
        }


        $secondShipment->clearInstance();

        /*End Shipment*/

    }


}