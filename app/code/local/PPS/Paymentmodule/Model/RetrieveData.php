<?php
require 'PPSObjects.php';

class PPS_Paymentmodule_Model_RetrieveData extends Mage_Payment_Model_Method_Cc
{
    /*
     * All variables that end in PPS will be created and have their information filled
     * All variables that end in Magento will be created and used to pull information from
     */
    private $orderMagento;
    private $customerMagento;
    private $paymentMagento;
    private $purchasePPS;
    private $amount;

    public function __construct(Varien_Object $payment, $amount)
    {
        //Instantiate needed objects
        $this->orderMagento = $payment->getOrder();
        $this->purchasePPS = new Purchase();

        //Save passed variables in global
        $this->amount = $amount;
        $this->paymentMagento = $payment;

        $this->customerMagento = $payment->getorder()->getCustomer();
    }

    //Pull all credit information from Magento
    public function retrieveCardInformation($token = false, $forRefund = false)
    {
        $cardAccountPPS = new CardAccount();
        $cardAccountPPS->number = str_replace(' ', '', $this->paymentMagento->getCcNumber());
        $cardAccountPPS->expiryMonth = $this->paymentMagento->getCcExpMonth();
        $cardAccountPPS->expiryYear = $this->paymentMagento->getCcExpYear();
        $cardAccountPPS->cvv = $this->paymentMagento->getCcCid();
        $cardAccountPPS->avsZip = $this->paymentMagento->getData('postcode');
        $cardAccountPPS->last4 = $this->paymentMagento->getCcLast4();
        $cardAccountPPS->token = $token;

        return $cardAccountPPS;
    }

    //Pull customer information from Magento
    public function retrieveCustomerInformation()
    {
        $customerPPS = new Customer();
        //Retrieve the addresses
        $billingaddress = $this->orderMagento->getBillingAddress();

        $customerPPS->email = $this->orderMagento->getCustomer()->getEmail();
        $customerPPS->name = $billingaddress->getData('firstname') . $billingaddress->getData('lastname');
        $customerPPS->number = Mage::getSingleton('customer/session')->getCustomer()->getId();

        return $customerPPS;
    }

    //Pull the customer account information from Magento
    public function retrieveCustomerAccountInformation()
    {
        $customerAccountPPS = new Account();

        $customerAccountPPS->email = $this->orderMagento->getEmail();
        $customerAccountPPS->firstName = $this->orderMagento->getData('firstname');
        $customerAccountPPS->lastName = $this->orderMagento->getData('lastname');
        $customerAccountPPS->id = Mage::getSingleton('customer/sessions')->getCustomer()->getid();

        return $customerAccountPPS;
    }

    //Pull order specific data from magento
    public function retrieveOrderInformation($forRefund = false)
    {
        $orderPPS = new Order();

        $orderPPS->purchaseOrderNumber = $this->orderMagento->getincrementId();
        $forRefund ? $orderPPS->totalAmount = $this->amount*(-1) : $this->amount;
        $orderPPS->shipAmount = $this->getShippingAmount();
        $orderPPS->taxAmount = $this->orderMagento->getTaxInvoiced();
        $orderPPS->merchantId =  $this->getMerchantId();
        $orderPPS->billingAddress = $this->getBillingAddress();
        $orderPPS->shippingAddress = $this->getShippingAddress();

        $orderPPS->paymentType = 'Card';
        $orderPPS->type = "Sale";

        $orderPPS->purchases = $this->retrievePurchaseInformation();
        return $orderPPS;
    }

    //Pull payment specific information from Magento
    public function retrievePaymentInformation($authOnly, $authCode = null)
    {
        $paymentPPS = new Payment();
        $paymentPPS->amount = $this->amount;
        $paymentPPS->tax = $this->orderMagento->getTaxAmount();
        $paymentPPS->authOnly = $authOnly;
        $paymentPPS->cardPresent = false;
        $paymentPPS->currency = 'USD';
        $paymentPPS->merchantId = $this->getMerchantId();
        $paymentPPS->tenderType = 'card';
        $paymentPPS->authCode = $authCode;

        return $paymentPPS;
    }

    //Pull all item data for each item purchased and store it in a list of Purchase objects
    public function retrievePurchaseInformation()
    {
        $items = $this->orderMagento->getAllItems();

        $purchasePPS = array();
        foreach($items as $item)
        {
            $purchase = new Purchase();
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            $purchase->quantity = $item->getQtyOrdered()*1;
            $purchase->description = Mage::getModel('catalog/product')->load($product->getId())->getDescription();
            $purchase->id = $item->getSku();
            $purchase->price = $item->getprice();
            $purchase->productId = $item->getSku();
            $purchase->productName = $item->getName();
            $purchase->totalAmount = $item->getQtyOrdered() * $item->getPrice()*1;
            $purchase->discountAmount = $item->getDiscountAmount();
            $purchase->size = $item->getSize();
            $purchasePPS[] = $purchase;
        }
        return $purchasePPS;
    }

    public function successfulPayment($paymentId)
    {
        $this->paymentMagento->setIsTransactionPending(false);
        $state = 'Complete';
        $status = 'complete';
        $comment = 'complete';
        $isCustomerNotified = false;

        $this->orderMagento->setState($state, $status, $comment, $isCustomerNotified);
        $this->orderMagento->setCanInvoiceFlag(true);
        $this->paymentMagento->setAnetTransType(self::REQUEST_TYPE_AUTH_CAPTURE);
        $this->paymentMagento->setCcTransId($paymentId);
        $transactionSave = Mage::getModel('core/resource_transaction');
        $transactionSave->addObject($this->orderMagento);
        $transactionSave->save();
        $this->paymentMagento->setLastTransId();
        $this->orderMagento->save();
        return true;
    }

    public function unsuccessfulPayment()
    {
        $this->orderMagento->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, 'pending_payment', '', false);
        $this->orderMagento->setCanSendNewEmailFlag(false);
        $this->orderMagento->setCanInvoiceFlag(false);
        $this->orderMagento->save();
        return true;
    }

    public function getTransactionId()
    {
        $transactionId = $this->paymentMagento->getCcTransId();
        return $transactionId ? $transactionId : false;
    }

    public function getOrderTotal(Varien_Object $payment)
    {
        $orderAmount = $this->orderMagento->getGrandTotal();
        return $orderAmount ? $orderAmount : false;
    }

    //Pull the merchant Id from Magento
    private function getMerchantId()
    {
        $config = Mage::getStoreConfig('payment/paymentmodule');
        if(!($merchantId = Mage::helper('core')->decrypt($config['login']))) {
            Mage::log("Merchant ID not available");
            return null;
        }
        else
            return $merchantId;
    }

    //Pull the customer billing address from Magento
    private function getBillingAddress()
    {
        $billingAddress = $this->orderMagento->getBillingAddress();
        $billingStreetAddress = $billingAddress->getStreet();
        //Setup billing address to be sent to PPS
        $ppsBillingAddress = array();
        $ppsBillingAddress['description'] = 'Billing Address';
        $ppsBillingAddress['address1'] = $billingStreetAddress[0];
        $ppsBillingAddress['city'] =  $billingAddress->getData('city');
        $ppsBillingAddress['state'] = $billingAddress->getData('region');
        $ppsBillingAddress['zip'] = $billingAddress->getData('postcode');
        $ppsBillingAddress['country'] = $billingAddress->getData('country_id');

        //Check conditionals
        if(isset($billingStreetAddress[1])) {
            $ppsBillingAddress['address2'] = $billingStreetAddress[1];
        }
        return $ppsBillingAddress;
    }


    //Pull the customer shipping address from Magento
    private function getShippingAddress()
    {
        $shippingaddress = $this->orderMagento->getShippingAddress();
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

    //Pull the shipping cost from Magento
    private function getShippingAmount()
    {
        $shippingaddress = $this->orderMagento->getShippingAddress();
        return $shippingaddress['shipping_amount'];
    }

}//End class