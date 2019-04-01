<?php

namespace Tests\Unit\Traits;

use App\GraphQL\v31\OauthClientTypeTrait;

/**
 * Class OauthClientTypeTraitTest
 * @package Tests\Unit\Traits
 */
class OauthClientTypeTraitTest extends \BaseUnitTransaction
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var \stdClass
     */
    protected $classInstance;

    const TRAIT_CLASS_NAME = OauthClientTypeTrait::class;

    /**
     * OauthClientTypeTraitTest constructor.
     * @param string|null $name
     * @param array $data
     * @param string $dataName
     */
    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {

        $this->classInstance = (new class {
            use OauthClientTypeTrait;

            public $mockId;

            //mock get clientId
            private function getClientId()
            {
                return $this->mockId;
            }

        });

        parent::__construct($name, $data, $dataName);
    }

    /**
     * @throws \ReflectionException
     */
    public function testInstance()
    {
        $classReflection = new \ReflectionClass($this->classInstance);
        $traits = $classReflection->getTraits();

        $this->assertArrayHasKey(self::TRAIT_CLASS_NAME, $traits);
    }

    /**
     * testGetTokenClient
     */
    public function testGetTokenClient()
    {
        $this->classInstance->mockId = config('citylife_settings.cashier_android_client_id');
        $this->assertEquals('cashier_android', $this->classInstance->getTokenClient());

        $this->classInstance->mockId = config('citylife_settings.cashier_ios_client_id');
        $this->assertEquals('cashier_ios', $this->classInstance->getTokenClient());

        $this->classInstance->mockId = 80;
        $this->assertEquals('current', $this->classInstance->getTokenClient());
    }

    /**
     * testNegativeGetTokenClient
     */
    public function testNegativeGetTokenClient()
    {
        $this->classInstance->mockId = 71;
        $this->assertNotEquals('cashier_android', $this->classInstance->getTokenClient());

        $this->classInstance->mockId = 71;
        $this->assertNotEquals('cashier_ios', $this->classInstance->getTokenClient());

        $this->classInstance->mockId = config('citylife_settings.cashier_ios_client_id');
        $this->assertNotEquals('current', $this->classInstance->getTokenClient());

        $this->classInstance->mockId = config('citylife_settings.cashier_android_client_id');
        $this->assertNotEquals('current', $this->classInstance->getTokenClient());

    }
}