<?php

namespace Compass\Federation;

require_once __DIR__ . "/../../../../../../../start.php";

use SimpleSAML\Metadata\SAMLParser;
use SimpleSAML\Utils;
use Symfony\Component\VarExporter\VarExporter;

// получаем содержимое
$xml_data = trim(file_get_contents("../../../../../../../dev/php/metadata.xml"));

// парсим XML файл
try {
	$entities = SAMLParser::parseDescriptorsString($xml_data);
} catch (\Exception $e) {

	console(redText("Ошибка:"));
	console($e->getMessage());
	exit(1);
}

// если не спарсили никакие сущности
if (is_null($entities)) {

	console(redText("Ошибка:"));
	console("Не удалось спарсить XML файл. Возможно содержимое файла некорректно");
	exit(1);
}

// получаем все метаданные из сущностей
foreach ($entities as &$entity) {

	$entity = [
		"saml20-idp-remote" => $entity->getMetadata20IdP(),
	];
}

// собираем результат
$array_utils = new Utils\Arrays();
$temp        = $array_utils->transpose($entities);
$text        = "";
foreach ($temp as $entity_list) {

	foreach ($entity_list as $entity_metadata) {

		// если метаданных нет, то пропускаем
		if ($entity_metadata === null) {
			continue;
		}

		// убираем лишние, неиспользуемые данные
		unset($entity_metadata["entityDescriptor"]);
		unset($entity_metadata["expire"]);

		// собираем вывод
		$text .= sprintf("%s;\r\n", VarExporter::export($entity_metadata));
	}
}

console($text);