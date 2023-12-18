<?php declare(strict_types=1);

namespace Compass\Conversation;

use BaseFrame\System\Locale;

/**
 * Задача прикрепления превью к сообщению.
 *
 * Запускается при:
 *      — Успешном парсинге превью
 *      — Переиндексации сообщения
 */
class Domain_Search_Entity_Preview_Task_AttachToConversationMessage extends Domain_Search_Entity_Task {

	/** @var int тип задачи */
	public const TASK_TYPE = Domain_Search_Const::TASK_TYPE_CONVERSATION_PREVIEW_INDEX;

	/** @var int сколько задач указанного типа можно брать на индексацию в одну итерацию */
	protected const _PER_ITERATION_LIMIT = 1000;

	/** @var int сколько ошибок допустимо для задачи этого типа */
	protected const _ERROR_LIMIT = 1;

	/**
	 * Добавляет задачу в очередь индексации.
	 * На вход принимает массив массивов вида ["preview_map" = string, "message_map" = string].
	 */
	public static function queueList(array $data_list, array $user_id_list, string $locale = Locale::LOCALE_ENGLISH):void {

		$entity_list = [];
		$task_list   = [];

		foreach ($data_list as $data) {

			$entity_list[] = new Struct_Domain_Search_AppEntity(Domain_Search_Const::TYPE_PREVIEW, $data["preview_map"]);

			foreach (array_chunk($user_id_list, Domain_Search_Config_Task::maxTaskComplexity(static::class)) as $user_id_list_chunk) {

				$task_data   = new Struct_Domain_Search_IndexTask_MessagePreview($data["preview_map"], $data["message_map"], $user_id_list_chunk, $locale);
				$task_list[] = Struct_Domain_Search_Task::fromDeclaration(static::TASK_TYPE, $task_data);
			}
		}

		// вставляем данные
		static::_queue($entity_list, $task_list);
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
			static fn(Struct_Domain_Search_Task $el) => new Struct_Domain_Search_IndexTask_MessagePreview(...$el->data),
			$task_list
		);

		// загружаем все необходимые сущности
		static::_preload($task_data_list);

		// превращаем задачи в строки для вставки
		// и вставляем добавляем их в индекс
		$prepared_list = array_map(static fn(Struct_Domain_Search_IndexTask_MessagePreview $task) => static::_toIndexData($task), $task_data_list);
		$prepared_list = array_filter($prepared_list);

		if (count($prepared_list) === 0) {
			return;
		}

		Gateway_Search_Main::insert(array_merge(...$prepared_list));
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
	 * @param Struct_Domain_Search_IndexTask_MessagePreview[] $task_data_list
	 */
	public static function _preload(array $task_data_list):void {

		$preview_map_list                       = [];
		$message_map_list                       = [];
		$to_resolve_search_id_preview_list      = [];
		$to_resolve_search_id_message_list      = [];
		$to_resolve_search_id_conversation_list = [];

		foreach ($task_data_list as $task_data) {

			$preview_map_list[] = $task_data->preview_map;
			$message_map_list[] = $task_data->message_map;

			$to_resolve_search_id_preview_list[] = new Struct_Domain_Search_AppEntity(Domain_Search_Const::TYPE_PREVIEW, $task_data->preview_map);
			$to_resolve_search_id_message_list[] = new Struct_Domain_Search_AppEntity(Domain_Search_Const::TYPE_CONVERSATION_MESSAGE, $task_data->message_map);

			$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($task_data->message_map);

			// не добавляем map диалога, если он уже ранее должен был быть загружен
			if (!isset($conversation_map_list[$conversation_map])) {

				$conversation_map_list[$conversation_map] = \CompassApp\Pack\Message\Conversation::getConversationMap($task_data->message_map);
				$to_resolve_search_id_conversation_list[] = new Struct_Domain_Search_AppEntity(Domain_Search_Const::TYPE_CONVERSATION, $task_data->message_map);
			}
		}

		if (count($message_map_list) === 0) {
			return;
		}

		// загружаем сообщения, для индексации нужно тело сообщения
		// поэтому нужно загрузить его из базы данных
		Domain_Search_Repository_ProxyCache_ConversationMessage::load($message_map_list);
		Domain_Search_Repository_ProxyCache_Preview::load($preview_map_list);

		// загружаем требуемые search_id для требуемых сущностей
		Domain_Search_Repository_ProxyCache_EntitySearchId::load(
			$to_resolve_search_id_message_list + $to_resolve_search_id_conversation_list + $to_resolve_search_id_preview_list
		);
	}

	/**
	 * Готовит данные для вставки сообщения в индекс из данных задачи индексации.
	 * @return Struct_Domain_Search_Insert[]
	 */
	public static function _toIndexData(Struct_Domain_Search_IndexTask_MessagePreview $task_data):array | false {

		// загружаем саму индексируемую сущность
		$load    = Domain_Search_Repository_ProxyCache_Preview::load([$task_data->preview_map]);
		$preview = reset($load);

		// проверяем, что превьюха не пустая
		if ($preview === false || (($preview["data"]["title"] ?? "") === "" && ($preview["data"]["description"] ?? "") === "")) {
			return false;
		}

		// получаем связь поиск-сущность
		$search_rel = Domain_Search_Repository_ProxyCache_EntitySearchId::load([
			new Struct_Domain_Search_AppEntity(Domain_Search_Const::TYPE_PREVIEW, $preview["preview_map"]),
		]);

		if (!isset($search_rel[$preview["preview_map"]])) {

			static::yell("[WARN] search rel для превью %s не найдена", $preview["preview_map"]);
			return false;
		}

		$preview_search_id = $search_rel[$preview["preview_map"]];

		// получаем данные родителей и проверяем, что они корректные
		$parent_data = static::_resolveParentData($task_data);
		if ($parent_data === false) {
			return false;
		}

		$loaded  = Domain_Search_Repository_ProxyCache_ConversationMessage::load([$task_data->message_map]);
		$message = reset($loaded);

		// очищаем от emoji
		$title       = removeEmojiFromText($preview["data"]["title"] ?? "");
		$description = removeEmojiFromText($preview["data"]["description"] ?? "");

		$title       = Domain_Search_Helper_Stemmer::stemText($title, [$task_data->locale, Locale::LOCALE_RUSSIAN]);
		$description = Domain_Search_Helper_Stemmer::stemText($description, [$task_data->locale, Locale::LOCALE_RUSSIAN]);

		$insert_arr = [
			$message["sender_user_id"],
			$preview_search_id,
			Domain_Search_Const::TYPE_PREVIEW,
			Domain_Search_Const::ATTRIBUTE_SHARED_BELONG_TO_CONVERSATION,
			(int) $message["created_at"],
			...$parent_data,
			...[$title, $description,],
		];

		return array_map(
			static fn(int $user_id) => new Struct_Domain_Search_Insert($user_id, ...$insert_arr),
			$task_data->user_id_list
		);
	}

	/**
	 * Формирует список родителей для превью.
	 * Формат ответа: [прямой родитель, наследованные родители, маска типов родителей, родитель для группировки]
	 */
	protected static function _resolveParentData(Struct_Domain_Search_IndexTask_MessagePreview $task_data):array | false {

		// получаем диалог, которому принадлежит превью
		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($task_data->message_map);
		$search_rel       = Domain_Search_Repository_ProxyCache_EntitySearchId::load([
			new Struct_Domain_Search_AppEntity(Domain_Search_Const::TYPE_CONVERSATION_MESSAGE, $task_data->message_map),
			new Struct_Domain_Search_AppEntity(Domain_Search_Const::TYPE_CONVERSATION, $conversation_map),
		]);

		if (
			!isset($search_rel[$task_data->message_map])
			|| !isset($search_rel[$conversation_map])
		) {

			static::yell("[WARN] search rel для сообщения %s или диалога %s не найдена", $task_data->message_map, $conversation_map);
			return false;
		}

		$message_search_id      = $search_rel[$task_data->message_map];
		$conversation_search_id = $search_rel[$conversation_map];

		$parent_list      = [$message_search_id, $conversation_search_id];
		$parent_type_mask = Domain_Search_Const::TYPE_CONVERSATION | Domain_Search_Const::TYPE_CONVERSATION_MESSAGE;
		$group_parent_id  = $conversation_search_id;

		return [$message_search_id, $parent_list, $parent_type_mask, $group_parent_id];
	}
}
