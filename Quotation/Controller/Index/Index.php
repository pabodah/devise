<?php
/**
 * Copyright (c) Devis
 */
namespace Devis\Quotation\Controller\Index;

use Devis\Quotation\Model\Quote\Save as QuoteSave;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;

class Index extends Action
{
    /**
     * @var QuoteSave
     */
    protected $quoteSave;
    /**
     * @var Session
     */
    protected $cart;

    /**
     * Index constructor.
     * @param Context $context
     * @param QuoteSave $quoteSave
     * @param Session $cart
     */
    public function __construct(
        Context $context,
        QuoteSave $quoteSave,
        Session $cart
    ) {
        parent::__construct($context);
        $this->quoteSave = $quoteSave;
        $this->cart = $cart;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Zend_Pdf_Exception
     */
    public function execute()
    {
        $attributes = $this->getRequest()->getParam('attribute_data');

        if ($attributes) {
            $customQuote = $this->quoteSave->createCustomQuote($attributes, 'product');
        } else {
            $customQuote = $this->quoteSave->createCustomQuote($this->cart->getQuote(), 'quote');
        }

        return $this->resultFactory
            ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
            ->setData([
                'status'  => $customQuote
            ]);

    }
}
