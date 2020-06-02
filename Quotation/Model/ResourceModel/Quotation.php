<?php
/**
 * Copyright (c) Devis
 */
namespace Devis\Quotation\Model\ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Quotation extends AbstractDb
{
    /**
     * Quotation constructor.
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Resource initialisation
     */
    protected function _construct()
    {
        $this->_init('devis_quotation', 'id');
    }
}
