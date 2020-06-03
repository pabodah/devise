<?php
/**
 * Copyright (c) Devis
 */
namespace Devis\Quotation\Model\Quote;

use Devis\Quotation\Model\QuotationFactory as CustomQuotationFactory;
use Devis\Quotation\Model\QuotationRepository as CustomQuotationRepository;
use Devis\Quotation\Model\ResourceModel\Quotation as ResourceQuotation;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteFactory;
use Psr\Log\LoggerInterface;

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

    protected $scopeConfig;

    protected $urlBuilder;

    protected $logo;
    protected $quoteFactory;

    /**
     * Pdf constructor.
     * @param FileFactory $fileFactory
     * @param DateTime $dateTime
     * @param CartRepositoryInterface $cartRepositoryInterface
     * @param Session $checkoutSession
     * @param LoggerInterface $logger
     * @param CustomQuotationFactory $quotationFactory
     * @param ResourceQuotation $resourceQuotation
     * @param ProductFactory $productFactory
     * @param ProductRepository $productRepository
     * @param CustomQuotationRepository $quotationRepository
     * @param FormKey $formKey
     * @param Cart $cart
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        FileFactory $fileFactory,
        DateTime $dateTime,
        CartRepositoryInterface $cartRepositoryInterface,
        Session $checkoutSession,
        LoggerInterface $logger,
        CustomQuotationFactory $quotationFactory,
        ResourceQuotation $resourceQuotation,
        ProductFactory $productFactory,
        ProductRepository $productRepository,
        CustomQuotationRepository $quotationRepository,
        FormKey $formKey,
        Cart $cart,
        ScopeConfigInterface $scopeConfig,
        UrlInterface $urlBuilder,
        \Magento\Theme\Block\Html\Header\Logo $logo,
        QuoteFactory $quoteFactory
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
        $this->scopeConfig = $scopeConfig;
        $this->urlBuilder = $urlBuilder;
        $this->logo = $logo;
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * @param $params
     * @param $type
     * @return mixed
     */
    public function createCustomQuote($params, $type)
    {
        try {
            if ($type == 'product') {
                $model = $this->quotationFactory->create();
                $model->setData('product_id', $params["product"]);
                $model->setData('qty', $params["qty"]);
                $model->setData('quote_id', 0);

                if (isset($params['attributes'])) {
                    $model->setData('product_options', json_encode($params['attributes']));
                    $model->setData('product_options_names', json_encode($params['attribute_values']));
                }

                $this->resourceQuotation->save($model);
                return $model->getId();
            } else {
                $items = $params->getItems();
                $productIds = [];
                $qty = [];
                $customOptions_ids = [];
                $customOptions_values = [];

                $model = $this->quotationFactory->create();

                foreach ($items as $item) {
                    $productIds[] = $item->getProductId();
                    $qty[] = $item->getQty();

                    $attributes = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                    $customOptions = $attributes['attributes_info'];
                    $product_options = [];
                    $product_options_names = [];
                    if (!empty($customOptions)) {
                        foreach ($customOptions as $option) {
                            $product_options[$option['option_id']] = $option['option_value'];
                            $product_options_names[$option['label']] = $option['value'];
                        }
                    }

                    $customOptions_ids[] = $product_options;
                    $customOptions_values[] = $product_options_names;

                    /*$model->setData('product_id', $item->getProductId());
                    $model->setData('qty', $item->getQty());
                    $model->setData('quote_id', $item->getQuoteId());

                    $attributes = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                    $customOptions = $attributes['attributes_info'];
                    $val = [];
                    if (!empty($customOptions)) {
                        foreach ($customOptions as $option) {
                            $val[] = $option;
                        }
                    }

                    if (isset($customOptions)) {
                        $model->setData('product_options', json_encode($val));
                    }*/


                }
                if (isset($customOptions)) {
                    $model->setData('product_options', json_encode($customOptions_ids));
                    $model->setData('product_options_names', json_encode($customOptions_values));
                }
                $model->setData('qty', json_encode($qty));
                $model->setData('quote_id', $item->getQuoteId());
                $model->setData('product_id', json_encode($productIds));
                $this->resourceQuotation->save($model);

                return $model->getId();
            }
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
            return false;
        }
    }

    /**
     * Save custom quote data in Magento quote
     * @param $id
     * @return Cart
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function saveToQuote($id)
    {
        try {
            $customQuote = $this->quotationRepository->getById($id);

            /*if ($customQuote->getQuoteId()) {
                $quote = $this->quoteFactory->create()->load($customQuote->getQuoteId());

                if ($quote) {
                    $quote->setIsActive(1);
                    try {
                        $this->cart->setQuote($quote);
                        $this->cart->saveQuote();
                        $quote->save();
                        //$this->messageManager->addSuccess(__('Added to cart successfully.'));
                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                        //$this->messageManager->addException($e, __('%1', $e->getMessage()));
                    }
                }
            } else {*/


            $product = $this->productRepository->getById($customQuote->getProductId());

            $params = [];
            $options = [];
            //$params['form_key'] = $this->formKey->getFormKey();
            $params['qty'] = $customQuote->getQty();
            $params['product'] = $customQuote->getProductId();
            $params['item'] = $customQuote->getProductId();

            if ($customQuote->getProductOptions()) {
                foreach (json_decode($customQuote->getProductOptions()) as $key=>$value) {
                    $options[$key] = $value;
                    $params['super_attribute'] = $options;
                }
            }

            $this->cart->addProduct($product, $params);
            return $this->cart->save();
            /*}*/
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
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
        $quote = $this->quotationRepository->getById($id);
        $product = $this->productRepository->getById($quote['product_id']);
        return $product->getName();
    }

    /**
     * @param $id
     * @return string
     */
    public function getSelectedOptions($id)
    {
        try {
            $options = '';
            $quote = $this->quotationRepository->getById($id);

            if (isset($quote['product_options_names'])) {
                $output = json_decode($quote['product_options_names']);

                foreach ($output as $key=>$value) {
                    $options .= $key . ': ' . $value . "," . "";
                }
            }
            return $options;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return false;
    }

    public function pdf()
    {
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
        $page->drawText(__("Quote ID : %1", '10000'), $x + 5, $this->y+33, 'UTF-8');

        $style->setFont($font, 12);
        $page->setStyle($style);
        $page->drawText(__("Product Name"), $x + 60, $this->y-10, 'UTF-8');
        $page->drawText(__("Product Options"), $x + 200, $this->y-10, 'UTF-8');

        $style->setFont($font, 10);
        $page->setStyle($style);
        $add = 9;
        $page->drawText('Test', $x + 210, $this->y-30, 'UTF-8');
        $pro = 'TEST2';
        $page->drawText($pro, $x + 65, $this->y-30, 'UTF-8');
        $page->drawRectangle(30, $this->y -62, $page->getWidth()-30, $this->y + 10, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);

        $style->setFont($font, 10);
        $page->setStyle($style);
        $page->drawText('TESTEST', $x + 65, $this->y-100);

        $style->setFont($font, 10);
        $page->setStyle($style);
        /*$page->drawText(__("ABC Footer example"), ($page->getWidth()/2)-50, $this->y-200);*/

        $logoUrl = $this->getLogoSrc();
        if ($logoUrl) {
            if (is_file($logoUrl)) {
                $pdfImage = \Zend_Pdf_Image::imageWithPath($logoUrl);
                $page->drawImage($pdfImage, 10, 500);
            }
        }

        return $this->fileFactory->create(
            sprintf('quotation.pdf', $this->dateTime->date('Y-m-d_H-i-s')),
            $pdf->render(),
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }

    public function getLogoSrc()
    {
        return $this->logo->getLogoSrc();
    }
}
