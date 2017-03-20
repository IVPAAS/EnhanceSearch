<?php
require_once (__DIR__ .'/../../../vendor/autoload.php');
chdir(dirname(__FILE__));
require_once(__DIR__ . '/../../bootstrap.php');

function isEntryEntitled($elasticClient, $entryId, $kuserId)
{
    if(isUserEntitledToEntry($elasticClient, $entryId, $kuserId) || isEntryInSomeCategory($elasticClient, $entryId, $kuserId))
    {
        echo "entry [".$entryId."] is entitled to [".$kuserId."]\n";
        return true;
    }
    else
    {
        echo "entry [".$entryId."] is  not entitled to [".$kuserId."]\n";
        return false;
    }
}

function isUserEntitledToEntry($elasticClient, $entryId, $userId)
{
    $params = [
        'index' => 'kaltura_entry_nadav',
        'type' => 'entry',
        'size' => 0,
        'terminate_after' => 1,
        'body' => [
            'query' => [
                'bool' => [
                    'filter' => [
                        'term' => [
                            'entry_id' => $entryId
                        ]
                    ],
                    'should' => [
                        [
                            'terms' => [
                                'entitled_kusers_edit' => [
                                    'index' => 'kaltura_kuser_kgroup_nadav',
                                    'type' => 'KuserKgroup',
                                    'id' => $userId,
                                    'path' => 'groupIds'
                                ]
                            ]
                        ],
                        [
                            'terms' => [
                                'entitled_kusers_publish' => [
                                    'index' => 'kaltura_kuser_kgroup_nadav',
                                    'type' => 'KuserKgroup',
                                    'id' => $userId,
                                    'path' => 'groupIds'
                                ]
                            ]
                        ],
                        [
                            'term' => [
                                'creator_kuser_id' => $userId
                            ]
                        ],
                        [
                            'term' => [
                                'kuser_id' => $userId
                            ]
                        ]
                    ],
                    'minimum_number_should_match' => 1
                ]
            ]
        ]
    ];
    $results = $elasticClient->search($params);
    // echo print_r($results, true);
    // echo "\n";
    if ($results['hits']['total'])
    {
        echo "entry [".$entryId."] have userId [".$userId."] edit or publish\n";
        return true;
    }
    else
    {
        echo "entry [".$entryId."] doesnt have userId [".$userId."] edit or publish\n";
        return false;
    }
}

function isEntryInSomeCategory($elasticClient, $entryId, $userId)
{
    $params = [
        'index' => 'kaltura_category_entry_user_nadav',
        'type' => 'category',
        'size' => 0,
        'terminate_after' => 1,
        'body' => [
            'query' => [
                'bool' => [
                    'filter' => [
                        'has_child' => [
                            'type' =>  'category_entry',
                            'query' => [
                                'bool' => [
                                    'filter' => [
                                        [
                                            'term' => [
                                                'entry_id' => $entryId
                                            ],
                                            'terms' => [
                                                'status' => [1,2] //PENDING, ACTIVE
                                            ]
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
                                            'terms' => [
                                                'user' => [
                                                    'index' => 'kaltura_kuser_kgroup_nadav',
                                                    'type' => 'KuserKgroup',
                                                    'id' => $userId,
                                                    'path' => 'groupIds'
                                                ]
                                            ]
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
    // echo print_r($results, true);
    // echo "\n";
    if ($results['hits']['total'])
    {
        echo "entry [".$entryId."] is in some category for userId [".$userId."]\n";
        return true;
    }
    else
    {
        echo "entry [".$entryId."] is not in some category for userId [".$userId."]\n";
        return false;
    }
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
