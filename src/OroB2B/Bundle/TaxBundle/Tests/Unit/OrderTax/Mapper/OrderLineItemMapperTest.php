<?php

namespace OroB2B\Bundle\TaxBundle\Tests\Unit\OrderTax\Mapper;

use Oro\Bundle\CurrencyBundle\Model\Price;
use Oro\Component\Testing\Unit\EntityTrait;

use OroB2B\Bundle\OrderBundle\Entity\OrderLineItem;
use OroB2B\Bundle\TaxBundle\Model\Taxable;
use OroB2B\Bundle\TaxBundle\OrderTax\Mapper\OrderLineItemMapper;

class OrderLineItemMapperTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    const ITEM_ID = 123;
    const ITEM_PRICE_VALUE = 12.34;
    const ITEM_QUANTITY = 12;

    /**
     * @var OrderLineItemMapper
     */
    protected $mapper;

    protected function setUp()
    {
        $this->mapper = new OrderLineItemMapper();
    }

    protected function tearDown()
    {
        unset($this->mapper);
    }

    public function testGetProcessingClassName()
    {
        $this->assertEquals('OroB2B\Bundle\OrderBundle\Entity\OrderLineItem', $this->mapper->getProcessingClassName());
    }

    public function testMap()
    {
        $lineItem = $this->createLineItem(self::ITEM_ID, self::ITEM_QUANTITY, self::ITEM_PRICE_VALUE);

        $taxable = $this->mapper->map($lineItem);

        $this->assertTaxable($taxable, self::ITEM_ID, self::ITEM_QUANTITY, self::ITEM_PRICE_VALUE);
    }

    public function testMapWithoutPrice()
    {
        $lineItem = $this->createLineItem(self::ITEM_ID, self::ITEM_QUANTITY);

        $taxable = $this->mapper->map($lineItem);

        $this->assertTaxable($taxable, self::ITEM_ID, self::ITEM_QUANTITY, 0);
    }

    /**
     * @param int $id
     * @param int $quantity
     * @param float $priceValue
     * @return OrderLineItem
     */
    protected function createLineItem($id, $quantity, $priceValue = null)
    {
        /** @var OrderLineItem $lineItem */
        $lineItem = $this->getEntity('OroB2B\Bundle\OrderBundle\Entity\OrderLineItem', ['id' => $id]);
        $lineItem
            ->setPrice($priceValue ? Price::create($priceValue, 'USD') : null)
            ->setQuantity($quantity);

        return $lineItem;
    }

    /**
     * @param Taxable $taxable
     * @param int $id
     * @param int $quantity
     * @param float $priceValue
     */
    protected function assertTaxable($taxable, $id, $quantity, $priceValue)
    {
        $this->assertInstanceOf('OroB2B\Bundle\TaxBundle\Model\Taxable', $taxable);
        $this->assertEquals($id, $taxable->getIdentifier());
        $this->assertEquals($quantity, $taxable->getQuantity());
        $this->assertEquals($priceValue, $taxable->getPrice());
        $this->assertEquals(0, $taxable->getAmount());
        $this->assertEmpty($taxable->getItems());
    }
}
