<?php

namespace Compass\Conversation;

/**
 * интерфейс описывающий поведение парсера конкретного ресурса
 */
interface Type_Preview_Parser_Interface {

	/**
	 * функция для определения – уместна ли переданная ссылка для парсинга конкретным парсером
	 *
	 * @return bool
	 */
	public static function isRelevantUrl(string $url):bool;

	/**
	 * функция для создания превью из mime type text/html
	 *
	 * @return array
	 */
	public static function makeDataFromHtml(string $user_id, string $url, string $short_url, string $html):array;

	/**
	 * функция для создания превью из mime type application/json
	 *
	 * @return array
	 */
	public static function makeDataFromJson(string $user_id, string $url, string $short_url, array $content):array;

	/**
	 * функция для подготовки ссылки перед непосредственным парсингом (перед curl запросом)
	 *
	 * @return string
	 */
	public static function prepareUrl(string $url):string;
}