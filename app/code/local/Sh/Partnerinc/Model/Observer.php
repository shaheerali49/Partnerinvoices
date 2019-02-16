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

}