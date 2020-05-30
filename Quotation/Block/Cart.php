<?php
/**
 * Copyright (c) Devis
 */

namespace Devis\Quotation\Block;

use Magento\Framework\View\Element\Template;
use Devis\Quotation\Model\Config;

class Cart extends Template
{
    protected $modelGenerate;

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
    public function getContent()
    {
        return $this->modelGenerate->getData();
    }

    /**
     * @return mixed
     */
    public function isQuotationEnabled()
    {
        return $this->config->isEnabled();
    }
}
