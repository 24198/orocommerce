<?php

namespace Oro\Bundle\ProductBundle\Tests\Unit\Command;

use Doctrine\ORM\EntityRepository;

use Prophecy\Argument;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ProductBundle\Command\ResizeAllProductImagesCommand;
use Oro\Bundle\ProductBundle\Entity\ProductImage;
use Oro\Bundle\ProductBundle\Event\ProductImageResizeEvent;

class ResizeAllProductImagesCommandTest extends \PHPUnit_Framework_TestCase
{
    const PRODUCT_IMAGE_CLASS = 'ProductImage';
    const FORCE_OPTION = false;

    /**
     * @var ResizeAllProductImagesCommand
     */
    protected $command;

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var EntityRepository
     */
    protected $productImageRepository;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $options = [
        ResizeAllProductImagesCommand::OPTION_FORCE => self::FORCE_OPTION
    ];

    public function setUp()
    {
        $this->productImageRepository = $this->prophesize(EntityRepository::class);
        $this->productImageRepository->findAll()->willReturn([
            new ProductImage(),
            new ProductImage(),
            new ProductImage()
        ]);

        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);

        $this->doctrineHelper = $this->prophesize(DoctrineHelper::class);
        $this->doctrineHelper
            ->getEntityRepositoryForClass(self::PRODUCT_IMAGE_CLASS)
            ->willReturn($this->productImageRepository);

        $this->container = $this->prophesize(ContainerInterface::class);
        $this->container->get('oro_entity.doctrine_helper')->willReturn($this->doctrineHelper);
        $this->container->get('event_dispatcher')->willReturn($this->eventDispatcher);
        $this->container->getParameter('oro_product.entity.product_image.class')->willReturn(self::PRODUCT_IMAGE_CLASS);

        $this->command = new ResizeAllProductImagesCommand();
        $this->command->setContainer($this->container->reveal());
    }

    public function testNoProductImages()
    {
        $this->productImageRepository->findAll()->willReturn([]);

        $this->command->run($this->prepareInput(), $this->prepareOutput('No product images found.'));
    }

    public function testResizeAllImages()
    {
        $this->eventDispatcher
            ->dispatch(ProductImageResizeEvent::NAME, Argument::type(ProductImageResizeEvent::class))
            ->shouldBeCalledTimes(3);

        $this->command->run(
            $this->prepareInput(),
            $this->prepareOutput('3 product image(s) successfully queued for resize.')
        );
    }

    /**
     * @return object
     */
    protected function prepareInput()
    {
        $input = $this->prophesize(InputInterface::class);

        foreach ($this->options as $name => $value) {
            $input->getOption($name)->willReturn($value);
        }
        $input->bind(Argument::any())->shouldBeCalled();
        $input->isInteractive()->shouldBeCalled();
        $input->hasArgument('command')->shouldBeCalled();
        $input->validate()->shouldBeCalled();

        return $input->reveal();
    }

    /**
     * @param string $message
     * @return object
     */
    protected function prepareOutput($message)
    {
        $output = $this->prophesize(OutputInterface::class);
        $output->writeln($message)->shouldBeCalled();

        return $output->reveal();
    }
}
