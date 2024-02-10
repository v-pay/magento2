<?php

/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    VirtualPay
 * @package     VirtualPay_Payment
 *
 *
 */

namespace VirtualPay\Payment\Gateway\Request\Pix;

use VirtualPay\Payment\Gateway\Request\PaymentsRequest;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;

class TransactionRequest extends PaymentsRequest implements BuilderInterface
{
    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function build(array $buildSubject)
    {
        if (
            !isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $buildSubject['payment']->getPayment();
        $order = $payment->getOrder();

        $request = $this->getTransaction($order, $buildSubject['amount']);

        return ['request' => $request, 'client_config' => ['store_id' => $order->getStoreId()]];
    }
}
