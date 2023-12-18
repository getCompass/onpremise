<?php

namespace Compass\Conversation;

/**
 * Возвращает все сообщения-заявки из чата наймов и увольнений.
 * Ленивое действие — вызывает фоном при необходимости.
 */
class Domain_Conversation_Action_Lazy_GetHiringConversationRequestMessageRelList {

	// приблизительный множитель для определения числа необходимых блоков
	// по идее в блоке 30 сообщения, но помимо заявок там могуть быть обычные сообщения
	// на 30 заявок прикидываем 5 сообщений не заявок и получаем итоговое число необходимых блоков
	protected const _EXPECTED_REQUEST_MESSAGE_PER_BLOCK_MULTIPLIER = 5 / 30;

	// максимально допустимое число блоков для чтения
	protected const _MAX_BLOCK_COUNT_PER_ATTEMPT = 15;

	// ключи для кэширования данных уже полученный связей сообщение-заявка
	protected const _HIRING_REL_CACHE_KEY    = "HIRING_REL_CACHE_KEY";
	protected const _DISMISSAL_REL_CACHE_KEY = "DISMISSAL_REL_CACHE_KEY";

	// время жизни данных в кэше
	protected const _CACHE_TTL = 1 * 60;

	/**
	 * Ищет связи сообщение-заявка наема/увольнения для всех переданных заявок.
	 */
	#[\JetBrains\PhpStorm\ArrayShape(["hiring_request_message_map_rel_list" => "array", "dismissal_request_message_map_rel_list" => "array"])]
	public static function run(int $date_from, array $hiring_request_id_list, array $dismissal_request_id_list):array {

		// пытаемся получить закэшированные данные для заявок
		$hiring_rel_list    = static::_getCachedHiringRequestMessageRels($hiring_request_id_list);
		$dismissal_rel_list = static::_getCachedDismissalRequestMessageRels($dismissal_request_id_list);

		// если вдруг в кэше не нашлись необходимые данные,
		// то пытаемся из спарсить из чата и записать в кэш для потомков
		if ($hiring_rel_list === false || $dismissal_rel_list === false) {

			// диалог, откуда будем читать данные для заявок
			$conversation_map = Type_Company_Default::getCompanyGroupConversationMapByKey(Domain_Company_Entity_Config::HIRING_CONVERSATION_KEY_NAME);

			// получаем n блоков сообщений — считаем, что на каждую заявку у нас потенциально есть еще 5 сообщений
			// но при этом не запрашиваем более определенного числа блоков блоков за раз
			$request_count = count($hiring_request_id_list) + count($dismissal_request_id_list);
			$limit         = min(ceil($request_count * static::_EXPECTED_REQUEST_MESSAGE_PER_BLOCK_MULTIPLIER), static::_MAX_BLOCK_COUNT_PER_ATTEMPT);
			$limit         = max($limit, 2); // если пришел всего 1 $request_count и он оказался 1 сообщением в блоке, то случится баг, нужно брать минимум 2 блока

			// получаем все связи для всех заявок
			[$hiring_rel_list, $dismissal_rel_list] = static::_getRelsFromConversationMessages($conversation_map, $date_from, $limit);

			// пишем в кэш новые данные
			static::_writeHiringRequestMessageRelsToCache($hiring_rel_list);
			static::_writeDismissalRequestMessageRelsToCache($dismissal_rel_list);
		}

		return [$hiring_rel_list, $dismissal_rel_list,];
	}

	/**
	 * Получаем все связи с сообщениями для заявок наемов и увольнений.
	 */
	#[\JetBrains\PhpStorm\ArrayShape(["hiring_request_message_rel_list" => "array", "dismissal_request_message_rel_list" => "array"])]
	protected static function _getRelsFromConversationMessages(string $conversation_map, int $date_from, int $limit):array {

		// список блоков с сообщениями
		$block_list = Gateway_Db_CompanyConversation_MessageBlock::getByPeriod($conversation_map, $date_from, $limit);

		$dismissal_rel_list = [];
		$hiring_rel_list    = [];

		foreach ($block_list as $block_row) {

			// раскидываем сообщения по массивам для дальнейше работы
			foreach ($block_row["data"] as $raw_message) {

				// форматируем сообщение и ищем там заявки
				$message = Type_Conversation_Message_Main::getHandler($raw_message)::prepareForFormat($raw_message);

				if ((int) $message["type"] === CONVERSATION_MESSAGE_TYPE_DISMISSAL_REQUEST) {

					$dismissal_request_id                      = (int) $message["data"]["dismissal_request_id"];
					$dismissal_rel_list[$dismissal_request_id] = $message["message_map"];
				} else if ((int) $message["type"] === CONVERSATION_MESSAGE_TYPE_HIRING_REQUEST) {

					$hiring_request_id                   = (int) $message["data"]["hiring_request_id"];
					$hiring_rel_list[$hiring_request_id] = $message["message_map"];
				}
			}
		}

		return [$hiring_rel_list, $dismissal_rel_list];
	}

	/**
	 * Пытается получить из кэша данные по указанным заявкам на наем.
	 */
	protected static function _getCachedHiringRequestMessageRels(array $hiring_request_id_list):array|false {

		return static::_readCache(static::_HIRING_REL_CACHE_KEY, $hiring_request_id_list);
	}

	/**
	 * Пытается получить из кэша данные по указанным заявкам на увольнение.
	 */
	protected static function _getCachedDismissalRequestMessageRels(array $hiring_request_id_list):array|false {

		return static::_readCache(static::_DISMISSAL_REL_CACHE_KEY, $hiring_request_id_list);
	}

	/**
	 * Сопоставляет данные из кеша и раскидывает их по списку ребуемых ид.
	 */
	protected static function _readCache(string $key, array $required_key_list):array|false {

		// если ничего не передали, то в кэш идти незачем
		if (count($required_key_list) === 0) {
			return [];
		}

		$cached = ShardingGateway::cache()->get(static::_makeKey($key));
		if ($cached === false) {
			return false;
		}

		$cached_data = fromJson($cached);
		$output      = [];

		// ищем все нужные заявки в кэше и заносим из в список
		foreach ($required_key_list as $required_key) {

			if (isset($cached_data[$required_key])) {
				$output[$required_key] = $cached_data[$required_key];
			}
		}

		// если не сошлось, то какая-то заявка не была найдена
		// в таком случае проще поискать все по новой (ну пока так сделано)
		if (count($output) !== count($required_key_list)) {
			return false;
		}

		return $output;
	}

	/**
	 * Обновляеть кэш для заявок наема.
	 */
	protected static function _writeHiringRequestMessageRelsToCache(array $rel_list):void {

		static::_updateCache(static::_HIRING_REL_CACHE_KEY, $rel_list);
	}

	/**
	 * Обновляеть кэш для заявок увольнения.
	 */
	protected static function _writeDismissalRequestMessageRelsToCache(array $rel_list):void {

		static::_updateCache(static::_DISMISSAL_REL_CACHE_KEY, $rel_list);
	}

	/**
	 * Выполняет обновление кэша для указанного ключа.
	 * Мерджет данные в кэше с новыми, а не перезаписывает.
	 */
	protected static function _updateCache(string $key, array $rel_list):void {

		// если нечего дописывать, то ничего и не делаем
		if (count($rel_list) === 0) {
			return;
		}

		// читаем существующие записи из кэша, их нужно обновлять, а не затирать
		$cached      = ShardingGateway::cache()->get(static::_makeKey($key));
		$cached_data = $cached !== false ? fromJson($cached) : [];

		// записываем в кэш обновленные данные
		ShardingGateway::cache()->set(static::_makeKey($key), toJson($rel_list + $cached_data), static::_CACHE_TTL);
	}

	/**
	 * Генерирует безопасный ключ для запроса в кэш.
	 */
	protected static function _makeKey(string $key):string {

		return COMPANY_ID . "_$key";
	}
}