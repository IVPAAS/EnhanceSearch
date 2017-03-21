<?php
require_once (__DIR__ .'/../../../vendor/autoload.php');

chdir(dirname(__FILE__));
require_once(__DIR__ . '/../../bootstrap.php');

function addCategory(category $category)
{
    global $elasticClient;
    $params = [
        'index' => 'kaltura_category_entry_user_nadav',
        'type' => 'category',
        'id' => $category->getId(),
        'body' => [
            'partner_id' => $category->getPartnerId(),
            'privacy' => $category->getPrivacy(),
            'privacy_context' => $category->getPrivacyContext(),
            'privacy_contexts' => $category->getPrivacyContexts(),
            'status' => $category->getStatus()
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
        'index' => 'kaltura_category_entry_user_nadav',
        'type' => 'category_entry',
        'parent' => $categoryEntry->getCategoryId(),
        'body' => [
            'entry_id' => $categoryEntry->getEntryId(),
            'privacy_context' => $categoryEntry->getPrivacyContext(),
            'status' => $categoryEntry->getStatus()
        ]
    ];
    $response = $elasticClient->index($params);
    echo "added entry[".$categoryEntry->getEntryId()."] to category[".$categoryEntry->getCategoryId()."]\n";
    print_r($response);
    echo "\n";
}

function addUserToCategory(categoryKuser  $categoryUser)
{
    global $elasticClient;
    $params = [
        'index' => 'kaltura_category_entry_user_nadav',
        'type' => 'category_user',
        'parent' => $categoryUser->getCategoryId(),
        'body' => [
            'user' => $categoryUser->getKuserId()
        ]
    ];
    $response = $elasticClient->index($params);
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
                'inline' => 'if (ctx._source.groupIds.contains(params.groupId)) { ctx.op = \"none\" } else { ctx._source.groupIds.Add(params.groupId) }',
                'lang' => 'painless',
                'params' => [
                    'groupId' => $kuserkgroup->getKgroupId()
                ]
            ],
            'upsert' => [
                "groupIds" => [$kuserkgroup->getKuserId(), $kuserkgroup->getKgroupId()] //added kuser to groupIds in order to do terms lookup
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
        'body' => [
            'entry_id' => $entry->getId(),
            'parent_id' => $entry->getParentEntryId(),
            'entry_status' => $entry->getStatus(),
            'entitled_kusers_edit' => $entry->getEntitledKusersEdit(),
            'entitled_kusers_publish' => $entry->getEntitledKusersPublish(),
            'kuser_id' => $entry->getKuserId(),
            'puser_id' => $entry->getPuserId(),
            'creator_kuser_id' => $entry->getCreatorKuserId()
        ]
    ];
    $response = $elasticClient->index($params);
    echo "added entry[".$entry->getEntryId()."]\n";
    print_r($response);
    echo "\n";
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
            }
            catch(Exception $e){
            }
        }

        $c->setOffset($c->getOffset() + count($entries));
        kMemoryManager::clearMemory();
        $entries = entryPeer::doSelect($c, $con);
    }
}

function getCategoryEntryUserIndexParams($numberOdShards, $numOfReplicas)
{
    $params = [
        'index' => 'kaltura_category_entry_user_nadav',
        'body' => [
            'settings' => [
                'number_of_shards' => $numberOdShards,
                'number_of_replicas' => $numOfReplicas
            ],
            'mappings' => [
                'category' => [
                    'properties' => [
                        'partner_id' => [
                            'type' => 'text'
                        ]//add status
                    ]
                ],
                'category_entry' => [
                    '_parent' => [
                        'type' => 'category'
                    ],
                    'properties' => [
                        'entry_id' => [
                            'type' => 'text'
                        ]
                    ]
                ],
                'category_user' => [
                    '_parent' => [
                        'type' => 'category'
                    ],
                    'properties' => [
                        'user_id' => [
                            'type' => 'text'
                        ]
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
                    'properties' => [
                        'groupIds' => [
                            'type' => 'text'
                        ]
                    ]
                ]
            ]
        ]
    ];
    return $params;
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
                        'entry_id' => [
                            'type' => 'text'
                        ],
                        'parent_id' => [
                            'type' => 'text'
                        ],
                        'entry_status' => [
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
                        'puser_id' => [
                            'type' => 'text'
                        ],
                        'creator_kuser_id' => [
                            'type' => 'text'
                        ]
                    ]
                ]
            ]
        ]
    ];
    return $params;
}

function getParamsForIndex($indexName,$numberOdShards, $numOfReplicas)
{
    if($indexName == 'kaltura_category_entry_user_nadav')
        return getCategoryEntryUserIndexParams($numberOdShards, $numOfReplicas);
    if($indexName == 'kaltura_kuser_kgroup_nadav')
        return getKuserKgroupIndexParams($numberOdShards, $numOfReplicas);
    if($indexName == 'kaltura_entry_nadav')
        return getEntryIndexParams($numberOdShards, $numOfReplicas);
}

function createIndices($elasticClient, $numberOdShards, $numOfReplicas)
{
    $indices = array();
    $indices[] = 'kaltura_category_entry_user_nadav';
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
            echo "cound not delete index [".$index."]\n";
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
    echo "Usage: php populateObjectsForEntitlement.php {partnerId} {elasticHostUrl:port} {createIndex}\n";
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
populateCategory($partnerId);
populateCategoryEntry($partnerId);
populateCategoryUser($partnerId);
populateKuserKgroup($partnerId);
populateEntry($partnerId);
