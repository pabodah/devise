<?php
/**
 * Copyright (c) Devis
 */
namespace Devis\Quotation\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Devis\Quotation\Model\Quote\Pdf as QuotePdf;

class Index extends Action
{
    /**
     * @var QuotePdf
     */
    protected $quotePdf;

    /**
     * Index constructor.
     * @param Context $context
     * @param QuotePdf $quotePdf
     */
    public function __construct(
        Context $context,
        QuotePdf $quotePdf
    ) {
        $this->quotePdf = $quotePdf;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Zend_Pdf_Exception
     */
    public function execute()
    {
        $this->quotePdf->getPdf();
    }
}
