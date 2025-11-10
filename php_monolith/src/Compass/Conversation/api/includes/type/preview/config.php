<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для работы с конфиг-файлом превью api/conf/preview.php
 * @package Compass\Conversation
 */
class Type_Preview_Config
{
	/** Ключ для получения конфига с основными параметрами парсинга превью */
	protected const _KEY_PREVIEW = "PREVIEW";

	/**
	 * Получаем флаг надо ли парсить превью
	 *
	 * @throws ParseFatalException
	 */
	public static function isPreviewEnabled(): bool
	{

		return (bool) self::_getConfig(self::_KEY_PREVIEW)["url_parsing_flag"] ?? true;
	}

	/**
	 * Проверяем заполняли ли белый список
	 *
	 * @throws ParseFatalException
	 */
	public static function isWhiteListEmpty(): bool
	{

		return count(self::getWhiteDomainList()) === 0;
	}

	/**
	 * Проверяем есть ли домен в белом списке
	 *
	 * @throws ParseFatalException
	 */
	public static function isDomainInWhiteList(string $domain): bool
	{

		$white_list = Type_Preview_Config::getWhiteDomainList();

		if (count($white_list) < 1) {
			return false;
		}

		return self::_checkIsInDomainList(self::_prepareDomain($domain), $white_list);
	}

	/**
	 * Получаем список доменоч
	 *
	 * @throws ParseFatalException
	 */
	public static function getWhiteDomainList(): array
	{

		$white_list = self::_getConfig(self::_KEY_PREVIEW)["white_list"];

		// если пустой
		if (count($white_list) < 1) {
			return [];
		}

		$domain_list = [];
		foreach ($white_list as $domain) {

			// парсим домены
			$domain_list[] = self::_getDomain($domain);
		}
		return $domain_list;
	}

	/**
	 * Проверяем заполняли ли черный список
	 *
	 * @throws ParseFatalException
	 */
	public static function isBlackListEmpty(): bool
	{

		return count(self::getBlackDomainList()) === 0;
	}

	/**
	 * Проверяем есть ли домен в черном списке
	 *
	 * @throws ParseFatalException
	 */
	public static function isDomainInBlackList(string $domain): bool
	{

		$black_list = Type_Preview_Config::getBlackDomainList();

		if (count($black_list) < 1) {
			return false;
		}

		return self::_checkIsInDomainList(self::_prepareDomain($domain), $black_list);
	}

	/**
	 * Получаем список доменов в черном списке
	 *
	 * @throws ParseFatalException
	 */
	public static function getBlackDomainList(): array
	{

		$black_list = self::_getConfig(self::_KEY_PREVIEW)["black_list"];

		// если пустой
		if (count($black_list) < 1) {
			return [];
		}

		$domain_list = [];
		foreach ($black_list as $domain) {

			// парсим домены
			$domain_list[] = self::_getDomain($domain);
		}
		return $domain_list;
	}

	/**
	 * Проверяем заполняли ли черный список редиректов
	 *
	 * @throws ParseFatalException
	 */
	public static function isRedirectBlackListEmpty(): bool
	{

		return count(self::getRedirectBlackDomainList()) === 0;
	}

	/**
	 * Проверяем есть ли домен в списке
	 *
	 * @throws ParseFatalException
	 */
	public static function isDomainInRedirectBlackList(string $domain): bool
	{

		$redirect_black_list = Type_Preview_Config::getRedirectBlackDomainList();

		if (count($redirect_black_list) < 1) {
			return false;
		}

		return in_array(self::_prepareDomain($domain), $redirect_black_list);
	}

	/**
	 * Получаем список доменов в черном списке для редиректа
	 *
	 * @throws ParseFatalException
	 */
	public static function getRedirectBlackDomainList(): array
	{

		$redirect_black_list = self::_getConfig(self::_KEY_PREVIEW)["redirect_black_list"];

		// если пустой
		if (count($redirect_black_list) < 1) {
			return [];
		}

		$domain_list = [];
		foreach ($redirect_black_list as $domain) {

			// парсим домены
			$domain_list[] = self::_getDomain($domain);
		}
		return $domain_list;
	}

	/**
	 * Готовим домен если был редирект
	 */
	protected static function _prepareDomain(string $domain): string
	{

		if (str_starts_with($domain, "www.")) {
			$domain = substr($domain, 4);
		}

		return $domain;
	}

	/**
	 * Проверить, есть ли домен в списке
	 */
	protected static function _checkIsInDomainList(string $check_domain, array $domain_list): bool
	{

		foreach ($domain_list as $domain) {

			// для wildcard домена
			if (str_starts_with($domain, "*.")) {

				// выделяем постфикс без wildcard
				$domain_postfix = mb_substr($domain, 2);

				// проверяем, что вайлдкард домен является концом проверяемого, и не равен ему
				if ($check_domain !== $domain_postfix && str_ends_with($check_domain, $domain_postfix)) {
					return true;
				}

			}

			// если домен полностью совпадет - значит есть в списке
			if ($check_domain === $domain) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Получаем контент конфига
	 */
	protected static function _getDomain(string $url): string | false
	{

		$prepared_url = Helper_Preview::prepareUrl($url);

		// получаем домен из ссылки
		$domain = parse_url($prepared_url, PHP_URL_HOST);
		$domain = self::_prepareDomain($domain);

		// если не получилось получить домен
		if ($domain === false || is_null($domain)) {

			return false;
		}

		return $domain;
	}

	/**
	 * Получаем контент конфига
	 *
	 * @throws ParseFatalException
	 */
	protected static function _getConfig(string $config_key): array
	{

		$config = getConfig($config_key);

		// если пришел пустой конфиг
		if (count($config) < 1) {
			throw new ParseFatalException("unexpected content");
		}

		return $config;
	}
}
