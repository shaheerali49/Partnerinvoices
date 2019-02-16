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
            $firstInvoice = array();
            $secondInvoice = array();
            foreach ($order->getAllVisibleItems() as $item) {
                if ($counter <= $firstHalf) {
                    $firstInvoice[$item->getId()] = $item->getQtyOrdered();
                }
                elseif ($counter>$firstHalf)
                {
                    $secondInvoice[$item->getId()] = $item->getQtyOrdered();
                }

                $counter++;
            }

            //echo "<pre>";
            //print_r($firstInvoice);
            //print_r($secondInvoice);

            //exit;


            if (count($firstInvoice)>0) {
                $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice($firstInvoice);
                $this->invoiceSplitExecute($invoice);
            }

            if (count($secondInvoice)>0) {
                $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice($secondInvoice);
                $this->invoiceSplitExecute($invoice);
            }

        } else{
            Mage::log('Cannot create an invoice for order ' . $order->getIncrementId(), null, 'partnerinc.log', true);
        }
    }

    public function invoiceSplitExecute($invoice)
    {
        if ($invoice) {

            $invoice->getOrder()->setIsInProcess(true);

            $invoice->register();
            $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($invoice->getOrder());
            $transactionSave->save();
            $invoice->getOrder()->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true)->save();

        }
    }

}