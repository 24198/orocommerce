<?php

namespace OroB2B\Bundle\SaleBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\LocaleBundle\Formatter\NumberFormatter;

use OroB2B\Bundle\ProductBundle\Formatter\ProductUnitValueFormatter;
use OroB2B\Bundle\ProductBundle\Entity\ProductUnit;
use OroB2B\Bundle\SaleBundle\Validator\Constraints;

abstract class AbstractQuoteToProductTestCase extends FormIntegrationTestCase
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|TranslatorInterface
     */
    protected function getTranslator()
    {
        $translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');
        $translator->expects($this->any())
            ->method('trans')
            ->with('orob2b.frontend.sale.quoteproductoffer.allow_increments.label')
            ->willReturn('or more');

        return $translator;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|NumberFormatter
     */
    protected function getNumberFormatter()
    {
        $formatter = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Formatter\NumberFormatter')
            ->disableOriginalConstructor()
            ->getMock();

        $formatter->expects($this->any())
            ->method('formatCurrency')
            ->with($this->isType('float'), $this->isType('string'))
            ->willReturnCallback(
                function ($value, $currency) {
                    return $currency . $value;
                }
            );

        return $formatter;
    }

    /**
     * @return ProductUnitValueFormatter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getUnitFormatter()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ProductUnitValueFormatter $unitFormatter */
        $unitFormatter = $this->getMockBuilder('OroB2B\Bundle\ProductBundle\Formatter\ProductUnitValueFormatter')
            ->disableOriginalConstructor()
            ->getMock();
        $unitFormatter->expects($this->any())
            ->method('formatShort')
            ->with($this->isType('int'), $this->isInstanceOf('OroB2B\Bundle\ProductBundle\Entity\ProductUnit'))
            ->willReturnCallback(
                function ($quantity, ProductUnit $unit) {
                    return sprintf('%s %s', $quantity, $unit->getCode());
                }
            );

        return $unitFormatter;
    }
}
