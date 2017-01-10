<?php

namespace Oro\Bundle\VisibilityBundle\Tests\Unit\Entity\EntityListener;

use Doctrine\ORM\Event\PreUpdateEventArgs;

use Oro\Bundle\VisibilityBundle\Driver\AccountPartialUpdateDriverInterface;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\VisibilityBundle\Entity\EntityListener\AccountListener;
use Oro\Bundle\VisibilityBundle\Model\MessageFactoryInterface;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;

class AccountListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MessageFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $factory;

    /**
     * @var MessageProducerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $producer;

    /**
     * @var AccountPartialUpdateDriverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $driver;

    /**
     * @var Customer
     */
    protected $account;

    /**
     * @var AccountListener
     */
    protected $listener;

    protected function setUp()
    {
        $this->factory = $this->getMockBuilder(MessageFactoryInterface::class)
            ->getMock();
        $this->producer = $this->getMockBuilder(MessageProducerInterface::class)
            ->getMock();
        $this->driver = $this->getMockBuilder(AccountPartialUpdateDriverInterface::class)
            ->getMock();

        $this->account = new Customer();
        $this->listener = new AccountListener($this->factory, $this->producer, $this->driver);
    }

    public function testPostPersistWithoutGroup()
    {
        $this->producer->expects($this->never())
            ->method('send');
        $this->driver->expects($this->once())
            ->method('createAccountWithoutAccountGroupVisibility')
            ->with($this->account);

        $this->listener->postPersist($this->account);
    }

    public function testPostPersistWithGroup()
    {
        $message = new Message();
        $this->factory->expects($this->once())
            ->method('createMessage')
            ->with($this->account)
            ->willReturn($message);
        $this->producer->expects($this->once())
            ->method('send')
            ->with('', $message);
        $this->driver->expects($this->never())
            ->method('createAccountWithoutAccountGroupVisibility');

        $this->account->setGroup(new CustomerGroup());
        $this->listener->postPersist($this->account);
    }

    public function testPreRemove()
    {
        $this->driver->expects($this->once())
            ->method('deleteAccountVisibility')
            ->with($this->account);

        $this->listener->preRemove($this->account);
    }

    public function testPreUpdate()
    {
        /** @var PreUpdateEventArgs|\PHPUnit_Framework_MockObject_MockObject $args */
        $args = $this->getMockBuilder(PreUpdateEventArgs::class)
            ->disableOriginalConstructor()
            ->getMock();
        $args->expects($this->once())
            ->method('hasChangedField')
            ->with('group')
            ->willReturn(true);

        $message = new Message();
        $this->factory->expects($this->once())
            ->method('createMessage')
            ->with($this->account)
            ->willReturn($message);
        $this->producer->expects($this->once())
            ->method('send')
            ->with('', $message);

        $this->listener->preUpdate($this->account, $args);
    }
}
