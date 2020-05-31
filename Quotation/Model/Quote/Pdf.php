<?php
/**
 * Copyright (c) Devis
 */
namespace Devis\Quotation\Model\Quote;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Quote\Api\CartRepositoryInterface;
use Psr\Log\LoggerInterface;

class Pdf
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

    /**
     * Pdf constructor.
     * @param FileFactory $fileFactory
     * @param DateTime $dateTime
     * @param CartRepositoryInterface $cartRepositoryInterface
     * @param Session $checkoutSession
     * @param LoggerInterface $logger
     */
    public function __construct(
        FileFactory $fileFactory,
        DateTime $dateTime,
        CartRepositoryInterface $cartRepositoryInterface,
        Session $checkoutSession,
        LoggerInterface $logger
    ) {
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPdf()
    {
        try {
            $currentQuote = $this->cartRepositoryInterface->get($this->checkoutSession->getQuoteId());
            $this->generate($currentQuote);
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }

    /**
     * @param $quote
     * @return \Magento\Framework\App\ResponseInterface
     * @throws \Zend_Pdf_Exception
     */
    public function generate($quote)
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
        $page->drawText(__("Quote ID : %1", "100000"), $x + 5, $this->y+33, 'UTF-8');

        $style->setFont($font, 12);
        $page->setStyle($style);
        $page->drawText(__("PRODUCT NAME"), $x + 60, $this->y-10, 'UTF-8');
        $page->drawText(__("Product Options"), $x + 200, $this->y-10, 'UTF-8');

        $style->setFont($font, 10);
        $page->setStyle($style);
        $add = 9;
        $page->drawText("Size: M, Color: Red", $x + 210, $this->y-30, 'UTF-8');
        $pro = "ABC product";
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
