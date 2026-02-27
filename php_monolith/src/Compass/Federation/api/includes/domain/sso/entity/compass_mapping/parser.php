<?php

namespace Compass\Federation;

/**
 * класс описывающий парсер данных учетной записи SSO, которые маппятся в профиль Compass пользователя
 * @package Compass\Federation
 */
class Domain_Sso_Entity_CompassMapping_Parser
{
	/** @var string регулярное выражение для парсинга всех атрибутов из строки */
	protected const _PARSE_ATTRIBUTE_REGEX = "/\{(\w+)\}/";

	/** @var string регулярное выражение для парсинга всех выражений из строки. Вложенные выражения не поддерживаются */
	protected const _PARSE_ASSIGMENT_REGEX = "/\[\[([\s\S]+?(?=]]))\]\]/";

	/** @var string регулярное выражение для парсинга всех сторонних атрибутов из строки */
	protected const _PARSE_FOREIGN_ATTRIBUTE_REGEX = "/\{(\w+)=>(\w+)\}/";

	/**
	 * парсим поле
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function parseField(string $field_name, mixed $sso_entity_data, Domain_Sso_Entity_CompassMapping_ParserInterface $sso_protocol_field_parser): ?string
	{

		// получаем содержимое поля из конфига
		$field_content = Domain_Sso_Entity_CompassMapping_Config::getMappedFieldContent($field_name);
		$value         = self::parseFieldContent($field_content, $sso_entity_data, $sso_protocol_field_parser);

		if (is_null($value)) {
			return null;
		}

		// если это бейдж и значение пустое - возвращем null чтобы поставить дефолтный бейдж
		if ($field_name === Domain_Sso_Entity_CompassMapping_Config::MAPPED_FIELD_BADGE && mb_strlen($value) < 1) {
			return null;
		}

		return $value;
	}

	/**
	 * Парсим содержимое поля, заменяя на значения атрибутов и сущности AD
	 */
	public static function parseFieldContent(string $field_content, mixed $sso_entity_data, Domain_Sso_Entity_CompassMapping_ParserInterface $sso_protocol_field_parser): ?string
	{

		// если пустое значение, то возвращаем null
		if (mb_strlen($field_content) == 0) {
			return null;
		}

		$field_content = self::_parseForeignFields($field_content, $sso_entity_data, $sso_protocol_field_parser);

		// может оказаться так, что не нашли значение для замены, и ключ останется не тронутым
		// в таком случае стираем оставшиеся ключи, чтобы не оставались висеть в карточке
		$field_content = preg_replace(self::_PARSE_FOREIGN_ATTRIBUTE_REGEX, "", $field_content);

		return self::_parseFields($field_content, $sso_entity_data, $sso_protocol_field_parser);
	}

	protected static function _parseFields(string $field_content, mixed $sso_entity_data, Domain_Sso_Entity_CompassMapping_ParserInterface $sso_protocol_field_parser): string
	{

		// получаем все атрибуты, которые нужно спарсить из учетной записи SSO
		preg_match_all(self::_PARSE_ATTRIBUTE_REGEX, $field_content, $attribute_list);
		$attribute_list = $attribute_list[1];

		// если в содержимом поля нет атрибутов, то возвращаем содержимое в том виде, что оно есть
		if (count($attribute_list) == 0) {
			return $field_content;
		}

		$attribute_value_map = [];

		// пробегаемся по каждому атрибуту и парсим из данных учетной записи SSO
		foreach ($attribute_list as $attribute) {
			$attribute_value_map[$attribute] = $sso_protocol_field_parser::parseField($sso_entity_data, $attribute);
		}

		// заменяем атрибуты полученными значениями в содержимом поле
		return format($field_content, $attribute_value_map);
	}

	protected static function _parseForeignFields(string $field_content, mixed $sso_entity_data, Domain_Sso_Entity_CompassMapping_ParserInterface $sso_protocol_field_parser): string
	{

		[$search_entity_data_list, $field_content] = self::_parseAssignments($field_content, $sso_entity_data, $sso_protocol_field_parser);

		// если ничего не присваивали - тогда и заменять будет нечего
		if ($search_entity_data_list === []) {
			return trim($field_content);
		}

		preg_match_all(self::_PARSE_FOREIGN_ATTRIBUTE_REGEX, $field_content, $foreign_attribute_matches);

		$foreign_attribute_map = [];

		foreach ($foreign_attribute_matches[0] as $index => $assign_exp) {

			$foreign_attribute_map[$assign_exp] = [
				"key"       => $foreign_attribute_matches[1][$index],
				"attribute" => $foreign_attribute_matches[2][$index],
			];
		}

		// пробегаемся по каждому атрибуту и парсим из данных учетной записи SSO
		foreach ($foreign_attribute_map as $assign_exp => $data) {

			if (!isset($search_entity_data_list[$data["key"]])) {
				continue;
			}

			$replace       = $sso_protocol_field_parser::parseField($search_entity_data_list[$data["key"]], $data["attribute"]);
			$field_content = str_replace($assign_exp, $replace, $field_content);
		}

		return trim($field_content);
	}

	protected static function _parseAssignments(string $field_content, mixed $sso_entity_data, Domain_Sso_Entity_CompassMapping_ParserInterface $sso_protocol_field_parser): array
	{

		// получаем все выражения, которые нужно спарсить из учетной записи SSO
		preg_match_all(self::_PARSE_ASSIGMENT_REGEX, $field_content, $assignment_matches);

		$assignment_map = [];

		foreach ($assignment_matches[0] as $index => $assign_exp) {

			$assignment_map[$assign_exp] = $assignment_matches[1][$index];
		}

		$search_entity_data_list = [];

		// парсим все доступные выражения
		foreach ($assignment_map as $expression => $exp_content) {

			$search_entity_data_list = array_merge($search_entity_data_list, $sso_protocol_field_parser::parseAssignment($sso_entity_data, $exp_content));
			$field_content           = str_replace($expression, "", $field_content);
		}

		return [$search_entity_data_list, $field_content];
	}
}
