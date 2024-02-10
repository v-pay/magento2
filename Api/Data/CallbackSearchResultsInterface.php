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

declare(strict_types=1);

namespace VirtualPay\Payment\Api\Data;

interface CallbackSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get transaction list.
     * @return \VirtualPay\Payment\Api\Data\CallbackInterface[]
     */
    public function getItems();

    /**
     * Set entity_id list.
     * @param \VirtualPay\Payment\Api\Data\CallbackInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

