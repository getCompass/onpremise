<?php

namespace Compass\Federation;

/**
 * класс описывающий парсер данных учетной записи SSO, которые маппятся в профиль Compass пользователя
 * @package Compass\Federation
 */
class Domain_Sso_Entity_CompassMapping_Parser {

	/** @var string регулярное выражение для парсинга всех атрибутов из строки */
	protected const _PARSE_ATTRIBUTE_REGEX = "/\{(\w+)\}/";

	/**
	 * парсим содержимое поля, заменяя названия атрибутов учетной записи – полученными значениями
	 *
	 * @return string
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function parseField(string $field_name, mixed $sso_entity_data, Domain_Sso_Entity_CompassMapping_ParserInterface $sso_protocol_field_parser):?string {

		// получаем содержимое поля из конфига
		$field_content = Domain_Sso_Entity_CompassMapping_Config::getMappedFieldContent($field_name);

		// если пустое значение, то возвращаем null
		if (mb_strlen($field_content) == 0) {
			return null;
		}

		// получаем все атрибуты, которые нужно спарсить из учетной записи SSO
		preg_match_all(self::_PARSE_ATTRIBUTE_REGEX, $field_content, $attribute_list);
		$attribute_list = $attribute_list[1];

		// если в содержимом поля нет атрибутов, то возвращаем содержимое в том виде, что оно есть
		if (count($attribute_list) == 0) {
			return $field_content;
		}

		// пробегаемся по каждому атрибуту и парсим из данных учетной записи SSO
		$attribute_value_map = [];
		foreach ($attribute_list as $attribute) {
			$attribute_value_map[$attribute] = $sso_protocol_field_parser::parseField($sso_entity_data, $attribute);
		}

		// заменяем атрибуты полученными значениями в содержимом поле
		$value = format($field_content, $attribute_value_map);

		// если это бейдж и значение пустое - возвращем null чтобы поставить дефолтный бейдж
		if ($field_name === Domain_Sso_Entity_CompassMapping_Config::MAPPED_FIELD_BADGE && mb_strlen($value) < 1) {
			return null;
		}

		return $value;
	}
}