<?php

namespace Oro\Bundle\FlatRateShippingBundle\Tests\Unit\Builder;

use Oro\Bundle\FlatRateShippingBundle\Factory\FlatRateMethodFromChannelFactory;
use Oro\Bundle\FlatRateShippingBundle\Entity\FlatRateSettings;
use Oro\Bundle\FlatRateShippingBundle\Method\FlatRateMethod;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Bundle\ShippingBundle\Method\Identifier\IntegrationMethodIdentifierGeneratorInterface;

class FlatRateMethodFromChannelFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IntegrationMethodIdentifierGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $identifierGenerator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|LocalizationHelper
     */
    private $localizationHelper;

    /**
     * @var FlatRateMethodFromChannelFactory
     */
    private $factory;

    protected function setUp()
    {
        $this->identifierGenerator = $this->createMock(IntegrationMethodIdentifierGeneratorInterface::class);

        $this->localizationHelper = $this->getMockBuilder(LocalizationHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->factory = new FlatRateMethodFromChannelFactory($this->identifierGenerator, $this->localizationHelper);
    }

    public function testBuildReturnsCorrectObjectWithLabel()
    {
        $label = 'test';
        $channel = $this->getChannel();
        $identifier = 'flat_rate_1';

        $this->localizationHelper->expects(static::once())
            ->method('getLocalizedValue')
            ->willReturn($label);

        $this->identifierGenerator->expects($this->once())
            ->method('generateIdentifier')
            ->with($channel)
            ->willReturn($identifier);

        $method = $this->factory->create($channel);

        static::assertInstanceOf(FlatRateMethod::class, $method);
        static::assertSame($identifier, $method->getIdentifier());
        static::assertSame($label, $method->getLabel());
        static::assertTrue($method->isEnabled());
    }

    /**
     * @return Channel
     */
    private function getChannel()
    {
        $settings = new FlatRateSettings();

        $channel = new Channel();
        $channel->setTransport($settings);
        $channel->setEnabled(true);

        return $channel;
    }
}
