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

namespace VirtualPay\Payment\Observer;

use Magento\Quote\Api\Data\CartInterface;
use VirtualPay\Payment\Model\Ui\Pix\ConfigProvider;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;

class CheckoutSubmitBefore implements ObserverInterface
{
    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        /** @var Quote $quote */
        $quote = $event->getQuote();

        if (
            $quote->getPayment()
            && $quote->getPayment()->getMethod() == ConfigProvider::CODE
        ) {
            $quote->unsetData(CartInterface::KEY_RESERVED_ORDER_ID);
            $quote->reserveOrderId();
            $this->quoteRepository->save($quote);
        }
    }
}
