<?php declare(strict_types=1);

namespace Compass\Conversation;

use BaseFrame\System\Locale;

/**
 * Задача повторной индексации сообщений.
 * Вызывается при изменении сущности, после первичной индексации.
 */
class Domain_Search_Entity_ConversationMessage_Task_Reindex extends Domain_Search_Entity_Task {

	/** @var int тип задачи */
	public const TASK_TYPE = Domain_Search_Const::TASK_TYPE_CONVERSATION_MESSAGE_REINDEX;

	/** @var int сколько задач указанного типа можно брать на индексацию в одну итерацию */
	protected const _PER_ITERATION_LIMIT = 1000;

	/** @var int сколько ошибок допустимо для задачи этого типа */
	protected const _ERROR_LIMIT = 3;

	/**
	 * Добавляет задачи в очередь индексации.
	 */
	public static function queueList(array $message_list, string $locale = Locale::LOCALE_ENGLISH):void {

		// убираем сообщения, непригодные для индексации
		// и формируем список задач индексации
		$message_list = array_filter($message_list, static fn(array $el) => static::isSuitable($el));
		$task_list    = array_map(static fn(array $el) => static::makeTask($el, $locale), $message_list);

		// вставляем данные
		static::_queue(task_list: $task_list);
	}

	/**
	 * Конвертирует сообщение в задачу индексации.
	 */
	public static function makeTask(array $message, string $locale):Struct_Domain_Search_Task|false {

		if (!static::isSuitable($message)) {
			return false;
		}

		return Struct_Domain_Search_Task::fromDeclaration(
			static::TASK_TYPE,
			new Struct_Domain_Search_IndexTask_LocalizedEntity($message["message_map"], $locale)
		);
	}

	/**
	 * Проверяет, подходит ли сообщение для задачи
	 */
	public static function isSuitable(array $message):bool {

		return Type_Conversation_Message_Main::getHandler($message)::isSearchable($message)
			&& Type_Conversation_Message_Main::getHandler($message)::isIndexable($message);
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
			static fn(Struct_Domain_Search_Task $el) => new Struct_Domain_Search_IndexTask_LocalizedEntity(...$el->data),
			$task_list
		);

		// загружаем все необходимые сущности
		static::_preload($task_data_list);

		/** @var Struct_Domain_Search_Replace[] $replace_list формируем список замен */
		$replace_list = array_map(static fn(Struct_Domain_Search_IndexTask_LocalizedEntity $task_data) => static::_toIndexData($task_data), $task_data_list);
		$replace_list = array_filter($replace_list);

		foreach ($replace_list as $replace) {

			// обновляем записи по одной
			// поскольку там выборка слияние с имеющейся записью
			Gateway_Search_Main::replace($replace, Domain_Search_Config_Task::perExecutionComplexityLimit(static::class));

			// удаляем привязанные превью, они будут перепаршены после
			// возможно тут еще нужно отцеплять файлы, но такие сообщения нельзя редактировать
			Gateway_Search_Main::deleteTypedByParent($replace->search_id, [Domain_Search_Const::TYPE_PREVIEW]);
		}
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
	 * @param Struct_Domain_Search_IndexTask_LocalizedEntity[] $task_data_list
	 */
	protected static function _preload(array $task_data_list):void {

		$message_map_list                  = [];
		$to_resolve_search_id_message_list = [];

		foreach ($task_data_list as $task_data) {

			$message_map_list[]                  = $task_data->entity_map;
			$to_resolve_search_id_message_list[] = new Struct_Domain_Search_AppEntity(Domain_Search_Const::TYPE_CONVERSATION_MESSAGE, $task_data->entity_map);
		}

		if (count($message_map_list) === 0) {
			return;
		}

		// загружаем сообщения, для индексации нужно тело сообщения
		// поэтому нужно загрузить его из базы данных
		Domain_Search_Repository_ProxyCache_ConversationMessage::load($message_map_list);
		Domain_Search_Repository_ProxyCache_EntitySearchId::load($to_resolve_search_id_message_list);
	}

	/**
	 * Готовит данные для вставки сообщения в индекс из данных задачи индексации.
	 * @return Struct_Domain_Search_Replace
	 */
	public static function _toIndexData(Struct_Domain_Search_IndexTask_LocalizedEntity $task_data):Struct_Domain_Search_Replace|false {

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

		return new Struct_Domain_Search_Replace(
			$search_id,
			(int) $message["created_at"],
			...Type_Conversation_Message_Main::getHandler($message)::prepareIndexText($message, $task_data->locale)
		);
	}

	/**
	 * Определяет search_id для сообщения.
	 */
	protected static function _resolveMessageSearchId(array $message):int|false {

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
}
