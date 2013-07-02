<?php
require 'PPSObjects.php';
require 'ppsProxy.php';
require 'RetrieveData.php';

class PPS_Paymentmodule_Model_CleanPaymentMethod extends Mage_Payment_Model_Method_Cc
{
    protected $_code = 'paymentmodule';

    protected $token;
    protected $tokenSecret;
    protected $_isGateway               = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = true;
    protected $_canVoid                 = true;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = true;
    protected $_canSaveCc               = false;
    protected $params;
    protected $method;
    protected $query;
    protected $consumerKey;
    protected $consumerSecret;
    protected $base_string;
    protected $merchantId;
    protected $testMode;
    protected $oauth_host;
    protected $oa;
    protected $numberOfPurchases;
    protected $ppsMethod;

    const REQUEST_TYPE_AUTH_CAPTURE = 'AUTH_CAPTURE';
    const REQUEST_TYPE_AUTH_ONLY    = 'AUTH_ONLY';
    const REQUEST_TYPE_CAPTURE_ONLY = 'CAPTURE_ONLY';
    const REQUEST_TYPE_CREDIT       = 'CREDIT';
    const REQUEST_TYPE_VOID         = 'VOID';
    const REQUEST_TYPE_PRIOR_AUTH_CAPTURE = 'PRIOR_AUTH_CAPTURE';

    //Called to only authorize a payment
    public function authorize(Varien_Object $payment, $amount)
    {

    }//End authorize function

    /**
     * Called to capture a payment
     * -Create the order first
     * -Pass the payment and customer information through the order endpoint returned
     */
    public function capture(Varien_Object $payment, $amount)
    {
        Mage::throwException("Line 55");
        //Sanity check
        if ($amount <= 0)
            Mage::throwException(Mage::helper('paygate')->__('Invalid amount for authorization.'));

        $authOnly = false;
        //Create an object to retrieve all data from Magento
        $retrieveData = new PPS_Paymentmodule_Model_RetrieveData($payment, $amount);

        //Create an object to interface with the proxy
        $proxy = new PPS_Paymentmodule_Model_ppsProxy($this->consumerKey, $this->consumerSecret, $this->testMode);

        //Get the order information to be used in order creation
        $orderInformation = $retrieveData->retrieveOrderInformation();
        //create an object to send orders
        $orderController = new PPS_Paymentmodule_Model_OrderController($proxy);
        //Send the data and check for an order ID
        $orderId = $proxy->getIdFromLocationHeader($orderController->_Create($orderInformation));
        if(empty($orderId))
            Mage::throwException("Order did not complete");

        //Retrieve all information needed for a payment
        $customerInformation = $retrieveData->retrieveCustomerInformation();
        $cardInformation = $retrieveData->retrieveCardInformation();
        $purchaseInformation = $retrieveData->retrievePurchaseInformation();
        $paymentInformation = $retrieveData->retrievePaymentInformation($authOnly);
        $JSONObject = $this->serializeObjects($customerInformation, $paymentInformation, $cardInformation, $purchaseInformation);

        //Create an object to send payments
        $paymentController = new PPS_Paymentmodule_Model_OrderPaymentController($proxy);
        //Send the data and check for a payment Id
        $response = $paymentController->_Pay($orderId, $JSONObject);
        if($proxy->parseHeaderForString($response, 'status') != 'Approved')
            Mage::throwException("Payment did not complete");

        //Need to fix the paymentinformation
        $paymentId = $proxy->getIdFromLocationHeader($response);

        if(empty($paymentId))
        {
            if(!$retrieveData->unsuccessfulPayment())
                Mage::throwException("Could not save payment information");
            Mage::throwException("Payment did not complete");
        }
        else
        {
            if(!$retrieveData->successfulPayment($paymentId))
                Mage::throwException("Could not save payment information");
        }
        return true;
    }//End capture function

    /**
     * Called to refund an order
     * The order will have already been batched in this event
     */
    public function refund(Varien_Object $payment, $requestedAmount)
    {
        /*
        //Sanity check
        if(!$this->_canRefund)
            Mage::throwException("This transaction can not be refunded");

        //Create an object to retrieve all data from Magento
        $retrieveData = new PPS_Paymentmodule_Model_RetrieveData($payment, $requestedAmount);

        //Create an object to interface with the proxy
        $proxy = new PPS_Paymentmodule_Model_ppsProxy($this->consumerKey, $this->consumerSecret, $this->testMode);

        $transactionId = $retrieveData->getTransactionId($payment);
        if(!$transactionId)
            Mage::throwException("Could not find transaction id");

        $orderAmount = $retrieveData->getOrderTotal($payment);
        if(!$orderAmount)
            Mage::throwException("Could not retrieve the order amount");

        if(!$this->_canCapturePartial && ($requestedAmount != $orderAmount))
                Mage::throwException("Refund amount does not match order amount");

        //Check to see if the payment has settled
        $status = $proxy->getPaymentStatus($transactionId);

        switch (strtolower($status))
        {
            //If the payment has been approved, it must be deleted
            case ("approved") :
                //Send a delete request
                $proxy->deletePayment($retrieveData->retrievePaymentInformation(false), $transactionId);

                //Check to make sure that the payment has been voided
                $status= $proxy->getPaymentStatus($transactionId);
                if(strtolower($status) != "voided")
                    Mage::throwException("Could not void payment");
                break;

            case ("declined") :

                break;

            //If the payment has settled, another payment must be created with a negative amount
            case ("settled") :
                $forRefund = true;
                $authOnly = false;
                $orderInformation = $retrieveData->retrieveOrderInformation($forRefund);
                $paymentInformation = $retrieveData->retrievePaymentInformation($authOnly);
                $customerInformation = $retrieveData->retrieveCustomerInformation();
                $cardInformation = $retrieveData->retrieveCardInformation();
                $purchaseInformation = $retrieveData->retrievePurchaseInformation();

                break;
            case ("voided") :

                break;
            case ("chargedback") :

                break;
        }
        */
    }//End function refund

    /**
     * Called to void an order
     * The order will NOT have been batched in this event
     */
    public function void(Varien_Object $payment, $amount)
    {


    }//End void function

    /**
     * This method will convert all passed objects into JSON format
     */
    private function serializeObjects(
        $purchaseInformation = null,
        $paymentInformation = null,
        $customerInformation = null,
        $cardInformation = null)
    {
        $masterArray = array();

        $purchaseArray = array();
        $customerArray = array();
        $cardArray = array();

        foreach($purchaseInformation as $pi => $key)
            $purchaseArray[$key] = $pi;

        foreach($paymentInformation as $pi => $key)
            $masterArray[$key] = $pi;

        foreach($customerInformation as $ci => $key)
            $customerArray[$key] = $ci;

        foreach($cardInformation as $ci => $key)
            $cardArray = $ci;

        $masterArray['purchases'] = $purchaseArray;
        $masterArray['cardAccount'] = $cardArray;
        return $masterArray;

    }

}//End class
