<?php
namespace Eddie\ElasticSearch;

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

    public function getQueryParam()
    {
        return ['query' => $this->getQuery()->format()];
    }

    protected function getQuery()
    {
        if (empty($this->query)) {
            $this->query = new \Eddie\ElasticSearchCore\Query();
        }
        return $this->query;
    }
}