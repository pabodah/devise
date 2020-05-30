<?php
/**
 * Copyright (c) Devis
 */

namespace Devis\Quotation\Block;

use Magento\Framework\View\Element\Template;
use Devis\Quotation\Model\Generate as ModelGenerate;

class Generate extends Template
{
    protected $modelGenerate;

    /**
     * Generate constructor.
     * @param Template\Context $context
     * @param ModelGenerate $modelGenerate
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        ModelGenerate $modelGenerate,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->modelGenerate->getData();
    }
}
