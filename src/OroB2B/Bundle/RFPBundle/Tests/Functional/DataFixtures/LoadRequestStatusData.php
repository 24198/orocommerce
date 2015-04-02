<?php

namespace OroB2B\Bundle\RFPBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use OroB2B\Bundle\RFPBundle\Entity\RequestStatus;

class LoadRequestStatusData extends AbstractFixture
{
    /**
     * @var array
     */
    protected $requestStatuses = [
        [
            'order'   => 10,
            'name'    => 'open',
            'label'   => 'Open',
            'locale'  => 'en_US',
            'deleted' => false
        ], [
            'order'   => 20,
            'name'    => 'inprogress',
            'label'   => 'In Progress',
            'locale'  => 'en_US',
            'deleted' => false
        ], [
            'order'   => 30,
            'name'    => 'closed',
            'label'   => 'Closed',
            'locale'  => 'en_US',
            'deleted' => false
        ], [
            'order'   => 40,
            'name'    => 'deleted',
            'label'   => 'Deleted',
            'locale'  => 'en_US',
            'deleted' => true
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $om)
    {
        foreach ($this->requestStatuses as $requestStatusData) {
            $requestStatus = new RequestStatus();

            $requestStatus
                ->setSortOrder($requestStatusData['order'])
                ->setName($requestStatusData['name'])
                ->setLabel($requestStatusData['label'])
                ->setLocale($requestStatusData['locale'])
                ->setDeleted($requestStatusData['deleted'])
            ;

            $om->persist($requestStatus);
        }

        $om->flush();
    }
}
