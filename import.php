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

$domains = file('jobs/domains.txt');
try {
	foreach ($domains as $line) {
		$domain = trim($line);
		echo "import $domain...";

		$authHeaders = "-H \"X-Auth-Key: $cfKey\" -H \"X-Auth-Email: $cfEmail\"";

		$cmd = "curl -s -X POST $authHeaders -H \"Content-Type: application/json\" \"https://api.cloudflare.com/client/v4/zones\"";
	 	$cmd .= " --data '{\"account\": {\"id\": \"$cfAccountId\"}, \"name\":\"$domain\",\"jump_start\":false}'";

	 	$resp = json_decode(shell_exec($cmd));

	 	if(count($resp->errors) > 0) {
	 		echo "errors : \n";
	 		foreach($resp->errors as $err) {
	 			echo $err->message . "\n";
	 		}
	 		continue;
	 	}

	 	sleep(2); // rate-limit CF

	 	$idZone = $resp->result->id;
	 	$zoneFile = "$jobs/$domain.hosts";

	 	while(true) {
		 	$cmd = "curl -s -X POST $authHeaders \"https://api.cloudflare.com/client/v4/zones/$idZone/dns_records/import\"";
		 	$cmd .= " --form 'file=@$zoneFile' --form 'proxied=false'";

		 	$raw = shell_exec($cmd);
		 	$resp = json_decode($raw);

		 	if($resp == false) { // usually 429 => rate-limit CF
		 		echo $raw . "\n";
		 		sleep(5);
		 		continue;
		 	}
		 	break;
	 	}

	  	if(count($resp->errors) > 0) {
	 		echo "errors : \n";
	 		foreach($resp->errors as $err) {
	 			echo $err->message . "\n";
	 		}
	 		continue;
	 	}

	 	echo "ok ! ({$resp->result->total_records_parsed} records)\n";

	 	sleep(2); // rate-limit CF
	}
} catch (Exception $e) {
	echo (string) $e;
	exit(1);
}
