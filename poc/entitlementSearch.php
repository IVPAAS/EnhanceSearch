<?php

require("/opt/kaltura/app/vendor/autoload.php");
require_once('/opt/kaltura/app/alpha/scripts/bootstrap.php');

class entitlementSearch{
	function __construct($partnerId,$userId,$host)
	{
		
		$this->elasticClient = Elasticsearch\ClientBuilder::create()->setHosts($host)->build();
		$this->partnerId = $partnerId;
		$this->userId = $userId;
	}

	private function isEntryEntitled($entryId)
	{

		//Get the group that this user belogs to + Add this kuserID
		$groupList = $this->getGroupList();
		print ("\n UserGroup list - ");
		print_r($groupList);

		//check if the userID or the group is owner on the entry	
		if($this->isUserGroupEntryOwner($groupList,$entryId))
		{
			return true;
		}

		//Get categories that the user holds
		$categoryList = $this->getCategories($groupList);
//		print ("\n Category list - ");
//		print_r($categoryList);
		
		//2.2 add categories to the list base on privacy context
		//TODO

		//	check if these categories are attached to the entry
		//search entry by user ID and category
		if ($this->getEntry($entryId,$categoryList,$groupList))
			return true;
		return false;
	}

	private function isUserGroupEntryOwner($groupUserList,$entryId)
	{
		$userListParams = array();
		foreach($groupUserList as $user)
		{
			$userListParams []= ['term' => ['creator_kuser_id' => $user ] ];
			$userListParams []= ['term' => ['kuser_id' => $user ] ];
		}

		$params = [
		    'index' => 'kaltura_entry_index_moshe',
		    'body' => [
	        	'query'=> [
	        	  	'bool' => [
	        			'filter' => [
								['term' => ["partner_id" 	=> $this->partnerId]],
				        		['term' => ["entry_type_id" => $entryId 		]]
				        	],
				     	'should' => 
				     		$userListParams,
				       		"minimum_should_match" => 1
			        	]
			    	]
				]
			];

		$ret=$this->elasticClient->search($params);
//			print ("\n".__FUNCTION__."T: {$ret['took']} \n");
		if (count ($ret['hits']['hits']) > 0)
			return true;
		return false;
	}

	private function checkEntitlementBasedOnCategory($entryId,$categoryList,$groupList)
	{
		$categoryListParams = array();
		foreach($categoryList as $category)
		{
			$categoryListParams []= ['term' => ['category_id' => $category ] ];
		}
		if(!count($categoryList))
			return 0;

		$params = [
		    'index' => 'kaltura_category_entry_index_moshe',
		    'body' => [
	        	'query'=> [
	        	  	'bool' => [
	        			'filter' => [
								['term' => ["partner_id" 	=> $this->partnerId ]],
				        		['term' => ["entry_id" 		=> $entryId 		]]
				        		],
				       	'should' => 
				       		$categoryListParams,
				       		"minimum_should_match" => 1
			        ]
			    ]
			]
		];
		$ret=$this->elasticClient->search($params);
		$entryIds=array();
		for ($i=0 ; $i < count ($ret['hits']['hits']) ; $i++) 
		{
			$entryId = $ret['hits']['hits'][$i]['_source']['entry_id'];
			isset($entryIds[$entryId]) ? $entryIds[$entryId]++ : $entryIds[$entryId]=0;
		}
	//	print ("\n".__FUNCTION__."T: {$ret['took']} \n");
		//print_r($entryIds);
		return($entryIds);
	}


	private function getEntry($entryId,$categoryList,$groupList)
	{
		$entryList =  $this->checkEntitlementBasedOnCategory($entryId,$categoryList,$groupList);
		return $entryList;
	}
	private function getGroupList()
	{
		$params = [
		    'index' => 'kaltura_kuser_kgroup_index_moshe',
		    'body' => [
	        	'query'=> [
	        			'bool' =>  [
		        			'filter' => [
				        				['term' => [	"partner_id" => $this->partnerId ] ],
					        			['term' => [	"kuser_id" => $this->userId ]]
		        					]
		        				]
			        		]
			        	]
		        	];

		$ret=$this->elasticClient->search($params);
		$kgrousIds = array($this->userId);
		for ($i=0 ; $i < count ($ret['hits']['hits']) ; $i++) 
		{
			$kgrousIds[] = $ret['hits']['hits'][$i]['_source']['kgroup_id'];
		}
	//	print ("\n".__FUNCTION__."T: {$ret['took']} \n");
		return $kgrousIds;

	}
	private function getCategories($groupUserList)
	{
		$usersList = array();
		$usersList []= ['term' => [	"kuser_id" => $this->userId ]];

		foreach($groupUserList as $user)
		{
			$usersList []= ['term' => ['kuser_id' => $user ] ];
		}

		$params = [
		    'index' => 'kaltura_category_kuser_index_moshe',
		    'body' => [
	        	'query'=> [
   			        'bool' => [
		        		'filter' => [
								['term' => ["partner_id" => $this->partnerId]]
									],
						'should' => $usersList,
						 "minimum_should_match" => 1,
			           ]
			        ]
			    ]
			];

		$ret=$this->elasticClient->search($params);
		$categories = array();			
		for ($i=0 ; $i < count ($ret['hits']['hits']) ; $i++) 
		{
			$categories[] = $ret['hits']['hits'][$i]['_source']['category_id'];
		}

		//print ("\n".__FUNCTION__."T: {$ret['took']} \n");

		return $categories;
	}
	
	private function getKuserId()
	{
		$params = [
		    'index' => 'kaltura_kuser_index_moshe',
		    'body' => [
		        'query' => [
		            'match' => [
		                'kuser_type_id' => $this->userId
		            ]
		        ]
		    ]
		];
		$ret=$this->elasticClient->search($params);
		//print ("\n".__FUNCTION__."T: {$ret['took']} \n");
		return $ret;
	}


	public function search($entryId)
	{
		$entitled = $this->isEntryEntitled($entryId) ? "Yes" : "No";
		print("\n  ParterID {$this->partnerId} userId {$this->userId} is entitled to access entryId {$entryId}? [{$entitled}]! \n");
	}
}
