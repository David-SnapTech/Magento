<?php

class PPS_Paymentmodule_Adminhtml_Model_System_Config_Source_AuthType
{
    public function toOptionArray()
    {
	return true;
	/*
        return array(
            array(
                'value' => Mage_Paygate_Model_Authorizenet::ACTION_AUTHORIZE,
                'label' => Mage::helper('paygate')->__('Authorize Only')
            ),
            array(
                'value' => Mage_Paygate_Model_Authorizenet::ACTION_AUTHORIZE_CAPTURE,
                'label' => Mage::helper('paygate')->__('Authorize and Capture')
            ),
        );
	}
	*/
	}
}
