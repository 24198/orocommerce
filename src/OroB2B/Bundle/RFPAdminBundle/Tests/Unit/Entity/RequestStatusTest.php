<?php

namespace OroB2B\Bundle\RFPAdminBundle\Tests\Unit\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use OroB2B\Bundle\RFPAdminBundle\Entity\RequestStatus;
use OroB2B\Bundle\RFPAdminBundle\Entity\RequestStatusTranslation;

use OroB2B\Bundle\RFPBundle\Tests\Unit\Entity\RequestStatusTestCase;

class RequestStatusTest extends RequestStatusTestCase
{
    /**
     * Test setters getters
     */
    public function testAccessors()
    {
        $properties = [
            ['id', 1],
            ['locale', 'en'],
            ['name', 'opened'],
            ['label', 'Opened'],
            ['sortOrder', 1],
            ['deleted', true],
            ['deleted', false],
        ];

        $propertyRequestStatus = new RequestStatus();

        $this->assertPropertyAccessors($propertyRequestStatus, $properties);
    }

    /**
     * Test translation setters getters
     */
    public function testTranslation()
    {
        $requestStatus = new RequestStatus();

        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $requestStatus->getTranslations());
        $this->assertCount(0, $requestStatus->getTranslations());

        $translation = new RequestStatusTranslation();

        $requestStatus->addTranslation($translation);

        $this->assertCount(1, $requestStatus->getTranslations());

        $requestStatus->addTranslation($translation);

        $this->assertCount(1, $requestStatus->getTranslations());

        $requestStatus->addTranslation(new RequestStatusTranslation());

        $this->assertCount(2, $requestStatus->getTranslations());

        $translation = new RequestStatusTranslation();
        $translation
            ->setLocale('en_US')
            ->setField('type');

        $translations = new ArrayCollection([$translation]);
        $requestStatus->setTranslations($translations);
        $this->assertCount(1, $requestStatus->getTranslations());
    }
}
