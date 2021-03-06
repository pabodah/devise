<?php
/**
 * Copyright (c) Devis
 */
namespace Devis\Quotation\Model\Quote;

use Devis\Quotation\Model\QuotationFactory as CustomQuotationFactory;
use Devis\Quotation\Model\QuotationRepository as CustomQuotationRepository;
use Devis\Quotation\Model\ResourceModel\Quotation as ResourceQuotation;
use Magento\Catalog\Model\ProductRepository;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
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
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var CustomQuotationFactory
     */
    protected $quotationFactory;
    /**
     * @var ResourceQuotation
     */
    protected $resourceQuotation;
    /**
     * @var ProductRepository
     */
    protected $productRepository;
    /**
     * @var
     */
    protected $cart;
    /**
     * @var CustomQuotationRepository
     */
    protected $quotationRepository;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Theme\Block\Html\Header\Logo
     */
    protected $logo;
    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * Save constructor.
     * @param FileFactory $fileFactory
     * @param DateTime $dateTime
     * @param LoggerInterface $logger
     * @param CustomQuotationFactory $quotationFactory
     * @param ResourceQuotation $resourceQuotation
     * @param ProductRepository $productRepository
     * @param CustomQuotationRepository $quotationRepository
     * @param Cart $cart
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Theme\Block\Html\Header\Logo $logo
     * @param QuoteFactory $quoteFactory
     */
    public function __construct(
        FileFactory $fileFactory,
        DateTime $dateTime,
        LoggerInterface $logger,
        CustomQuotationFactory $quotationFactory,
        ResourceQuotation $resourceQuotation,
        ProductRepository $productRepository,
        CustomQuotationRepository $quotationRepository,
        Cart $cart,
        ScopeConfigInterface $scopeConfig,
        \Magento\Theme\Block\Html\Header\Logo $logo,
        QuoteFactory $quoteFactory
    ) {
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
        $this->logger = $logger;
        $this->quotationFactory = $quotationFactory;
        $this->resourceQuotation = $resourceQuotation;
        $this->productRepository = $productRepository;
        $this->quotationRepository = $quotationRepository;
        $this->cart = $cart;
        $this->scopeConfig = $scopeConfig;
        $this->logo = $logo;
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * Product detail page and Cart page has Get Quotation button
     * when the button is clicked, a record will be saved in devis_quotation table
     * A PDF will be downloaded
     * @param $params
     * @param $type
     * @return mixed
     */
    public function createCustomQuote($params, $type)
    {
        try {
            if ($type == 'product') {
                // get data from the product detail page
                $productIds[0] = $params["product"];
                $qty[0] = $params["qty"];

                $model = $this->quotationFactory->create();
                $model->setData('product_id', json_encode($productIds));
                $model->setData('qty', json_encode($qty));
                $model->setData('quote_id', 0);

                if (isset($params['attributes'])) {
                    $attr[0] = $params['attributes'];
                    $attrVal[0] = $params['attribute_values'];
                    $model->setData('product_options', json_encode($attr));
                    $model->setData('product_options_names', json_encode($attrVal));
                }

                $this->resourceQuotation->save($model);
                return $model->getId();
            } else {
                // get data from cart page
                $items = $params->getItems();
                $productIds = [];
                $qty = [];
                $customOptions_ids = [];
                $customOptions_values = [];

                $model = $this->quotationFactory->create();

                foreach ($items as $item) {
                    $productIds[] = $item->getProductId();
                    $qty[] = (string)$item->getQty();

                    $attributes = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());

                    if (isset($attributes['attributes_info'])) {
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
                    } else {
                        $customOptions_ids[] = [];
                        $customOptions_values[] = [];
                    }
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
            $this->logger->error($e->getMessage());
            return false;
        }
    }

    /**
     * Save custom quote data in Magento quote, add products to cart and redirect to the cart page
     * @param $id
     * @return Cart
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addToCart($id)
    {
        try {
            $customQuote = $this->quotationRepository->getById($id);

            $params = [];
            $options = [];
            $i = 0;
            $cartObj = null;

            foreach (json_decode($customQuote->getProductId()) as $itemId) {
                $params['product'] = $itemId;
                $params['qty'] = json_decode($customQuote->getQty())[$i];
                $params['item'] = json_decode($customQuote->getProductId())[$i];

                if (json_decode($customQuote->getProductOptions())) {
                    foreach (json_decode($customQuote->getProductOptions())[$i] as $key=>$value) {
                        $options[$key] = $value;
                        $params['super_attribute'] = $options;
                    }
                }
                $product = $this->productRepository->getById($itemId);
                $this->cart->addProduct($product, $params);

                $i++;
            }

            $cartObj = $this->cart->save();
            return $cartObj;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return null;
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
        $i = 0;
        $a = '';
        $quote = $this->quotationRepository->getById($id);
        foreach (json_decode($quote['product_id']) as $item) {
            $product = $this->productRepository->getById($item);
            $a .= $product->getName() . "\r\n";
            $i++;
        }
        return $a;
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

                foreach ($output as $item) {
                    foreach ($item as $key=>$value) {
                        $options .= $key . ': ' . $value . "," . "";
                    }
                }
            }
            return rtrim($options, ',');
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return false;
    }

    public function getLogoSrc()
    {
        return $this->logo->getLogoSrc();
    }
}
