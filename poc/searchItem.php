<?php

if($argc!=4) 
{
	print ("\nUsage {$argv[0]} partner_id kuser_id entry_id\n");
	exit();
}

require_once("/opt/kaltura/app/vendor/autoload.php");
require_once('/opt/kaltura/app/alpha/scripts/bootstrap.php');
require_once('./entitlementSearch.php');

$partnerId = $argv[1];
$kuserId = $argv[2];
$entryId = $argv[3];
$search = new entitlementSearch($partnerId,$kuserId,["dev-backend27.dev.kaltura.com"]);
$search->search($entryId);
