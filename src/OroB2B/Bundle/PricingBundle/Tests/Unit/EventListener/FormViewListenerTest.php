<?php

namespace OroB2B\Bundle\PricingBundle\Tests\Unit\Datagrid;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

use Oro\Bundle\UIBundle\Event\BeforeListRenderEvent;
use Oro\Bundle\UIBundle\View\ScrollData;
use OroB2B\Bundle\CustomerBundle\Entity\Customer;
use OroB2B\Bundle\CustomerBundle\Entity\CustomerGroup;
use OroB2B\Bundle\PricingBundle\Entity\PriceList;
use OroB2B\Bundle\PricingBundle\EventListener\FormViewListener;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;

class FormViewListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DoctrineHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $doctrineHelper;

    /**
     * @var FormViewListener
     */
    protected $listener;

    protected function setUp()
    {
        $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new FormViewListener($this->doctrineHelper);
    }

    public function testOnViewNoRequest()
    {
        $this->doctrineHelper->expects($this->never())
            ->method('getEntityReference');

        $event = $this->createEvent($this->getMock('\Twig_Environment'));
        $this->listener->onCustomerView($event);
        $this->listener->onCustomerGroupView($event);
    }

    /**
     * @return array
     */
    public function viewDataProvider()
    {
        return [
            'price list does not exist' => [false],
            'price list does exists'    => [true],
        ];
    }

    /**
     * @param bool $isPriceListExist
     * @dataProvider viewDataProvider
     */
    public function testOnCustomerView($isPriceListExist)
    {
        $customerId = 1;
        $customer = new Customer();
        $priceList = new PriceList();
        $templateHtml = 'template_html';

        $this->listener->setRequest(new Request(['id' => $customerId]));

        $priceRepository = $this->getMockBuilder('OroB2B\Bundle\PricingBundle\Entity\Repository\PriceListRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $priceRepository->expects($this->once())
            ->method('getPriceListByCustomer')
            ->with($customer)
            ->willReturn($isPriceListExist ? $priceList : null);

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityReference')
            ->with('OroB2BCustomerBundle:Customer', $customerId)
            ->willReturn($customer);
        $this->doctrineHelper->expects($this->once())
            ->method('getEntityRepository')
            ->with('OroB2BPricingBundle:PriceList')
            ->willReturn($priceRepository);

        $environment = $this->getMock('\Twig_Environment');

        if ($isPriceListExist) {
            $environment->expects($this->once())
                ->method('render')
                ->with('OroB2BPricingBundle:Customer:price_list_view.html.twig', ['priceList' => $priceList])
                ->willReturn($templateHtml);
        } else {
            $environment->expects($this->never())
                ->method('render');
        }

        $event = $this->createEvent($environment);
        $this->listener->onCustomerView($event);
        $scrollData = $event->getScrollData()->getData();

        if ($isPriceListExist) {
            $this->assertEquals([$templateHtml], $scrollData['dataBlocks'][0]['subblocks'][0]['data']);
        } else {
            $this->assertEmpty($scrollData['dataBlocks'][0]['subblocks'][0]['data']);
        }
    }

    /**
     * @param bool $isPriceListExist
     * @dataProvider viewDataProvider
     */
    public function testOnCustomerGroupView($isPriceListExist)
    {
        $customerGroupId = 1;
        $customerGroup = new CustomerGroup();
        $priceList = new PriceList();
        $templateHtml = 'template_html';

        $this->listener->setRequest(new Request(['id' => $customerGroupId]));

        $priceRepository = $this->getMockBuilder('OroB2B\Bundle\PricingBundle\Entity\Repository\PriceListRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $priceRepository->expects($this->once())
            ->method('getPriceListByCustomerGroup')
            ->with($customerGroup)
            ->willReturn($isPriceListExist ? $priceList : null);

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityReference')
            ->with('OroB2BCustomerBundle:CustomerGroup', $customerGroupId)
            ->willReturn($customerGroup);
        $this->doctrineHelper->expects($this->once())
            ->method('getEntityRepository')
            ->with('OroB2BPricingBundle:PriceList')
            ->willReturn($priceRepository);

        $environment = $this->getMock('\Twig_Environment');

        if ($isPriceListExist) {
            $environment->expects($this->once())
                ->method('render')
                ->with('OroB2BPricingBundle:Customer:price_list_view.html.twig', ['priceList' => $priceList])
                ->willReturn($templateHtml);
        } else {
            $environment->expects($this->never())
                ->method('render');
        }

        $event = $this->createEvent($environment);
        $this->listener->onCustomerGroupView($event);
        $scrollData = $event->getScrollData()->getData();

        if ($isPriceListExist) {
            $this->assertEquals([$templateHtml], $scrollData['dataBlocks'][0]['subblocks'][0]['data']);
        } else {
            $this->assertEmpty($scrollData['dataBlocks'][0]['subblocks'][0]['data']);
        }
    }

    public function testOnEntityEdit()
    {
        $formView = new FormView();
        $templateHtml = 'template_html';

        $environment = $this->getMock('\Twig_Environment');
        $environment->expects($this->once())
            ->method('render')
            ->with('OroB2BPricingBundle:Customer:price_list_update.html.twig', ['form' => $formView])
            ->willReturn($templateHtml);

        $event = $this->createEvent($environment, $formView);
        $this->listener->onEntityEdit($event);
        $scrollData = $event->getScrollData()->getData();

        $this->assertEquals([$templateHtml], $scrollData['dataBlocks'][0]['subblocks'][0]['data']);
    }

    /**
     * @param \Twig_Environment $environment
     * @param FormView $formView
     * @return BeforeListRenderEvent
     */
    protected function createEvent(\Twig_Environment $environment, FormView $formView = null)
    {
        $defaultData = [
            'dataBlocks' => [
                [
                    'subblocks' => [
                        [
                            'data' => []
                        ]
                    ]
                ]
            ]
        ];

        return new BeforeListRenderEvent($environment, new ScrollData($defaultData), $formView);
    }
}
