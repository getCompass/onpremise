<?php

namespace Compass\Conversation;

/**
 * @package Compass\Conversation
 */
class Type_Preview_Parser {

	/** @var Type_Preview_Parser_Interface[] персональные парсеры для конкретных ресурсов */
	protected const _PERSONAL_PARSER_CLASS_LIST = [
		Type_Preview_Parser_Instagram::class,
		Type_Preview_Parser_Youtube::class,
		Type_Preview_Parser_Facebook::class,
		Type_Preview_Parser_Mail::class,
		Type_Preview_Parser_Habrahabr::class,
		Type_Preview_Parser_Ok::class,
		Type_Preview_Parser_Compass::class,
	];

	/** @var Type_Preview_Parser_Interface парсерс используемый по-умолчанию для остальных сайтов */
	protected const _DEFAULT_PARSER_CLASS = Type_Preview_Parser_Default::class;

	/**
	 * определяем по ссылке класс, который будет использоваться для парсинга ссылок
	 *
	 * @return Type_Preview_Parser_Interface
	 * @long
	 */
	public static function resolveClass(string $url):Type_Preview_Parser_Interface {

		// проходимся по всем персональным парсерам для конкретных ресурсов
		foreach (self::_PERSONAL_PARSER_CLASS_LIST as $parser) {

			// если парсер работает с такой ссылкой, то возвращаем объект этого класса
			if ($parser::isRelevantUrl($url)) {
				return new $parser();
			}
		}

		// если не нашли парсер, то возвращаем тот что используется по умолчанию
		$default_parser_class = self::_DEFAULT_PARSER_CLASS;
		return new $default_parser_class();
	}
}