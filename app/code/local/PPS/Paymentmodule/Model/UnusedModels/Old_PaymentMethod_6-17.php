<?php
ini_set('display_errors', 'On');        //!!Remove before production!!//
error_reporting(E_ALL);                 //!!Remove before production!!//
require 'oauth.php';
require 'ppsMethods.php';
require 'PPSObjects.php';


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

    /**
     * called if authorizing only
     */
    public function authorize(Varien_Object $payment, $amount) {
        //Populate the MerchantId, and token values
        $this->populateTokens();
        Mage::log($this->merchantId);
        Mage::log("*** Starting Authorize Function ***");

        if ($amount <= 0) {
            Mage::throwException(Mage::helper('paygate')->__('Invalid amount for authorization.'));
        }

        // Define API Component URLs
        /////////////////////////////////////////
        // Order
        $order_api_url = $this->oauth_host."/order";
        // Payment
        $payment_api_url = $this->oauth_host."/payment";
        // Product
        $product_api_url = $this->oauth_host."/product";      //Not currently used
        // Customer
        $customer_api_url = $this->oauth_host."/customer";    //Not currently used
        // Exchange
        $exchange_api_url = $this->oauth_host."/exchange";    //Not currently used
        /////////////////////////////////////////

        //Retrieve order/customer/billing objects
        $order = $payment->getOrder();


        $newTransactionType = Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH;
        $payment->setTransactionId($order->getincrementId());
        $payment->setTransactionAdditionalInfo('','');
        $payment->setIsTransactionClosed(0);
        //$transaction = $payment->addTransaction($newTransactionType, null, false , false);


        $customer = $payment->getOrder()->getCustomer();
        $paymentAndOrderData = $this->getPaymentData($order, $payment, $amount,
            $this->getCustomerData($order, $customer),
            $this->getShippingAddress($order),
            $this->getBillingAddress($order),
            1
        );
        $paymentData = $paymentAndOrderData[0];
        $orderData = $paymentAndOrderData[1];


        $this->generateOauthTokens();
        //create order
        $order_response = $this->oa->sendData($this->consumerSecret, $order_api_url, 'POST', $this->token, $this->tokenSecret, $this->params, $this->merchantId, $orderData);
        //Get the orderId to be used in
        $orderId = $this->ppsMethod->getIdFromLocationHeader($order_response);
        //Mage::log($orderId);
        $paymentData['orderId'] = $orderId*1;

        if($paymentData['orderId'] == '') {
            //Order didn't go though
            Mage::thorwException("The order did not complete");
            return $this;
        }
        $payment_api_url = $order_api_url .  $orderId . "/payment";
        //create payment
        $payment_response = $this->oa->sendData($this->consumerSecret, $payment_api_url, 'POST', $this->token, $this->tokenSecret, $this->params, $this->merchantId, $paymentData);

        // Check for Payment ID
        $paymentid = $this->ppsMethod->getIdFromLocationHeader($payment_response);
        $location = $this->ppsMethod->parseHeaderForLocation($payment_response);
        $completionResponse =  $this->oa->getResponse($this->consumerSecret, $location, 'GET', $this->token, $this->tokenSecret, $this->params, $this->merchantId, $paymentData);

        $authCode = $this->ppsMethod->parseResponseForAuthCode($completionResponse);  //!! Need to create method !!//
        $authToken = $this->ppsMethod->parseresponseForAuthToken($completionResponse);
        //Create an array to save the auth code and token in
        //used when invoicing a payment later
        $authCodeAndTokenArray = array(
            'authCode' => $authCode,
            'authToken' => $authToken,
            'paymentGateway' => $payment_api_url. "/" . $paymentid);
        //'paymentData' => implode("!", $paymentData);

        $completionResponse =  $this->oa->getResponse($this->consumerSecret, $location, 'GET', $this->token, $this->tokenSecret, $this->params, $this->merchantId, $paymentData);



        //$completionResponse =  $this->oa->getResponse($this->consumerSecret, $location, 'GET', $this->token, $this->tokenSecret, $this->params, $this->merchantId, $paymentData);
        if($this->ppsMethod->parseHeaderForString($completionResponse, 'status') != 'Approved') {
            //Mage::throwException("The payment did not complete");
            //return $this;  //!! Commented out for testing !!//
        }

        $responseToken = $this->ppsMethod->parseHeaderForString($completionResponse, 'token');
        $payment->setTransactionAdditionalInfo(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS,$responseToken);

        if (!empty($paymentid)){  //!!should be (!empty($paymentid)) !!//
            Mage::log("!!!!! Payment successful !!!!!");
            Mage::log("paymentId: ");
            Mage::log($paymentid);
            //$payment->setIsTransactionPending(true);
            $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
            $status = 'pending_payment';
            $comment = 'pending';
            $isCustomerNotified = false;
            $order->setState($state, $status, $comment, $isCustomerNotified);

            //$order->setCanInvoiceFlag(false);
            //$payment->setAnetTransType(self::REQUEST_TYPE_AUTH);
            //$payment->setCcTransId($paymentid);
            //$transactionSave = Mage::getModel('core/resource_transaction');
            //$transactionSave->addObject($order);
            //$transactionSave->save();
            //$payment->setLastTransId();

            //Mage::log("Complete");
            //Mage::throwException(Mage::helper('paygate')->__('Invalid amount for capture.'));
            //Mage::throwException("k");

            $authCodeAndTokenString = '';
            //Save the payment response with the order information
            foreach($authCodeAndTokenArray as $key => $value) {
                $authCodeAndTokenString .= $key . '=>' . $value . ',';
            }
            $payment->setAdditionalData($authCodeAndTokenString)->save();
            //$order->setAdditionalData($authCodeAndToken)->save;

            $order->save();
            return $this;
        }
        else {
            $order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, 'pending_payment', '', false);
            Mage::log("**STARTING ORDER DEBUG**");
            Mage::log($order->debug());
            $order->setCanSendNewEmailFlag(false);
            $order->setCanInvoiceFlag(false);
            $order->save();
            Mage::throwException('Error processing credit card payment.');
            //Throw exception to stop order from being completed
            //Mage::throwException(Mage::helper('paygate')->__('Invalid amount for capture.'));
        }//End else

        return $this;
    }//End Authorize function



    /**
     * called if authorizing and capturing
     */
    public function capture(Varien_Object $payment, $amount)
    {
        $newFunction = new PPS_Paymentmodule_Model_CleanPaymentMethod();
        mage::throwException(("Line 196"));
        $newFunction->capture($payment, $amount);
        return true;


        if ($amount <= 0) {
            Mage::throwException(Mage::helper('paygate')->__('Invalid amount for authorization.'));
        }
        //Populate the MerchantId, and token values
        $this->populateTokens();
        Mage::log("*********************************************");
        Mage::log(Mage::getSingleton('checkout/session')->getQuote()->getItems());
        Mage::log("Starting capture function");



        // Define API Component URLs
        /////////////////////////////////////////
        // Order
        $order_api_url = $this->oauth_host."/order";
        // Payment
        $payment_api_url = $this->oauth_host."/payment";
        // Product
        $product_api_url = $this->oauth_host."/product";      //Not currently used
        // Customer
        $customer_api_url = $this->oauth_host."/customer";    //Not currently used
        // Exchange
        $exchange_api_url = $this->oauth_host."/exchange";    //Not currently used
        /////////////////////////////////////////

        //Retrieve order/customer/billing objects
        $order = $payment->getOrder();
        $billingaddress = $order->getBillingAddress();
        $billingStreetAddress = $billingaddress->getStreet();
        $shippingaddress = $order->getShippingAddress();
        $shippingStreetAddress = $shippingaddress->getStreet();

        //var_dump($items);
        $visibleItems  = $order->getAllVisibleItems();

        $newTransactionType = Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE;
        $payment->setTransactionId($order->getincrementId());

        //Check to see if there is previously saved order information
        $collection = Mage::getModel('sales/order_invoice')
            ->getCollection()
            ->addAttributeToFilter('increment_id', $order->getincrementId())
            ->getFirstItem();
        $additionalData = $payment->getData('additional_data');
        if($additionalData) {
            Mage::log("There is additional data");
            //!!Not complete yet!!//
            //Order has already been created.

            foreach(explode(",", $additionalData) as $value) {
                $additionalDataArray[explode("=>", $value)[0]] = explode("=>", $value)[1];
            }
            $payment_api_url = $additionalDataArray['paymentGateway'];
            //$paymentData = $additionalDataArray['paymentData'];
            $paymentData['authCode'] = $additionalDataArray['authCode'];
            Mage::log("authCode2");
            Mage::log($additionalDataArray);
            $paymentData['authOnly'] = false;
            $order_response = $this->oa->sendData($this->consumerSecret, $payment_api_url, 'UPDATE', $this->token, $this->tokenSecret, $this->params, $this->merchantId, $paymentData);
            Mage::log("Finished Order");
            Mage::log($order_response);
        }


        //Order information
        $orderInformation = array();
        $orderInformation['orderId'] = $order->getincrementId();
        $orderInformation['amount'] = number_format($amount, 2, '.', '');
        $orderInformation['CustomerEMail'] = $billingaddress->getData('email');
        $orderInformation['ClientIPAddress'] = $_SERVER['REMOTE_ADDR'];


        $customer = $payment->getOrder()->getCustomer();
        $paymentAndOrderData = $this->getPaymentData($order, $payment, $amount,
            $this->getCustomerData($order, $customer),
            $this->getShippingAddress($order),
            $this->getBillingAddress($order),
            false
        );
        $paymentData = $paymentAndOrderData[0];
        $orderPaymentData = $paymentAndOrderData[1];

        //Generate oauth tokens
        $this->generateOauthTokens();
        //create order
        $order_response = $this->oa->sendData($this->consumerSecret, $order_api_url, 'POST', $this->token, $this->tokenSecret, $this->params, $this->merchantId, $orderPaymentData);
        Mage::log("Finished Order ");
        Mage::log($order_response);
        //Get the orderId to be used in
        $orderId = $this->ppsMethod->getIdFromLocationHeader($order_response);
        Mage::log("***********************");
        Mage::log($orderId);
        Mage::log("***********************");
        if($orderId == '') {
            Mage::log('Order did not complete.  Response = ');
            Mage::log($order_response);
            Mage::throwException("Order did not complete.");
        }
        $paymentData['orderId'] = $orderId;

        if($paymentData['orderId'] == '') {
            //Order didn't go though
            Mage::thorwException("The order did not complete");
            return $this;
        }
        $payment_api_url = $order_api_url .  $orderId . "/payment";
        //create payment
        $payment_response = $this->oa->sendData($this->consumerSecret, $payment_api_url, 'POST', $this->token, $this->tokenSecret, $this->params, $this->merchantId, $paymentData);

        // Check for Payment ID
        $paymentid = $this->ppsMethod->getIdFromLocationHeader($payment_response);  //Was parseheadersforpaymentid
        $location = $this->ppsMethod->parseHeaderForLocation($payment_response);

        $completionResponse =  $this->oa->getResponse($this->consumerSecret, $location, 'GET', $this->token, $this->tokenSecret, $this->params, $this->merchantId, $paymentData);

        if($this->ppsMethod->parseHeaderForString($completionResponse, 'status') != 'Approved') {
            Mage::throwException("The payment did not complete");
            return $this;
        }

        $responseToken = $this->ppsMethod->parseHeaderForString($completionResponse, 'token');
        $payment->setTransactionAdditionalInfo(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS,$responseToken);

        if (!empty($paymentid)){
            $payment->setIsTransactionPending(false);
            $state = 'Complete';
            $status = 'complete';
            $comment = 'complete';
            $isCustomerNotified = false;

            $order->setState($state, $status, $comment, $isCustomerNotified);
            $order->setCanInvoiceFlag(true);
            $payment->setAnetTransType(self::REQUEST_TYPE_AUTH_CAPTURE);
            $payment->setCcTransId($paymentid);
            $transactionSave = Mage::getModel('core/resource_transaction');
            $transactionSave->addObject($order);
            $transactionSave->save();
            $payment->setLastTransId();

            $order->save();
            return $this;
        }
        else {
            $order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, 'pending_payment', '', false);
            $order->setCanSendNewEmailFlag(false);
            $order->setCanInvoiceFlag(false);
            $order->save();
            $order->save();
        }//End else
        return $this;
    }//End capture function


    /**
     * Called to populate the OAuth tokens
     */
    protected function populateTokens() {
        $this->oa = new oauth();
        $this->ppsMethod = new PPS_Paymentmodule_Model_ppsMehods();
        //Get the payment info for the Paymentmodule and the pull the merchantId and tokens
        $config = Mage::getStoreConfig('payment/paymentmodule');
        //If any of the values are empty, throw an exception and log it
        if(!($this->merchantId = Mage::helper('core')->decrypt($config['login']))) {
            Mage::log("Merchant ID not available");
        }
        if(!($this->consumerKey = Mage::helper('core')->decrypt($config['consumer_key']))) {
            Mage::log("Customer Key not available");
        }
        if(!($this->consumerSecret = Mage::helper('core')->decrypt($config['consumer_secret']))) {
            Mage::log("Customer secret not available");
        }

        $this->params = array('oauth_callback'=> 'callback.php',
            'oauth_consumer_key'=>$this->consumerKey,
            'oauth_nonce'=> sha1(microtime()),
            'oauth_signature_method'=>'HMAC-SHA1',
            'oauth_timestamp'=> time(),
            'oauth_version'=>'1.0');

        //Swap the endpoint depending on test mode
        if($config['test']) {
            $this->oauth_host = "http://test.api.mxmerchant.com/v1";
        }
        else {
            $this->oauth_host = "http://prod.api.mxmerchant.com/v1";
            //$this->oauth_host = $this->getConfigData('cgi_url');  //to get the URL from the admin-config menu
        }
    }//End function

    /**
     * called if refunding
     */
    public function refund(Varien_Object $payment, $requestedAmount)
    {
        if(!$this->_canRefund) {
            Mage::throwException("This transaction can not be refunded");
        }
        $order = $payment->getOrder();
        $this->populateTokens();
        $this->generateOauthTokens();
        $orderAmount = $order->getGrandTotal();
        $transId = $payment->getCcTransId();

        if($orderAmount != $requestedAmount) {
            Mage::throwException("Refund amount must match charged amount");
        }
        // Payment
        $refund_api_url = $this->oauth_host."/payment" ."/". $transId;

        $cardsStorage = $this->getCardsStorage($payment);

        $paymentData = array();
        $paymentData['merchantId'] = $this->merchantId;
        $paymentData['tenderType'] = 'Card';
        $paymentData['amount'] = $requestedAmount;

        //Check to see if the payment has settled.
        $location = 'http://test.api.mxmerchant.com/v1/payment/' . $transId;
        $completionResponse =  $this->oa->getResponse($this->consumerSecret, $location, 'GET', $this->token, $this->tokenSecret, $this->params, $this->merchantId, $paymentData);
        $isSettled = $this->ppsMethod->parseHeaderForSettledAmount($completionResponse);
        $status = $this->ppsMethod->parseHeaderForString($completionResponse, 'status');

        if($status == 'Approved') {
            //Transaction has not settled -Void
            //Void payment
            $payment_response = $this->oa->sendDeleteData($this->consumerSecret, $refund_api_url, 'DELETE', $this->token, $this->tokenSecret, $this->params, $this->merchantId, $paymentData);


            if($this->ppsMethod->parseHeaderForString(
                    $this->oa->getResponse(
                        $this->consumerSecret, $location, 'GET', $this->token, $this->tokenSecret, $this->params,
                        $this->merchantId, $paymentData),
                    'status') != 'Voided')
            {
                Mage::throwException("Unable to void transaction");
            }

        }//End if
        elseif($status == 'Settled') {

            //Transaction has settled -Refund
            $customer = $payment->getOrder()->getCustomer();
            $billingaddress = $order->getBillingAddress();
            $billingStreetAddress = $billingaddress->getStreet();
            $shippingaddress = $order->getShippingAddress();
            $shippingStreetAddress = $shippingaddress->getStreet();
            $items = $order->getAllItems();
            $refundAmount = $requestedAmount * '-1';

            // Create Payment Array
            /////////////////////////////////////////////////
            $paymentData = array();
            $paymentData['merchantId'] = $this->merchantId;
            $paymentData['tenderType'] = 'Card';
            //$paymentData['authOnly'] = 0;
            $paymentData['amount'] = $refundAmount;

            $paymentData['cardAccount']['expiryMonth'] = $payment->getCcExpMonth();
            $paymentData['cardAccount']['expiryYear'] = $payment->getCcExpYear();
            $paymentData['cardAccount']['cvv'] = $payment->getCcCid();
            $paymentData['cardAccount']['avsZip'] = $billingaddress->getData('postcode');
            /////////////////////////////////////////////////

            $location = 'http://test.api.mxmerchant.com/v1/payment/' . $transId;
            $paymentData['cardAccount']['token'] = ($this->ppsMethod->parseHeaderForString($this->oa->getResponse(
                $this->consumerSecret, $location, 'GET', $this->token, $this->tokenSecret, $this->params, $this->merchantId, $paymentData), 'token')
            );


            $payment_response = $this->oa->sendData($this->consumerSecret, $refund_api_url, 'POST', $this->token, $this->tokenSecret, $this->params, $this->merchantId, $paymentData);
            Mage::log("payment_response");
            Mage::log($payment_response);
            return $this;

        }
        else {
            Mage::throwException("Unable to void transaction.  Current state of transaction is " . $status);
        }//End else

        // Check for Payment ID
        $paymentid = $this->ppsMethod->parseHeadersForPaymentID($payment_response);
        $payment->setCcTransId($paymentid);

        return $this;

        if ($this->ppsMethod->formatAmount(
                $cardsStorage->getCapturedAmount() - $cardsStorage->getRefundedAmount()
            ) < $requestedAmount
        ) {
            Mage::throwException(Mage::helper('paygate')->__('Invalid amount for refund.'));
        }

        $messages = array();
        $isSuccessful = false;
        $isFiled = false;
        foreach($cardsStorage->getCards() as $card) {
            if ($requestedAmount > 0) {
                $cardAmountForRefund = $this->ppsMethod->formatAmount($card->getCapturedAmount() - $card->getRefundedAmount());
                if ($cardAmountForRefund <= 0) {
                    continue;
                }
                if ($cardAmountForRefund > $requestedAmount) {
                    $cardAmountForRefund = $requestedAmount;
                }
                try {
                    $newTransaction = $this->_refundCardTransaction($payment, $cardAmountForRefund, $card);
                    $messages[] = $newTransaction->getMessage();
                    $isSuccessful = true;
                } catch (Exception $e) {
                    $messages[] = $e->getMessage();
                    $isFiled = true;
                    continue;
                }
                $card->setRefundedAmount($this->ppsMethod->formatAmount($card->getRefundedAmount() + $cardAmountForRefund));
                $cardsStorage->updateCard($card);
                $requestedAmount = $this->ppsMethod->formatAmount($requestedAmount - $cardAmountForRefund);
            } else {
                $payment->setSkipTransactionCreation(true);
                return $this;
            }
        }//End foreach

        $this->populateTokens();

        // Create Payment Array
        /////////////////////////////////////////////////
        $paymentData = array();
        $paymentData['merchantId'] = $this->merchantId;
        $paymentData['tenderType'] = 'Card';

        $paymentData['authOnly'] = 0;
        $paymentData['amount'] = $orderInformation['amount'];
        $paymentData['purchaseOrderNumber'] = $orderInformation['orderId'];
        $paymentData['echo'] = 'true';


        $payment_response = $this->oa->createPayment($this->consumerSecret, $payment_api_url, 'POST', $this->token, $this->tokenSecret, $this->params, $this->merchantId, $paymentData);

        // Check for Payment ID
        Mage::log("Payment Response");
        Mage::log($payment_response);
        $paymentid = $this->ppsMethod->getIdFromLocationHeader($payment_response);

        // Check response
        //////////////////////////////////////////////////
        if (!empty($paymentid)){  //should be (!empty($paymentid))
            Mage::log("!!!!! Payment successful !!!!!");

            $state = 'Refund    ';
            $status = 'complete';
            $comment = 'complete';
            $isCustomerNotified = false;
            $order->setState($state, $status, $comment, $isCustomerNotified);
            $order->setCanInvoiceFlag(true);
            $payment->setAnetTransType(self::REQUEST_TYPE_AUTH_CAPTURE);

            $transactionSave = Mage::getModel('core/resource_transaction');
            $transactionSave->addObject($order);
            //$transactionSave->addObject($model_two)
            $transactionSave->save();
            $payment->setLastTransId();
            $payment->setCcTransId($paymentid);
            $order->save();

        }
        else {
            $order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, 'pending_payment', '', false);
            Mage::log("**STARTING ORDER DEBUG**");
            Mage::log($order->debug());
            $order->setCanSendNewEmailFlag(false);
            $order->setCanInvoiceFlag(false);
            $order->save();
            Mage::log('PRIORITY DIRECT PAYMENT ERROR: Error processing credit card payment.');
            $order->save();
            Mage::log("end else");
        }//End else
    }//End Refund function

    /**
     * @param Varien_Object $payment
     * called if voiding a payment
     */
    public function void (Varien_Object $payment)
    {   Mage::throwException("VOID function called");
        $this->populateTokens();
        //This method should pass the payment ID and possibly other datails to void
    }




    /**
     * called to add a transaction
     */
    protected function _addTransaction(Mage_Sales_Model_Order_Payment $payment, $transactionId, $transactionType,
                                       array $transactionDetails = array(),
                                       array $transactionAdditionalInfo = array(), $message = false
    ) {
        $payment->setTransactionId($transactionId);
        $payment->resetTransactionAdditionalInfo();
        foreach ($transactionDetails as $key => $value) {
            $payment->setData($key, $value);
        }

        foreach ($transactionAdditionalInfo as $key => $value) {
            $payment->setTransactionAdditionalInfo($key, $value);
        }

        $transaction = $payment->addTransaction($transactionType, null, false , $message);
        foreach ($transactionDetails as $key => $value) {
            $payment->unsetData($key);
        }

        $payment->unsLastTransId();

        $transaction->setMessage($message);

        return $transaction;
    }//End _addTransaction function


    /**
     * called to get the payment data for a order and purchase
     */
    protected function getPaymentData($order,
                                      $payment,
                                      $amount,
                                      $ppsCustomerInformation,
                                      $ppsShippingAddress,
                                      $ppsBillingAddress,
                                      $authOnly = false)
    {
        $billingaddress = $order->getBillingAddress();

        Mage::log("Start to create payment array");
        // Create Payment Array
        /////////////////////////////////////////////////
        $paymentData = array();
        $paymentData['merchantId'] = $this->merchantId;
        $paymentData['tenderType'] = 'Card';
        $paymentData['authOnly'] = $authOnly;
        $paymentData['amount'] = $amount;
        $paymentData['purchaseOrderNumber'] = $order->getincrementId();
        //$paymentData['customer'] = $ppsCustomerInformation;

        $paymentData['cardAccount']['number'] = str_replace(' ', '', $payment->getCcNumber());
        $paymentData['cardAccount']['expiryMonth'] = $payment->getCcExpMonth();
        $paymentData['cardAccount']['expiryYear'] = $payment->getCcExpYear();
        $paymentData['cardAccount']['cvv'] = $payment->getCcCid();
        $paymentData['cardAccount']['avsZip'] = $billingaddress->getData('postcode');

        $paymentDataToReturn = $paymentData;
        //Setup purchase-item info
        //!! Need to test on $visibleItems !!//
        $items = $order->getAllItems();
        $itemCount = count($items);
        $orderSubtotal = $order->getSubtotal();
        $orderTotal = $order->getTotal();
        $numberOfPurchases = 0;
        foreach( $items as $item) {
            $paymentData[$numberOfPurchases]['purchases']['quantity'] = $item->getQtyOrdered()*1;
            $paymentData[$numberOfPurchases]['purchases']['productid'] = $item->getSku();
            $paymentData[$numberOfPurchases]['purchases']['price'] = $item->getPrice()*1;
            $paymentData[$numberOfPurchases]['purchases']['productname'] = $item->getName();
            $paymentData[$numberOfPurchases]['purchases']['totalAmount'] = $item->getQtyOrdered() * $item->getPrice()*1;
            $product = Mage::getModel('catalog/product')->load($item->getProductId());

            $paymentData[$numberOfPurchases]['purchases']['description'] = Mage::getModel('catalog/product')->load($product->getId())->getDescription();
            $numberOfPurchases++;
            if($item->getDiscountAmount()) {
                Mage::throwException($item->getDiscountAmount());
                $paymentData[$numberOfPurchases]['purchases']['discountAmount'] = $item->getDiscountAmount() * $item->getPrice();
            }
            $this->numberOfPurchases = $numberOfPurchases;
        }
        /////////////////////////////////////////////////
        Mage::log("End create payment array");


        $billingaddress = $order->getBillingAddress();
        $billingStreetAddress = $billingaddress->getStreet();
        $shippingaddress = $order->getShippingAddress();
        $shippingStreetAddress = $shippingaddress->getStreet();

        //Setup the order details that will be used to create the order
        $orderPaymentData = array();
        $date = new DateTime();
        mage::log($paymentData);
        $orderPaymentData['purchases'] = array();
        for($i = 0; $i <$this->numberOfPurchases; $i++) {
            $orderPaymentData['purchases'][] = $paymentData[$i]['purchases'];
        }

        $totals = Mage::getSingleton('checkout/cart')->getQuote()->getTotals();
        $subtotal = $totals["subtotal"]->getValue();
        $orderPaymentData['customer'] = $ppsCustomerInformation;
        $orderPaymentData['shipAmount'] = $shippingaddress['shipping_amount'];
        $orderPaymentData['subTotalAmount'] = $totals["subtotal"]->getValue()*1.0;
        $orderPaymentData['quantity'] = $numberOfPurchases*1;
        $orderPaymentData['type'] = 'Sale';
        $orderPaymentData['taxAmount'] = $order->getTaxInvoiced();
        $orderPaymentData['merchantId'] = $this->merchantId;
        $orderPaymentData['totalAmount'] = $paymentData['amount']*1.0;
        $orderPaymentData['echo'] = 'true';
        $orderPaymentData['billingAddress'] =  $ppsBillingAddress;
        $orderPaymentData['shippingAddress'] = $ppsShippingAddress;
        $quote=Mage::getModel('checkout/session')->getQuote();
        $shippingMethod = $quote->getShippingAddress();
        $orderPaymentData['shipAmount'] = $shippingMethod['shipping_amount'];
        $orderPaymentData['shipMethod'] = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getShippingMethod();

        $paymentAndOrderData = array($paymentDataToReturn, $orderPaymentData);
        return $paymentAndOrderData;
    }


    /**
     * called to get the customers shipping address
     * @param $order
     * @return array
     */
    protected function getShippingAddress($order) {
        $shippingaddress = $order->getShippingAddress();
        $shippingStreetAddress = $shippingaddress->getStreet();

        //Setup shipping address to be sent to PPS
        $ppsShippingAddress = array();
        $ppsShippingAddress['description'] = 'Shipping Address';
        $ppsShippingAddress['address1'] = $shippingStreetAddress[0];
        $ppsShippingAddress['city'] =  $shippingaddress->getData('city');
        $ppsShippingAddress['state'] = $shippingaddress->getData('region');
        $ppsShippingAddress['zip'] = $shippingaddress->getData('postcode');
        $ppsShippingAddress['country'] = $shippingaddress->getData('country_id');

        //Check conditionals
        if (isset($shippingStreetAddress[1])) {
            $ppsShippingAddress['address2'] = $shippingStreetAddress[1];
        }
        return $ppsShippingAddress;
    }

    /**
     * called to get the customers billing address
     * @param $order
     * @return array
     */
    protected function getBillingAddress($order) {
        $billingaddress = $order->getBillingAddress();
        $billingStreetAddress = $billingaddress->getStreet();

        //Setup billing address to be sent to PPS
        $ppsBillingAddress = array();
        $ppsBillingAddress['description'] = 'Billing Address';
        $ppsBillingAddress['address1'] = $billingStreetAddress[0];
        $ppsBillingAddress['city'] =  $billingaddress->getData('city');
        $ppsBillingAddress['state'] = $billingaddress->getData('region');
        $ppsBillingAddress['zip'] = $billingaddress->getData('postcode');
        $ppsBillingAddress['country'] = $billingaddress->getData('country_id');

        //Check conditionals
        if(isset($billingStreetAddress[1])) {
            $ppsBillingAddress['address2'] = $billingStreetAddress[1];
        }

        return $ppsBillingAddress;
    }

    /**
     * called to get customer information
     * @param $order
     * @param $customer
     * @return array
     */
    protected function getCustomerData($order, $customer) {
        $billingaddress = $order->getBillingAddress();
        $ppsCustomerInformation = array();
        $ppsCustomerInformation['number'] = $customer->getId();
        $ppsCustomerInformation['name'] = $billingaddress->getData('firstname') . ' ' . $billingaddress->getData('lastname');
        $ppsCustomerInformation['email'] = $customer->getEmail();
        $ppsCustomerInformation['phone'] = $billingaddress->getData('telephone');
        $ppsCustomerInformation['fax'] = $billingaddress->getData('fax');

        return $ppsCustomerInformation;
    }

    /**
     * called to generate OAuth tokens
     */
    protected function generateOauthTokens( ) {

        // Request and Access Token URLS
        $request_token_url = $this->oauth_host."/OAuth/1A/RequestToken";
        $access_token_url = $this->oauth_host."/OAuth/1A/AccessToken";
        $authorize_token_url = $this->oauth_host."/OAuth/1A/Authorize";

        // Generate OAuth Tokens
        ///////////////////////////////////////////////
        $initCall = array();
        $access_data = array();
        parse_str($this->oa->startProcess($this->consumerKey, $this->consumerSecret, $request_token_url, 'POST', $this->params), $initCall);
        //Mage::log("Done requesting token");
        parse_str($this->oa->getAccessToken($this->consumerKey, $this->consumerSecret, $initCall['oauth_token'], $initCall['oauth_token_secret'], $access_token_url,'POST', $this->params), $access_data);
        $this->token = $access_data['oauth_token'];
        $this->tokenSecret = $access_data['oauth_token_secret'];
        ///////////////////////////////////////////////

        // Check for valid tokens
        // Check for auth or die
        if (empty($this->token) || empty($this->tokenSecret)){
            Mage::log('PRIORITY DIRECT PAYMENT ERROR: Empty token response.');
            $json['error'] = 'Unable to authneticate.';
        }
    }//End function generateOauthtokens

}//End class


?>