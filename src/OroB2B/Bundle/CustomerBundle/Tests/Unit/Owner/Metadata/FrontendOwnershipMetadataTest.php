<?php

namespace OroB2B\Bundle\CustomerBundle\Tests\Unit\Owner\Metadata;

use OroB2B\Bundle\CustomerBundle\Owner\Metadata\FrontendOwnershipMetadata;

class FrontendOwnershipMetadataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $ownerType
     * @param int $expectedOwnerType
     * @param array $exceptionDefinition
     *
     * @dataProvider frontendOwnerTypeException
     */
    public function testSetFrontendOwner(array $ownerType, $expectedOwnerType, array $exceptionDefinition = [])
    {
        if ($exceptionDefinition) {
            list ($exception, $message) = $exceptionDefinition;
            $this->setExpectedException($exception, $message);
        }

        list ($frontendOwnerType, $frontendOwnerFieldName, $frontendOwnerColumnName) = $ownerType;
        $metadata = new FrontendOwnershipMetadata(
            $frontendOwnerType, $frontendOwnerFieldName, $frontendOwnerColumnName
        );

        $this->assertEquals($expectedOwnerType, $metadata->getOwnerType());
        $this->assertEquals($frontendOwnerFieldName, $metadata->getOwnerFieldName());
        $this->assertEquals($frontendOwnerColumnName, $metadata->getOwnerColumnName());
    }

    /**
     * @return array
     */
    public function frontendOwnerTypeException()
    {
        return [
            [
                ['USER', 'account_user', 'account_user_id'],
                FrontendOwnershipMetadata::OWNER_TYPE_FRONTEND_USER,
            ],
            [
                ['CUSTOMER', 'customer', 'customer_id'],
                FrontendOwnershipMetadata::OWNER_TYPE_FRONTEND_CUSTOMER,
            ],
            [
                ['UNKNOWN', 'customer', 'customer_id'],
                FrontendOwnershipMetadata::OWNER_TYPE_FRONTEND_CUSTOMER,
                [
                    '\InvalidArgumentException',
                    'Unknown owner type: UNKNOWN.',
                ],
            ],
            [
                ['UNKNOWN', 'customer', 'customer_id'],
                FrontendOwnershipMetadata::OWNER_TYPE_FRONTEND_CUSTOMER,
                [
                    '\InvalidArgumentException',
                    'Unknown owner type: UNKNOWN.',
                ],
            ],
            [
                ['', '', ''],
                FrontendOwnershipMetadata::OWNER_TYPE_NONE,
            ],
            [
                ['CUSTOMER', '', 'customer_id'],
                FrontendOwnershipMetadata::OWNER_TYPE_FRONTEND_CUSTOMER,
                [
                    '\InvalidArgumentException',
                    'The owner field name must not be empty.',
                ],
            ],
            [
                ['CUSTOMER', 'customer', ''],
                FrontendOwnershipMetadata::OWNER_TYPE_FRONTEND_CUSTOMER,
                [
                    '\InvalidArgumentException',
                    'The owner column name must not be empty.',
                ],
            ],
        ];
    }

    public function testIsBasicLevelOwned()
    {
        $metadata = new FrontendOwnershipMetadata();
        $this->assertFalse($metadata->isBasicLevelOwned());

        $metadata = new FrontendOwnershipMetadata('USER', 'account_user', 'account_user_id');
        $this->assertTrue($metadata->isBasicLevelOwned());

        $metadata = new FrontendOwnershipMetadata('CUSTOMER', 'customer', 'customer_id');
        $this->assertFalse($metadata->isBasicLevelOwned());
    }

    public function testIsLocalLevelOwned()
    {
        $metadata = new FrontendOwnershipMetadata();
        $this->assertFalse($metadata->isLocalLevelOwned());
        $this->assertFalse($metadata->isLocalLevelOwned(true));

        $metadata = new FrontendOwnershipMetadata('CUSTOMER', 'customer', 'customer_id');
        $this->assertTrue($metadata->isLocalLevelOwned());
        $this->assertTrue($metadata->isLocalLevelOwned(true));

        $metadata = new FrontendOwnershipMetadata('USER', 'account_user', 'account_user_id');
        $this->assertFalse($metadata->isLocalLevelOwned());
        $this->assertFalse($metadata->isLocalLevelOwned(true));
    }

    public function testSerialization()
    {
        $metadata = new FrontendOwnershipMetadata('USER', 'account_user', 'account_user_id');
        $data = serialize($metadata);

        $metadata = new FrontendOwnershipMetadata();
        $this->assertFalse($metadata->isBasicLevelOwned());
        $this->assertFalse($metadata->isLocalLevelOwned());
        $this->assertEquals('', $metadata->getOwnerFieldName());
        $this->assertEquals('', $metadata->getOwnerColumnName());

        $metadata = unserialize($data);
        $this->assertTrue($metadata->isBasicLevelOwned());
        $this->assertFalse($metadata->isLocalLevelOwned());
        $this->assertEquals('account_user', $metadata->getOwnerFieldName());
        $this->assertEquals('account_user_id', $metadata->getOwnerColumnName());
    }

    public function testIsGlobalLevelOwned()
    {
        $metadata = new FrontendOwnershipMetadata();
        $this->assertFalse($metadata->isGlobalLevelOwned());
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage Frontend entities are not owned by organization
     */
    public function testGetGlobalOwnerColumnName()
    {
        $metadata = new FrontendOwnershipMetadata();
        $this->assertFalse($metadata->getGlobalOwnerColumnName());
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage Frontend entities are not owned by organization
     */
    public function testGetGlobalOwnerFieldName()
    {
        $metadata = new FrontendOwnershipMetadata();
        $this->assertFalse($metadata->getGlobalOwnerFieldName());
    }
}
