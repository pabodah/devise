<?php
/**
 * Copyright (c) Devis
 */
namespace Devis\Quotation\Model\ResourceModel;

class Quotation extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function _construct()
    {
        $this->_init("devis_quotation", "id");
    }
}
