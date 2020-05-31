<?php
/**
 * Copyright (c) Devis
 */

namespace Devis\Quotation\Block\Product\View;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Devis\Quotation\Model\Config;

class AddToQuotation extends Template
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Cart constructor.
     * @param Template\Context $context
     * @param Config $config
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Config $config,
        Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->registry = $registry;
    }

    /**
     * @return mixed
     */
    public function isQuotationEnabled()
    {
        return $this->config->isEnabled();
    }

    /**
     * @return mixed|null
     */
    public function getProductId()
    {
        return $this->registry->registry('current_product')->getId();
    }
}
