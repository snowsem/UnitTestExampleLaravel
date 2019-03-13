<?php

namespace Tests\Unit\Service;

use GeoIp2\Model\City;
use GeoIp2\Model\Country;
use App\Service\GeoIpService;
use Illuminate\Http\Request;

/**
 * Class GeoIpServiceTest
 * @package Tests\Unit\Service
 * Scenario Driven Testing
 */
class GeoIpServiceTest extends \BaseUnitTransaction
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var GeoIpService
     */
    public $resolveInstance;

    /**
     * @var mixed
     */
    public $request;

    /**
     * @var mixed
     */
    protected $myIp;

    /**
     * @throws \Exception
     */
    public function _before()
    {
        $this->resolveInstance = resolve('geoip.service');
        $this->myIp = '161.185.160.93';
        $this->request = $this->makeEmpty(Request::class, ['getClientIp' => $this->myIp]);

        parent::_before();
    }

    public function testInstance()
    {
        $this->assertInstanceOf(GeoIpService::class, $this->resolveInstance);
    }

    public function testMethodAllow()
    {
        $this->assertTrue(method_exists($this->resolveInstance, 'getCountryByIp'));
    }

    public function testGetCountryByIp()
    {
        $this->assertEquals(get_class($this->resolveInstance->getCountryByIp($this->myIp)), Country::class);
    }

    public function testGetCountryByIpNull()
    {
        $this->assertEquals($this->resolveInstance->getCountryByIp($this->myIp . '233'), null);
    }

    public function testGetCountry()
    {
        $this->assertEquals(get_class($this->resolveInstance->getCountry($this->request)), Country::class);
    }

    public function testGetCityByIp()
    {
        $this->assertEquals(get_class($this->resolveInstance->getCityByIp($this->myIp)), City::class);
    }

    public function testGetCity()
    {
        $this->assertEquals(get_class($this->resolveInstance->getCity($this->request)), City::class);
    }
}