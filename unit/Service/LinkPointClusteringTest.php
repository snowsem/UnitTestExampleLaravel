<?php

namespace Tests\Unit\Service;

use App\Service\LinkPointClustering;

/**
 * Class LinkPointClusteringTest
 * @package Tests\Unit\Service
 */
class LinkPointClusteringTest extends \BaseUnitTransaction
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var mixed
     */
    protected $resolveInstance;

    /**
     * before init tests
     */
    public function _before()
    {
        $service = app('point.clustering');
        $this->resolveInstance = $service;

        parent::_before();
    }

    /**
     * test instance
     */
    public function testInstance()
    {
        $this->tester->assertInstanceOf(LinkPointClustering::class, $this->resolveInstance);
    }

    public function testGetClusteringPoints()
    {
        $this->resolveInstance->setBounds(10,100,10,100);

        $point = [
            ['latitude' => '59.928957', 'longitude' => '30.411995'],
            ['latitude' => '51.928957', 'longitude' => '30.411995'],
            ['latitude' => '30.928957', 'longitude' => '70.411995'],
                     ];

        $this->resolveInstance->addPoints($point);
        $this->resolveInstance->setPrecision(5);
        $clusterized = $this->resolveInstance->getClusteringPoints();
        $this->assertTrue(is_array($clusterized));
        $this->assertCount(3, $clusterized);
    }
}