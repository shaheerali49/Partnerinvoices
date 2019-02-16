<?php



class Sh_Partnerinc_Model_Observer
{

    /*
     * Check url for partner param and store it in cookies
     */
    public function checkPartner() {

        $partner = Mage::app()->getRequest()->getParam('partner');


        /*If customer is coming from a partner*/
        if($partner!=NULL && $partner!="")
        {
            $cookieName = "partner";
            $cookieValue = $partner;
            $cookieLifeTime = 86400;
            // set cookie
            Mage::getModel("core/cookie")->set($cookieName, $cookieValue,$cookieLifeTime);

        }
    }

    /**
     * Add partner column to orders grid
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function addPartnerColumnToGrid(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        //echo $block->getNameInLayout(); exit;
        // Check whether the loaded block is the orders grid block
        if (!($block instanceof Mage_Adminhtml_Block_Sales_Order_Grid)
            || $block->getNameInLayout() != 'sales_order.grid'
        ) {
            return $this;
        }

        // Add a partner column rigth after the "Ship to Name" column
        $block->addColumnAfter('partnername', [
            'header' => $block->__('Partner name'),
            'index' => 'partnername',
            'filter_index' => 'sales_flat_order.partner',
            'type'  => 'text',
        ], 'status');

        return $this;
    }

    /**
     *
     * @param Varien_Event_Observer $observer
     */
    public function salesOrderGridCollectionLoadBefore(Varien_Event_Observer $observer)
    {
        $collection = $observer->getOrderGridCollection();
        $collection->getSelect()->
        joinLeft('sales_flat_order', 'main_table.entity_id=sales_flat_order.entity_id', array("partnername"=>"partner"));
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function savePartnerToOrder(Varien_Event_Observer $observer){
        try{
            $cookieName = "partner";
            $partner = Mage::getModel('core/cookie')->get($cookieName);
            if($partner!=NULL && $partner!="")
            {
                $order = $observer->getEvent()->getOrder();
                $order->setData('partner', $partner);
                $order->getResource()->saveAttribute($order, 'partner');
                Mage::getModel('core/cookie')->delete($cookieName);
            }


        }catch(exception $e){
            Mage::log($e->getMessage(), null, 'exception.log');
        }

    }



}