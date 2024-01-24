<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ReturnFatalException;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Поиск совпадений в указанной локации для поискового запроса.
 */
class Domain_Search_Action_FindHits {

	protected const CURRENT_EXCLUDE_CACHE_VERSION = 2;

	public const _RULESET_SPACE                 = 1;
	public const _RULESET_LOCATION_CONVERSATION = 10;
	public const _RULESET_LOCATION_THREAD       = 20;

	// любой подходящий атрибут, используем int max, он точно будет больше значения int32
	// однако на 32-битных системах он может совпасть с полной маской (мы не используем такие)
	protected const _ANY_ATTRIBUTE = PHP_INT_MAX;

	protected const _RULE_BEHAVIOUR_HIT           = "hit";
	protected const _RULE_BEHAVIOUR_NEST_LOCATION = "nest_location";
	protected const _RULE_BEHAVIOUR_ATTACH_EXTRA  = "attach_extra";

	// наборы правил для поиска
	protected const _RULESET_LIST = [

		self::_RULESET_SPACE                 => [

			Domain_Search_Const::TYPE_CONVERSATION_MESSAGE => [
				[
					"attribute_mask_selector" => self::_ANY_ATTRIBUTE,
					"attribute_mask_list"     => [self::_ANY_ATTRIBUTE => true],
					"behaviour"               => self::_RULE_BEHAVIOUR_HIT,
					"data"                    => [],
				],
			],

			Domain_Search_Const::TYPE_THREAD_MESSAGE => [
				[
					"attribute_mask_selector" => self::_ANY_ATTRIBUTE,
					"attribute_mask_list"     => [self::_ANY_ATTRIBUTE => true],
					"behaviour"               => self::_RULE_BEHAVIOUR_HIT,
					"data"                    => [],
				],
			],

			Domain_Search_Const::TYPE_PREVIEW => [
				[
					"attribute_mask_selector" => self::_ANY_ATTRIBUTE,
					"attribute_mask_list"     => [self::_ANY_ATTRIBUTE => true],
					"behaviour"               => self::_RULE_BEHAVIOUR_HIT,
					"data"                    => [],
				],
			],
		],

		// поиск с использованием диалогоа в качестве локации
		self::_RULESET_LOCATION_CONVERSATION => [

			// сообщения диалога берутся как есть,
			// они считаются завершенным совпадением сразу
			Domain_Search_Const::TYPE_CONVERSATION_MESSAGE => [
				[
					"attribute_mask_selector" => self::_ANY_ATTRIBUTE,
					"attribute_mask_list"     => [self::_ANY_ATTRIBUTE => true],
					"behaviour"               => self::_RULE_BEHAVIOUR_HIT,
					"data"                    => [],
				],
			],

			// комментарий оборачивается во вложенный поиск
			// внутри локации треда, к которому он принадлежит
			// сама локация крепится к родительскому сообщению
			Domain_Search_Const::TYPE_THREAD_MESSAGE       => [
				[
					"attribute_mask_selector" => self::_ANY_ATTRIBUTE,
					"attribute_mask_list"     => [self::_ANY_ATTRIBUTE => true],
					"behaviour"               => self::_RULE_BEHAVIOUR_NEST_LOCATION,
					"data"                    => [
						"parent"   => [
							"relation" => "typed",
							"type"     => Domain_Search_Const::TYPE_CONVERSATION_MESSAGE,
						],
						"location" => [
							"relation" => "typed",
							"type"     => Domain_Search_Const::TYPE_THREAD,
						],
						"ruleset"  => [
							Domain_Search_Const::TYPE_THREAD_MESSAGE => [
								[
									"attribute_mask_selector" => self::_ANY_ATTRIBUTE,
									"attribute_mask_list"     => [0 => true],
									"behaviour"               => self::_RULE_BEHAVIOUR_HIT,
									"data"                    => [],
								],
							],
						],
					],
				],
			],

			// для превью нужно сначала получить родительское сообщение
			// и прикрепить к нему информацию о превью
			Domain_Search_Const::TYPE_PREVIEW              => [
				[
					// превью в сообщении диалога, отдаем как часть сообщения
					"attribute_mask_selector" => Domain_Search_Const::ATTRIBUTE_SHARED_BELONG_TO_THREAD,
					"attribute_mask_list"     => [0 => true],
					"behaviour"               => self::_RULE_BEHAVIOUR_ATTACH_EXTRA,
					"data"                    => [
						"parent" => [
							"relation" => "typed",
							"type"     => Domain_Search_Const::TYPE_CONVERSATION_MESSAGE,
						],
					],
				],
				[
					// превью в сообщении треда, отдаем как часть сообщения треда
					// само сообщение заворачивается во вложенный поиск
					"attribute_mask_selector" => Domain_Search_Const::ATTRIBUTE_SHARED_BELONG_TO_THREAD,
					"attribute_mask_list"     => [Domain_Search_Const::ATTRIBUTE_SHARED_BELONG_TO_THREAD => true],
					"behaviour"               => self::_RULE_BEHAVIOUR_NEST_LOCATION,
					"data"                    => [
						"parent"   => [
							"relation" => "typed",
							"type"     => Domain_Search_Const::TYPE_CONVERSATION_MESSAGE,
						],
						"location" => [
							"relation" => "typed",
							"type"     => Domain_Search_Const::TYPE_THREAD,
						],
						"ruleset"  => [
							Domain_Search_Const::TYPE_PREVIEW => [
								// вложенный поиск, цепляем к сообщению-комментарию
								[
									"attribute_mask_selector" => self::_ANY_ATTRIBUTE,
									"attribute_mask_list"     => [self::_ANY_ATTRIBUTE => true],
									"behaviour"               => self::_RULE_BEHAVIOUR_ATTACH_EXTRA,
									"data"                    => [
										"parent" => [
											"relation" => "typed",
											"type"     => Domain_Search_Const::TYPE_THREAD_MESSAGE,
										],
									],
								],
							],
						],
					],
				],
			],

			// для файлов нужно сначала получить родительское сообщение
			// и прикрепить к нему информацию о файле
			Domain_Search_Const::TYPE_FILE                 => [
				[
					// файл в сообщении диалога, отдаем как часть сообщения
					"attribute_mask_selector" => Domain_Search_Const::ATTRIBUTE_SHARED_BELONG_TO_THREAD,
					"attribute_mask_list"     => [0 => true],
					"behaviour"               => self::_RULE_BEHAVIOUR_ATTACH_EXTRA,
					"data"                    => [
						"parent" => [
							"relation" => "typed",
							"type"     => Domain_Search_Const::TYPE_CONVERSATION_MESSAGE,
						],
					],
				],
				[
					// файл в сообщении треда, отдаем как часть сообщения треда
					// само сообщение заворачивается во вложенный поиск
					"attribute_mask_selector" => Domain_Search_Const::ATTRIBUTE_SHARED_BELONG_TO_THREAD,
					"attribute_mask_list"     => [Domain_Search_Const::ATTRIBUTE_SHARED_BELONG_TO_THREAD => true],
					"behaviour"               => self::_RULE_BEHAVIOUR_NEST_LOCATION,
					"data"                    => [
						"parent"   => [
							"relation" => "typed",
							"type"     => Domain_Search_Const::TYPE_CONVERSATION_MESSAGE,
						],
						"location" => [
							"relation" => "typed",
							"type"     => Domain_Search_Const::TYPE_THREAD,
						],
						"ruleset"  => [
							Domain_Search_Const::TYPE_FILE => [
								// вложенный поиск, цепляем к сообщению-комментарию
								[
									"attribute_mask_selector" => self::_ANY_ATTRIBUTE,
									"attribute_mask_list"     => [self::_ANY_ATTRIBUTE => true],
									"behaviour"               => self::_RULE_BEHAVIOUR_ATTACH_EXTRA,
									"data"                    => [
										"parent" => [
											"relation" => "typed",
											"type"     => Domain_Search_Const::TYPE_THREAD_MESSAGE,
										],
									],
								],
							],
						],
					],
				],
			],
		],

		// использование треда как локации для поиска
		self::_RULESET_LOCATION_THREAD       => [

			// сообщения ищутся как самостоятельные совпадения
			Domain_Search_Const::TYPE_THREAD_MESSAGE => [
				[
					"attribute_mask_selector" => self::_ANY_ATTRIBUTE,
					"attribute_mask_list"     => [self::_ANY_ATTRIBUTE => true],
					"behaviour"               => self::_RULE_BEHAVIOUR_HIT,
					"data"                    => [],
				],
			],

			// превью расширяют родительское сообщение
			Domain_Search_Const::TYPE_PREVIEW        => [
				[
					"attribute_mask_selector" => self::_ANY_ATTRIBUTE,
					"attribute_mask_list"     => [self::_ANY_ATTRIBUTE => true],
					"behaviour"               => self::_RULE_BEHAVIOUR_ATTACH_EXTRA,
					"data"                    => [
						"parent" => [
							"relation" => "typed",
							"type"     => Domain_Search_Const::TYPE_THREAD_MESSAGE,
						],
					],
				],
			],

			// превью расширяют родительское сообщение
			Domain_Search_Const::TYPE_FILE           => [
				[
					"attribute_mask_selector" => self::_ANY_ATTRIBUTE,
					"attribute_mask_list"     => [self::_ANY_ATTRIBUTE => true],
					"behaviour"               => self::_RULE_BEHAVIOUR_ATTACH_EXTRA,
					"data"                    => [
						"parent" => [
							"relation" => "typed",
							"type"     => Domain_Search_Const::TYPE_THREAD_MESSAGE,
						],
					],
				],
			],
		],
	];

	/**
	 * Выполняет поиск совпадений в указанной локации.
	 */
	#[ArrayShape([0 => "Struct_Domain_Search_RawHit[]", 1 => "int", 2 => "bool"])]
	public static function run(int $user_id, Struct_Domain_Search_Dto_SearchRequest $params, string $ruleset_name = self::_RULESET_LOCATION_CONVERSATION):array {

		try {

			// пробуем получить search ид локации, если не получилось, то она скорее всего не проиндексирована
			$location_search_id = static::_resolveLocationSearchId($params->location_type, $params->location_key);
		} catch (Domain_Search_Exception_LocationDenied) {
			return [[], 0, false];
		}

		// снимаем метрику производительности
		$execution_time_metric = \BaseFrame\Monitor\Core::metric("search_hits_execution_time_ms");

		$ruleset = static::_RULESET_LIST[$ruleset_name];
		[$hit_row_list, $total_hit_count, $has_next] = static::_getHits($user_id, $location_search_id, $params->morphology_query, $ruleset, $params->limit, $params->offset);

		// фиксируем время исполнения и закрываем метрику производительности
		$execution_time_metric->since()->seal();
		return [static::_toRawHitList($user_id, $hit_row_list), $total_hit_count, $has_next];
	}

	/**
	 * Определяет search_id для локации.
	 * @throws Domain_Search_Exception_LocationDenied
	 */
	protected static function _resolveLocationSearchId(int $location_type, string $location_key):int {

		$location_entity = new Struct_Domain_Search_AppEntity($location_type, $location_key);
		$loaded          = Domain_Search_Repository_ProxyCache_EntitySearchId::load([$location_entity]);

		if (!isset($loaded[$location_key])) {
			throw new Domain_Search_Exception_LocationDenied("location not found");
		}

		return $loaded[$location_key];
	}

	/**
	 * Выполняет поиск по указанному набору правил.
	 */
	#[ArrayShape([0 => "Struct_Domain_Search_HitRaw[]", 1 => "int", 2 => "bool"])]
	protected static function _getHits(int $user_id, int $location_search_id, string $query, array $ruleset, int $limit, int $offset):array {

		// формируем список исключений
		$exclude_data = static::_makeExcludeData($user_id, $location_search_id, $query, $offset);

		// сразу получаем все необходимые сущности
		$fetched_hit_row_list = static::_searchHits($user_id, $location_search_id, $query, $ruleset, $exclude_data, $limit, $offset);

		// фиксируем счетчик совпадений или записываем в него текущее значение
		[$exclude_data, $total_hit_count] = static::_resolveInitialHitCount($exclude_data, static fn() => Gateway_Search_Main::fetchTotalCountFromLastRequest());

		// делаем ремаппинг по search_id для удобства
		// в этом массиве лежат просто объекты, у которых родительские сущности неопределены,
		// их можно использовать только для получения search_id или данных совпадения!
		$unique_fetched_row_list = arrayRemap($fetched_hit_row_list, "search_id");
		static::_setSearchIdsFound($exclude_data, ...array_keys($unique_fetched_row_list));

		// $done_hit_row_list — списки записей, по которым больше не нужно получать данные (фактически результат поиска)
		// $to_wrap_location_hit_row_list — списки записей, которыми нужно расширить родителя (родители попадут в результаты поиска)
		// $to_attach_hit_row_list — списки записей, которые нужно обернуть в поиск внутри родителя (родители попадут в результаты поиска)
		[$done_hit_row_list, $to_attach_hit_row_list, $to_wrap_location_hit_row_list] = static::_distributeHitRowsByBehaviour($fetched_hit_row_list, $ruleset);

		// теперь нужно получить данные для родительских сущностей,
		// которые требуются для расширения совпадений или замены локацией
		$parent_search_id_list = static::_getParentSearchIdList(...$to_attach_hit_row_list, ...$to_wrap_location_hit_row_list);

		// получаем связи всех родительских сущностей
		// это нужно, чтобы понять каких именно родителей мы ищем
		$parent_search_entity_rel_list = Domain_Search_Repository_ProxyCache_SearchIdEntity::load($parent_search_id_list);

		// теперь выбираем тех родителей, которые действительно нужны
		$to_attach_hit_row_list_grouped_by_parent = static::_getRequiredParentSearchIdForAttachExtra($to_attach_hit_row_list, $parent_search_entity_rel_list, $ruleset);
		$to_wrap_hit_row_list_grouped_by_parent   = static::_getRequiredParentSearchIdForNestLocation($to_wrap_location_hit_row_list, $parent_search_entity_rel_list, $ruleset);

		// получаем список все ключей необходимых родителей
		$required_parent_search_id_list = array_unique(array_merge(
			array_keys($to_attach_hit_row_list_grouped_by_parent),
			array_keys($to_wrap_hit_row_list_grouped_by_parent),
			...array_map(static fn(array $to_wrap_hit_row_list):array => array_keys($to_wrap_hit_row_list), $to_wrap_hit_row_list_grouped_by_parent)
		));

		// дальше будет финт ушами, нужно поискать родителя в поиске
		// а если он не найдется, то заменить фейковым hit_row
		$unique_fetched_row_list = static::_fillRequiredParent($user_id, $query, $unique_fetched_row_list, $required_parent_search_id_list, $parent_search_entity_rel_list);

		// начинаем дополнять результаты поиска расширенными
		// совпадения и вложенными локациями
		[$done_hit_row_list, $exclude_data] = static::_attachExtraToHits($done_hit_row_list, $unique_fetched_row_list, $to_attach_hit_row_list_grouped_by_parent, $exclude_data);
		[$done_hit_row_list, $exclude_data] = static::_attachNestedLocation($user_id, $query, $done_hit_row_list, $unique_fetched_row_list, $to_wrap_hit_row_list_grouped_by_parent, $ruleset, $exclude_data);

		// дополняем данными список исключений и сохраняем его
		// предполагаем, в запросе что offset будет смещен на limit
		static::_setSearchIdsFound($exclude_data, ...array_keys($done_hit_row_list));
		static::_writeExcludeData($exclude_data, $limit);

		return [$done_hit_row_list, $total_hit_count, count($fetched_hit_row_list) >= $limit];
	}

	/**
	 * @return Struct_Domain_Search_HitRow[]
	 */
	protected static function _searchHits(int $user_id, int $location_search_id, string $query, array $ruleset, array $exclude_data, int $limit, int $offset):array {

		[$exclude_search_id_list, $exclude_parent_id_list, $offset] = static::_getExcludeParameters($exclude_data, $offset);
		return Gateway_Search_Main::getHits($user_id, array_keys($ruleset), $location_search_id, $query, $exclude_search_id_list, $exclude_parent_id_list, $limit, $offset);
	}

	/**
	 * @param Struct_Domain_Search_HitRow[] $hit_row_list
	 *
	 * @return Struct_Domain_Search_HitRow[][]
	 */
	protected static function _distributeHitRowsByBehaviour(array $hit_row_list, array $ruleset):array {

		$done_hit_row_list             = []; // списки записей, по которым больше не нужно получать данные (фактически результат поиска)
		$to_attach_hit_row_list        = []; // списки записей, которыми нужно расширить родителя (родители попадут в результаты поиска)
		$to_wrap_location_hit_row_list = []; // списки записей, которые нужно обернуть в поиск внутри родителя (родители попадут в результаты поиска)

		foreach ($hit_row_list as $hit_row) {

			// выбираем подходящее правило данного совпадения
			$rule_variety = static::_tryPickSuitableRuleVariety($hit_row, $ruleset);

			if ($rule_variety === false) {
				continue;
			}

			switch ($rule_variety["behaviour"]) {

				case self::_RULE_BEHAVIOUR_HIT:

					self::_debugAction("inserted id {$hit_row->search_id} = {$hit_row->search_id} case hit");
					$done_hit_row_list[$hit_row->search_id] = $hit_row;
					break;
				case self::_RULE_BEHAVIOUR_ATTACH_EXTRA:
					$to_attach_hit_row_list[] = $hit_row;
					break;
				case self::_RULE_BEHAVIOUR_NEST_LOCATION:
					$to_wrap_location_hit_row_list[] = $hit_row;
					break;
				default:
					throw new ReturnFatalException("passed unknown behaviour rule");
			}
		}

		return [$done_hit_row_list, $to_attach_hit_row_list, $to_wrap_location_hit_row_list];
	}

	/**
	 * Формирует полный список parent_search_id для указанного списка совпадения.
	 *
	 * @param Struct_Domain_Search_HitRow ...$hit_row_list
	 *
	 * @return int[]
	 */
	protected static function _getParentSearchIdList(Struct_Domain_Search_HitRow ...$hit_row_list):array {

		$output = array_reduce($hit_row_list, static fn(array $carry, Struct_Domain_Search_HitRow $item) => array_merge($carry, $item->parent_search_id_list), []);
		return array_unique($output);
	}

	/**
	 * Формирует список search_id для расширения родителей.
	 *
	 * @param Struct_Domain_Search_HitRow[]             $hit_row_list
	 * @param Struct_Db_SpaceSearch_EntitySearchIdRel[] $search_entity_rel_list
	 * @param array                                     $ruleset
	 *
	 * @return Struct_Domain_Search_HitRow[][]
	 */
	protected static function _getRequiredParentSearchIdForAttachExtra(array $hit_row_list, array $search_entity_rel_list, array $ruleset):array {

		$output = [];

		foreach ($hit_row_list as $hit_row) {

			// выбираем подходящее правило данного совпадения
			$rule_variety = static::_tryPickSuitableRuleVariety($hit_row, $ruleset);

			if ($rule_variety === false) {
				continue;
			}

			$parent_rule                 = $rule_variety["data"]["parent"];
			$parent_search_id            = static::_getParentSearchIdByRule($hit_row, $search_entity_rel_list, $parent_rule);
			$output[$parent_search_id][] = $hit_row;
		}

		return $output;
	}

	/**
	 * Формирует список search_id для необходимых локаций.
	 *
	 * @param Struct_Domain_Search_HitRow[]             $hit_row_list
	 * @param Struct_Db_SpaceSearch_EntitySearchIdRel[] $search_entity_rel_list
	 * @param array                                     $ruleset
	 *
	 * @return Struct_Domain_Search_HitRow[][][]
	 */
	protected static function _getRequiredParentSearchIdForNestLocation(array $hit_row_list, array $search_entity_rel_list, array $ruleset):array {

		$output = [];

		foreach ($hit_row_list as $hit_row) {

			// выбираем подходящее правило данного совпадения
			$rule_variety = static::_tryPickSuitableRuleVariety($hit_row, $ruleset);

			if ($rule_variety === false) {
				continue;
			}

			// получаем search_id для локации
			$location_rule      = $rule_variety["data"]["location"];
			$location_search_id = static::_getParentSearchIdByRule($hit_row, $search_entity_rel_list, $location_rule);

			// получаем search_id для родителя
			$parent_rule      = $rule_variety["data"]["parent"];
			$parent_search_id = static::_getParentSearchIdByRule($hit_row, $search_entity_rel_list, $parent_rule);

			// вставляем в массив, группируя по локации внутри родителей
			$output[$location_search_id][$parent_search_id][] = $hit_row;
		}

		return $output;
	}

	/**
	 * Получает search_id родителя для hit_row по правилу выборки.
	 */
	protected static function _getParentSearchIdByRule(Struct_Domain_Search_HitRow $hit_row, array $search_entity_rel_list, array $parent_rule):int {

		if ($parent_rule["relation"] === "direct") {
			return $hit_row->direct_parent_search_id;
		}

		if ($parent_rule["relation"] === "typed") {

			foreach ($hit_row->parent_search_id_list as $parent_search_id) {

				if (isset($search_entity_rel_list[$parent_search_id]) && $search_entity_rel_list[$parent_search_id]->entity_type === $parent_rule["type"]) {
					return $parent_search_id;
				}
			}

			return 0;
		}

		throw new ReturnFatalException("passed unknown parent relation");
	}

	/**
	 * Загружает недостающие родительские hit_row элементы.
	 *
	 * @param Struct_Domain_Search_HitRow[]             $unique_fetched_hit_row_list
	 * @param int[]                                     $required_search_id_list
	 * @param Struct_Db_SpaceSearch_EntitySearchIdRel[] $parent_search_entity_rel_list
	 *
	 * @return Struct_Domain_Search_HitRow[]
	 */
	protected static function _fillRequiredParent(int $user_id, string $query, array $unique_fetched_hit_row_list, array $required_search_id_list, array $parent_search_entity_rel_list):array {

		// ищем родителей, внутри них тоже может быть совпадение и его нужно будет подсветить
		// получаем те записи родителей, которые еще не были загружены
		$to_get_from_parent_search_id_list = array_diff($required_search_id_list, array_keys($unique_fetched_hit_row_list));
		$to_get_from_parent_search_id_list = array_diff($to_get_from_parent_search_id_list, array_column($unique_fetched_hit_row_list, "search_id"));
		$to_get_from_parent_search_id_list = array_filter($to_get_from_parent_search_id_list);

		if (count($to_get_from_parent_search_id_list) > 0) {

			// дергаем поиск по родительским сущностям
			$found_parent_hit_row_list = Gateway_Search_Main::getRows($user_id, $query, $to_get_from_parent_search_id_list);

			// добавляем в массив найденных родителей новые записи
			foreach ($found_parent_hit_row_list as $parent_hit_row) {
				$unique_fetched_hit_row_list[$parent_hit_row->search_id] = $parent_hit_row;
			}
		}

		// дальше будет финт ушами, нужно поискать родителя в поиске
		// а если он не найдется, то заменить фейковым hit_row
		foreach ($required_search_id_list as $search_id) {

			// если родитель не был загружен, то формируем фейковую запись
			if (!isset($unique_fetched_hit_row_list[$search_id])) {

				$search_entity_rel                       = $parent_search_entity_rel_list[$search_id];
				$unique_fetched_hit_row_list[$search_id] = new Struct_Domain_Search_HitRow($search_id, $search_entity_rel->entity_type, 0, 0, 0, 0, [], 0);
			}
		}

		return $unique_fetched_hit_row_list;
	}

	/**
	 * Добавляет экстра-данные к совпадениям.
	 *
	 * @param Struct_Domain_Search_HitRow[]   $done_hit_list
	 * @param array                           $loaded_hit_row_list
	 * @param Struct_Domain_Search_HitRow[][] $attachable_hit_row_list_grouped_by_parent
	 * @param array                           $exclude_data
	 *
	 * @return array
	 */
	#[ArrayShape([0 => "Struct_Domain_Search_HitRow[]", 1 => "array"])]
	protected static function _attachExtraToHits(array $done_hit_list, array $loaded_hit_row_list, array $attachable_hit_row_list_grouped_by_parent, array $exclude_data):array {

		foreach ($attachable_hit_row_list_grouped_by_parent as $parent_search_id => $attachable_hit_row_list) {

			// если родитель еще не было добавлен в exclude список,
			// то добавляем и увеличиваем дельту офсета
			// логика такова — если родитель ранее был отдан или найден в текущем запросе,
			// то для следующего запроса его исключение нужно учесть в коррекции лимита
			$exclude_data = static::_setSearchIdsExcluded($exclude_data, $parent_search_id);

			if (!isset($loaded_hit_row_list[$parent_search_id])) {
				continue;
			}

			if (!isset($done_hit_list[$parent_search_id])) {

				self::_debugAction("inserted id {$parent_search_id} = {$loaded_hit_row_list[$parent_search_id]->search_id} case attach extra");
				$done_hit_list[$parent_search_id] = $loaded_hit_row_list[$parent_search_id];
			}

			foreach ($attachable_hit_row_list as $hit_row) {
				$done_hit_list[$parent_search_id]->attachExtra($hit_row);
			}
		}

		return [$done_hit_list, $exclude_data];
	}

	/**
	 * Добавляет в совпадения вложенные локации с отдельными списками совпадений.
	 *
	 * @param Struct_Domain_Search_HitRow[]     $done_hit_row_list
	 * @param Struct_Domain_Search_HitRow[][][] $to_wrap_hit_row_list_grouped_by_location
	 *
	 * @long большая логика
	 */
	#[ArrayShape([0 => "Struct_Domain_Search_HitRow[]", 1 => "array"])]
	protected static function _attachNestedLocation(int $user_id, string $query, array $done_hit_row_list, array $loaded_hit_row_list, array $to_wrap_hit_row_list_grouped_by_location, array $ruleset, array $exclude_data):array {

		// для начала к родителю прицепим
		foreach ($to_wrap_hit_row_list_grouped_by_location as $location_search_id => $to_wrap_hit_row_list_grouped_by_parent) {

			// убираем локацию из следующего запроса, никаких совпадений
			// по ней больше не будет найдено, поиск будет вложенный
			// здесь дельту офсета не меняем, поскольку локация не должна попадать в выборку
			$exclude_data = static::_setParentIdsExcluded($exclude_data, $location_search_id);

			// на всякий случай проверяем наличие локации
			if (!isset($loaded_hit_row_list[$location_search_id])) {
				continue;
			}

			$full_hit_row_list = [];

			foreach ($to_wrap_hit_row_list_grouped_by_parent as $hit_row_list) {
				array_push($full_hit_row_list, ...$hit_row_list);
			}

			// совпадения внутри вложенной локации
			$nested_ruleset = static::_resolveLocationRuleset($full_hit_row_list, $ruleset);
			[$nested_hit_row_list, $total_hit_count] = static::_getHits($user_id, $location_search_id, $query, $nested_ruleset, 10, 0);

			if (count($nested_hit_row_list) === 0) {
				continue;
			}

			foreach ($to_wrap_hit_row_list_grouped_by_parent as $parent_search_id => $hit_row_list) {

				// если родитель еще не был добавлен в exclude список, то добавляем и увеличиваем дельту офсета
				// логика такова — если родитель ранее был отдан или найден в текущем запросе,
				// то для следующего запроса его исключение нужно учесть в коррекции лимита
				$exclude_data = static::_setSearchIdsExcluded($exclude_data, $parent_search_id);

				// поскольку следующий запрос должен полностью исключить локацию
				// из выборки, нужно сместить офсет на количество найденных элементов
				$exclude_data = static::_incExcludeOffsetDelta($exclude_data, count($hit_row_list));

				// на всякий случай проверяем наличие родителя
				if (!isset($loaded_hit_row_list[$parent_search_id])) {
					continue;
				}

				if (!isset($done_hit_row_list[$parent_search_id])) {

					self::_debugAction("inserted id {$parent_search_id} = {$loaded_hit_row_list[$parent_search_id]->search_id} case nested");
					$done_hit_row_list[$parent_search_id] = $loaded_hit_row_list[$parent_search_id];
				}

				// добавляем вложенную локацию к совпадению
				$done_hit_row_list[$parent_search_id]->attachLocation($loaded_hit_row_list[$location_search_id], $nested_hit_row_list, $total_hit_count);
			}
		}

		return [$done_hit_row_list, $exclude_data];
	}

	/**
	 * Формирует набор правил для вложенной локации.
	 *
	 * @param Struct_Domain_Search_HitRow[] $hit_row_list
	 */
	protected static function _resolveLocationRuleset(array $hit_row_list, array $ruleset):array {

		$output              = [];
		$processed_type_list = [];

		foreach ($hit_row_list as $hit_row) {

			if (isset($processed_type_list[$hit_row->type])) {
				continue;
			}

			foreach ($ruleset[$hit_row->type] as $rule_variety) {

				if ($rule_variety["behaviour"] !== self::_RULE_BEHAVIOUR_NEST_LOCATION) {
					continue;
				}

				foreach ($rule_variety["data"]["ruleset"] as $type => $nested_ruleset) {

					foreach ($nested_ruleset as $nested_rule) {
						$output[$type][] = $nested_rule;
					}
				}
			}

			// отмечаем тип как обработанный
			$processed_type_list[$hit_row->type] = true;
		}

		return $output;
	}

	/**
	 * Пытается выбрать подходящее правило из набора правил.
	 */
	protected static function _tryPickSuitableRuleVariety(Struct_Domain_Search_HitRow $hit_row, array $ruleset):array|false {

		foreach ($ruleset[$hit_row->type] as $rule_variety) {

			// если указан ANY_ATTRIBUTE,
			// то считаем правило подходящим без проверки атрибутов
			if ($rule_variety["attribute_mask_selector"] === static::_ANY_ATTRIBUTE) {
				return $rule_variety;
			}

			// оставляем только нужные биты, ненужные единицы в маске обнулятся
			$attribute_mask = $rule_variety["attribute_mask_selector"] & $hit_row->attribute_mask;

			// проверяем, что полученная маска есть в списке поддерживаемых вариацией правила
			// если нет, то переходим к следующему правилу
			if (isset($rule_variety["attribute_mask_list"][$attribute_mask])) {
				return $rule_variety;
			}
		}

		return false;
	}

	/**
	 * Конвертирует структуру из базы в структуру для работы с сущностью совпадения.
	 *
	 * @param int                           $user_id
	 * @param Struct_Domain_Search_HitRow[] $hit_row_list
	 *
	 * @return Struct_Domain_Search_RawHit[]
	 */
	protected static function _toRawHitList(int $user_id, array $hit_row_list):array {

		$search_id_list         = static::_collectSearchIdFromHitRows($hit_row_list);
		$search_entity_rel_list = Domain_Search_Repository_ProxyCache_SearchIdEntity::load($search_id_list);

		return array_map(static fn(Struct_Domain_Search_HitRow $el) => static::_convertHitRowToRawHit($user_id, $el, $search_entity_rel_list), $hit_row_list);
	}

	/**
	 * @param Struct_Domain_Search_HitRow[] $hit_row_list
	 * @param int[]                         $output
	 *
	 * @return int[]
	 */
	protected static function _collectSearchIdFromHitRows(array $hit_row_list, array $output = []):array {

		foreach ($hit_row_list as $hit_row) {

			$output[] = $hit_row->search_id;

			foreach ($hit_row->extra_list as $extra_hit_row) {
				$output[] = $extra_hit_row->search_id;
			}

			foreach ($hit_row->nested_location_list as $nested_location) {

				$output[] = $nested_location->hit_row->search_id;
				$output   = static::_collectSearchIdFromHitRows($nested_location->nested_hit_row_list, $output);
			}
		}

		return array_unique($output);
	}

	/**
	 * @param int                                       $user_id
	 * @param Struct_Domain_Search_HitRow               $hit_row
	 * @param Struct_Db_SpaceSearch_EntitySearchIdRel[] $search_entity_rel_list
	 *
	 * @return Struct_Domain_Search_RawHit
	 */
	protected static function _convertHitRowToRawHit(int $user_id, Struct_Domain_Search_HitRow $hit_row, array $search_entity_rel_list):Struct_Domain_Search_RawHit {

		// формируем attach-extra данные для совпадения
		$extra_list = array_map(
			static fn(Struct_Domain_Search_HitRow $el) => static::_convertHitRowToRawHit($user_id, $el, $search_entity_rel_list),
			$hit_row->extra_list
		);

		$nested_location_list = [];

		// формируем данные для вложенных локаций
		foreach ($hit_row->nested_location_list as $nested_location) {

			$nested_location_list[] = new Struct_Domain_Search_RawHitNestedLocation(
				static::_convertHitRowToRawHit($user_id, $nested_location->hit_row, $search_entity_rel_list),
				array_map(
					static fn(Struct_Domain_Search_HitRow $el) => static::_convertHitRowToRawHit($user_id, $el, $search_entity_rel_list),
					$nested_location->nested_hit_row_list,
				),
				$nested_location->total_hit_count,
				max(array_column($nested_location->nested_hit_row_list, "updated_at")),
			);
		}

		return new Struct_Domain_Search_RawHit(
			$user_id,
			0,
			$search_entity_rel_list[$hit_row->search_id],
			$hit_row->parent_search_id_list,
			$hit_row->updated_at,
			$hit_row->field_hit_mask,
			$nested_location_list,
			$extra_list,
		);
	}

	/**
	 * Возвращает список исключений, которые не нужно искать.
	 * Потенциально они уже найдены ранее и отданы как локации/родители.
	 */
	protected static function _makeExcludeData(int $user_id, int $location_search_id, string $query, int $offset):array {

		// формируем ключ
		$key    = "$user_id:$location_search_id:$query:$offset";
		$cached = ShardingGateway::cache()->get($key);

		// проверяем, что кэш есть и у него корректная версия
		if ($cached === false || !isset($cached["version"]) || $cached["version"] !== static::CURRENT_EXCLUDE_CACHE_VERSION) {

			return [
				"data"                    => [
					"user_id"            => $user_id,
					"location_search_id" => $location_search_id,
					"query"              => $query,
					"offset"             => $offset,
				],
				"found_search_id_list"    => [],
				"excluded_search_id_list" => [],
				"excluded_parent_id_list" => [],
				"offset_delta"            => $offset,
				"version"                 => static::CURRENT_EXCLUDE_CACHE_VERSION,
				"initial_hit_count"       => -1,
			];
		}

		return $cached;
	}

	/**
	 * Возвращает данные для запроса с учетом ранее составленного списка исключений.
	 */
	#[ArrayShape([0 => "int[]", 1 => "int[]", 2 => "int"])]
	protected static function _getExcludeParameters(array $exclude_data, int $offset):array {

		return [
			array_keys($exclude_data["excluded_search_id_list"]),
			array_keys($exclude_data["excluded_parent_id_list"]),
			$offset - $exclude_data["offset_delta"],
		];
	}

	/**
	 * Добавляем список search_id совпадений в исключение для следующего запроса.
	 * То есть при пагинации эти search_id будут исключены из ответа.
	 *
	 * @param array $exclude_data
	 * @param int   ...$search_id_list
	 *
	 * @return array
	 */
	protected static function _setSearchIdsExcluded(array $exclude_data, int ...$search_id_list):array {

		foreach ($search_id_list as $search_id) {

			if (!isset($exclude_data["found_search_id_list"][$search_id])) {

				$exclude_data["found_search_id_list"][$search_id]    = 1;
				$exclude_data["excluded_search_id_list"][$search_id] = 1;
			} else {
				$exclude_data["offset_delta"]++;
			}
		}

		return $exclude_data;
	}

	/**
	 * Добавляем список search_id в список найденных сущностей.
	 *
	 * @param array $exclude_data
	 * @param int   ...$search_id_list
	 *
	 * @return array
	 */
	protected static function _setSearchIdsFound(array $exclude_data, int ...$search_id_list):array {

		foreach ($search_id_list as $search_id) {

			if (!isset($exclude_data["found_search_id_list"][$search_id])) {
				$exclude_data["found_search_id_list"][$search_id] = 1;
			}
		}

		return $exclude_data;
	}

	/**
	 * Добавляем список search_id в исключение для следующего запроса.
	 * То есть при пагинации все совпадения, принадлежащие локации будут исключены.
	 *
	 * @param array $exclude_data
	 * @param int   ...$parent_id_list
	 *
	 * @return array
	 */
	protected static function _setParentIdsExcluded(array $exclude_data, int ...$parent_id_list):array {

		foreach ($parent_id_list as $parent_id) {
			$exclude_data["excluded_parent_id_list"][$parent_id] = 1;
		}

		return $exclude_data;
	}

	/**
	 * Увеличивает дельту смещения в данных исключения.
	 */
	protected static function _incExcludeOffsetDelta(array $exclude_data, int $delta):array {

		$exclude_data["offset_delta"] += $delta;
		return $exclude_data;
	}

	/**
	 * Определяет актуально значение счетчика совпадений.
	 */
	protected static function _resolveInitialHitCount(array $exclude_data, callable $suggested_count_fn):array {

		if ($exclude_data["initial_hit_count"] === -1) {
			$exclude_data["initial_hit_count"] = $suggested_count_fn();
		}

		return [$exclude_data, $exclude_data["initial_hit_count"]];
	}

	/**
	 * Возвращает список исключений, которые не нужно искать.
	 * Потенциально они уже найдены ранее и отданы как локации/родители.
	 */
	protected static function _writeExcludeData(array $exclude_data, int $limit):void {

		// формируем ключ
		$data        = $exclude_data["data"];
		$next_offset = $data["offset"] + $limit;
		$key         = "{$data["user_id"]}:{$data["location_search_id"]}:{$data["query"]}:{$next_offset}";

		// подменяем смещение для следующей страницы
		$exclude_data["data"]["offset"] = $next_offset;

		// записываем данные
		ShardingGateway::cache()->set($key, $exclude_data);
	}

	/**
	 * Дебажим что-либо в экшене в отдельный лог-файл
	 */
	protected static function _debugAction(string $debug_text):void {

		Type_System_Admin::log("search_action_find_hits", $debug_text);
	}
}