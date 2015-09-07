<?php

namespace OroB2B\Bundle\ProductBundle\Tests\Unit\Model;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use OroB2B\Bundle\ProductBundle\Model\DataStorageAwareProcessor;
use OroB2B\Bundle\ProductBundle\Storage\ProductDataStorage;

class DataStorageAwareProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|UrlGeneratorInterface
     */
    protected $router;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ProductDataStorage
     */
    protected $storage;

    /**
     * @var DataStorageAwareProcessor
     */
    protected $processor;

    protected function setUp()
    {
        $this->router = $this->getMock('Symfony\Component\Routing\Generator\UrlGeneratorInterface');
        $this->storage = $this->getMockBuilder('OroB2B\Bundle\ProductBundle\Storage\ProductDataStorage')
            ->disableOriginalConstructor()
            ->getMock();

        $this->processor = new DataStorageAwareProcessor($this->router, $this->storage);
    }

    protected function tearDown()
    {
        unset($this->router, $this->storage, $this->processor);
    }

    public function testProcessWithoutRedirectRoute()
    {
        $data = ['data' => ['param' => 42]];

        $this->router->expects($this->never())
            ->method($this->anything());

        $this->storage->expects($this->once())
            ->method('set')
            ->with($data);

        $this->assertNull($this->processor->process($data, new Request()));
    }

    public function testProcessWithRedirectRoute()
    {
        $data = ['data' => ['param' => 42]];
        $redirectRouteName = 'redirect_route';
        $redirectUrl = '/redirect/url';
        $expectedResponse = new RedirectResponse($redirectUrl);

        $this->router->expects($this->once())
            ->method('generate')
            ->with(
                $redirectRouteName,
                [DataStorageAwareProcessor::QUICK_ADD_PARAM => 1],
                UrlGeneratorInterface::ABSOLUTE_PATH
            )
            ->willReturn($redirectUrl);

        $this->storage->expects($this->once())
            ->method('set')
            ->with($data);

        $this->processor->setRedirectRouteName($redirectRouteName);

        $this->assertEquals($expectedResponse, $this->processor->process($data, new Request()));
    }

    public function testProcessorName()
    {
        $name = 'test_name';

        $this->assertNull($this->processor->getName());

        $this->processor->setName($name);

        $this->assertEquals($name, $this->processor->getName());
    }

    public function testValidationRequired()
    {
        $this->assertFalse($this->processor->isValidationRequired());

        $this->processor->setValidationRequired(true);

        $this->assertTrue($this->processor->isValidationRequired());
    }
}
