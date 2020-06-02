<?php
/**
 * Copyright (c) Devis
 */
namespace Devis\Quotation\Model\Quote;

use Devis\Quotation\Model\QuotationFactory;
use Devis\Quotation\Model\ResourceModel\Quotation as ResourceQuotation;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\ProductFactory;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Quote\Api\CartRepositoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\ProductRepository;

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

    protected $option;
    protected $productRepository;

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
     * @param Option $option
     * @param ProductRepository $productRepository
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
        Option $option,
        ProductRepository $productRepository
    ) {
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
        $this->quotationFactory = $quotationFactory;
        $this->resourceQuotation = $resourceQuotation;
        $this->productFactory = $productFactory;
        $this->option = $option;
        $this->productRepository = $productRepository;
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addOtherData()
    {
        try {
            $model = $this->quotationFactory->create();
            $items = $this->cartRepositoryInterface->get($this->checkoutSession->getQuoteId())->getItems();

            foreach ($items as $item) {
                $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                $customOptions = $options['attributes_info'];

                $model->addData([
                    "product_id" => $item->getItemId(),
                    "qty" => $item->getQty(),
                    "product_options" => json_encode($customOptions),
                    "quote_id" => $item->getQuoteId()
                ]);
                $saveData = $this->resourceQuotation->save($model);
            }
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }

    /**
     * @param $post
     * @throws \Magento\Framework\Exception\AlreadyExistsException
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

            /*if ($saveData) {
                $this->generatePdf($model);
            }*/
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }

    /**
     * @param $model
     * @return mixed
     */
    public function getAttributeOptions($model)
    {
        return $model['product_options_name'];
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
            $options .= $key . ': ' . $value . "\r\n";
        }
        return $options;
    }

    public function createQuote()
    {
        /*$collection = $this->quotationFactory->create();
        $items = $collection->getCollection()->addFilter('quote_id', 10)->getItems();
        //$quote->addAttributeToFilter('quote_id', 10);
        foreach ($items as $item) {
            $product = $this->productFactory->create()->load(26);
            $cart = $this->cartRepositoryInterface;

            $params = [];
            $options = [];
            $params['qty'] = $item->getData('qty');
            $params['product'] = $item->getData('product_id');

            foreach (json_decode($item->getData('product_options')) as $o) {
                foreach ($o->getValues() as $value) {
                    $options[$value['option_id']] = $value['option_value'];
                }
            }
            $params['options'] = $options;
            $cart->addProduct($product, $params);
            $cart->save();
        }*/

        /*$product = $this->productFactory->load(26);

        $cart = $this->cartRepositoryInterface;

        $params = [];
        $options = [];
        $params['qty'] = 1;
        $params['product'] = 26;

        foreach ($product->getOptions() as $o) {
            foreach ($o->getValues() as $value) {
                $options[$value['option_id']] = $value['option_type_id'];
            }
        }

        $params['options'] = $options;
        $cart->addProduct($product, $params);
        $cart->save();*/
    }

    /**
     * @param $id
     * @return \Magento\Framework\App\ResponseInterface
     * @throws \Zend_Pdf_Exception
     */
    public function generatePdf($id)
    {

        $quote = $this->quotationFactory->create()->load($id);

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
        $page->drawText(__("PRODUCT NAME"), $x + 60, $this->y-10, 'UTF-8');
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
        $page->drawText(__("ABC Footer example"), ($page->getWidth()/2)-50, $this->y-200);

        return $this->fileFactory->create(
            sprintf('quotation.pdf', $this->dateTime->date('Y-m-d_H-i-s')),
            $pdf->render(),
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
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
        $page->drawText(__("Quote ID : %1", 00000), $x + 5, $this->y+33, 'UTF-8');

        $style->setFont($font, 12);
        $page->setStyle($style);
        $page->drawText(__("PRODUCT NAME"), $x + 60, $this->y-10, 'UTF-8');
        $page->drawText(__("Product Options"), $x + 200, $this->y-10, 'UTF-8');

        $style->setFont($font, 10);
        $page->setStyle($style);
        $add = 9;
        $page->drawText('test', $x + 210, $this->y-30, 'UTF-8');
        //$pro = $this->getProductName($customQuote);
        $pro = 'TEST';
        $page->drawText($pro, $x + 65, $this->y-30, 'UTF-8');
        $page->drawRectangle(30, $this->y -62, $page->getWidth()-30, $this->y + 10, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);

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
}
