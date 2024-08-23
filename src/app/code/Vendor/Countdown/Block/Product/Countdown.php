<?php
namespace Vendor\Countdown\Block\Product;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;
use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;


class Countdown extends Template
{
    protected $_product;
    protected $_registry;
    protected $_ruleCollectionFactory;


    public function __construct(
        Template\Context $context,
        Product $product,
        Registry $registry,
        RuleCollectionFactory $ruleCollectionFactory,
        DateTime $dateTime,

        array $data = []
    ) {
        $this->_product = $product;
        $this->_registry = $registry;
        $this->_ruleCollectionFactory = $ruleCollectionFactory;
        $this->dateTime = $dateTime;
        parent::__construct($context, $data);
    }

    public function getSpecialPriceEndDate()
    {
        $product = $this->getProduct();

        return [
            'endDate' => $product->getSpecialToDate(),
            'type' => 'Special Price',
        ];
    }

    public function getProduct()
    {
        return $this->_registry->registry('current_product');
    }

    public function getCatalogRuleEndDate()
    {
        $productId = $this->getProduct()->getId();

        $rules = $this->_ruleCollectionFactory->create();
        $rules->addFieldToFilter('is_active', 1);
        $rules->addFieldToFilter('to_date', ['gteq' => $this->dateTime->gmtDate()]);

        $catalogRuleEndDate = null;
        $ruleName =  null;
        foreach ($rules as $rule) {
            $productIds = $rule->getMatchingProductIds();

            if (is_array($productIds) && in_array($productId, array_keys($productIds))) {
                $ruleName = $rule->getName();
                $ruleEndDate = $rule->getToDate();

                if ($catalogRuleEndDate === null || $ruleEndDate < $catalogRuleEndDate) {
                    $catalogRuleEndDate = $ruleEndDate;
                }
            }
        }

        return [
            'endDate' =>$catalogRuleEndDate,
            'type' => $ruleName,
        ];
    }

    public function getEarliestEndDate()
    {
        $specialPriceEndDate = $this->getSpecialPriceEndDate();
        $catalogRuleEndDate = $this->getCatalogRuleEndDate();

        if ($specialPriceEndDate['endDate'] && $catalogRuleEndDate['endDate']) {
            return ($specialPriceEndDate['endDate'] < $catalogRuleEndDate['endDate']) ? $specialPriceEndDate : $catalogRuleEndDate;
        }

        return $specialPriceEndDate ?: $catalogRuleEndDate;
    }
}
