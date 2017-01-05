<?php

namespace Oro\Bundle\VisibilityBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\CatalogBundle\Tests\Functional\DataFixtures\LoadCategoryProductData;
use Oro\Bundle\CustomerBundle\Entity\Account;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadAccounts;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadGroups;
use Oro\Bundle\CustomerBundle\Migrations\Data\ORM\LoadAnonymousAccountGroup;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\VisibilityBundle\Entity\Visibility\AccountGroupProductVisibility;
use Oro\Bundle\VisibilityBundle\Entity\Visibility\AccountProductVisibility;
use Oro\Bundle\VisibilityBundle\Entity\Visibility\ProductVisibility;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

class LoadProductVisibilityData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

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
        return [
            LoadGroups::class,
            LoadAccounts::class,
            LoadCategoryProductData::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        // set default fallback to categories
        $configVisibilities = $manager->getRepository('OroVisibilityBundle:Visibility\ProductVisibility')
            ->findBy(['visibility' => ProductVisibility::CONFIG]);
        foreach ($configVisibilities as $visibilityEntity) {
            $visibilityEntity->setVisibility(ProductVisibility::CATEGORY);
        }
        $manager->flush();

        // load visibilities
        foreach ($this->getProductVisibilities() as $productReference => $productVisibilityData) {
            /** @var Product $product */
            $product = $this->getReference($productReference);
            $this->createProductVisibilities($manager, $product, $productVisibilityData);
        }
        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @param Product $product
     * @param array $data
     */
    protected function createProductVisibilities(ObjectManager $manager, Product $product, array $data)
    {
        $productVisibility = new ProductVisibility();
        $productVisibility->setProduct($product)
            ->setVisibility($data['all']['visibility']);

        $scope = $this->container->get('oro_scope.scope_manager')
            ->findOrCreate(ProductVisibility::VISIBILITY_TYPE);
        $productVisibility->setScope($scope);

        $manager->persist($productVisibility);

        $this->setReference($data['all']['reference'], $productVisibility);

        $this->createAccountGroupVisibilities($manager, $product, $data['groups']);

        $this->createAccountVisibilities($manager, $product, $data['accounts']);
    }

    /**
     * @param string $groupReference
     * @return CustomerGroup
     */
    private function getAccountGroup($groupReference)
    {
        if ($groupReference === 'account_group.anonymous') {
            $accountGroup = $this->container
                ->get('doctrine')
                ->getManagerForClass('OroCustomerBundle:CustomerGroup')
                ->getRepository('OroCustomerBundle:CustomerGroup')
                ->findOneBy(['name' => LoadAnonymousAccountGroup::GROUP_NAME_NON_AUTHENTICATED]);
        } else {
            /** @var CustomerGroup $accountGroup */
            $accountGroup = $this->getReference($groupReference);
        }

        return $accountGroup;
    }

    /**
     * @param ObjectManager $manager
     * @param Product $product
     * @param array $accountGroupsData
     */
    protected function createAccountGroupVisibilities(
        ObjectManager $manager,
        Product $product,
        array $accountGroupsData
    ) {
        foreach ($accountGroupsData as $groupReference => $accountGroupData) {
            /** @var CustomerGroup $accountGroup */
            $accountGroup = $this->getAccountGroup($groupReference);

            $accountGroupProductVisibility = new AccountGroupProductVisibility();
            $accountGroupProductVisibility->setProduct($product)
                ->setVisibility($accountGroupData['visibility']);

            $scopeManager = $this->container->get('oro_scope.scope_manager');
            $scope = $scopeManager->findOrCreate(
                AccountGroupProductVisibility::VISIBILITY_TYPE,
                ['accountGroup' => $accountGroup]
            );

            $accountGroupProductVisibility->setScope($scope);

            $manager->persist($accountGroupProductVisibility);

            $this->setReference($accountGroupData['reference'], $accountGroupProductVisibility);
        }
    }

    /**
     * @param ObjectManager $manager
     * @param Product $product
     * @param array $accountsData
     */
    protected function createAccountVisibilities(
        ObjectManager $manager,
        Product $product,
        array $accountsData
    ) {
        foreach ($accountsData as $accountReference => $accountData) {
            /** @var Account $account */
            $account = $this->getReference($accountReference);

            $accountProductVisibility = new AccountProductVisibility();
            $accountProductVisibility->setProduct($product)
                ->setVisibility($accountData['visibility']);

            $scopeManager = $this->container->get('oro_scope.scope_manager');
            $scope = $scopeManager->findOrCreate('account_product_visibility', ['account' => $account]);
            $accountProductVisibility->setScope($scope);

            $manager->persist($accountProductVisibility);

            $this->setReference($accountData['reference'], $accountProductVisibility);
        }
    }

    /**
     * @return array
     */
    protected function getProductVisibilities()
    {
        $fixturesFileName = __DIR__ . '/data/product_visibilities.yml';

        return Yaml::parse(file_get_contents($fixturesFileName));
    }
}
