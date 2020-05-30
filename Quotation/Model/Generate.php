<?php
/**
 * Copyright (c) Devis
 */

namespace Devis\Quotation\Model;

use Magento\Quote\Api\CartRepositoryInterface;

class Generate
{
    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepositoryInterface;

    /**
     * Generate constructor.
     * @param CartRepositoryInterface $cartRepositoryInterface
     */
    public function __construct(
        CartRepositoryInterface $cartRepositoryInterface
    ) {
        $this->cartRepositoryInterface = $cartRepositoryInterface;
    }

    public function getData()
    {
        //$quote = $this->cartRepositoryInterface->get(1);
    }
}
