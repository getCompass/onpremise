<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

// скрипт для конвертации tsv файла с данными автономных система  в массив php
// актуальные данные для tsv файла можно получить здесь https://iptoasn.com/data/ip2asn-v4.tsv.gz

/**
 * Парсит одну запись в массив для вставки.
 */
function parse_row(string $row):array|false {

	$raw_data = explode("\t", $row, 5);

	if (count($raw_data) !== 5) {
		return false;
	}

	return [
		"ip_range_start" => ip2long($raw_data[0]),
		"ip_range_end"   => ip2long($raw_data[1]),
		"code"           => $raw_data[2],
		"country_code"   => $raw_data[3],
		"name"           => $raw_data[4],
	];
}

$file_to_read = fopen("ip2asn.tsv", "rb");
if ($file_to_read === false) {
	die("file ip2asn.tsv not found for reading");
}

$iteration         = 0;
$line_insert_count = 0;

do {

	$wrote_line_count = 0;
	$to_write         = [];

	$iteration++;

	while (!feof($file_to_read) && $wrote_line_count < 1000) {

		$line   = fgets($file_to_read);
		$insert = parse_row(trim($line));

		if ($insert === false) {

			console("line $line has incorrect format\n");
			continue;
		}

		$to_write[] = $insert;
		$wrote_line_count++;
		$line_insert_count++;
	}

	console("inserting chunk number $iteration...");
	ShardingGateway::database("pivot_system")->insertArray("autonomous_system", $to_write);
} while (!feof($file_to_read));

fclose($file_to_read);
console("done, $line_insert_count lines read");
