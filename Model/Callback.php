<?php

/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    VirtualPay
 * @package     VirtualPay_Payment
 */

namespace VirtualPay\Payment\Model;

use VirtualPay\Payment\Api\Data\CallbackExtensionInterface;
use VirtualPay\Payment\Api\Data\CallbackInterface;
use Magento\Framework\Model\AbstractModel;

class Callback extends AbstractModel implements CallbackInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'virtualpay_callback';

    /**
     * @var string
     */
    protected $_cacheTag = 'virtualpay_callback';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'virtualpay_callback';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Callback::class);
    }

    /**
     * @ingeritdoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @ingeritdoc
     */
    public function setStatus($status)
    {
        $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritDoc
     */
    public function getMethod()
    {
        return $this->getData(self::METHOD);
    }

    /**
     * @inheritDoc
     */
    public function setMethod($method)
    {
        $this->setData(self::METHOD, $method);
    }

    /**
     * @ingeritdoc
     */
    public function getIncrementId()
    {
        return $this->getData(self::INCREMENT_ID);
    }

    /**
     * @ingeritdoc
     */
    public function setIncrementId($incrementId)
    {
        $this->setData(self::INCREMENT_ID, $incrementId);
    }

    /**
     * @ingeritdoc
     */
    public function getPayload()
    {
        return $this->getData(self::PAYLOAD);
    }

    /**
     * @ingeritdoc
     */
    public function setPayload($payload)
    {
        $this->setData(self::PAYLOAD, $payload);
    }

    /**
     * @ingeritdoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @ingeritdoc
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @ingeritdoc
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @ingeritdoc
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritDoc
     */
    public function setExtensionAttributes(CallbackExtensionInterface $extensionAttributes)
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
