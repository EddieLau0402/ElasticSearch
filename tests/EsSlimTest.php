<?php
declare(strict_types=1);

namespace Eddie\ElasticSearch\Slim\Tests;

use Eddie\ElasticSearch\Slim\Es;

require_once __DIR__.'/../vendor/autoload.php';

class EsSlimTest extends \PHPUnit\Framework\TestCase
{
    protected $hosts = [
        'localhost:9200'
    ];

    /**
     * This method is called before each test.
     */
    protected function setUp()
    {
        //
    }

    /**
     * This method is called after each test.
     */
    protected function tearDown()
    {
        //
    }


    /**
     * @test
     *
     * @author Eddie
     */
    public function testGetClient()
    {
        $index = 'test';
        $type = 'users';
        $esLime = $this->getEsLimeClient($index, $type);

        $this->assertInstanceOf(\Eddie\ElasticSearch\Slim\Es::class, $esLime);
        $this->assertEquals($esLime->getIndex(), $index);
        $this->assertEquals($esLime->getType(), $type);
    }

    /**
     * @test
     *
     * @author Eddie
     */
    public function testDocument()
    {
        $esLime = $this->getEsLimeClient('test', 'users');

        // Create document
        $docId = 'test-user-'.time();
        $ret = $esLime->createDocument([
            'id' => $docId,
            'name' => 'Bob',
            'gender' => 'male',
            'age' => 28
        ]);
        $this->assertEquals($docId, $ret['_id']);

        // Get document
        $ret = $esLime->getDocument($docId);
        $this->assertTrue($ret['found']);

        // Delete document
        $ret = $esLime->deleteDocument($docId);
        $this->assertTrue(!!$ret['_shards']['successful']);
    }


    /**
     * @test
     *
     * @author Eddie
     */
    public function testQuery()
    {
        $esLime = $this->getEsLimeClient('test', 'users');


        $ret = $esLime
            ->where('name.keyword', 'Bob')
            ->whereBetween('age', [15, 20])
            ->get(['name', 'age'])
        ;

        $this->assertArrayHasKey('hits', $ret['hits']);
        $this->assertGreaterThanOrEqual(0, $ret['hits']['total']); // found OR empty
    }


    /**
     * @test
     *
     * @author Eddie
     */
    public function testAggregation()
    {
        $esLime = $this->getEsLimeClient('test', 'users');

        $groupField = 'group_by_gender';
        $ret = $esLime
            ->aggregate([
                'aggs' => [
                    $groupField => [
                        'terms' => [
                            'field' => 'gender.keyword',
                            'size' => 10
                        ]
                    ]
                ]
            ])
            ->limit(0)
            ->get()
        ;

        $this->assertArrayHasKey('aggregations',$ret);
        $this->assertArrayHasKey($groupField, $ret['aggregations']);
        $this->assertGreaterThanOrEqual(0, $ret['aggregations'][$groupField]['buckets']);
    }


    /**
     * Get instance of "Es"
     *
     * @author Eddie
     *
     * @param $index
     * @param $type
     * @return Es
     */
    private function getEsLimeClient($index, $type = 'doc')
    {
        return new Es([
            'hosts' => $this->hosts,
            'index' => $index,
            'type' => $type
        ]);
    }

}