<?php
require_once (__DIR__ .'/../../../../vendor/autoload.php');

chdir(dirname(__FILE__));
require_once(__DIR__ . '/../../../bootstrap.php');

function addCategory(category $category)
{
    global $elasticClient;
    $params = [
        'index' => 'kaltura_category_nadav',
        'type' => 'category',
        'id' => $category->getId(),
        'body' => [
            'partner_id' => $category->getPartnerId(),
            'privacy' => $category->getPrivacy(),
            'privacy_context' => $category->getPrivacyContext(),
            'privacy_contexts' => $category->getPrivacyContexts(),
            'status' => $category->getStatus(),
        ]
    ];
    $response = $elasticClient->index($params);
    echo "indexed category \n";
    print_r($response);
    echo "\n";
}

function addEntryToCategory(categoryEntry $categoryEntry)
{
    global $elasticClient;
    $params = [
        'index' => 'kaltura_entry_nadav',
        'type' => 'entry',
        'id' => $categoryEntry->getEntryId(),
        'body' => [
            'script' => [
                'inline' => 'if (ctx._source.category_ids == null) { ctx._source.category_ids = new ArrayList(); ctx._source.category_ids.add(params.category_id)} else { if (!ctx._source.category_ids.contains(params.category_id)) {ctx._source.category_ids.add(params.category_id) }}',
                'lang' => 'painless',
                'params' => [
                    'category_id' => $categoryEntry->getCategoryId()
                ]
            ]
        ]
    ];
    $response = $elasticClient->update($params);
    echo "added entry[".$categoryEntry->getEntryId()."] to category[".$categoryEntry->getCategoryId()."]\n";
    print_r($response);
    echo "\n";
}

function addUserToCategory(categoryKuser  $categoryUser)
{
    global $elasticClient;
    $params = [
        'index' => 'kaltura_category_nadav',
        'type' => 'category',
        'id' => $categoryUser->getCategoryId(),
        'body' => [
            'script' => [
                'inline' => 'if (ctx._source.kuser_ids == null) { ctx._source.kuser_ids = new ArrayList(); ctx._source.kuser_ids.add(params.kuser_id)} else { if (!ctx._source.kuser_ids.contains(params.kuser_id)) {ctx._source.kuser_ids.add(params.kuser_id) }}',
                'lang' => 'painless',
                'params' => [
                    'kuser_id' => $categoryUser->getKuserId()
                ]
            ],
            'upsert' => [
                "kuser_ids" => [$categoryUser->getKuserId()]
            ]
        ]
    ];
    $response = $elasticClient->update($params);
    echo "added user[".$categoryUser->getKuserId()."] to category[".$categoryUser->getCategoryId()."]\n";
    print_r($response);
    echo "\n";
}

function addUpadteKuserKgroup(KuserKgroup $kuserkgroup)
{
    global $elasticClient;
    $params = [
        'index' => 'kaltura_kuser_kgroup_nadav',
        'type' => 'KuserKgroup',
        'id' => $kuserkgroup->getKuserId(),
        'body' => [
            'script' => [
                'inline' => 'if (ctx._source.group_ids.contains(params.group_id)) { ctx.op = \"none\" } else { ctx._source.group_ids.Add(params.group_id) }',
                'lang' => 'painless',
                'params' => [
                    'group_id' => $kuserkgroup->getKgroupId()
                ]
            ],
            'upsert' => [
                "group_ids" => [$kuserkgroup->getKgroupId()]
            ]
        ]
    ];
    $response = $elasticClient->update($params);
    echo "added user [".$kuserkgroup->getKuserId()."] to group [".$kuserkgroup->getKgroupId()."]\n";
    print_r($response);
    echo "\n";
}

function addEntry(entry $entry)
{
    global $elasticClient;
    $params = [
        'index' => 'kaltura_entry_nadav',
        'type' => 'entry',
        'id' => $entry->getId(),
        'body' => [
            'parent_id' => $entry->getParentEntryId(),
            'status' => $entry->getStatus(),
            'entitled_kusers_edit' => $entry->getEntitledKusersEdit(),
            'entitled_kusers_publish' => $entry->getEntitledKusersPublish(),
            'kuser_id' => $entry->getKuserId(),
            'puser_id' => $entry->getPuserId(),
            'creator_kuser_id' => $entry->getCreatorKuserId(),
            'name' => $entry->getName()
        ]
    ];
    $response = $elasticClient->index($params);
    echo "added entry[".$entry->getEntryId()."]\n";
    print_r($response);
    echo "\n";
}

function addCaption($entryId)
{
    global $elasticClient;
    $params =[
        'index' => 'kaltura_entry_nadav',
        'type' => 'caption',
        'parent' => $entryId,
        'body' => [
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
        ]
    ];

    $response = $elasticClient->index($params);
    echo "Added caption to entry[".$entryId."]\n";
    print_r($response);
    echo "\n";
}

function getEntryIndexParams($numberOdShards, $numOfReplicas)
{
    $params = [
        'index' => 'kaltura_entry_nadav',
        'body' => [
            'settings' => [
                'number_of_shards' => $numberOdShards,
                'number_of_replicas' => $numOfReplicas
            ],
            'mappings' => [
                'entry' => [
                    '_source' => [
                        'enabled' => true
                    ],
                    'properties' => [
                        'name' => [
                            'type' => 'text'
                        ],
                        'description' => [
                            'type' => 'text'
                        ],
                        'partner_id' => [
                            'type' => 'text'
                        ],
                        'parent_id' => [
                            'type' => 'text'
                        ],
                        'status' => [
                            'type' => 'short'
                        ],
                        'entitled_kusers_edit' => [
                            'type' => 'text'
                        ],
                        'entitled_kusers_publish' => [
                            'type' => 'text'
                        ],
                        'kuser_id' => [
                            'type' => 'text'
                        ],
                        'creator_kuser_id' => [
                            'type' => 'text'
                        ],
                        'category_ids' => [ //array
                            'type' => 'text'
                        ]
                    ]
                ],
                'caption'  => [
                    '_parent'  => [
                        'type'  => 'entry'
                    ],
                    'properties' => [
                        'language' => [
                            'type'  => 'text'
                        ],
                        'lines'  => [
                            'type'  => 'nested',
                            'properties'  => [
                                'content' => [
                                    'type' => 'text'
                                ],
                                'start_time'  => [
                                    'type' => 'long'
                                ],
                                'end_time'  => [
                                    'type' => 'long'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];
    return $params;
}

function getCategoryIndexParams($numberOdShards, $numOfReplicas)
{
    $params = [
        'index' => 'kaltura_category_nadav',
        'body' => [
            'settings' => [
                'number_of_shards' => $numberOdShards,
                'number_of_replicas' => $numOfReplicas
            ],
            'mappings' => [
                'category' => [
                    '_source' => [
                        'enabled' => true
                    ],
                    'properties' => [
                        'partner_id' => [
                            'type' => 'text'
                        ],
                        'status' => [
                            'type' => 'keyword'
                        ],
                        'privacy' => [
                            'type' => 'text'
                        ],
                        'privacy_context' => [
                            'type' => 'text'
                        ],
                        'privacy_contexts' => [
                            'type' => 'text'
                        ],
                        'kuser_ids' => [
                            'type' => 'text'
                        ],
                    ]
                ]
            ]
        ]
    ];
    return $params;
}

function getKuserKgroupIndexParams($numberOdShards, $numOfReplicas)
{
    $params = [
        'index' => 'kaltura_kuser_kgroup_nadav',
        'body' => [
            'settings' => [
                'number_of_shards' => $numberOdShards,
                'number_of_replicas' => $numOfReplicas
            ],
            'mappings' => [
                'KuserKgroup' => [
                    '_source' => [
                        'enabled' => true
                    ],
                    'properties' => [
                        'group_ids' => [
                            'type' => 'text'
                        ]
                    ]
                ]
            ]
        ]
    ];
    return $params;
}

function populateCategory($partnerId)
{
    $c = new Criteria();
    $c->add(categoryPeer::PARTNER_ID,$partnerId, Criteria::EQUAL);
    $c->addAscendingOrderByColumn(categoryPeer::UPDATED_AT);
    $c->addAscendingOrderByColumn(categoryPeer::ID);
    $c->setLimit(10000);

    $con = myDbHelper::getConnection(myDbHelper::DB_HELPER_CONN_PROPEL2);

    categoryPeer::setUseCriteriaFilter(false);
    $categories = categoryPeer::doSelect($c, $con);
    categoryPeer::setUseCriteriaFilter(true);

    while(count($categories))
    {
        foreach($categories as $category)
        {
            try {
                addCategory($category);
            }
            catch(Exception $e){
            }
        }

        $c->setOffset($c->getOffset() + count($categories));
        kMemoryManager::clearMemory();
        $categories = categoryPeer::doSelect($c, $con);
    }
}

function populateCategoryEntry($partnerId)
{
    $c = new Criteria();
    $c->add(categoryEntryPeer::PARTNER_ID,$partnerId, Criteria::EQUAL);
    $c->addAscendingOrderByColumn(categoryEntryPeer::UPDATED_AT);
    $c->addAscendingOrderByColumn(categoryEntryPeer::ID);
    $c->setLimit(10000);

    $con = myDbHelper::getConnection(myDbHelper::DB_HELPER_CONN_PROPEL2);

    categoryEntryPeer::setUseCriteriaFilter(false);
    $categoryEntries = categoryEntryPeer::doSelect($c, $con);
    categoryEntryPeer::setUseCriteriaFilter(true);

    while(count($categoryEntries))
    {
        foreach($categoryEntries as $categoryEntry)
        {
            try {
                addEntryToCategory($categoryEntry);
            }
            catch(Exception $e){
            }
        }

        $c->setOffset($c->getOffset() + count($categoryEntries));
        kMemoryManager::clearMemory();
        $categoryEntries = categoryEntryPeer::doSelect($c, $con);
    }
}

function populateCategoryUser($partnerId)
{
    $c = new Criteria();
    $c->add(categoryKuserPeer::PARTNER_ID,$partnerId, Criteria::EQUAL);
    $c->addAscendingOrderByColumn(categoryKuserPeer::UPDATED_AT);
    $c->addAscendingOrderByColumn(categoryKuserPeer::ID);
    $c->setLimit(10000);

    $con = myDbHelper::getConnection(myDbHelper::DB_HELPER_CONN_PROPEL2);

    categoryKuserPeer::setUseCriteriaFilter(false);
    $categoryKusers = categoryKuserPeer::doSelect($c, $con);
    categoryKuserPeer::setUseCriteriaFilter(true);

    while(count($categoryKusers))
    {
        foreach($categoryKusers as $categoryKuser)
        {
            try {
                addUserToCategory($categoryKuser);
            }
            catch(Exception $e){
            }
        }

        $c->setOffset($c->getOffset() + count($categoryKusers));
        kMemoryManager::clearMemory();
        $categoryKusers = categoryKuserPeer::doSelect($c, $con);
    }
}

function populateKuserKgroup($partnerId)
{
    $c = new Criteria();
    $c->add(KuserKgroupPeer::PARTNER_ID,$partnerId, Criteria::EQUAL);
    $c->addAscendingOrderByColumn(KuserKgroupPeer::UPDATED_AT);
    $c->addAscendingOrderByColumn(KuserKgroupPeer::ID);
    $c->setLimit(10000);

    $con = myDbHelper::getConnection(myDbHelper::DB_HELPER_CONN_PROPEL2);

    KuserKgroupPeer::setUseCriteriaFilter(false);
    $kuserkgroups = KuserKgroupPeer::doSelect($c, $con);
    KuserKgroupPeer::setUseCriteriaFilter(true);

    while(count($kuserkgroups))
    {
        foreach($kuserkgroups as $kuserkgroup)
        {
            try {
                addUpadteKuserKgroup($kuserkgroup);
            }
            catch(Exception $e){
            }
        }

        $c->setOffset($c->getOffset() + count($kuserkgroups));
        kMemoryManager::clearMemory();
        $kuserkgroups = KuserKgroupPeer::doSelect($c, $con);
    }
}

function populateEntry($partnerId)
{
    $c = new Criteria();
    $c->add(entryPeer::PARTNER_ID,$partnerId, Criteria::EQUAL);
    $c->addAscendingOrderByColumn(entryPeer::UPDATED_AT);
    $c->addAscendingOrderByColumn(entryPeer::ID);
    $c->setLimit(10000);

    $con = myDbHelper::getConnection(myDbHelper::DB_HELPER_CONN_PROPEL2);

    entryPeer::setUseCriteriaFilter(false);
    $entries = entryPeer::doSelect($c, $con);
    entryPeer::setUseCriteriaFilter(true);

    while(count($entries))
    {
        foreach($entries as $entry)
        {
            try {
                addEntry($entry);
                if(rand(0,1)==1)
                    addCaption($entry->getId());
            }
            catch(Exception $e){
            }
        }

        $c->setOffset($c->getOffset() + count($entries));
        kMemoryManager::clearMemory();
        $entries = entryPeer::doSelect($c, $con);
    }
}

function getParamsForIndex($indexName,$numberOdShards, $numOfReplicas)
{
    if($indexName == 'kaltura_category_nadav')
        return getCategoryIndexParams($numberOdShards, $numOfReplicas);
    if($indexName == 'kaltura_kuser_kgroup_nadav')
        return getKuserKgroupIndexParams($numberOdShards, $numOfReplicas);
    if($indexName == 'kaltura_entry_nadav')
        return getEntryIndexParams($numberOdShards, $numOfReplicas);
}

function createIndices($elasticClient, $numberOdShards, $numOfReplicas)
{
    $indices = array();
    $indices[] = 'kaltura_category_nadav';
    $indices[] = 'kaltura_kuser_kgroup_nadav';
    $indices[] = 'kaltura_entry_nadav';

    foreach ($indices as $index)
    {
        try{
            $params = ['index' => $index];
            $response = $elasticClient->indices()->delete($params);
            echo "deleted indexed [".$index."]\n";
            print_r($response);
            echo "\n";
        }
        catch (Exception $e)
        {
            echo "could not delete index [".$index."]\n";
        }

        $params = getParamsForIndex($index, $numberOdShards, $numOfReplicas);
        $response = $elasticClient->indices()->create($params);
        echo "created indexed [".$index."]\n";
        print_r($response);
        echo "\n";
    }
}

if($argc != 4) {
    echo "Arguments missing.\n\n";
    echo "Usage: php populateEntitlementWithCaptions.php {partnerId} {elasticHostUrl:port} {createIndex}\n";
    exit;
}

$partnerId = $argv[1];
$elasticHostUrl = $argv[2];
$createIndex = $argv[3];

$hosts = [$elasticHostUrl];
$elasticClient = Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();
$numberOdShards = 3;
$numOfReplicas = 2;
if($createIndex)
    createIndices($elasticClient, $numberOdShards, $numOfReplicas);
populateEntry($partnerId);
populateCategory($partnerId);
populateKuserKgroup($partnerId);
populateCategoryEntry($partnerId);
populateCategoryUser($partnerId);

