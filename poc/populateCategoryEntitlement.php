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
            'entry' => $categoryEntry->getEntryId()
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


function createIndex($elasticClient, $numberOdShards, $numOfReplicas)
{
    try{
        $params = ['index' => 'kaltura_category_entry_user_nadav'];
        $response = $elasticClient->indices()->delete($params);
        echo "deleted indexed [kaltura_category_entry_user_nadav]\n";
        print_r($response);
        echo "\n";
    }
    catch (Exception $e)
    {
        echo "could not delete index [kaltura_category_entry_user_nadav]\n";
    }

    $params = getCategoryEntryUserIndexParams($numberOdShards, $numOfReplicas);
    $response = $elasticClient->indices()->create($params);
    echo "created indexed [kaltura_category_entry_user_nadav]\n";
    print_r($response);
    echo "\n";
}

if($argc != 4) {
    echo "Arguments missing.\n\n";
    echo "Usage: php populateCategoryEntitlement.php {partnerId} {elasticHostUrl:port} {createIndex}\n";
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
    createIndex($elasticClient, $numberOdShards, $numOfReplicas);
populateCategory($partnerId);
populateCategoryEntry($partnerId);
populateCategoryUser($partnerId);

