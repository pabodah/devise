<?php
/**
 * Copyright (c) Devis
 */
namespace Devis\Quotation\Controller\Index;

use Devis\Quotation\Model\Quote\Save as QuoteSave;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Cart;

class Index extends Action
{
    /**
     * @var QuoteSave
     */
    protected $quoteSave;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * Index constructor.
     * @param Context $context
     */
    public function __construct(
        Context $context,
        QuoteSave $quoteSave,
        Cart $cart
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
        if ($this->getRequest()->getParam('attribute_data')) {
            $attributes = $this->getRequest()->getParam('attribute_data');
            $customQuote = $this->quoteSave->createCustomQuote($attributes, 'product');

            return $this->resultFactory
                ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                ->setData([
                    'status'  => $customQuote
                ]);
        } else {
            $customQuote = $this->quoteSave->createCustomQuote($this->cart->getQuote(), 'quote');

            return $this->resultFactory
                ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                ->setData([
                    'status'  => $customQuote
                ]);
            /*if ($quote) {
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('checkout/cart');
                return $resultRedirect;
            }*/
        }

    }
}
