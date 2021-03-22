#!/usr/bin/env php
<?php
require __DIR__ . '/env.php';

function errorHandler($errno, $errstr, $errfile, $errline)
{
	if (error_reporting() == 0) {
		return;
	}
	throw new Exception("ERROR l.$errline ($errfile) : $errstr\n");
}
set_error_handler('errorHandler');

chdir(__DIR__);

$authHeaders = "-H \"X-Auth-Key: $cfKey\" -H \"X-Auth-Email: $cfEmail\"";

$cmd = "curl -s -X GET $authHeaders -H \"Content-Type: application/json\" \"https://api.cloudflare.com/client/v4/zones?per_page=50\"";
$raw = shell_exec($cmd);
$zones = json_decode($raw);

//$zones = json_decode(file_get_contents(__DIR__ . '/zones.json'));
//echo 'nb domaines : ' . count($zones->result);

foreach($zones->result as $zone) {
	// echo $zone->name . ' : ' . implode(' ', $zone->name_servers) . "\n";
	if($zone->status !== 'pending') continue; // only delete pending domains
	
	echo $zone->name . "\n";
	
	/* DANGER ZONE ! */
	$cmd = "curl -s -X DELETE $authHeaders -H \"Content-Type: application/json\" \"https://api.cloudflare.com/client/v4/zones/{$zone->id}\"";
	$raw = shell_exec($cmd);
	$res = json_decode($raw);
	if(count($res->errors) > 0) {
		echo " failed\n";
	} else {
		echo " deleted\n";
	}

	sleep(5);
}
