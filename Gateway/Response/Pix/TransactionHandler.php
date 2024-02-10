<?php
/**
 *
 *
 *
 *
 *
 *
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

namespace VirtualPay\Payment\Gateway\Response\Pix;

use VirtualPay\Payment\Helper\Order as HelperOrder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Api\Data\TransactionInterface;

class TransactionHandler implements HandlerInterface
{
    /**
     * @var \VirtualPay\Payment\Helper\Order
     */
    protected $helperOrder;

    /**
     * constructor.
     * @param HelperOrder $helperOrder
     */
    public function __construct(
        HelperOrder $helperOrder
    ) {
        $this->helperOrder = $helperOrder;
    }

    /**
     * Handles transaction id
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function handle(array $handlingSubject, array $response)
    {
        if (!isset($handlingSubject['payment'])
            || !$handlingSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException(__('Payment data object should be provided'));
        }

        /** @var PaymentDataObjectInterface $paymentData */
        $paymentData = $handlingSubject['payment'];
        $transaction = $response['transaction'];

        if ((isset($response['status_code']) && $response['status_code'] >= 300) || !isset($transaction['data_response'])) {
            throw new LocalizedException(__('There was an error processing your request.'));
        }

        /** @var $payment \Magento\Sales\Model\Order\Payment */
        $payment = $paymentData->getPayment();
        $responseTransaction = $transaction['data_response']['transaction'];
        $payment = $this->helperOrder->updateDefaultAdditionalInfo($payment, $responseTransaction);
        $payment = $this->helperOrder->updatePixAdditionalInfo($payment, $responseTransaction);
        $payment->setIsTransactionClosed(false);

        $state = $this->helperOrder->getPaymentStatusState($payment);

        if ($this->helperOrder->canSkipOrderProcessing($state)) {
            $payment->getOrder()->setState($state);
            $payment->setSkipOrderProcessing(true);
            $payment->addTransaction(TransactionInterface::TYPE_AUTH);
        }
    }
}
