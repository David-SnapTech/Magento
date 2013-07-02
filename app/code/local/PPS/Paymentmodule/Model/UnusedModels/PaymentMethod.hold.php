<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);
require 'oauth.php';


class PPS_Paymentmodule_Model_PaymentMethod3 extends Mage_Payment_Model_Method_Cc
{
	protected $_code = 'newmodule';

	protected $token = '';
	protected $tokenSecret = '';
	protected $_isGateway               = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = false;
    protected $_canVoid                 = true;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = true;
    protected $_canSaveCc = false;
    protected $params;
    protected $method;
    protected $query;
	protected $consumerKey = 'EkAJQdTQssAGUPkRdugYewQS';
	protected $consumerSecret = 'yhJ6PdXohHKP7IJq2P5Ux0O55KA=';
    protected $base_string;
    protected $response;
    protected $merchantId = '-66';
	
    public $link;
    public $headers;

    /**
     * called if authorizing only
     */
    public function authorize(Varien_Object $payment, $amount) {
        Mage::log("*** Starting Authorize Function ***");

        if ($amount <= 0) {
            Mage::throwException(Mage::helper('paygate')->__('Invalid amount for authorization.'));
        }

        $this->_initCardsStorage($payment);

        if ($this->isPartialAuthorization($payment)) {
            $this->_partialAuthorization($payment, $amount, self::REQUEST_TYPE_AUTH_ONLY);
            $payment->setSkipTransactionCreation(true);
            return $this;
        }

        $this->_place($payment, $amount, self::REQUEST_TYPE_AUTH_ONLY);
        $payment->setSkipTransactionCreation(true);
        return $this;
    }

    /**
     * called if authorizing and capturing
     */
    public function capture(Varien_Object $payment, $amount)
    {
    	mage::log("*********************************************");
    	Mage::log("Starting capture function");
    	
    	if ($amount <= 0) {
            Mage::throwException(Mage::helper('paygate')->__('Invalid amount for authorization.'));
        }

	    $links=array(
			    'request'=> array('http://test.api.mxmerchant.com/v1/OAuth/1A/RequestToken',"POST"),
			    'authorize'=> array('http://test.api.mxmerchant.com/v1/oauth/authorize',"GET"),
			    'access'=> array('http://test.api.mxmerchant.com/v1/OAuth/1A/AccessToken', "POST")
		);
		
		$params = array(
		    'oauth_callback'=> 'callback.php',
		    'oauth_consumer_key'=>$this->consumerKey,
		    'oauth_nonce'=> sha1(microtime()),
		    'oauth_signature_method'=>'HMAC-SHA1',
		    'oauth_timestamp'=> time(),
		    'oauth_version'=>'1.0'
		);

		//Retrieve order information
		$order = $payment->getOrder();
        Mage::log($order->debug());
        Mage::log($payment->debug());
        $billingaddress = $order->getBillingAddress();
		$shippingaddress = $order->getShippingAddress();
        $billingStreetAddress = $billingaddress->getStreet();
        $shippingStreetAddress = $shippingaddress->getStreet();




        //Order information
        $orderInformation = array();
        $orderInformation['orderId'] = $order->getincrementId();
        $orderInformation['amount'] = number_format($amount, 2, '.', '');

        //Billing Information
        $billingInformatio = array();
        $billingInformatio['BillingSurname'] = $billingaddress->getData('lastname');
        $billingInformatio['BillingFirstnames'] = $billingaddress->getData('firstname');
        $billingInformatio['BillingSurname'] = $billingaddress->getData('lastname');
        $billingInformatio['BillingAddress1'] = $billingStreetAddress[0];

        if(isset($billingStreetAddress[1])) {
            $billingInformatio['BillingAddress2'] = $billingStreetAddress[1];
        }
        else {
            $billingInformatio['BillingAddress2'] = '';
        }

        $billingInformatio['BillingCity'] = $billingaddress->getData('city');
        $billingInformatio['BillingPostCode'] = $billingaddress->getData('postcode');
        $billingInformatio['BillingCountry'] = $billingaddress->getData('');
        $billingInformatio['BillingCountry'] = $billingaddress->getData('counrty_id');

        if ($billingaddress->getData('country_id') == 'US') {
            $billingInformatio['BillingState'] = $billingaddress->getData('state');
        } else {
            $billingInformatio['BillingState'] = '';
        }

        $billingInformatio['BillingPhone'] = $billingaddress->getData('telephone');

        //Shipping Information
        $shippingInformation['DeliverySurname'] = $shippingaddress->getData('lastname');
        $shippingInformation['DeliveryFirstnames'] = $shippingaddress->getData('firstname');
        $shippingInformation['DeliveryAddress1'] = $shippingStreetAddress[0];
        if (isset($shippingStreetAddress[1])) {
            $shippingInformation['DeliveryAddress2'] = $shippingStreetAddress[1];
        } else {
            $shippingInformation['DeliveryAddress2'] = '';
        }

        $shippingInformation['DeliveryCity'] = $shippingaddress->getData('city');
        $shippingInformation['DeliveryPostCode'] = $shippingaddress->getData('postcode');
        $shippingInformation['DeliveryCountry'] = $shippingaddress->getData('country_id');

        if ($shippingaddress->getData('country_id') == 'US') {
            $shippingInformation['DeliveryState'] = $shippingaddress->getData('state');
        } else {
            $shippingInformation['DeliveryState'] = '';
        }

        $shippingInformation['CustomerName'] = $shippingaddress->getData('firstname') . ' ' . $shippingaddress->getData('lastname');
        $shippingInformation['DeliveryPhone'] = $shippingaddress->getData('telephone');

        $orderInformation['CustomerEMail'] = $billingaddress->getData('email');
        $orderInformation['ClientIPAddress'] = $_SERVER['REMOTE_ADDR'];


		// Request and Access Token URLS
		$oauth_host = "http://test.api.mxmerchant.com/v1";
		$request_token_url = $oauth_host."/OAuth/1A/RequestToken";
		$access_token_url = $oauth_host."/OAuth/1A/AccessToken";
		
		// Define API Component URLs
		/////////////////////////////////////////
		// Order
		$order_api_url = $oauth_host."/order";
		// Payment
		$payment_api_url = $oauth_host."/payment";
		// Product
		$product_api_url = $oauth_host."/product";
		// Customer
		$customer_api_url = $oauth_host."/customer";
		// Exchange
		$exchange_api_url = $oauth_host."/exchange";
		/////////////////////////////////////////
		
		//echo"Testing";
		$oa = new oauth();

		// Generate OAuth Tokens
        ///////////////////////////////////////////////
        $initCall = array();
        $access_data = array();
        parse_str($oa->startProcess($this->consumerKey, $this->consumerSecret, $request_token_url, 'POST', $params), $initCall);
        //Mage::log("Done requesting token");
        parse_str($oa->getAccessToken($this->consumerKey, $this->consumerSecret, $initCall['oauth_token'], $initCall['oauth_token_secret'], $access_token_url,'POST', $params), $access_data);
        //Mage::log("Done getting access token");
        Mage::log("access_data contains:");
        Mage::log($access_data);
        $this->token = $access_data['oauth_token'];
        $this->tokenSecret = $access_data['oauth_token_secret'];
        ///////////////////////////////////////////////

		// Check for valid tokens
        // Check for auth or die
        if (empty($this->token) || empty($this->tokenSecret)){
        	Mage::log('PRIORITY DIRECT PAYMENT ERROR: Empty token response.');
        	$json['error'] = 'Unable to authneticate.';
        }
        Mage::log("Start to create payment array");
		// Create Payment Array
        /////////////////////////////////////////////////
        $paymentData = array();
        $paymentData['merchantId'] = $this->merchantId;
        $paymentData['tenderType'] = 'Card';
        $paymentData['quantity'] = 3;
        $paymentData['authOnly'] = 0;
        $paymentData['amount'] = $orderInformation['amount'];
        $paymentData['purchaseOrderNumber'] = $orderInformation['orderId'];
        $paymentData['purchases']['quantity'] = 5;
        $paymentData['purchases']['price'] = 1.00;
        $paymentData['purchases']['productid'] = '1234';
        $paymentData['purchases']['price'] = '1.99';
        $paymentData['purchases']['productnae'] = 'shoe';
        $paymentData['purchases']['totalAmount'] = '2.05';

       // $paymentData['purchases'][''] = $orderInformation[''];
       // $paymentData['purchases'][''] = $orderInformation[''];
        Mage::log($payment->getCcNumber());
        $paymentData['cardAccount']['number'] = str_replace(' ', '', $payment->getCcNumber());
        $paymentData['cardAccount']['expiryMonth'] = $payment->getCcExpMonth();
        $paymentData['cardAccount']['expiryYear'] = $payment->getCcExpYear();
        $paymentData['cardAccount']['cvv'] = $payment->getCcCid();
        $paymentData['cardAccount']['avsZip'] = $billingaddress->getData('postcode');
        /////////////////////////////////////////////////
		Mage::log("End create payment array");
        // Create Payment

        $payment_response = $oa->createPayment($this->consumerSecret, $payment_api_url, 'POST', $this->token, $this->tokenSecret, $params, $this->merchantId, $paymentData);

        // Check for Payment ID
        Mage::log("Payment Response");
        Mage::log($payment_response);
        $paymentid = $oa->parseHeadersForPaymentID($payment_response);


        if (!empty($paymentid)){
        	Mage::log("!!!!! Payment successful !!!!!");
            Mage::log("222");
            //$payment->setAnetTransType(self::REQUEST_TYPE_AUTH_CAPTURE);
            Mage::log("224");
            $payment->setAmount($amount);
            Mage::log("226");
            //var_dump($payment);
            //$request= $this->_buildRequest($payment);
            //$result = $this->_postRequest($request);
            Mage::log(print_r($payment));
            die();


         	//$paymentid_link = $links['baseurl'][0].'/order/'.$paymentid.'/payment';
        	// If payment is successful then goto success page
        	//$json['success'] = $this->url->link('checkout/success', '', 'SSL');
        	//$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('priority_direct_order_status_id'));
        } else {
        	//echo("Error processing credit card payment");
        	Mage::log('PRIORITY DIRECT PAYMENT ERROR: Error processing credit card payment.');
        	$json['error'] = 'There was an error processing the payment.';
        }
        //!! Commented out for testing !!//
        //$this->response->setOutput(json_encode($json));
        //!! End commented out for testing !!//
        
        
        
     /* 
		$OA = new oauth();
		
		$getRequestToken = $this->doIt($links['request'][1],$links['request'][0],$consumerKey,$consumerSecret);
	
		$this->headers = $this->createHeader();
		parse_str($this->sendRequest($this->headers, $this->link),$initCall);
	
		
		$getAccessToken= $this->doIt($links['access'][1],$links['access'][0],$consumerKey,$consumerSecret,$initCall['oauth_token'],$initCall['oauth_token_secret']);
		$getAccessTokenResponse= $this->createHeader();
		parse_str($this->sendRequest($this->headers,$this->link),$accessData);
	
		$token=$accessData['oauth_token'];
		$tokenSecret=$accessData['oauth_token_secret'];	
    */
    }//end authorize function







































    /**
     * Init cards storage model
     *
     * @param Mage_Payment_Model_Info $payment
     */
    protected function _initCardsStorage($payment)
    {
        $this->_cardsStorage = Mage::getModel('paygate/authorizenet_cards')->setPayment($payment);
    }



    /**
     * Return true if there are authorized transactions
     *
     * @param Mage_Payment_Model_Info $payment
     * @return bool
     */
    protected function _isPreauthorizeCapture($payment)
    {
        if ($this->getCardsStorage()->getCardsCount() <= 0) {
            return false;
        }
        foreach($this->getCardsStorage()->getCards() as $card) {
            $lastTransaction = $payment->getTransaction($card->getLastTransId());
            if (!$lastTransaction
                || $lastTransaction->getTxnType() != Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH
            ) {
                return false;
            }
        }
        return true;
    }






    /**
     * called if refunding
     */
    public function refund (Varien_Object $payment, $amount)
    {

    }

    /**
     * called if voiding a payment
     */
    public function void (Varien_Object $payment)
    {

    }






}

?>