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

declare(strict_types=1);

namespace VirtualPay\Payment\Api;

interface CallbackRepositoryInterface
{
    /**
     * Save Queue
     * @param \VirtualPay\Payment\Api\Data\CallbackInterface $callback
     * @return \VirtualPay\Payment\Api\Data\CallbackInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        Data\CallbackInterface $callback
    );

    /**
     * Retrieve CallbackInterface
     * @param string $id
     * @return \VirtualPay\Payment\Api\Data\CallbackInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($id);

    /**
     * Retrieve Queue matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \VirtualPay\Payment\Api\Data\CallbackSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );
}
