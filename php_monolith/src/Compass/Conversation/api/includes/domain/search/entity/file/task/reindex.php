<?php declare(strict_types=1);

namespace Compass\Conversation;

use BaseFrame\System\Locale;

/**
 * Задача обновления данных файла.
 * Запускается при успешном парсинге содержимого файла.
 */
class Domain_Search_Entity_File_Task_Reindex extends Domain_Search_Entity_Task {

	/** @var int тип задачи */
	public const TASK_TYPE = Domain_Search_Const::TASK_TYPE_FILE_REINDEX;

	/** @var int сколько задач указанного типа можно брать на индексацию в одну итерацию */
	protected const _PER_ITERATION_LIMIT = 1;

	/** @var int сколько ошибок допустимо для задачи этого типа */
	protected const _ERROR_LIMIT = 3;

	/**
	 * Добавляет задачу в очередь индексации.
	 *
	 * К файлу привязывается search_id сразу после загрузки,
	 * при этом никакие данные пока не нужно индексировать.
	 *
	 * Если появится поиск файлов, то нужно будет проиндексировать имя.
	 */
	public static function queueList(array $file_map_list):void {

		// формируем список сущностей
		$task_list = array_map(
			static fn(string $el) => Struct_Domain_Search_Task::fromDeclaration(static::TASK_TYPE, new Struct_Domain_Search_IndexTask_Entity($el)),
			$file_map_list
		);

		// вставляем данные
		static::_queue(task_list: $task_list);
	}

	/**
	 * @inheritDoc
	 */
	public static function execList(array $task_list):void {

		// формируем список с данными задачи
		$task_data_list = array_map(
			static fn(Struct_Domain_Search_Task $el) => new Struct_Domain_Search_IndexTask_Entity(...$el->data),
			$task_list
		);

		// загружаем все необходимые сущности
		static::_preload($task_data_list);

		// превращаем задачи в строки для вставки
		// и вставляем добавляем их в индекс
		$prepared_list = array_map(static fn(Struct_Domain_Search_IndexTask_Entity $task) => static::_toIndexData($task), $task_data_list);

		foreach ($prepared_list as $replace) {
			$replace !== false && Gateway_Search_Main::replace($replace, Domain_Search_Config_Task::perExecutionComplexityLimit(static::class));
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
	 * @param Struct_Domain_Search_IndexTask_Entity[] $task_data_list
	 */
	protected static function _preload(array $task_data_list):void {

		$file_map_list                  = [];
		$to_resolve_search_id_file_list = [];

		foreach ($task_data_list as $task_data) {

			$file_map_list[]                  = $task_data->entity_map;
			$to_resolve_search_id_file_list[] = new Struct_Domain_Search_AppEntity(Domain_Search_Const::TYPE_FILE, $task_data->entity_map);
		}

		if (count($file_map_list) === 0) {
			return;
		}

		// загружаем сообщения, для индексации нужно тело сообщения
		// поэтому нужно загрузить его из базы данных
		Domain_Search_Repository_ProxyCache_File::load($file_map_list);
		Domain_Search_Repository_ProxyCache_EntitySearchId::load($to_resolve_search_id_file_list);
	}

	/**
	 * Готовит данные для вставки сообщения в индекс из данных задачи индексации.
	 * @return Struct_Domain_Search_Replace[]|false
	 */
	public static function _toIndexData(Struct_Domain_Search_IndexTask_Entity $task_data):Struct_Domain_Search_Replace|false {

		$load = Domain_Search_Repository_ProxyCache_File::load([$task_data->entity_map]);
		$file = reset($load);

		if ($file === false) {
			return false;
		}

		$search_rel = Domain_Search_Repository_ProxyCache_EntitySearchId::load([
			new Struct_Domain_Search_AppEntity(Domain_Search_Const::TYPE_FILE, $file["file_map"])
		]);

		if (!isset($search_rel[$file["file_map"]])) {

			static::yell("[WARN] search rel для файла %s не найдена", $file["file_map"]);
			return false;
		}

		// прогоним название через стеммер
		// контент не будем гонять, чтобы не повесить парсер
		$file_name = Domain_Search_Helper_Stemmer::stemText($file["file_name"], [Locale::LOCALE_ENGLISH, Locale::LOCALE_RUSSIAN]);

		return new Struct_Domain_Search_Replace(
			$search_rel[$file["file_map"]],
			0,
			$file_name,
			$file["content"]
		);
	}
}
