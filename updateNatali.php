#!/usr/bin/php
<?php

print 'Start update' . PHP_EOL;

function update($URL, $data)
{
	$result = getDataByUrl($URL, $data);
	$result = json_decode($result, true);
	print 'Elements need to update: ' . $result['items'] . PHP_EOL;

	if ($result['items'] > 0)
	{
		update($URL, $data);
	}
	else
	{
		print 'Nothing to update ' . PHP_EOL;
	}
}

function sync($URL, $data)
{
	$result = getDataByUrl($URL, $data);
	$result = json_decode($result, true);

	if ($result['success'])
	{
		print 'Sync done ' . PHP_EOL;
	}
}

function getDataByUrl($url, $data = [])
{
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	if (($json = curl_exec($ch)) === false)
	{
		echo 'Curl error: ' . curl_error($ch);
	}

	curl_close($ch);

	return $json;
}

$ajaxUrl = $argv[1] . '/wp-admin/admin-ajax.php';

sync($ajaxUrl, [
	'action' => 'cadesign_natali_import',
	'ajax' => 'Y',
	'data' => [
		'startSync' => 'syncElements'
	]
]);
update($ajaxUrl, [
	'action' => 'cadesign_natali_import',
	'ajax' => 'Y',
	'data' => [
		'updateNext' => 'updateNext'
	]
]);