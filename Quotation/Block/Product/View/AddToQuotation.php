<?php
/**
 * Copyright (c) Devis
 */

namespace Devis\Quotation\Block\Product\View;

use Magento\Framework\View\Element\Template;
use Devis\Quotation\Model\Config;

class AddToQuotation extends Template
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * Cart constructor.
     * @param Template\Context $context
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
    }

    /**
     * @return mixed
     */
    public function isQuotationEnabled()
    {
        return $this->config->isEnabled();
    }
}
