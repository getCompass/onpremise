<?php

namespace Compass\Announcement;

// @formatter:off
// название текущего модуля
define("DATE_FORMAT_FULL"		, "d.m.Y H:i");
define("DATE_FORMAT_FULL_S"		, "d.m.Y H:i:s");
define("DATE_FORMAT_SMALL"		, "d.m.Y");

define("FOREVER"				, 3600 * 24 * 365 * 10);
define("HOUR1"				, 3600 * 1);
define("HOUR2"				, 3600 * 2);
define("HOUR3"				, 3600 * 3);
define("HOUR4"				, 3600 * 4);
define("HOUR5"				, 3600 * 5);
define("HOUR6"				, 3600 * 6);
define("HOUR12"				, 3600 * 12);
define("HOUR24"				, 3600 * 24);
define("DAY1"				, 3600 * 24 * 1);
define("DAY2"				, 3600 * 24 * 2);
define("DAY3"				, 3600 * 24 * 3);
define("DAY4"				, 3600 * 24 * 4);
define("DAY5"				, 3600 * 24 * 5);
define("DAY7"				, 3600 * 24 * 7);
define("DAY10"				, 3600 * 24 * 10);
define("DAY14"				, 3600 * 24 * 14);
define("DAY15"				, 3600 * 24 * 15);

// коды ответов, ошибок сервера:
// 	https://yandex.ru/support/webmaster/error-dictionary/http-codes.xml
// 	https://ru.wikipedia.org/wiki/%D0%A1%D0%BF%D0%B8%D1%81%D0%BE%D0%BA_%D0%BA%D0%BE%D0%B4%D0%BE%D0%B2_%D1%81%D0%BE%D1%81%D1%82%D0%BE%D1%8F%D0%BD%D0%B8%D1%8F_HTTP

// виноват клиент
define("HTTP_CODE_400"			, 400); // ошибка 400 сожет быть вызвана, когда не передали обязательный параметр в post запросе, или передаваемый тип не соотвествовал строго ожидаемому.
define("HTTP_CODE_401"			, 401); // требуется авторизация
define("HTTP_CODE_403"			, 403); // сервер понял запрос, но отказался его авторизовывать
define("HTTP_CODE_404"			, 404); // если нет указанного контролера или метода
define("HTTP_CODE_405"			, 405); // если раньше метод был, но сейчас не поддерживается (или вместо POST, прислали GET)
define("HTTP_CODE_423"			, 423); // сервер отказывается обработать запрос, так как превышено число попыток

// критические - виноваты мы
define("HTTP_CODE_500"			, 500); // ошибка сервера (у клиента все ок - проблема у нас)

define("FILE_KEY_VALID_TILL"		, 60 * 2); // время, которое валиден file_key
define("ENCRYPT_CIPHER_METHOD"	, "AES-256-CBC"); // метод шифрования для openssl_encrypt

// @formatter:on