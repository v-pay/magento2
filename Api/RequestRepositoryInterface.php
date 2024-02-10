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

interface RequestRepositoryInterface
{
    /**
     * Save Queue
     * @param \VirtualPay\Payment\Api\Data\RequestInterface $callback
     * @return \VirtualPay\Payment\Api\Data\RequestInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        Data\RequestInterface $callback
    );

    /**
     * Retrieve RequestInterface
     * @param string $id
     * @return \VirtualPay\Payment\Api\Data\RequestInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($id);

    /**
     * Retrieve Queue matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \VirtualPay\Payment\Api\Data\RequestSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );
}
