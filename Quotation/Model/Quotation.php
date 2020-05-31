<?php
/**
 * Copyright (c) Devis
 */
namespace Devis\Quotation\Model;

class Quotation extends \Magento\Framework\Model\AbstractModel
{
    public function _construct()
    {
        $this->_init("Devis\Quotation\Model\ResourceModel\Quotation");
    }
}
