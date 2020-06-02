<?php
/**
 * Copyright (c) Devis
 */
namespace Devis\Quotation\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Devis\Quotation\Model\Quote\Save as QuoteSave;

class AddToCart extends Action
{

    protected $quoteSave;

    /**
     * Index constructor.
     * @param Context $context
     * @param QuoteSave $quoteSave
     */
    public function __construct(
        Context $context,
        QuoteSave $quoteSave
    ) {
        $this->quoteSave = $quoteSave;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Zend_Pdf_Exception
     */
    public function execute()
    {
        $this->quoteSave->createQuote();
    }
}