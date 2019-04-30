<?php
declare(strict_types=1);

namespace Eddie\ElasticSearchLime\Tests;

use Eddie\ElasticSearchLime\EsLime;

require_once __DIR__.'/../vendor/autoload.php';

class EsLimeTest extends \PHPUnit\Framework\TestCase
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

        $this->assertInstanceOf(\Eddie\ElasticSearchLime\EsLime::class, $esLime);
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
     * Get instance of "EsLime"
     *
     * @author Eddie
     *
     * @param $index
     * @param $type
     * @return EsLime
     */
    private function getEsLimeClient($index, $type = 'doc'): EsLime
    {
        return new EsLime([
            'hosts' => $this->hosts,
            'index' => $index,
            'type' => $type
        ]);
    }

}