<?php
namespace Eddie\ElasticSearch\Slim;

trait Queryable
{
    private $query;

    private function canCallQueryMethods($method)
    {
        try {
            $ret = (new \ReflectionObject($this->getQuery()))->getMethod($method);
            return !!$ret;

        } catch (\ReflectionException $e) {
            //
        } catch (\Exception $e) {
            //
        }
        return false;
    }

    protected function getQuery()
    {
        if (empty($this->query)) {
            $this->query = new \Eddie\ElasticSearch\Query();
        }
        return $this->query;
    }
}