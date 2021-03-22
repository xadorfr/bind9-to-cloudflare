#!/usr/bin/env php
<?php
$domains = file('jobs/domains.txt');

foreach ($domains as $line) {
	$domain = trim($line);
	echo "$domain...";

	$cmd = "whois $domain|grep -i '^Name Server:'|awk '{print $3}'";
	$res = shell_exec($cmd);

	if(empty($res)) {
		$cmd = "whois $domain|grep -i '^nserver:'|awk '{print $2}'";
		$res = shell_exec($cmd);
	}

	echo str_replace("\n", ' ', $res) . "\n";
}
