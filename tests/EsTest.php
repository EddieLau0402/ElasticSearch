<?php
declare(strict_types=1);

namespace Eddie\ElasticSearch\Tests;

use Eddie\ElasticSearchCore\Aggregation;
use Eddie\ElasticSearch\Es;

require_once __DIR__.'/../vendor/autoload.php';

class EsTest extends \PHPUnit\Framework\TestCase
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
        $es = $this->getEsLimeClient($index, $type);

        $this->assertInstanceOf(\Eddie\ElasticSearch\Es::class, $es);
        $this->assertEquals($es->getIndex(), $index);
        $this->assertEquals($es->getType(), $type);
    }

    /**
     * @test
     *
     * @author Eddie
     */
    public function testDocument()
    {
        $es = $this->getEsLimeClient('test', 'users');

        // Create document
        $docId = 'test-user-'.time();
        $ret = $es->createDocument([
            'id' => $docId,
            'name' => 'Bob',
            'gender' => 'male',
            'age' => 28
        ]);
        $this->assertEquals($docId, $ret['_id']);

        // Get document
        $ret = $es->getDocument($docId);
        $this->assertTrue($ret['found']);

        // Delete document
        $ret = $es->deleteDocument($docId);
        $this->assertTrue(!!$ret['_shards']['successful']);
    }


    /**
     * @test
     *
     * @author Eddie
     */
    public function testQuery()
    {
        $es = $this->getEsLimeClient('test', 'users');
        $ret = $es
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
    public function testFlushQueryCondition()
    {
        $es = $this->getEsLimeClient('test', 'users');
        $es
            ->where('name.keyword', 'Bob')
            ->whereBetween('age', [15, 20])
        ;

        $this->assertNotEmpty($es->getQueryParam()['query']['bool']['must']);

        $es->flush();

        $this->assertEmpty($es->getQueryParam()['query']['bool']['must']);
    }


    /**
     * @test
     *
     * @author Eddie
     */
    public function testAggregation()
    {
        $es = $this->getEsLimeClient('test', 'users');

        $groupField = 'group_by_gender';

        try {
            $ret = $es
//                ->aggregate([
//                    'aggs' => [
//                        $groupField => [
//                            'terms' => [
//                                'field' => 'gender.keyword',
//                                'size' => 10
//                            ]
//                        ]
//                    ]
//                ])
                ->aggregate(
                    (new Aggregation())
                        //->setTerms('gender', $groupField) // test - throw exception
                        ->setTerms('gender.keyword', $groupField)
                        ->addSubAgg((new Aggregation())->setMax('age', 'max_age'), ['size' => 10])
                        ->addSubAgg((new Aggregation())->setMin('age', 'min_age'))
                        ->addSubAgg((new Aggregation())->setAvg('age'))
                )
                ->limit(0)
                ->get();

            $this->assertArrayHasKey('aggregations', $ret);
            $this->assertArrayHasKey($groupField, $ret['aggregations']);
            $this->assertGreaterThanOrEqual(0, $ret['aggregations'][$groupField]['buckets']);

            if ($ret['aggregations'][$groupField]['buckets'] ?? 0 > 0) {
                $this->assertArrayHasKey('max_age', $ret['aggregations'][$groupField]['buckets'][0]);
                $this->assertArrayHasKey('min_age', $ret['aggregations'][$groupField]['buckets'][0]);
                $this->assertArrayHasKey('avg_age', $ret['aggregations'][$groupField]['buckets'][0]);
            }
        } catch (\Elasticsearch\Common\Exceptions\BadRequest400Exception $e) {
            $this->expectException(\Elasticsearch\Common\Exceptions\BadRequest400Exception::class);
            throw $e;
        } catch (\Exception $e) {
            $this->expectException(\Elasticsearch\Common\Exceptions\BadRequest400Exception::class);
            throw $e;
        }
    }


    /**
     * @test
     *
     * @author Eddie
     */
    public function testExistsIndex()
    {
        //$es = $this->getEsLimeClient('test', 'users');
        $es = new Es([
            'hosts' => $this->hosts,
            //'index' => $index,
            //'type' => $type
        ]);;

        $existsIndex = 'test';
        $ret = $es->existsIndex($existsIndex);
        $this->assertTrue($ret);

        $notExistsIndex = 'abc123';
        $ret = $es->existsIndex($notExistsIndex);
        $this->assertFalse($ret);
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