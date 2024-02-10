<?php

/**
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    VirtualPay
 * @package     VirtualPay_Payment
 *
 */

namespace VirtualPay\Payment\Observer;

use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Model\Quote\Payment;

class DataAssignObserver extends AbstractDataAssignObserver
{
    /**
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);

        /** @var array $additionalData */
        $additionalData = $data->getAdditionalData();

        if (!empty($additionalData)) {
            /** @var Payment $paymentInfo */
            $paymentInfo = $this->readPaymentModelArgument($observer);
            $paymentInfo->setAdditionalInformation('virtualpay_customer_taxvat', $additionalData['taxvat'] ?? '');
            $paymentInfo->setAdditionalInformation('finger_print', $additionalData['fingerprint'] ?? '');
        }
    }
}
