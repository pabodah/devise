<?php
/**
 * Copyright (c) Devis
 */
namespace Devis\Quotation\Model;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Devis\Quotation\Api\QuotationRepositoryInterface;
use Devis\Quotation\Api\Data\QuotationInterface;
use Devis\Quotation\Api\Data\QuotationInterfaceFactory;
use Devis\Quotation\Api\Data\QuotationSearchResultsInterfaceFactory;
use Devis\Quotation\Model\ResourceModel\Quotation as ResourceData;
use Devis\Quotation\Model\ResourceModel\Quotation\CollectionFactory as QuotationCollectionFactory;

class QuotationRepository implements QuotationRepositoryInterface
{
    /**
     * @var array
     */
    protected $instances = [];
    /**
     * @var ResourceData
     */
    protected $resource;
    /**
     * @var QuotationCollectionFactory
     */
    protected $quotationCollectionFactory;
    /**
     * @var QuotationSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;
    /**
     * @var QuotationInterfaceFactory
     */
    protected $quotationInterfaceFactory;
    /**
     * @var DataObjectHelper
     */
    protected $quotationObjectHelper;

    /**
     * QuotationRepository constructor.
     * @param ResourceData $resource
     * @param QuotationCollectionFactory $dataCollectionFactory
     * @param QuotationSearchResultsInterfaceFactory $dataSearchResultsInterfaceFactory
     * @param QuotationInterfaceFactory $dataInterfaceFactory
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        ResourceData $resource,
        QuotationCollectionFactory $dataCollectionFactory,
        QuotationSearchResultsInterfaceFactory $dataSearchResultsInterfaceFactory,
        QuotationInterfaceFactory $dataInterfaceFactory,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->resource = $resource;
        $this->quotationCollectionFactory = $dataCollectionFactory;
        $this->searchResultsFactory = $dataSearchResultsInterfaceFactory;
        $this->quotationInterfaceFactory = $dataInterfaceFactory;
        $this->quotationObjectHelper = $dataObjectHelper;
    }

    /**
     * @param $id
     * @return mixed|void
     */
    public function get($id)
    {
        // TODO: Implement get() method.
    }

    public function getById($dataId)
    {
        if (!isset($this->_instances[$dataId])) {
            /** @var \Devis\Quotation\Api\Data\QuotationInterface|\Magento\Framework\Model\AbstractModel $data */
            $data = $this->quotationInterfaceFactory->create();
            $this->resource->load($data, $dataId);
            if (!$data->getId()) {
                throw new NoSuchEntityException(__('Requested data doesn\'t exist'));
            }
            $this->instances[$dataId] = $data;
        }
        return $this->instances[$dataId];
    }

    /**
     * @param QuotationInterface $quotation
     * @return QuotationInterface|\Magento\Framework\Model\AbstractModel|mixed
     * @throws CouldNotSaveException
     */
    public function saveQuotation(QuotationInterface $quotation)
    {
        try {
            /** @var QuotationInterface|\Magento\Framework\Model\AbstractModel $data */
            $this->resource->save($data);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the data: %1',
                $exception->getMessage()
            ));
        }
        return $data;
    }

    public function getQuotationList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        // TODO: Implement getQuotationList() method.
    }

    public function deleteQuotation($quoteId, array $sharedStoreIds)
    {
        // TODO: Implement deleteQuotation() method.
    }

    public function getItems($quoteId)
    {
        // TODO: Implement getItems() method.
    }
}
