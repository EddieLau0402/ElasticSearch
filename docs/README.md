
### 环境需求
- PHP >= 7.0

### 依赖
`"elasticsearch/elasticsearch": "^6.0"`

### 使用说明

> 获取实例
```php

$es = new \Eddie\ElasticSearchLime\EsLime([
    'hosts' => [
        'localhost:9200',
        /* others... */
    ],
    
    /* 指定 index, type 方式1 */
    //'index' => 'your_index',
    //'type' => 'doc'
]);


```

> 索引操作
```php

/* 指定 index, type 方式2 */
$es
    ->setIndex('your_index')
    ->setType('doc')
;
/* 获取当前 index, type */
$es->getIndex();
$es->getType();

/* 创建索引 */
$ret = $es->createIndex('your_index');

/* 带参数创建索引 */
//ret = $es->createIndex('your_index', [
//    'settings' => [
//        'numbber_of_shards' => 1,
//        'number_of_replicas' => 1,
//    ]
//]);

/* 删除索引 */
$ret = $es->deleteIndex('your_index');

```

> 文档操作
```php

/* 创建文档 */
$ret = $es->createDocument([
    'id' => 'abc123',
    'key1' => 'val1',
    'key2' => 'val2',
]);

/* 获取文档 */
$ret = $es->getDocument('abc123');

/* 删除文档 */
$ret = $es->deleteDocument('abc123');

```

> 查询操作
```php

$ret = $es
   /* 条件 "且" */
   ->where('key', 'val')
   ->where(['key' => 'val'])
   
   /* 条件 "或" */
   ->orWhere('key', 'val')
   ->orWhere(['key' => 'val'])
   
   /* 条件 "非" */
   ->whereNot('key', 'val')
   ->whereNot(['key' => 'val'])
   
   ->whereGt('key', 'val')               // 大于
   ->whereGte('key', 'val')              // 大于等于
   ->whereLt('key', 'val')               // 小于
   ->whereLte('key', 'val')              // 小于等于
   ->whereBetween('key', ['min', 'max']) // 指定范围
   

//   TODO : 未完成   
//   ->aggregate([
//       // 聚合查询条件
//   ])
//   //->aggs([]) // 方法"aggregate" 的别名
   
   ->limit(10) // 获取记录条目数, 相当于"size" 
   ->skip(0)   // 偏移量, 相当于"from"
   
   // 执行查询
   ->get()
   //->get(['key1', 'key2']) // 返回部分字段
;

```
