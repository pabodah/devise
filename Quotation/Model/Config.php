<?php
/**
 * Copyright (c) Devis
 */
namespace Devis\Quotation\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    const XML_DEVIS_QUOTATION_ENABLE = 'quotation_settings/general/is_enable';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return mixed
     */
    public function isEnabled()
    {
        return $this->scopeConfig->getValue(static::XML_DEVIS_QUOTATION_ENABLE, 'store');
    }
}
