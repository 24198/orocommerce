<?php

namespace Oro\Bundle\SEOBundle\EventListener;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\UIBundle\Event\BeforeListRenderEvent;
use Oro\Bundle\UIBundle\View\ScrollData;

abstract class BaseFormViewListener
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var int
     */
    protected $blockPriority = 10;

    /**
     * @param RequestStack $requestStack
     * @param TranslatorInterface $translator
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(
        RequestStack $requestStack,
        TranslatorInterface $translator,
        DoctrineHelper $doctrineHelper
    ) {
        $this->requestStack = $requestStack;
        $this->translator = $translator;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @param int $blockPriority
     * @return BaseFormViewListener
     */
    public function setBlockPriority($blockPriority)
    {
        $this->blockPriority = $blockPriority;

        return $this;
    }

    /**
     * @param BeforeListRenderEvent $event
     * @param string $entityClass
     */
    protected function addViewPageBlock(BeforeListRenderEvent $event, $entityClass)
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }

        $objectId = (int)$request->get('id');
        if (!$objectId) {
            return;
        }

        $object = $this->doctrineHelper->getEntityReference($entityClass, $objectId);
        if (!$object) {
            return;
        }

        $template = $event->getEnvironment()->render('OroSEOBundle:SEO:view.html.twig', [
            'entity' => $object,
            'labelPrefix' => $this->getMetaFieldLabelPrefix()
        ]);

        $this->addSEOBlock($event->getScrollData(), $template);
    }

    /**
     * @param BeforeListRenderEvent $event
     */
    protected function addEditPageBlock(BeforeListRenderEvent $event)
    {
        $template = $event->getEnvironment()->render(
            'OroSEOBundle:SEO:update.html.twig',
            ['form' => $event->getFormView()]
        );

        $this->addSEOBlock($event->getScrollData(), $template);
    }

    /**
     * @param ScrollData $scrollData
     * @param string $html
     */
    protected function addSEOBlock(ScrollData $scrollData, $html)
    {
        $blockLabel = $this->translator->trans('oro.seo.label');
        $blockId = $scrollData->addBlock($blockLabel, $this->blockPriority);
        $subBlockId = $scrollData->addSubBlock($blockId);
        $scrollData->addSubBlockData($blockId, $subBlockId, $html);
    }

    /**
     * @return string
     */
    abstract public function getMetaFieldLabelPrefix();
}
