<?php declare(strict_types=1);

namespace Compass\Conversation;

use BaseFrame\System\Locale;

/**
 * Задача первичной индексации сообщений.
 */
class Domain_Search_Entity_ConversationMessage_Task_Index extends Domain_Search_Entity_Task {

	/** @var int тип задачи */
	public const TASK_TYPE = Domain_Search_Const::TASK_TYPE_CONVERSATION_MESSAGE_INDEX;

	/** @var int сколько задач указанного типа можно брать на индексацию в одну итерацию */
	protected const _PER_ITERATION_LIMIT = 1000;

	/** @var int сколько ошибок допустимо для задачи этого типа */
	protected const _ERROR_LIMIT = 3;

	/** @var string ключ для массива группировки скрывших сообщение по умолчанию */
	protected const _DEFAULT_HIDDEN_BY_USERS_KEY = "0";

	/**
	 * Добавляет задачи в очередь индексации.
	 */
	public static function queueList(array $message_list, array $user_id_list, string $locale = Locale::LOCALE_ENGLISH):void {

		// убираем сообщения, непригодные для индексации
		$message_list = array_filter($message_list, static fn(array $el) => static::isSuitable($el));

		// если все сообщения неподходящие, то завершаем работу
		if (count($message_list) === 0) {
			return;
		}

		$task_list   = [];
		$entity_list = [];

		// так как некоторые сообщения могут быть скрыты,
		// то нужно провести индексацию с учетом этой информации
		foreach (static::_iterateOverMessagesGroupedByHiddenByUsers($message_list, $user_id_list) as $message_group_by_hidden_info) {

			// деструктуризируем данные из итератора
			[$message_arr, $user_id_list] = $message_group_by_hidden_info;

			foreach ($message_arr as $message) {

				// формируем связь сущность-поиск
				$entity_list[] = new Struct_Domain_Search_AppEntity(Domain_Search_Const::TYPE_CONVERSATION_MESSAGE, $message["message_map"]);

				// разбиваем список пользователей на чанки
				// чтобы итоговое количество записей для одной задачи было контролируемым
				foreach (array_chunk($user_id_list, Domain_Search_Config_Task::maxTaskComplexity(static::class)) as $user_id_list_chunk) {

					// формируем задачу индексации
					$task_data   = new Struct_Domain_Search_IndexTask_LocalizedCommon($message["message_map"], $user_id_list_chunk, $locale);
					$task_list[] = Struct_Domain_Search_Task::fromDeclaration(static::TASK_TYPE, $task_data);
				}
			}
		}

		// вставляем данные одной пачкой
		static::_queue($entity_list, $task_list);
	}

	/**
	 * Группирует сообщения по группам скрытия пользователями.
	 */
	protected static function _iterateOverMessagesGroupedByHiddenByUsers(array $message_list, array $user_id_list):\Generator {

		// в этом массив храним сообщения, скрытые конкретной группой пользователей
		// индексом выступает конкатенированная строка из user_id, для которых сообщение скрыто
		$message_list_groped_by_hidden_by_user = [];

		// в этом массиве индексом выступает та же строка, что и в $message_list_groped_by_hidden_by_user,
		// однако тут хранятся списки пользователей, для которых соответствующие сообщения нужно отрисовать
		$opposite_user_id_list_groped_by_hidden_by_user[static::_DEFAULT_HIDDEN_BY_USERS_KEY] = $user_id_list;

		foreach ($message_list as $message) {

			// объявляем дефолтный ключ, считаем, что сообщение никем не скрыто
			$key = static::_DEFAULT_HIDDEN_BY_USERS_KEY;

			// получаем список пользователей, скрывших сообщение
			$hidden_by_user_id_list = Type_Conversation_Message_Main::getHandler($message)::getHiddenByUserIdList($message);

			// если сообщение скрыто хотя бы кем-то,
			// то пытаемся определить, нет ли скрывшего в списке на индексацию
			if (count($hidden_by_user_id_list) > 0) {

				// сортируем, чтобы не было дубликатов ключей
				// из-за разного порядка пользователей
				sort($hidden_by_user_id_list);

				// оставляем пользователей, пришедших в списке на индексацию, но скрывших сообщение
				$hidden_by_user_id_list = array_intersect($user_id_list, $hidden_by_user_id_list);

				// если в списке на индексацию были пользователи, скрывшие сообщение
				// то формируем соответствующие массивы с группами
				if (count($hidden_by_user_id_list) > 0) {

					$key = implode(" ", $hidden_by_user_id_list);

					// не добавляем ключ, если оон ранее был добавлен
					if (!isset($opposite_user_id_list_groped_by_hidden_by_user[$key])) {
						$opposite_user_id_list_groped_by_hidden_by_user[$key] = array_diff($user_id_list, $hidden_by_user_id_list);
					}
				}
			}

			// заносим сообщение в соответствующую группу по полученному ключу
			// если никто из пользователей на переиндексацию не скрывал, то индексируем для всех
			$message_list_groped_by_hidden_by_user[$key][] = $message;
		}

		foreach ($message_list_groped_by_hidden_by_user as $key => $message_group) {
			yield [$message_group, $opposite_user_id_list_groped_by_hidden_by_user[$key]];
		}
	}

	/**
	 * Проверяет, подходит ли сообщение для задачи
	 */
	public static function isSuitable(array $message):bool {

		return Type_Conversation_Message_Main::getHandler($message)::isSearchable($message)
			&& Type_Conversation_Message_Main::getHandler($message)::isIndexable($message);
	}

	/**
	 * Нужно ограничить число сущностей индексируемых за раз, в зависимости от числа пользователей.
	 * Иначе высок шанс того, что упремся в ООМ или в лимит передачи данных, если пользователей будет очень много.
	 *
	 * @param Struct_Domain_Search_Task[] $full_task_list
	 */
	public static function splitIntoChunks(array $full_task_list):array {

		return static::_distributeTasksByComplexityEstimate(
			$full_task_list,
			Domain_Search_Config_Task::perExecutionComplexityLimit(static::class),
			static fn(Struct_Domain_Search_Task $task) => count($task->data["user_id_list"])
		);
	}

	/**
	 * Выполняет указанный список задач.
	 *
	 * @param Struct_Domain_Search_Task[] $task_list
	 */
	public static function execList(array $task_list):void {

		// здесь можно было все сделать в один обход массива,
		// но для ускорения выборок через прокси-кэш
		// сначала нужно все задачи собрать, а потом уже загружать

		// формируем список с данными задачи
		$task_data_list = array_map(
			static fn(Struct_Domain_Search_Task $el) => new Struct_Domain_Search_IndexTask_LocalizedCommon(...$el->data),
			$task_list
		);

		// загружаем все необходимые сущности
		static::_preload($task_data_list);

		// превращаем задачи в строки для вставки
		// и вставляем добавляем их в индекс
		$prepared_list = array_map(static fn(Struct_Domain_Search_IndexTask_LocalizedCommon $task) => static::_toIndexData($task), $task_data_list);
		$prepared_list = array_filter($prepared_list);

		if (count($prepared_list) === 0) {
			return;
		}

		Gateway_Search_Main::insert(array_merge(...$prepared_list));
		static::_attachFiles($task_data_list);
	}

	/**
	 * Метод предварительной загрузки данных.
	 *
	 * Если нужно обработать много задач разом, то стоит вызвать
	 * предварительную загрузку, чтобы поместить все данные в прокси-кэш
	 * за минимальное количество запросов к БД.
	 *
	 * Предзагрузка не гарантирует, что в процессе индексации не потребуются
	 * дополнительные данные, но все равно должна заметно сокращать число запросов.
	 *
	 * @param Struct_Domain_Search_IndexTask_LocalizedCommon[] $task_data_list
	 */
	public static function _preload(array $task_data_list):void {

		$message_map_list                       = [];
		$to_resolve_search_id_message_list      = [];
		$to_resolve_search_id_conversation_list = [];

		foreach ($task_data_list as $task_data) {

			$message_map_list[]                  = $task_data->entity_map;
			$to_resolve_search_id_message_list[] = new Struct_Domain_Search_AppEntity(Domain_Search_Const::TYPE_CONVERSATION_MESSAGE, $task_data->entity_map);

			$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($task_data->entity_map);

			// не добавляем map диалога, если он уже ранее должен был быть загружен
			if (!isset($conversation_map_list[$conversation_map])) {

				$conversation_map_list[$conversation_map] = \CompassApp\Pack\Message\Conversation::getConversationMap($task_data->entity_map);
				$to_resolve_search_id_conversation_list[] = new Struct_Domain_Search_AppEntity(Domain_Search_Const::TYPE_CONVERSATION, $task_data->entity_map);
			}
		}

		if (count($message_map_list) === 0) {
			return;
		}

		// загружаем сообщения, для индексации нужно тело сообщения
		// поэтому нужно загрузить его из базы данных
		Domain_Search_Repository_ProxyCache_ConversationMessage::load($message_map_list);

		// загружаем требуемые search_id для требуемых сущностей
		Domain_Search_Repository_ProxyCache_EntitySearchId::load($to_resolve_search_id_message_list + $to_resolve_search_id_conversation_list);
	}

	/**
	 * Готовит данные для вставки сообщения в индекс из данных задачи индексации.
	 * @return Struct_Domain_Search_Insert[]
	 */
	public static function _toIndexData(Struct_Domain_Search_IndexTask_LocalizedCommon $task_data):array | false {

		$load    = Domain_Search_Repository_ProxyCache_ConversationMessage::load([$task_data->entity_map]);
		$message = reset($load);

		if ($message === false) {
			return false;
		}

		// получаем что search_id для сообщения доступен для сообщения
		$search_id = static::_resolveMessageSearchId($message);
		if ($search_id === false) {
			return false;
		}

		// получаем данные родителей и проверяем, что они корректные
		$parent_data = static::_resolveParentData($message);
		if ($parent_data === false) {
			return false;
		}

		$insert_arr = [
			(int) $message["sender_user_id"],
			$search_id,
			Domain_Search_Const::TYPE_CONVERSATION_MESSAGE,
			Domain_Search_Const::ATTRIBUTE_SHARED_BELONG_TO_CONVERSATION,
			(int) $message["created_at"],
			...$parent_data,
			...Type_Conversation_Message_Main::getHandler($message)::prepareIndexText($message, $task_data->locale)
		];

		return array_map(
			static fn(int $user_id) => new Struct_Domain_Search_Insert($user_id, ...$insert_arr),
			$task_data->user_id_list
		);
	}

	/**
	 * Определяет search_id для сообщения.
	 */
	protected static function _resolveMessageSearchId(array $message):int | false {

		// получаем что search_id для сообщения доступен для сообщения
		$search_rel = Domain_Search_Repository_ProxyCache_EntitySearchId::load([
			new Struct_Domain_Search_AppEntity(Domain_Search_Const::TYPE_CONVERSATION_MESSAGE, $message["message_map"])
		]);

		$search_id = $search_rel[$message["message_map"]] ?? false;

		// если не нашли
		if (!$search_id) {
			static::yell("[WARN] search rel для сообщения %s не найдена", $message["message_map"]);
		}

		return $search_id;
	}

	/**
	 * Данные родителей для сообщения из диалога.
	 * Формат ответа: [прямой родитель, наследованные родители, маска типов родителей, родитель для группировки]
	 */
	protected static function _resolveParentData(array $message):array | false {

		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message["message_map"]);
		$search_rel       = Domain_Search_Repository_ProxyCache_EntitySearchId::load([
			new Struct_Domain_Search_AppEntity(Domain_Search_Const::TYPE_CONVERSATION, $conversation_map)
		]);

		if (!isset($search_rel[$conversation_map])) {

			static::yell("[WARN] search rel для диалога %s не найдена", $conversation_map);
			return false;
		}

		$conversation_search_id = $search_rel[$conversation_map];

		$parent_list      = [$conversation_search_id];
		$parent_type_mask = Domain_Search_Const::TYPE_CONVERSATION;
		$group_parent_id  = $conversation_search_id;

		return [$conversation_search_id, $parent_list, $parent_type_mask, $group_parent_id];
	}

	/**
	 * Добавляет задачи на создания связи индексации для файлов.
	 * @param Struct_Domain_Search_IndexTask_LocalizedCommon[] $task_data_list
	 */
	protected static function _attachFiles(array $task_data_list):void {

		$message_map_list    = array_column($task_data_list, "entity_map");
		$loaded_message_list = Domain_Search_Repository_ProxyCache_ConversationMessage::load($message_map_list);

		foreach ($task_data_list as $task_data) {

			$file_task_data_list = [];

			if (!isset($loaded_message_list[$task_data->entity_map])) {
				continue;
			}

			$message = $loaded_message_list[$task_data->entity_map];

			[$bound_file_map, $nested_file_map_list] = Type_Conversation_Message_Main::getHandler($message)::prepareFiles($message);

			// задача для файла в сообщении
			if ($bound_file_map !== false) {

				static::say("привязываю файл $bound_file_map к сообщению {$message["message_map"]}");
				$file_task_data_list[] = ["file_map" => $bound_file_map, "message_map" => $message["message_map"]];
			}

			// задачи для вложенных файлов
			foreach ($nested_file_map_list as $nested_file_map) {

				static::say("привязываю вложенный файл $nested_file_map к сообщению {$message["message_map"]}");
				$file_task_data_list[] = ["file_map" => $nested_file_map, "message_map" => $message["message_map"]];
			}

			if (count($file_task_data_list) === 0) {
				continue;
			}

			// добавляем задачки для связи с файлом
			Domain_Search_Entity_File_Task_AttachToConversationMessage::queueList($file_task_data_list, $task_data->user_id_list, $task_data->locale);
		}
	}
}
