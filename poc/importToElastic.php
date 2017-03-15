<?php
require("../vendor/autoload.php");
require_once('/opt/kaltura/app/alpha/scripts/bootstrap.php');
ini_set('memory_limit', '1000M');

$genericFieldMapping=
[
    "id" => [
         'type' => 'text',
        //'index' => 'not_analyzed'
        'analyzer' => 'kalturaIdAnalyzer'
    ],
    "name" => [
         'type' => 'text',
        'index' => 'analyzed'
    ],
    "tags" => [
         'type' => 'text',
        'index' => 'analyzed'                
    ],
    "categories" => [
         'type' => 'text',
        'index' => 'analyzed'                
    ],
    "flavor_params_ids" => [
         'type' => 'text',
        'index' => 'not_analyzed'                
    ],
    "source_link" => [
         'type' => 'text',
        'index' => 'not_analyzed'                
    ],
    "kshow_id" => [
         'type' => 'text',
        'index' => 'not_analyzed'
    ],
    "group_id" => [
         'type' => 'text',
        'index' => 'not_analyzed'
    ],
    "description" => [
         'type' => 'text',
        'index' => 'analyzed'
    ],
    "admin_tags" => [
         'type' => 'text',
        'index' => 'analyzed'
    ],
    "plugins_data" => [//array of key:values
         'type' => 'text',
         'index' => 'not_analyzed'
    ],
    "duration_type" => [
        "type" => "number"
    ],
    "reference_id" => [
         'type' => 'text',
        //'index' => 'analyzed'
        'analyzer' => 'kalturaIdAnalyzer'
    ],
    "replacing_entry_id" => [
         'type' => 'text',
        //'index' => 'not_analyzed'
        'analyzer' => 'kalturaIdAnalyzer'
    ],
    "replaced_entry_id" => [
         'type' => 'text',
        //'index' => 'not_analyzed'
        'analyzer' => 'kalturaIdAnalyzer'
    ],
    "roots" => [
         'type' => 'text',
        'index' => 'not_analyzed'
    ],
    "kuser_id" => [
        "type" => "number",
    ],
    "puser_id" => [
         'type' => 'text',
        'analyzer' => 'userNameAnalyzer'
    ],
    "creator_kuser_id" => [
         'type' => 'text',
        'index' => 'not_analyzed',
        'analyzer' => 'userNameAnalyzer'
    ],
    "creator_puser_id" => [
        "type" => "number"
    ],
    "entitled_kusers_publish" => [
         'type' => 'text',
         'index' => 'not_analyzed',
        'analyzer' => 'userNameAnalyzer'
    ],
    "entitled_kusers_edit" => [
         'type' => 'text',
        'index' => 'not_analyzed',
        'analyzer' => 'userNameAnalyzer'             
    ],
    "privacy_by_contexts" => [
         'type' => 'text',
        'index' => 'not_analyzed'
    ],
    "user_names" => [
         'type' => 'text',
        'index' => 'not_analyzed' ,
        'analyzer' => 'userNameAnalyzer'
    ],
    "int_entry_id" => [
        "type" => "number",
    ],
    "type" => [
        "type" => "number",
    ],
    "media_type" => [
        "type" => "number",
    ],
    "views" => [
        "type" => "number",
    ],
    "moderation_status" => [
        "type" => "number",
    ],
    "length_in_msecs" => [
        "type" => "number",
    ],
    "access_control_id" => [
        "type" => "number",
    ],
    "moderation_count" => [
        "type" => "number",
    ],
    "rank" => [
        "type" => "number",
    ],
    "total_rank" => [
        "type" => "number",
    ],
    "plays" => [
        "type" => "number",
    ],
    "replacement_status" => [
        "type" => "number",
    ],
    "source" => [
        "type" => "boolean",
    ],
    "entry_status" => [
        "type" => "number",
    ],
    "partner_id" => [
        "type" => "number",
    ],
    "display_in_search" => [
        "type" => "number",
    ],
    "partner_sort_value" => [
        "type" => "number",
    ],
    "created_at" => [
        "type" => "date",
    ],
    "updated_at" => [
        "type" => "date",
    ],
    "modified_at" => [
        "type" => "date",
    ],
    "media_date" => [
        "type" => "date",
    ],
    "start_date" => [
        "type" => "date",
    ],
    "end_date" => [
        "type" => "date",
    ],
    "available_from" => [
        "type" => "date",
    ],
    "last_played_at" => [
        "type" => "date",
    ],
    "dynamic_attributes" => [//array of keys values
         'type' => 'text',
        'index' => 'not_analyzed'
    ],
    'custom_data' =>  [
        'type' => 'text'
        //'index' => 'not_analyzed'
    ],
    "category_full_ids" => [
         'type' => 'text',
        'index' => 'kalturaIdAnalyzer'                
    ],
    "privacy_context" => [
         'type' => 'text',
        'index' => 'not_analyzed'                
    ],
    "creator_kuser_id" => [
        "type" => "number"
    ],
    'instance_count' => [
        "type" => "number"
    ],
    "caption_asset_id" => [
         'type' => 'text',
        'index' => 'kalturaIdAnalyzer',
        "fields" => [
            "content" => [
                'type' => [ ['text'] , ['nested'] ]
            ],
            'start_time' => [
                'type' => [['number'] ,  ['nested']]
            ],
            'end_time' => [
                'type' => [['number'] ,  ['nested']]
            ]
        ]
    ],
   'kgroup_id' => [
        "type" => "number"
    ],
    'pgroup_id' => [
         'type' => 'text',
        'index' => 'kalturaIdAnalyzer',               
    ],
    'pgroup_id' => [
         'type' => 'text',
        'index' => 'kalturaIdAnalyzer',               
    ],


];

//create entry index + mapping 

$analyzer = [
                'analyzer' => [
                    "userNameAnalyzer" => [
                        "type" => "simple"
                    ],
                    "kalturaIdAnalyzer" => [
                       "type" => "custom",
                       "tokenizer"=> "standard",
                        "filter" => "lowercase"
                    ]
                ]
            ];

$settings = [ 
            'number_of_shards' => 5,
            'number_of_replicas' => 1,
            'analysis' => $analyzer
                ];

 $mappingParamsEntry = [
    'index' => 'kaltura_entry_index_moshe',
      'body' => [
        'settings' => $settings,
        "mapping" => [
            'entry_type' => [
                "properties" => $genericFieldMapping,
                "properties" => [
                    'entry_id' => [
                        'type' => 'text',
                        'index' => 'kalturaIdAnalyzer'
                        ]
                    ]
                ]
            ]
        ]
    ];    


$mappingParamsCategoryEntry = [
    'index' => 'kaltura_category_entry_index_moshe',
      'body' => [
        'settings' => $settings,
        "mapping" => [
            'category_entry_type' => [
                "properties" => $genericFieldMapping,
                "properties" => [
                   'category_entry_type_id' => [
                        'type' => 'number'
                        ]
                    ]
                ]
            ]
        ]    
    ];

$mappingParamsKuser = [
    'index' => 'kaltura_kuser_index_moshe',
      'body' => [
        'settings' => $settings,
        "mapping" => [
            'kuser_type' => [
                "properties" => [
                    "properties" => $genericFieldMapping,
                    "properties" => [
                   'kuser_type_id' => [
                        'type' => 'number'
                        ]
                    ]

                ]
            ]
        ]
    ]    
];

$mappingParamsCategory = [
    'index' => 'kaltura_category_index_moshe',
      'body' => [
        'settings' => $settings,
        "mapping" => [
            'category_type' => [
                "properties" => $genericFieldMapping,
                "properties" => [
                   'category_type_id' => [
                        'type' => 'text',
                        'index' => 'kalturaIdAnalyzer'
                        ]
                    ]
                ]
            ]
        ]
    ];


$mappingParamsCategoryKUser = [
    'index' => 'kaltura_category_kuser_index_moshe',
      'body' => [
        'settings' => $settings,
        "mapping" => [
            'category_kuser_type' => [
               "properties" => $genericFieldMapping,
                "properties" => [
                   'category_kuser_type_id' => [
                        'type' => 'number'
                        ]
                    ]
                ]
            ]
        ]
    ];

$mappingParamsTags = [
    'index' => 'kaltura_tag_index_moshe',
      'body' => [
        'settings' => $settings,
        "mapping" => [
            'tag_type' => [
               "properties" => $genericFieldMapping,
                "properties" => [
                        'tag_type_id' => [
                            'type' => 'number'
                            ]
                        ]
                    ]
                ]
            ]
        ];

$mappingParamsCaptionAssetItem = [
    'index' => 'kaltura_caption_asset_item_index_moshe',
      'body' => [
        'settings' => $settings,
        "mapping" => [
               "properties" => $genericFieldMapping,
                "properties" => [
                        'caption_asset_item_type_id' => [
                               'type' => 'text',
                                'index' => 'kalturaIdAnalyzer'
                            ]
                        ]
                    ]
                ]
            ];

$mappingParamsKuserKgropItem = [
    'index' => 'kaltura_kuser_kgroup_index_moshe',
      'body' => [
        'settings' => $settings,
        "mapping" => [
            'kuser_kgroup_type' => [
                "properties" => $genericFieldMapping,
                "properties" => [
                        'kuser_kgroup_type_id' => [
                               'type' => 'number',
                            ]
                        ]
                    ]
            ]
        ]
    ];

class objectConvertorHelper
{
    static function convertTypers(&$key,&$value,&$out)
    {
        
        switch(gettype($value))
        {
            case "integer":
            case "string":
            case "boolean":
            case "double":
                $out[$key]=$value;
            break;
        }
    }
}


class myEntryGet extends entry {
    public static function getMe(&$obj,&$out)
    {
        foreach ($obj as $key => $value) objectConvertorHelper::convertTypers($key,$value,$out);
    }
}
class myKUserGet extends kuser {
    public static function getMe(&$obj,&$out)
    {
       foreach ($obj as $key => $value) objectConvertorHelper::convertTypers($key,$value,$out);
    }
}



class myCategoryEntryGet extends categoryEntry {
    public static function getMe(&$obj,&$out)
    {
       foreach ($obj as $key => $value) objectConvertorHelper::convertTypers($key,$value,$out);
    }
}

class myCategoryGet extends category{
    public static function getMe(&$obj,&$out)
    {
       foreach ($obj as $key => $value) objectConvertorHelper::convertTypers($key,$value,$out);
    }
}

class myCategoryKUserGet extends categoryKuser{
    public static function getMe(&$obj,&$out)
    {
        foreach ($obj as $key => $value) objectConvertorHelper::convertTypers($key,$value,$out); 
    }
}

class myTagGet extends Tag{
    public static function getMe(&$obj,&$out)
    {
        foreach ($obj as $key => $value) objectConvertorHelper::convertTypers($key,$value,$out);        
    }    
}



class myCaptionAssetItem extends CaptionAssetItem{
    public static function getMe(&$obj,&$out)
    {
        foreach ($obj as $key => &$value)
        objectConvertorHelper::convertTypers($key,$value,$out);        
    }
}

class myKuserKgroup extends KuserKgroup{
    public static function getMe(&$obj,&$out)
    {
        foreach ($obj as $key => $value)
         objectConvertorHelper::convertTypers($key,$value,$out);        
    }
}



class tableConverter  {
   function __construct ($mapping , $hosts ,$indexName,$typeName,$sourcePeerName,$wrapperClass)
    {
        
        $this->elasticClient = Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();
        $this->mapping=$mapping;
        $this->createIndex();
        $this->typeName=$typeName;
        $this->indexName=$indexName;
        $this->sourcePeerName=$sourcePeerName;
        $this->wrapperClass=$wrapperClass;
        $this->hosts=$hosts;
        print("\n ".$this->typeName." ".$this->indexName." ".$this->sourcePeerName."\n ");
    }
    
    function execute( $numOfItems,$offsetStart=0)
    {
        $c = new Criteria();
       
        print ("\n {$this->indexName}:");
        for ($recordCount=$offsetStart;$recordCount<400000;$recordCount++)
        {
            $offset = $numOfItems*$recordCount;
            $c->setLimit($numOfItems);
            $c->setOffset($offset);
         //   kCurrentContext::$partner_id=1722461;//kuser_kgroup patch
            $c->add($this->sourcePeerName::PARTNER_ID,1722461,$c::EQUAL);
            print("\nAt index {$offset}\n");
            $items = $this->sourcePeerName::doSelect($c);
            $this->sourcePeerName::clearInstancePool();
            kMemoryManager::clearMemory();
            $found = count($items);
            if(!$found) break;
            print ("\n found {$found} in DB\n");
            for($itemCount=0;$itemCount<$found;$itemCount++)
            {
                $this->wrapperClass::getMe($items[$itemCount],$doc);
                $doc[$this->typeName.'_id']=$doc['id'];
                if(isset($doc['custom_data']))
                    $doc['custom_data'] = unserialize($doc['custom_data']);
                $params = 
                [
                    'index' => $this->indexName,
                    'type' => $this->typeName,
                    'body' => $doc
                ];

                try
                {
                    $this->elasticClient->index($params);
                }
                catch(Exception $e)
                {
                    print_r($e->getMessage());
                }

                unset($doc);
                unset($params);
                unset($items[$itemCount]);
            }
            unset($items);
            print ("\nMax allocated memory so far - ". memory_get_peak_usage(true));
            
            if($found < $numOfItems)
                break;
        }
        print ("\n {$this->indexName}:Done!");
    }

    //create category index + mapping
    function createIndex()
    {
        try
        {
            $response = $this->elasticClient->indices()->create($this->mapping);
        }
        catch(Exception $e)
        {
              //  print_r($e->getMessage());
        }

    }
}

$numOfItems=1000;
$hosts = ["dev-backend27.dev.kaltura.com"];
$m = new tableConverter($mappingParamsEntry,$hosts,'kaltura_entry_index_moshe','entry_type','entryPeer','myEntryGet');
$m->execute($numOfItems);
$m = new tableConverter($mappingParamsCategoryEntry,$hosts,'kaltura_category_entry_index_moshe','category_entry_type','categoryEntryPeer','myCategoryEntryGet');
$m->execute($numOfItems);
$m = new tableConverter($mappingParamsCategory,$hosts,'kaltura_category_index_moshe','category_type','categoryPeer','myCategoryGet');
$m->execute($numOfItems);
$m = new tableConverter($mappingParamsKuser,$hosts,'kaltura_kuser_index_moshe','kuser_type','kuserPeer','myKUserGet');
$m->execute($numOfItems);
$m = new tableConverter($mappingParamsCategoryKUser,$hosts,'kaltura_category_kuser_index_moshe','category_kuser_type','categoryKuserPeer','myCategoryKUserGet');
$m->execute($numOfItems);
$m = new tableConverter($mappingParamsTags,$hosts,'kaltura_tag_index_moshe','tag_type','TagPeer','myTagGet');
$m->execute($numOfItems);
$m = new tableConverter($mappingParamsTags,$hosts,'kaltura_caption_asset_item_index_moshe','caption_asset_item_type','CaptionAssetItemPeer','myCaptionAssetItem');
$m->execute($numOfItems);
$m = new tableConverter($mappingParamsTags,$hosts,'kaltura_kuser_kgroup_index_moshe','kuser_kgroup_type','KuserKgroupPeer','myKuserKgroup');
$m->execute($numOfItems);

