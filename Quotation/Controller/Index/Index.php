<?php
/**
 * Copyright (c) Devis
 */
namespace Devis\Quotation\Controller\Index;

use Devis\Quotation\Model\Quote\Save as QuoteSave;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Index extends Action
{
    /**
     * @var QuoteSave
     */
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
        parent::__construct($context);
        $this->quoteSave = $quoteSave;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Zend_Pdf_Exception
     */
    public function execute()
    {
        $this->quoteSave->addData();
    }
}
