<?php declare(strict_types=1);

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Задача полной переиндексации диалога.
 *
 * Запускается в следующих случаях:
 *    — начата переиндексация всего пространства
 *    — пользователь вступил в диалог
 */
class Domain_Search_Entity_Conversation_Task_Reindex extends Domain_Search_Entity_Task {

	/** @var int тип задачи */
	public const TASK_TYPE = Domain_Search_Const::TASK_TYPE_CONVERSATION_REINDEX;

	// шага задачи — очистка и фиксация исходны для диалога
	// в этот момент из поисковика будут
	//	— удалены все связи с диалогом
	//	— зафиксирован список участников на момент начала исполнения
	protected const _STEP_CLEAR = 1;

	// шаг задачи — пушить сообщения на индексацию
	// в этот момент перебираем все блоки сообщений диалога по очереди
	// и добавляем задачи в очередь для ранее зафиксированного списка пользователей
	protected const _STEP_PUSH_MESSAGES = 2;

	// таймаут для постановки сообщений из блоков в очередь
	protected const _PER_ITERATION_TIMEOUT_SEC = 1;

	// сколько сообщений можно поставить в очередь за одну итерацию задачи
	protected const _FETCH_MESSAGE_LIMIT = 900;

	/**
	 * Добавляет задачу переиндексации диалога для списка пользователей
	 */
	public static function queueForUser(string $conversation_map, array $user_id_list):void {

		// добавляем задачи в список
		$task_data   = new Struct_Domain_Search_IndexTask_Common($conversation_map, $user_id_list, self::_initTaskExtra());
		$task_list[] = Struct_Domain_Search_Task::fromDeclaration(static::TASK_TYPE, $task_data);

		// поставил задачу переиндксации диалога для пользователей
		static::_queue(task_list: $task_list);
	}

	/**
	 * Добавляет указанный список диалогов в очередь на переиндексацию.
	 */
	public static function queueList(array $conversation_map_list):void {

		// пытаемся загрузить мету для диалога
		$loaded_meta_list = Domain_Search_Repository_ProxyCache_ConversationMeta::load($conversation_map_list);

		$task_list   = [];
		$entity_list = [];

		// экстра данные для задачи везде одинаковые —
		// нет смысла генерировать их каждый раз
		$task_extra = self::_initTaskExtra();

		foreach ($loaded_meta_list as $conversation_meta) {

			// формируем список пользователей на момент начала задачи
			// для них данные диалога будут очищены, пользователи, которые вступят
			// в диалог после начала, будут проиндексированы соответствующими задачами
			$user_id_list = array_keys($conversation_meta["users"]);

			// пропускаем диалоги, где пользователей нет
			if (count($user_id_list) === 0) {
				continue;
			}

			// добавляем задачи в список
			$task_data   = new Struct_Domain_Search_IndexTask_Common($conversation_meta["conversation_map"], $user_id_list, $task_extra);
			$task_list[] = Struct_Domain_Search_Task::fromDeclaration(static::TASK_TYPE, $task_data);

			// добавляем связи сущность-поиск
			$entity_list[] = new Struct_Domain_Search_AppEntity(Domain_Search_Const::TYPE_CONVERSATION, $conversation_meta["conversation_map"]);
		}

		if (count($task_list) === 0) {
			return;
		}

		// добавляем задачи в очередь
		static::_queue($entity_list, $task_list);
	}

	/**
	 * Группирует задачи по массивам.
	 *
	 * В пачку набираем задачи индексации одного диалога,
	 * при выполнении перегруппируем пользователей, и сделаем одна итерация-один диалог.
	 */
	public static function splitIntoChunks(array $full_task_list):array {

		$output = [];

		foreach ($full_task_list as $task) {

			// группируем задачи по диалогам и текущему шагу
			$task_data = Struct_Domain_Search_IndexTask_Common::fromRaw($task->data);
			$extra     = $task_data->extra;

			$key            = sprintf("%s:%d:%d", $task_data->entity_map, static::_getTaskExtraStep($extra), static::_getTaskExtraLastBlockid($extra));
			$output[$key][] = $task;
		}

		return array_values($output);
	}

	/**
	 * @inheritDoc
	 * @long
	 */
	public static function execList(array $task_list):void {

		$task_data        = static::_mergeData($task_list);
		$conversation_map = $task_data->entity_map;

		// пытаемся загрузить мету для диалога
		$loaded_meta_list = Domain_Search_Repository_ProxyCache_ConversationMeta::load([$task_data->entity_map]);

		// если меты диалога не нашлось, то завершаем итерацию
		if (!isset($loaded_meta_list[$conversation_map])) {
			return;
		}

		// загружаем поисковый идентификатор
		$app_entity             = new Struct_Domain_Search_AppEntity(Domain_Search_Const::TYPE_CONVERSATION, $conversation_map);
		$loaded_search_rel_list = Domain_Search_Repository_ProxyCache_EntitySearchId::load([$app_entity]);

		// если записи для поиска не нашлось, то завершаем работу
		if (!isset($loaded_search_rel_list[$conversation_map])) {

			static::yell("[WARN] search rel для диалога %s не найдена", $conversation_map);
			return;
		}

		$conversation_meta = $loaded_meta_list[$conversation_map];
		$search_id         = $loaded_search_rel_list[$conversation_map];
		$task_extra        = $task_data->extra;

		// первым делом удаляем все записи для пользователей
		if (static::_getTaskExtraStep($task_extra) === static::_STEP_CLEAR) {

			static::say("очищаю диалог %s для %d пользователей", $conversation_map, count($task_data->user_id_list));
			Gateway_Search_Main::deleteByParentForUsers($search_id, $task_data->user_id_list);
			$task_extra = static::_setTaskExtraStep($task_extra, static::_STEP_PUSH_MESSAGES);
		}

		// ставим все сообщения в очередь индексации
		[$need_requeue, $task_extra] = static::_indexMessages($task_data, $task_extra, $conversation_meta);

		// завершаем, если больше не нужно ничего индексировать
		if (!$need_requeue) {
			return;
		}

		// заводим новую задачу на индексацию этого диалога,
		// но уже с актуальной информацией по индексированным блокам
		$task_data = new Struct_Domain_Search_IndexTask_Common($conversation_meta["conversation_map"], $task_data->user_id_list, $task_extra);
		$task      = Struct_Domain_Search_Task::fromDeclaration(static::TASK_TYPE, $task_data);

		static::say(
			"добавил повторную задачу индексации диалога %s со смещением %d для %d пользователей", $conversation_meta["conversation_map"],
			static::_getTaskExtraLastBlockid($task_data->extra), count($task_data->user_id_list)
		);

		static::_queue(task_list: [$task]);
	}

	/**
	 * Объединяет данные нескольких задач в один набор.
	 *
	 * @param Struct_Domain_Search_Task[] $task_list
	 */
	private static function _mergeData(array $task_list):Struct_Domain_Search_IndexTask_Common {

		$first_task      = array_shift($task_list);
		$first_task_data = Struct_Domain_Search_IndexTask_Common::fromRaw($first_task->data);

		$user_id_list = $first_task_data->user_id_list;
		$merged_count = 1;

		$expected_conversation_map = $first_task_data->entity_map;
		$expected_step             = static::_getTaskExtraStep($first_task_data->extra);
		$expected_block_id         = static::_getTaskExtraLastBlockid($first_task_data->extra);

		foreach ($task_list as $task) {

			$task_data = Struct_Domain_Search_IndexTask_Common::fromRaw($task->data);

			$conversation_map = $task_data->entity_map;
			$step             = static::_getTaskExtraStep($task_data->extra);
			$block_id         = static::_getTaskExtraLastBlockid($task_data->extra);

			if ($conversation_map !== $expected_conversation_map || $step !== $expected_step || $expected_block_id !== $block_id) {
				throw new ReturnFatalException("passed different conversation maps");
			}

			array_push($user_id_list, ...$task_data->user_id_list);
			$merged_count++;
		}

		$merged_count > 1 && static::say("объединил $merged_count задач");
		return new Struct_Domain_Search_IndexTask_Common($expected_conversation_map, array_unique($user_id_list), $first_task_data->extra);
	}

	/**
	 * Выполняет индексацию сообщений.
	 * @long
	 */
	protected static function _indexMessages(Struct_Domain_Search_IndexTask_Common $task_data, array $task_extra, array $conversation_meta):array {

		$need_requeue = true;

		// получаем последний блок, который парсили в рамках задачи
		$last_block_id = static::_getTaskExtraLastBlockid($task_extra);

		// получаем всех участников диалога на момент индексации
		// убираем тех, кто покинул диалог во время действия переиндексации
		$actual_user_id_list   = array_keys($conversation_meta["users"]);
		$to_index_user_id_list = array_intersect($task_data->user_id_list, $actual_user_id_list);

		// группируем пользователей по дате очистки диалога
		// и обязательно его инвертируем, чтобы был отсортирован по убыванию дат
		$user_id_list_grouped_by_clear_till_timestamp = static::_getUserListByClearTillDate($conversation_meta["conversation_map"], $to_index_user_id_list);
		$user_id_list_grouped_by_clear_till_timestamp = array_reverse($user_id_list_grouped_by_clear_till_timestamp, true);

		// в этом массиве будем хранить сообщения, сгруппированные по дате очистки
		// (т.е.) ключ — это дата, определяющий, какой ключ из $user_id_list_grouped_by_clear_till_timestamp
		// необходимо использовать для определения списка пользователей, которым видно это сообщение
		$message_list_grouped_by_clear_till = [];

		// список тредов, которые также нужно поставить на переиндексацию
		$thread_map_list_to_reindex = [];

		// список данных для задач переиндексации превью
		$preview_data_to_reindex = [];

		// фиксируем время начала индексации диалога
		// если диалог очень большой, то нужно будет делать перерывы
		// даже при наполнении очереди индексации (иначе запрос go_event упадет)
		$indexation_started_at = time();

		// это число сообщений, полученных в рамках работы задачи
		// за одну итерацию нужно поставить в очередь ожидаемое число сообщений
		// чтобы не забить очередь задачами индексации одного диалога
		$fetched_message_count = 0;

		// ходим циклом, пока не истечет отведенное время на сбор задач индексации
		while (time() < $indexation_started_at + static::_PER_ITERATION_TIMEOUT_SEC && $fetched_message_count <= static::_FETCH_MESSAGE_LIMIT) {

			// считаем, какие блоки нам нужны для выборки
			$from_block_id = $last_block_id + 1;
			$to_block_id   = $from_block_id + 15;

			$block_id_list = range($from_block_id, $to_block_id);

			// получаем блоки из базы и связи с тредами
			// треды тоже нужно сходу добавить на индексацию
			$message_block_list      = Gateway_Db_CompanyConversation_MessageBlock::getList($conversation_meta["conversation_map"], $block_id_list, true);
			$message_thread_rel_list = Type_Conversation_ThreadRel::getThreadRelByBlockList($conversation_meta["conversation_map"], $block_id_list);

			// выходим, если блоки больше не ищутся
			if (count($message_block_list) === 0) {

				$need_requeue = false;
				break;
			}

			// бежим по всем полученным блоками
			foreach ($message_block_list as $message_block) {

				// перебираем каждое сообщение в блоке
				foreach (Domain_Conversation_Entity_Message_Block_Message::iterate($message_block) as $message) {

					// если сообщение не индексируется, пропускаем сразу
					if (!Domain_Search_Entity_ConversationMessage_Task_Index::isSuitable($message)) {
						continue;
					}

					// получаем таймштамп-индекс для выборки подходящего списка пользователей
					// возможно тут можно немного оптимизировать, чтобы не ходить по циклу, сообщения итерируются по порядку
					$timestamp = static::_pickClearTillTimestampByMessageDate($message, $user_id_list_grouped_by_clear_till_timestamp);

					if ($timestamp === false) {
						continue;
					}

					$message_list_grouped_by_clear_till[$timestamp][] = $message;

					$thread_map_list_to_reindex = static::_fillThreadList($message, $message_thread_rel_list, $thread_map_list_to_reindex);
					$preview_data_to_reindex    = static::_fillPreviewList($message, $preview_data_to_reindex);

					$fetched_message_count++;
				}
			}

			$last_block_id = $to_block_id;
			$task_extra    = static::_setTaskExtraLastBlockId($task_extra, $last_block_id);
		}

		// убираем пустые элементы из списка
		$message_list_grouped_by_clear_till = array_filter($message_list_grouped_by_clear_till);

		// проходимся по всем сообщениям и добавляем их с учетом того,
		// у каких пользователей диалог не почищен на момент сообщения
		foreach ($message_list_grouped_by_clear_till as $timestamp => $message_list) {

			if (count($message_list) === 0) {
				continue;
			}

			// добавляем все задачи в очередь
			// делаем это кусками, чтобы не повесить базу в случае чего
			Domain_Search_Entity_ConversationMessage_Task_Index::queueList($message_list, $user_id_list_grouped_by_clear_till_timestamp[$timestamp]);
		}

		// задачи на индексацию сообщений, добавляем задачу на индексацию тредов этих сообщений
		if (count($thread_map_list_to_reindex) > 0) {
			Domain_Search_Entity_Thread_Task_Reindex::queueList($thread_map_list_to_reindex);
		}

		// добавляем данные задач превью для индексации
		if (count($preview_data_to_reindex) > 0) {
			Domain_Search_Entity_Preview_Task_AttachToConversationMessage::queueList($preview_data_to_reindex, $to_index_user_id_list);
		}

		return [$need_requeue, $task_extra];
	}

	/**
	 * Возвращает массивы пользователей, сгруппированных по дате очистки диалога.
	 *
	 * логика тут такая:
	 *    1) проходим по всем элементам массива, начиная с первой известной даты очистки диалога
	 *    2) для каждой даты фиксируем список пользователей, у которых диалог почищен на эту дату + все пользователи из предыдущей итерации
	 *
	 * т.е. если сходной список был [0 => [1, 2, 3], 15000000 => [4,5], 16000000 => [6]]
	 * то на выходе должно быть: [0 => [1, 2, 3], 15000000 => [1, 2, 3, 4, 5], 16000000 => [1, 2, 3, 4, 5, 6]]
	 * таким образом мы можем легко понять, какой список пользователей для какого сообщения использовать
	 */
	protected static function _getUserListByClearTillDate(string $conversation_map, array $full_user_id_list):array {

		// пытаемся загрузить мету для диалога
		$loaded_dynamic_list  = Domain_Search_Repository_ProxyCache_ConversationDynamic::load([$conversation_map]);
		$conversation_dynamic = $loaded_dynamic_list[$conversation_map];

		$user_id_list_grouped_by_clear_till_timestamp = [];

		foreach ($full_user_id_list as $user_id) {

			$cleared_till = Domain_Conversation_Entity_Dynamic::getClearUntil(
				$conversation_dynamic->user_clear_info,
				$conversation_dynamic->conversation_clear_info,
				$user_id
			);

			$user_id_list_grouped_by_clear_till_timestamp[$cleared_till][] = $user_id;
		}

		ksort($user_id_list_grouped_by_clear_till_timestamp);

		// получаем первый элемент из списка данных очистки
		$current_clear_info_item = [];

		// проходим по всем записям и восстанавливаем список пользователей на каждый timestamp
		foreach ($user_id_list_grouped_by_clear_till_timestamp as $timestamp => $user_id_list) {

			array_push($current_clear_info_item, ...$user_id_list);
			$user_id_list_grouped_by_clear_till_timestamp[$timestamp] = $current_clear_info_item;
		}

		return $user_id_list_grouped_by_clear_till_timestamp;
	}

	/**
	 * Возвращает список идентификаторов пользователей,
	 * у которых диалог не почищен на момент публикации сообщения.
	 */
	protected static function _pickClearTillTimestampByMessageDate(array $message, array $user_id_list_grouped_by_clear_till_timestamp):int|false {

		// для каждого сообщения выбираем подходящий timestamp
		foreach ($user_id_list_grouped_by_clear_till_timestamp as $timestamp => $user_id_list) {

			$created_at = Type_Conversation_Message_Main::getHandler($message)::getCreatedAt($message);

			if ($created_at >= $timestamp) {
				return $timestamp;
			}
		}

		// возможно диалог почищен для всех
		// в таким случае возвращаем ложь
		return false;
	}

	/**
	 * Добавляет превью, если оно есть
	 */
	protected static function _fillPreviewList(array $message, array $preview_list):array {

		if (!Type_Conversation_Message_Main::getHandler($message)::isAttachedPreview($message)) {
			return $preview_list;
		}

		$preview_map    = Type_Conversation_Message_Main::getHandler($message)::getPreview($message);
		$preview_list[] = ["preview_map" => $preview_map, "message_map" => $message["message_map"]];

		return $preview_list;
	}

	/**
	 * Добавляет тред для переиндексации, если нужно.
	 */
	protected static function _fillThreadList(array $message, array $message_thread_rel_list, array $thread_map_list_to_reindex):array {

		if (!isset($message_thread_rel_list[$message["message_map"]])) {
			return $thread_map_list_to_reindex;
		}

		$thread_map_list_to_reindex[] = $message_thread_rel_list[$message["message_map"]]["thread_map"];
		return $thread_map_list_to_reindex;
	}

	# region работа с экстра-данными задачи

	/**
	 * Инициализирует структуру с экстра-данными задачи.
	 */
	protected static function _initTaskExtra():array {

		return [
			"step"          => static::_STEP_CLEAR,
			"last_block_id" => 0,
		];
	}

	/**
	 * Возвращает текущий этап задачи.
	 */
	protected static function _getTaskExtraStep(array $extra):int {

		return $extra["step"];
	}

	/**
	 * Возвращает последнее значение смещения для выборки блоков..
	 */
	protected static function _getTaskExtraLastBlockid(array $extra):int {

		return $extra["last_block_id"];
	}

	/**
	 * Меняет текущий этап задачи на указанный.
	 */
	protected static function _setTaskExtraStep(array $extra, int $step):array {

		$extra["step"] = $step;
		return $extra;
	}

	/**
	 * Устанавливает указанный блок как последний проиндексированный.
	 */
	protected static function _setTaskExtraLastBlockId(array $extra, int $last_block_id):array {

		$extra["last_block_id"] = $last_block_id;
		return $extra;
	}

	# endregion работа с экстра-данными задачи
}

