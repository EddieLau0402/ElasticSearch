<?php
namespace Eddie\ElasticSearchLime;

trait Searchable
{
    protected $aggs;

    protected $source;

    protected $size = 10;

    protected $from = 0;

    protected $sort;


    /**
     * Alias of "setSize"
     *
     * @author Eddie
     *
     * @param int $size
     * @return Searchable
     */
    public function limit(int $size)
    {
        return $this->setSize($size);
    }

    /**
     * Alias of "setFrom"
     *
     * @author Eddie
     *
     * @param int $size
     * @return Searchable
     */
    public function skip(int $offset)
    {
        return $this->setFrom($offset);
    }

    /**
     * @param array|string $fields = ['*']
     * @return mixed
     */
    public function get(array $fields = [])
    {
        $body = [
            'from' => $this->from,
            'size' => $this->size,
            'query' => $this->getQuery()->format()
        ];

        if (count($fields) > 0) $this->setSource($fields);
        if (!empty($this->source)) $body['_source'] = $this->source;

        if (!empty($this->sort)) $body['sort'] = $this->sort;

        // Execute search
        return $this->client->search([
            'index' => $this->getIndex(),
            'type' => $this->getType(),
            'body' => $body
        ]);
    }



    /**
     * @param int $size
     * @return $this
     */
    public function setSize(int $size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @param int $from
     * @return $this
     */
    public function setFrom(int $from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @param array $source
     * @return $this
     */
    public function setSource(array $source)
    {
        $this->source = $source;
        return $this;
    }

}