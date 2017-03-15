<?php
require_once (__DIR__ .'/../../../vendor/autoload.php');

function isEntryEntitled($elasticClient, $entryId, $userId)
{
    $params = [
        'index' => 'kaltura_category_entry_user_nadav',
        'type' => 'category',
        'size' => 1,
        'body' => [
            'query' => [
                'bool' => [
                    'filter' => [
//                        'term' => [
//                            'partner_id' =>$partnerId
//                        ],
                        'has_child' => [
                            'type' =>  'category_entry',
                            'query' => [
                                'bool' => [
                                    'filter' => [
                                        [
                                            'term' => [ 'entry' =>$entryId ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'has_child' => [
                            'type' =>  'category_user',
                            'query' => [
                                'bool' => [
                                    'filter' => [
                                        [
                                            'term' => [ 'user' =>$userId ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];
    $results = $elasticClient->search($params);
    echo print_r($results, true);
    echo "\n";
    if ($results['hits']['total'])
        echo "entitled\n";
    else
        echo "not entitled\n";
}

if($argc != 4) {
    echo "Arguments missing.\n\n";
    echo "Usage: php isEntryEntitled.php {elasticHostUrl:port} {entryId} {userId} \n";
    exit;
}

$elasticHostUrl = $argv[1];
$entryId = $argv[2];
$userId = $argv[3];

$hosts = [$elasticHostUrl];
$elasticClient = Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();
isEntryEntitled($elasticClient, $entryId, $userId);

