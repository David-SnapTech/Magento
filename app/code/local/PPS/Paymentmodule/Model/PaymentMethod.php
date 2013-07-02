<?php
require 'ppsProxy.php';
require 'RetrieveData.php';
require 'Controllers.php';

class PPS_Paymentmodule_Model_PaymentMethod extends Mage_Payment_Model_Method_Cc
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
    protected $_canUseForMultiShipping  = true;
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
        //Sanity check
        if($amount <= 0)
            Mage::throwException(Mage::helper('paygate')->__('Invalid amount for authorization.'));

        $authOnly = 1;

        //Initialize Magento-saved variables
        $this->initializeVariables();

        //Create an object to retrieve all data from Magento
        $retrieveData = new PPS_Paymentmodule_Model_RetrieveData($payment, $amount);
        //Create an object to interface with the proxy

        $proxy = new PPS_Paymentmodule_Model_ppsProxy($this->consumerKey, $this->consumerSecret, $this->testMode);
        //Get the order information to be used in order creation
        $orderInformation = $retrieveData->retrieveOrderInformation();

        //create an object to send orders
        $orderController = new PPS_Paymentmodule_Model_OrderController($proxy);
        //Send the data and check for an order ID
        $response = $orderController->_Create($orderInformation);
        $orderId = $proxy->getIdFromLocationHeader($response);
        if(empty($orderId))
            Mage::throwException("Order did not complete");

        $JSONObject = $this->getPaymentInfo($retrieveData, $authOnly);

        //Create an object to send payments
        $orderPaymentController = new PPS_Paymentmodule_Model_OrderPaymentController($proxy);
        //Send the data and check for a payment Id
        $response = $orderPaymentController->_Pay($orderId, $JSONObject);
        $paymentId = $proxy->getIdFromLocationHeader($response);

        $order= $payment->getOrder();
        //If payment did  not complete, notify the user and do no save the payment as pending in Magento
        if(!$paymentId)
        {
            //Save the payment as pending and do not invoice
            $this->savePaymentDetails($payment, "pending", "pending", "pending", false, true);

            $order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, 'pending_payment', '', false);

            $order->setCanSendNewEmailFlag(false);
            $order->setCanInvoiceFlag(false);
            $order->save();
            Mage::throwException('Error processing credit card payment.');
        }
        //If the payment completed, save the order and payment details
        else
        {
            $paymentController = new PPS_Paymentmodule_Model_PaymentController($proxy);
            $paymentResponse = $paymentController->_Get($paymentId);

            $authCode = $proxy->parseHeaderForString($paymentResponse, "authCode");
            $authToken = $proxy->parseHeaderForString($paymentResponse, "token");

            $order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, "pending_payment", "pending", false);

            $payment->setAdditionalData("authCode=> $authCode,authToken=> $authToken,paymentId=> $paymentId")->save();

            $order->save();
            return $this;
        }

        return true;
    }

    //called to capture a payment
    public function capture(Varien_Object $payment, $amount)
    {
        //Sanity check
        if ($amount <= 0)
            Mage::throwException(Mage::helper('paygate')->__('Invalid amount for authorization.'));

        $authOnly = false;

        //Initialize Magento-saved variables
        $this->initializeVariables();

        //Create an object to retrieve all data from Magento
        $retrieveData = new PPS_Paymentmodule_Model_RetrieveData($payment, $amount);
        //Create an object to interface with the proxy
        $proxy = new PPS_Paymentmodule_Model_ppsProxy($this->consumerKey, $this->consumerSecret, $this->testMode);

        $additionalData = $payment->getData('additional_data');
        //Check to see if the order has already been authorized
        if($additionalData) {
            $additionalDataArray = "";
            foreach(explode(",", $additionalData) as $value) {
                $additionalDataArray[explode("=>", $value)[0]] = explode("=>", $value)[1];
            }

            $paymentController = new PPS_Paymentmodule_Model_PaymentController($proxy);
            $JSONObject = $this->getPaymentInfo(
                $retrieveData, $authOnly, trim($additionalDataArray['authCode'], ' '),
                trim($additionalDataArray['authToken'], ' ')
            );

            //Send the object and check for a response ID
            $response = $paymentController->_Create($JSONObject);
            $paymentId = $proxy->getIdFromLocationHeader($response);
            if(empty($paymentId))
                Mage::throwException("Payment did not complete");

            $this->savePaymentDetails($payment);
            $payment->setIsTransactionPending(false);

            $order = $payment->getOrder();
            $payment->setAnetTransType(self::REQUEST_TYPE_AUTH_CAPTURE);
            $payment->setCcTransId($paymentId);
            $transactionSave = Mage::getModel('core/resource_transaction');
            $transactionSave->addObject($order);
            $transactionSave->save();
            $payment->setLastTransId();

            $order->save();
            return $this;
        }

        //Get the order information to be used in order creation
        $orderInformation = $retrieveData->retrieveOrderInformation();

        //create an object to send orders
        $orderController = new PPS_Paymentmodule_Model_OrderController($proxy);
        //Send the data and check for an order ID
        $response = $orderController->_Create($orderInformation);
        $orderId = $proxy->getIdFromLocationHeader($response);
        if(empty($orderId))
            Mage::throwException("Order did not complete");

        //Retrieve all information needed for a payment
        $JSONObject = $this->getPaymentInfo($retrieveData, $authOnly);

        //Create an object to send payments
        $orderPaymentController = new PPS_Paymentmodule_Model_OrderPaymentController($proxy);
        //Send the data and check for a payment Id
        $response = $orderPaymentController->_Pay($orderId, $JSONObject);
        $paymentId = $proxy->getIdFromLocationHeader($response);

        $order = $payment->getOrder();
        //If payment did  not complete, notify the user and do no save the payment in Magento
        if(!$paymentId)
        {

            $payment->setIsTransactionPending(true);
            $state = 'Pending';
            $status = 'pending';
            $comment = 'pending';
            $isCustomerNotified = false;

            $order->setState($state, $status, $comment, $isCustomerNotified);
            $order->setCanInvoiceFlag(false);
            $payment->setAnetTransType(self::REQUEST_TYPE_AUTH_CAPTURE);
            $payment->setCcTransId($paymentId);

            $transactionSave = Mage::getModel('core/resource_transaction');
            $transactionSave->addObject($order);
            $transactionSave->save();

            $order->save();
            return $this;
        }
        //If the payment completed, save the order and payment details
        else
        {
            $this->savePaymentDetails($payment);
            $payment->setIsTransactionPending(false);

            $order = $payment->getOrder();
            $order->setCanInvoiceFlag(true);
            $payment->setAnetTransType(self::REQUEST_TYPE_AUTH_CAPTURE);
            $payment->setCcTransId($paymentId);
            $transactionSave = Mage::getModel('core/resource_transaction');
            $transactionSave->addObject($order);
            $transactionSave->save();
            $payment->setLastTransId();

            $order->save();
        }
        return true;
    }//End capture function

    public function refund(Varien_Object $payment, $requestedAmount)
    {
        //Sanity check
        if(!$this->_canRefund)
            Mage::throwException("This transaction can not be refunded");

        $this->initializeVariables();

        //Create an object to retrieve all data from Magento
        $retrieveData = new PPS_Paymentmodule_Model_RetrieveData($payment, $requestedAmount);

        //Create an object to interface with the proxy
        $proxy = new PPS_Paymentmodule_Model_ppsProxy($this->consumerKey, $this->consumerSecret, $this->testMode);

        $paymentId = $retrieveData->getTransactionId();

        if(!$paymentId)
            Mage::throwException("Could not find transaction id");

        $orderAmount = $retrieveData->getOrderTotal($payment);
        if(!$orderAmount)
            Mage::throwException("Could not retrieve the order amount");

        if(!$this->_canCapturePartial && ($requestedAmount != $orderAmount))
            Mage::throwException("Refund amount does not match order amount");

        $paymentController = new PPS_Paymentmodule_Model_PaymentController($proxy);

        //Get the status of the payment
        $response = $paymentController->_Get($paymentId);
        $status = $proxy->parseHeaderForString($response, "status");
        switch (strtolower($status))
        {
            //If the payment has been approved, it must be deleted
            case ("approved") :
               return $this->Void($payment);
                break;

            case ("declined") :
                Mage::throwException("The payment has been declined");
                break;

            //If the payment has settled, another payment must be created with a negative amount
            case ("settled") :
                if(!$this->_canSaveCc)
                    Mage::throwException("No credit card information available to use");
                return $this->capture($payment, ($requestedAmount*(-1)));
                break;

            case ("voided") :
                Mage::throwException("The payment has already been voided");
                break;

            case ("chargedback") :
                Mage::throwException("The payment has been charged Back");
                break;

            default :
                Mage::throwException("Payment status unknown.  Status: $status");
        }
    }//End function refund

    //Void a payment
    public function Void(Varien_Object $payment)
    {
        //Create an object to interface with the proxy
        $proxy = new PPS_Paymentmodule_Model_ppsProxy($this->consumerKey, $this->consumerSecret, $this->testMode);

        $paymentController = new PPS_Paymentmodule_Model_PaymentController($proxy);
        $retrieveData = new PPS_Paymentmodule_Model_RetrieveData($payment, $payment->getOrder()->getAmount());

        $paymentId = $retrieveData->getTransactionId();
        if(!$paymentId)
            Mage::throwException("Could not find transaction id");

        if(!$this->_canVoid)
            Mage::throwException("Unable to void transaction");

        //Send a delete request
        $paymentController->_Delete($paymentId);

        //Check to make sure that the delete request was processed
        $response = $paymentController->_Get($paymentId);
        $status = $proxy->parseHeaderForString($response, "status");

        if(strtolower($status) != "voided")
            Mage::throwException("Could not void payment, the current status is: $status");

        $order = $payment->getOrder();

        $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true);
        $order->save();
        return $this;
    }//End void function

    private function savePaymentDetails($payment, $state = 'Complete', $status = 'complete', $comment = 'complete',
                                        $isCustomerNotified = false, $transactionPending = false)
    {
        $order = $payment->getOrder();
        $payment->setIsTransactionPending($transactionPending);

        $payment->setTransactionId($order->getincrementId());

        $order->setCanInvoiceFlag($transactionPending);

        $order->setState($state, $status, $comment, $isCustomerNotified);

        $transactionSave = Mage::getModel('core/resource_transaction');
        $transactionSave->addObject($order);
        $transactionSave->save();

        $transactionSave = Mage::getModel('core/resource_transaction');
        $transactionSave->addObject($order);
        $transactionSave->save();
        $order->save();

        return true;
    }

    //Retrieve all information needed for a payment
    private function getPaymentInfo($retrieveData, $authOnly, $authCode = null, $authToken = null)
    {
        //$customerInformation = $retrieveData->retrieveCustomerInformation();
        $customerInformation = null;
        $cardInformation = $retrieveData->retrieveCardInformation($authToken);
        $purchaseInformation = $retrieveData->retrievePurchaseInformation();
        $paymentInformation = $retrieveData->retrievePaymentInformation($authOnly, $authCode);

        return $this->serializeObjects($purchaseInformation, $cardInformation, $customerInformation, $paymentInformation);
    }

    //Used to serialize objects into JSON format
    private function serializeObjects(
            $purchaseInformation = null,
            $cardInformation = null,
            $customerInformation = null,
            $paymentInformation = null)
    {
        $masterArray = array();
        $purchaseArray = array();
        $customerArray = array();
        $cardArray = array();

        if(!is_null($paymentInformation))
            foreach($paymentInformation as $key => $pi)
                $masterArray[$key] = $pi;

        if(!is_null($purchaseInformation))
        {
            foreach($purchaseInformation as $key => $pi)
                $purchaseArray[$key] = $pi;
            $masterArray['purchases'] = $purchaseInformation;
        }

        if(!is_null($customerInformation))
        {
            foreach($customerInformation as $key => $ci)
                $customerArray[$key] = $ci;
            $masterArray['customer'] = $customerInformation;
        }

        if(!is_null($cardInformation))
        {
            foreach($cardInformation as $key => $ci)
                $cardArray[$key] = $ci;
            $masterArray['cardAccount'] = $cardInformation;
        }

        return $masterArray;
    }

    private function initializeVariables()
    {
        //Check for test mode
        $config = Mage::getStoreConfig('payment/paymentmodule');
        $this->testMode = $config['test'];

        //Get the consumer key and secret
        if(!($this->consumerKey = Mage::helper('core')->decrypt($config['consumer_key'])))
            Mage::log("Customer Key not available");
        if(!($this->consumerSecret = Mage::helper('core')->decrypt($config['consumer_secret'])))
            Mage::log("Customer secret not available");
    }
}//End class