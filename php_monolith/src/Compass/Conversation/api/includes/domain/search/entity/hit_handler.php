<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Базовый класс для работы с совпадениями поиска.
 */
class Domain_Search_Entity_HitHandler {

	/** @var Domain_Search_Entity_Hit[] список известных типов локаций */
	protected const _KNOWN_HIT_TYPE_LIST = [
		Domain_Search_Entity_ConversationMessage_Hit::class,
		Domain_Search_Entity_ThreadMessage_Hit::class,
	];

	/**
	 * Загружает указанный список локаций.
	 *
	 * @param int                                    $user_id
	 * @param Struct_Domain_Search_RawHit[]          $raw_hit_list
	 * @param Struct_Domain_Search_Dto_SearchRequest $params
	 *
	 * @noinspection DuplicatedCode
	 * @return array
	 */
	public static function load(int $user_id, array $raw_hit_list, Struct_Domain_Search_Dto_SearchRequest $params):array {

		// снимаем метрику производительности
		$execution_time_metric = \BaseFrame\Monitor\Core::metric("load_hits_execution_time_ms")->since();

		$raw_hit_list_grouped_by_type = [];

		foreach ($raw_hit_list as $raw_hit) {
			$raw_hit_list_grouped_by_type[$raw_hit->entity_rel->entity_type][] = $raw_hit;
		}

		$output = [];

		// проходимся по всем известным типам совпадений и
		// пытаемся загрузить соответствующие элементы
		foreach (static::_KNOWN_HIT_TYPE_LIST as $hit_class) {

			if (!isset($raw_hit_list_grouped_by_type[$hit_class::HIT_TYPE])) {
				continue;
			}

			$output[] = $hit_class::loadSuitable($user_id, $raw_hit_list, $params);
		}

		$hit_list = array_filter(array_merge(...$output));
		usort($hit_list, static fn(object $a, object $b) => ($b->updated_at ?? 0) <=> ($a->updated_at ?? 0));

		// фиксируем и закрываем метрику
		$execution_time_metric->since()->seal();
		return $hit_list;
	}

	/**
	 * Подготавливает совпадения для отдачи клиенту.
	 */
	public static function toApi(int $user_id, array $hit_list):array {

		$output = [];

		foreach ($hit_list as $hit) {

			$output[] = match ($hit::class) {

				Struct_Domain_Search_Hit_ConversationMessage::class => Domain_Search_Entity_ConversationMessage_Hit::toApi($hit, $user_id),
				Struct_Domain_Search_Hit_ThreadMessage::class => Domain_Search_Entity_ThreadMessage_Hit::toApi($hit, $user_id),
				default => throw new ParseFatalException("passed unknown object type")
			};
		}

		return $output;
	}
}
