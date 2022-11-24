<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Adyen\Model;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface;
use OxidSolutionCatalysts\Adyen\Core\Module;
use OxidSolutionCatalysts\Adyen\Traits\ServiceContainer;

class AdyenHistory extends BaseModel
{
    use ServiceContainer;

    protected const PSPREFERENCEFIELD = 'pspreference';
    protected const PSPPARENTREFERENCEFIELD = 'parentpspreference';

    private QueryBuilderFactoryInterface $queryBuilderFactory;


    private ContextInterface $context;

    private Config $config;

    /**
     * Current class name
     *
     * @var string
     */
    protected $_sClassName = 'OxidSolutionCatalysts\Adyen\Model\AdyenHistory'; //NOSONAR

    /**
     * Core table name
     *
     * @var string
     */
    protected $_sCoreTable = Module::ADYEN_HISTORY_TABLE; //NOSONAR

    public function __construct()
    {
        parent::__construct();
        $this->init($this->_sCoreTable);
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function init($tableName = null, $forceAllFields = false): void
    {
        parent::init($tableName, $forceAllFields);
        $this->queryBuilderFactory = $this->getServiceFromContainer(QueryBuilderFactoryInterface::class);
        $this->context = $this->getServiceFromContainer(ContextInterface::class);

        $this->config = Registry::getConfig();
    }

    public function loadByPSPReference(string $pspReference): bool
    {
        return $this->loadByIdent(self::PSPREFERENCEFIELD, $pspReference);
    }

    public function loadByOxOrderId(string $oxorderid): bool
    {
        return $this->loadByIdent('oxorderid', $oxorderid);
    }

    public function getCapturedSum(string $pspReference): float
    {
        return $this->getSumByAction($pspReference, Module::ADYEN_ACTION_CAPTURE);
    }

    public function getRefundedSum(string $pspReference): float
    {
        return $this->getSumByAction($pspReference, Module::ADYEN_ACTION_REFUND);
    }

    protected function loadByIdent(string $var, string $value): bool
    {
        $result = false;

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->queryBuilderFactory->create();

        $queryBuilder->select('oxid')
            ->from($this->getCoreTableName())
            ->setMaxResults(1)
            ->where($var . ' = :var');

        $parameters = [
            'var' => $value
        ];

        if (!$this->config->getConfigParam('blMallUsers')) {
            $queryBuilder->andWhere('oxshopid = :oxshopid');
            $parameters['oxshopid'] = $this->context->getCurrentShopId();
        }

        /** @var Result $resultDB */
        $resultDB = $queryBuilder->setParameters($parameters)
            ->execute();

        if (is_a($resultDB, Result::class)) {
            $oxid = $resultDB->fetchOne();
            $result = $this->load($oxid);
        }
        return $result;
    }

    protected function getSumByAction(string $pspReference, string $action, string $status = ''): float
    {
        $result = 0;

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->queryBuilderFactory->create();

        $queryBuilder->select('sum(oxprice) as sum')
            ->from($this->getCoreTableName())
            ->setMaxResults(1)
            ->where(self::PSPPARENTREFERENCEFIELD . ' = :pspReference')
            ->andWhere('adyenaction = :adyenAction');

        $parameters = [
            'pspReference' => $pspReference,
            'adyenAction' => $action,
            'adyenStatus' => $status
        ];

        if ($status) {
            $queryBuilder->andWhere('adyenstatus = :adyenStatus');
            $parameters['adyenStatus'] = $status;
        }

        if (!$this->config->getConfigParam('blMallUsers')) {
            $queryBuilder->andWhere('oxshopid = :oxshopid');
            $parameters['oxshopid'] = $this->context->getCurrentShopId();
        }

        /** @var Result $resultDB */
        $resultDB = $queryBuilder->setParameters($parameters)
            ->execute();
        if (is_a($resultDB, Result::class)) {
            $result = (float)$resultDB->fetchOne();
        }
        return $result;
    }

    public function getOrderId(): string
    {
        return (string) $this->getFieldData('orderid');
    }

    public function getPSPReference(): string
    {
        return (string) $this->getFieldData(self::PSPREFERENCEFIELD);
    }

    public function getParentPSPReference(): string
    {
        return (string) $this->getFieldData(self::PSPPARENTREFERENCEFIELD);
    }

    public function getPrice(): float
    {
        return (float) $this->getFieldData('oxprice');
    }

    public function getFormatedPrice(): string
    {
        return Registry::getLang()->formatCurrency($this->getPrice());
    }

    public function getCurrency(): string
    {
        return $this->getFieldData('currency');
    }

    public function getAdyenStatus(): string
    {
        return (string) $this->getFieldData('adyenstatus');
    }

    public function getAdyenAction(): string
    {
        return (string) $this->getFieldData('adyenaction');
    }

    public function getTimeStamp(): string
    {
        return (string) $this->getFieldData('oxtimestamp');
    }

    public function setOrderId(string $orderId): void
    {
        $this->assign([
                'oxorderid' => $orderId
        ]);
    }

    public function setPSPReference(string $pspreference): void
    {
        $this->assign([
                self::PSPREFERENCEFIELD => $pspreference
        ]);
    }

    public function setParentPSPReference(string $parentpspreference): void
    {
        $this->assign([
                self::PSPPARENTREFERENCEFIELD => $parentpspreference
        ]);
    }

    public function setPrice(float $oxprice): void
    {
        $this->assign([
                'oxprice' => $oxprice
        ]);
    }

    public function setCurrency(string $currency): void
    {
        $this->assign([
                'currency' => $currency
        ]);
    }

    public function setTimeStamp(string $oxtimestamp): void
    {
        $this->assign([
                'oxtimestamp' => $oxtimestamp
        ]);
    }

    public function setAdyenStatus(string $adyenstatus): void
    {
        $this->assign([
                'adyenstatus' => $adyenstatus
        ]);
    }

    public function setAdyenAction(string $adyenAction): void
    {
        $possibleActions = [
            Module::ADYEN_ACTION_AUTHORIZE,
            Module::ADYEN_ACTION_REFUND,
            Module::ADYEN_ACTION_CAPTURE,
            Module::ADYEN_ACTION_CANCEL
        ];
        if (in_array($adyenAction, $possibleActions, true)) {
            $this->assign([
                'adyenaction' => $adyenAction
            ]);
        }
    }

    public function delete($oxid = null): bool
    {
        if ($oxid) {
            $this->load($oxid);
        }
        $pspReference = $this->getPSPReference();
        $this->deleteChildReferences($pspReference);

        return parent::delete($oxid);
    }

    /**
     * Deletes child records
     *
     * @param string $pspReference AdyenHistory PSP Reference
     */
    protected function deleteChildReferences($pspReference): void
    {
        if ($pspReference) {

            /** @var QueryBuilder $queryBuilder */
            $queryBuilder = $this->queryBuilderFactory->create();

            $queryBuilder->delete($this->getCoreTableName())
                ->where(self::PSPPARENTREFERENCEFIELD . ' = :pspreference');

            $parameters = [
                'pspreference' => $pspReference
            ];

            if (!$this->config->getConfigParam('blMallUsers')) {
                $queryBuilder->andWhere('oxshopid = :oxshopid');
                $parameters['oxshopid'] = $this->context->getCurrentShopId();
            }

            $queryBuilder->setParameters($parameters)
                ->execute();
        }
    }
}
