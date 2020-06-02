<?php
/**
 * Copyright (c) Devis
 */
namespace Devis\Quotation\Model\Quote;

use Devis\Quotation\Model\QuotationFactory;
use Devis\Quotation\Model\ResourceModel\Quotation as ResourceQuotation;
use Magento\Catalog\Model\ProductFactory;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Quote\Api\CartRepositoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\ProductRepository;
use Devis\Quotation\Model\QuotationRepository;
use Magento\Framework\Data\Form\FormKey;
use Magento\Checkout\Model\Cart;

class Save
{
    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepositoryInterface;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    protected $quotationFactory;

    protected $resourceQuotation;

    protected $productFactory;

    protected $productRepository;
    protected $formKey;
    protected $productModel;
    protected $cart;
    protected $quotationRepository;

    /**
     * Pdf constructor.
     * @param FileFactory $fileFactory
     * @param DateTime $dateTime
     * @param CartRepositoryInterface $cartRepositoryInterface
     * @param Session $checkoutSession
     * @param LoggerInterface $logger
     * @param QuotationFactory $quotationFactory
     * @param ResourceQuotation $resourceQuotation
     * @param ProductFactory $productFactory
     * @param ProductRepository $productRepository
     * @param QuotationRepository $quotationRepository
     * @param FormKey $formKey
     */
    public function __construct(
        FileFactory $fileFactory,
        DateTime $dateTime,
        CartRepositoryInterface $cartRepositoryInterface,
        Session $checkoutSession,
        LoggerInterface $logger,
        QuotationFactory $quotationFactory,
        ResourceQuotation $resourceQuotation,
        ProductFactory $productFactory,
        ProductRepository $productRepository,
        QuotationRepository $quotationRepository,
        FormKey $formKey,
        Cart $cart
    ) {
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
        $this->quotationFactory = $quotationFactory;
        $this->resourceQuotation = $resourceQuotation;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->quotationRepository = $quotationRepository;
        $this->cart = $cart;
    }

    /**
     * @param $post
     * @return mixed
     */
    public function createCustomQuote($post)
    {
        try {
            $model = $this->quotationFactory->create();
            $model->setData('product_id', $post["product"]);
            $model->setData('qty', $post["qty"]);
            $model->setData('product_options', json_encode($post['attributes']));
            $model->setData('quote_id', 0);
            $model->setData('product_options_names', json_encode($post['attribute_values']));
            $this->resourceQuotation->save($model);

            $saveData = $this->resourceQuotation->save($model);

            return $model->getId();

        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
            return false;
        }
    }

    /**
     * @return Cart
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createQuote()
    {
        $quote = $this->quotationRepository->getById(52);
        $product = $this->productRepository->getById($quote->getProductId());

        $params = [];
        $options = [];
        //$params['form_key'] = $this->formKey->getFormKey();
        $params['qty'] = $quote->getQty();
        $params['product'] = $quote->getProductId();
        $params['item'] = $quote->getProductId();

        foreach (json_decode($quote->getProductOptions()) as $key=>$value) {
            $options[$key] = $value;
        }

        $params['super_attribute'] = $options;
        $this->cart->addProduct($product, $params);
        return $this->cart->save();
    }

    /**
     * @param $id
     * @return \Magento\Framework\App\ResponseInterface
     * @throws \Zend_Pdf_Exception
     */
    public function generatePdf($id)
    {
        $quote = $this->quotationRepository->getById($id);

        $pdf = new \Zend_Pdf();
        $pdf->pages[] = $pdf->newPage(\Zend_Pdf_Page::SIZE_A4);
        $page = $pdf->pages[0]; // this will get reference to the first page.
        $style = new \Zend_Pdf_Style();
        $style->setLineColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font, 15);
        $page->setStyle($style);
        $width = $page->getWidth();
        $hight = $page->getHeight();
        $x = 30;
        $pageTopalign = 850; //default PDF page height
        $this->y = 850 - 100; //print table row from page top – 100px
        //Draw table header row’s
        $style->setFont($font, 16);
        $page->setStyle($style);
        $page->drawRectangle(30, $this->y + 10, $page->getWidth()-30, $this->y +70, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);

        $style->setFont($font, 11);
        $page->setStyle($style);
        $page->drawText(__("Quote ID : %1", $id), $x + 5, $this->y+33, 'UTF-8');

        $style->setFont($font, 12);
        $page->setStyle($style);
        $page->drawText(__("Product Name"), $x + 60, $this->y-10, 'UTF-8');
        $page->drawText(__("Product Options"), $x + 200, $this->y-10, 'UTF-8');

        $style->setFont($font, 10);
        $page->setStyle($style);
        $add = 9;
        $page->drawText($this->getSelectedOptions($id), $x + 210, $this->y-30, 'UTF-8');
        $pro = $this->getProductName($id);
        $page->drawText($pro, $x + 65, $this->y-30, 'UTF-8');
        $page->drawRectangle(30, $this->y -62, $page->getWidth()-30, $this->y + 10, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);

        $style->setFont($font, 10);
        $page->setStyle($style);
        $page->drawText('<a href="/quotation/index/addtocart/"> Purchase </a>', $x + 65, $this->y-100);

        $style->setFont($font, 10);
        $page->setStyle($style);
        $page->drawText(__("ABC Footer example"), ($page->getWidth()/2)-50, $this->y-200);

        return $this->fileFactory->create(
            sprintf('quotation.pdf', $this->dateTime->date('Y-m-d_H-i-s')),
            $pdf->render(),
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }

    /**
     * @param $id
     * @return \Magento\Catalog\Api\Data\ProductInterface|mixed|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductName($id)
    {
        $quote = $this->quotationFactory->create()->load($id);
        $product = $this->productRepository->getById($quote['product_id']);
        return $product->getName();
    }

    /**
     * @param $id
     * @return string
     */
    public function getSelectedOptions($id)
    {
        $options = '';
        $quote = $this->quotationFactory->create()->load($id);
        $output = json_decode($quote['product_options_names']);

        foreach ($output as $key=>$value) {
            $options .= $key . ': ' . $value . "," . "";
        }
        return $options;
    }
}
