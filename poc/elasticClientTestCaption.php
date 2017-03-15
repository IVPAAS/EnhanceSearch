<?php
require '../vendor/autoload.php';

/**
 *  Initialization part only the connector
 */

$hosts = [
  'dev-backend27.dev.kaltura.com:9200'
];

$client = Elasticsearch\ClientBuilder::create()
    ->setHosts($hosts)
    ->build();
//-------------- INIT END -------------------------

/**
 *  create the entry simulated index and insert data
 */

$entryCaptionsIndexMapping = [
    'settings' => [
        'number_of_shards' => 5,
        'number_of_replicas' => 1
    ],
    'mappings' => [
        'entry_data' => [
            'properties' => [
                'description' => [
                    'type' => 'text'
                ],
                'name' => [
                    'type' => 'text'
                ],
                'status' => [
                    'type' => 'short'
                ],
                'partner_id' => [
                    'type' => 'keyword'
                ]
            ]
        ],
        'caption' => [
            '_parent' => [
                'type' => 'entry_data'
            ],
            'properties' => [
                'lines' => [
                    'type' => 'nested',
                    'properties' => [
                        'start' => [
                            'type' => 'long'
                        ],
                        'end' => [
                            'type' => 'long'
                        ],
                        'content' => [
                            'type' => 'text'
                        ]
                    ]
                ]
            ]
        ]
    ]
];;

$indexParams = [];
$indexParams['index'] = 'kaltura_caption_client_test';
if ($client->indices()->exists($indexParams))
{
    $result = $client->indices()->delete($indexParams);
    print('Deleting entry for captions index with result: '.print_r($result, false).PHP_EOL);
}
$indexParams['body'] =  $entryCaptionsIndexMapping;
$result =$client->indices()->create($indexParams);
print('Creating entry for captions index with result: '.print_r($result, false).PHP_EOL);

// type of document
$indexParams['type'] = 'entry_data';
// fields
$indexParams['body'] = [
    'description' => 'Entry for captions test',
    'name' => 'Roku entry',
    'partner_id' => '1234',
    'status' => 2
];
// add document to the index
$result = $client->index($indexParams);
$firstEntryId = $result['_id'];
print('Added document to caption index with id: '.$firstEntryId.' and result: '.print_r($result, false).PHP_EOL);

$indexParams['body'] = [
    'description' => 'Second Entry for captions test',
    'name' => 'Ellen Rabbit',
    'partner_id' => '234',
    'status' => 2
];
// add document to the index
$result = $client->index($indexParams);
$secondEntryId = $result['_id'];
print('Added document to caption index with id: '.$secondEntryId.' and result: '.print_r($result, false).PHP_EOL);



//-------------- ENTRY END -------------------------


// type of document
$indexParams['type'] = 'caption';
// fields
$indexParams['parent'] = $firstEntryId;
$indexParams['body'] = [

    'lines' => [
        [
            'start' => 1,
            'end' => 2,
            'content' => 'Some words are identical'
        ],
        [
            'start' => 3,
            'end' => 4,
            'content' => 'Some words are different'
        ],
        [
            'start' => 5,
            'end' => 6,
            'content' => 'Some sentences are different'
        ],
        [
            'start' => 7,
            'end' => 8,
            'content' => 'Some sentences are identical'
        ],
        [
            'start' => 9,
            'end' => 10,
            'content' => 'Some sentences are identical'
        ]
    ]
];
// add document to the index
$result = $client->index($indexParams);
print('Added document to caption index with result: '.print_r($result, false).PHP_EOL);
// search for that document
$params = [
    'index'  => 'kaltura_caption_client_test',
    'type'   => 'caption',
    'body'   => [
        'query' => [
            'nested' => [
                'path' => 'lines',
                'query' => [
                    'bool' => [
                        'must' => [
                            ['match' => ['lines.content' => 'different' ]]
                        ]
                    ]
                ],
                'inner_hits' => ['size' => 10]
            ]
        ]
    ],
    'client' => [ 'ignore' => 404 ]
];
sleep(1);
// search with without child parent relationship
$results = $client->search($params);
print('First Search concluded in '.print_r($results, true).PHP_EOL);

$params = [
    'index'  => 'kaltura_caption_client_test',
    'type'   => 'entry_data',
    'body'   => [
        'query' => [
            'bool' => [
                'filter' => [
                    'has_child' => [
                        'type' => 'caption',
                        'query' => [
                            'nested' => [
                                'path' => 'lines',
                                'query' => [
                                    'bool' => [
                                        'must' => [
                                            [
                                                'match' => [
                                                    'lines.content' => 'different'
                                                ]
                                            ]
                                        ]
                                    ]

                                ],
                                'inner_hits' => [
                                    'size' => 10
                                ]
                            ]
                        ],
                        'inner_hits' => [
                            'size' => 10
                        ]
                    ]
                ],
                'must' => [
                    'match' => [
                        'status' => 2
                    ]
                ]
            ]
        ]
    ],
    'client' => [ 'ignore' => 404 ]
];

// search without child parent relationship
$results = $client->search($params);

print('Search with mismatch concluded in '.print_r($results, true).PHP_EOL);