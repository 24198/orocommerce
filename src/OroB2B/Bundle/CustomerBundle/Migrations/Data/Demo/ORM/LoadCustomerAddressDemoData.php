<?php

namespace OroB2B\Bundle\CustomerBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use OroB2B\Bundle\CustomerBundle\Entity\AccountUser;
use OroB2B\Bundle\CustomerBundle\Entity\CustomerAddress;
use OroB2B\Bundle\CustomerBundle\Entity\CustomerGroup;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadCustomerAddressDemoData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /** @var ObjectRepository|EntityRepository */
    protected $countryRepository;

    /** @var ObjectRepository|EntityRepository */
    protected $regionRepository;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return ['OroB2B\Bundle\CustomerBundle\Migrations\Data\Demo\ORM\LoadAccountUserDemoData'];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $userManager = $this->container->get('orob2b_account_user.manager');
        $manager = $userManager->getStorageManager();
        $this->countryRepository = $manager->getRepository('OroAddressBundle:Country');
        $this->regionRepository = $manager->getRepository('OroAddressBundle:Region');

        $locator = $this->container->get('file_locator');
        $filePath = $locator->locate('@OroB2BCustomerBundle/Migrations/Data/Demo/ORM/data/account-users.csv');
        if (is_array($filePath)) {
            $filePath = current($filePath);
        }

        $handler = fopen($filePath, 'r');
        $headers = fgetcsv($handler, 1000, ',');

        /** @var AccountUser[] $accountUsers */
        $accountUsers = $manager->getRepository('OroB2BCustomerBundle:AccountUser')->findAll();

        /** @var AccountUser[] $accountUserByEmail */
        $accountUserByEmail = [];
        foreach ($accountUsers as $accountUser) {
            $accountUserByEmail[$accountUser->getEmail()] = $accountUser;
        }

        while (($data = fgetcsv($handler, 1000, ',')) !== false) {
            $row = array_combine($headers, array_values($data));
            $accountUser = $accountUserByEmail[$row['email']];
            $accountUser
                ->getCustomer()
                ->addAddress($this->createCustomerAddress($row));

        }

        fclose($handler);
        $manager->flush();
    }

    protected function createCustomerAddress(array $data)
    {
        /** @var Country $country */
        $country = $this->countryRepository->findOneBy(['iso2Code' => $data['country']]);
        if (!$country) {
            throw new \RuntimeException('Can\'t find country with ISO ' . $data['country']);
        }

        /** @var Region $region */
        $region = $this->regionRepository->findOneBy(['country' => $country, 'code' => $data['state']]);
        if (!$region) {
            throw new \RuntimeException(
                printf('Can\'t find region with country ISO %s and code %s', $data['country'], $data['state'])
            );
        }

        $address = new CustomerAddress();
        $address
            ->setPrimary(true)
            ->setLabel('Primary address')
            ->setCountry($country)
            ->setStreet($data['street'])
            ->setCity($data['city'])
            ->setRegion($region)
            ->setPostalCode($data['zipCode']);

        return $address;
    }
}
