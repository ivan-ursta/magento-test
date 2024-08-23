<?php
namespace Vendor\Countdown\Block\Product;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;

class Countdown extends Template
{
    protected $_product;
    protected $_registry;

    public function __construct(
        Template\Context $context,
        Product $product,
        Registry $registry,
        array $data = []
    ) {
        $this->_product = $product;
        $this->_registry = $registry;
        parent::__construct($context, $data);
    }

    public function getSpecialPriceEndDate()
    {
        $product = $this->getProduct();
        return $product->getSpecialToDate();
    }

    public function getProduct()
    {
        return $this->_registry->registry('current_product');
    }
}
