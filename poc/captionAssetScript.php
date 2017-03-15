<?php

require("/opt/kaltura/app/vendor/autoload.php");
require_once('/opt/kaltura/app/alpha/scripts/bootstrap.php');
require_once('./entitlementSearch.php');

//going over list of captions, ask for entitlement
class captionAssetElastic
{
	function __construct($partnerId,$userId,$host)
	{
		
		$this->elasticClient = Elasticsearch\ClientBuilder::create()->setHosts($host)->build();
		$this->partnerId = $partnerId;
		$this->userId = $userId;
		$this->entitlement = new entitlementSearch($partnerId,$userId,$host);
	}
	function search($content=null,$time=null)
	{
		$timeProperties = array(); 

		if(!is_null($time))
		{
			$timeProperties = array(); 
			$timeProperties[]=[
			  ["range" => ["start_time" =>  [ "lte" => $time ] ] ], 
		      ["range" => ["end_time" =>  [ "gte" => $time ]  ] ]
				              ];
		}
		else
		{
			$timeProperties=null;
		}

		if(is_null($content))
		{
				$content='*';
		}
		$contentProperties = array(); 
		$contentProperties[] =  ["query_string" => ["query" => "content:".$content ]];

		$params = [
		    'index' => 'kaltura_caption_asset_item_index_moshe',
		    'body' => [
	        	'query'=> [
	        	  	'bool' => [
	        	  		'filter' => [
								['term' => ["partner_id" => $this->partnerId]]
								],
	        			'must' => [

				        		$contentProperties,
				        		$timeProperties
				        		]
			    		]
					]
				]
			];
		$ret=$this->elasticClient->search($params);
		for ($i=0 ; $i < count ($ret['hits']['hits']) ; $i++) 
		{
			$source = $ret['hits']['hits'][$i]['_source'];
			print ("Found - Content:{$source['content']} start_time:{$source['start_time']} end_time:{$source['end_time']}");
			$entryId = $source['entry_id'];
			if($this->entitlement->search($entryId))
			{
				print_r($source);	
			}
		}
	}
}


if($argc < 3)
{
	print ("\nUsage {$argv[0]} partner_id kuser_id <content string> <time of the frame>\n");
	exit();
}

$partnerId = $argv[1];
$kuserId = $argv[2];
$content = null;
$time = null;
if(isset($argv[3]))
{
	$content = $argv[3];
	if(isset($argv[4]))	
	{
		$time = $argv[4];
	}
}

$caption = new captionAssetElastic($partnerId,$kuserId,["dev-backend27.dev.kaltura.com"]);
$caption->search($content,$time);
// input partner , userid , caption ID

