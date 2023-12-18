<?php

namespace Compass\Conversation;

use BaseFrame\System\Locale;

/**
 * Базовый класс совпадения.
 * Нужен в основном для типизации и удобного рефакторинга.
 */
abstract class Domain_Search_Entity_Hit {

	public const    HIT_TYPE         = 0;
	protected const PREVIEW_HIT_TYPE = 0;
	protected const FILE_HIT_TYPE    = 0;

	/**
	 * Загружает список всех совпадений соответствующего типа.
	 *
	 * @param int                                    $user_id
	 * @param Struct_Domain_Search_RawHit[]          $hit_list
	 * @param Struct_Domain_Search_Dto_SearchRequest $params
	 */
	abstract public static function loadSuitable(int $user_id, array $hit_list, Struct_Domain_Search_Dto_SearchRequest $params):array;

	/**
	 * Загружаем сущности совпадений по файлам, превью
	 *
	 * @param Struct_Domain_Search_RawHit[] $hit_list
	 *
	 * @return array
	 */
	protected static function _loadExtraEntities(array $hit_list):array {

		// собираем список всех вложенных совпадений
		$extra_hit_list = array_filter(array_merge(...array_map(static fn(Struct_Domain_Search_RawHit $hit):array => $hit->extra, $hit_list)));

		// получаем список всех file_map для совпадений по файлам
		$file_map_list = array_map(
			static fn(Struct_Domain_Search_RawHit $hit):string => $hit->entity_rel->entity_map,
			array_filter($extra_hit_list, static fn(Struct_Domain_Search_RawHit $hit):bool => $hit->entity_rel->entity_type === Domain_Search_Const::TYPE_FILE)
		);

		// получаем список всех preview_map для совпадений по превью
		$preview_map_list = array_map(
			static fn(Struct_Domain_Search_RawHit $hit):string => $hit->entity_rel->entity_map,
			array_filter($extra_hit_list, static fn(Struct_Domain_Search_RawHit $hit):bool => $hit->entity_rel->entity_type === Domain_Search_Const::TYPE_PREVIEW)
		);

		// подгружаем все сущности
		$file_list    = Domain_Search_Repository_ProxyCache_File::load($file_map_list);
		$preview_list = Domain_Search_Repository_ProxyCache_Preview::load($preview_map_list);

		return [$file_list, $preview_list];
	}

	/**
	 * Формирует массив с именами спотом для превью.
	 */
	protected static function _makeExtraPreviewHitSpotNameList(string $header, string $body):array {

		return ["header" => $header, "body" => $body];
	}

	/**
	 * Формируем структуру совпадения в сущности файла.
	 * @return Struct_Domain_Search_SpotDetail[]
	 */
	protected static function _makeExtraPreviewHitSpotDetails(array $preview_item, Struct_Domain_Search_RawHit $extended_hit, Struct_Domain_Search_Dto_SearchRequest $query_param, array $spot_name_list, array|null $extra = null):array {

		$output = [];

		// если совпадение только по заголовку (тайтлу) превью
		if ($extended_hit->field_mask & static::_HIT_SPOT_PREVIEW_HEADER) {

			[$cleared_source_text, $output_text, $highlighted_list] = Domain_Search_Helper_Highlight::highlight(
				Type_Preview_Main::getTitle($preview_item),
				$query_param->raw_query,
				[$query_param->user_locale, Locale::LOCALE_ENGLISH]
			);

			$output[] = new Struct_Domain_Search_SpotDetail(
				$spot_name_list["header"], $extra, new Struct_Domain_Search_MessageHighlight($cleared_source_text, $output_text, $highlighted_list)
			);
		}

		// если совпадение только по телу (описанию) превью
		if ($extended_hit->field_mask & static::_HIT_SPOT_PREVIEW_BODY) {

			[$cleared_source_text, $output_text, $highlighted_list] = Domain_Search_Helper_Highlight::highlight(
				Type_Preview_Main::getDescription($preview_item), $query_param->raw_query, [$query_param->user_locale, Locale::LOCALE_ENGLISH]
			);

			$output[] = new Struct_Domain_Search_SpotDetail(
				$spot_name_list["body"], $extra, new Struct_Domain_Search_MessageHighlight($cleared_source_text, $output_text, $highlighted_list)
			);
		}

		return $output;
	}

	/**
	 * Формирует массив с именами спотом для файла.
	 */
	protected static function _makeExtraFileHitSpotNameList(string $header, string $body):array {

		return ["file_name" => $header, "file_content" => $body];
	}

	/**
	 * Формируем структуру совпадения в сущности файла.
	 * @return Struct_Domain_Search_SpotDetail[]
	 */
	protected static function _makeExtraFileHitSpotDetails(array $file, Struct_Domain_Search_RawHit $extended_hit, Struct_Domain_Search_Dto_SearchRequest $query_param, array $spot_name_list, array|null $extra = null):array {

		$output = [];

		// если нашли совпадение в названии файла
		if ($extended_hit->field_mask & static::_HIT_SPOT_FILE_NAME) {

			[$cleared_source_text, $output_text, $highlighed_list] = Domain_Search_Helper_Highlight::highlight(
				$file["file_name"] ?? "Unknown",
				$query_param->morphology_query,
				[$query_param->user_locale, Locale::LOCALE_ENGLISH]
			);

			$output[] = new Struct_Domain_Search_SpotDetail(
				$spot_name_list["file_name"],
				$extra,
				new Struct_Domain_Search_MessageHighlight($cleared_source_text, $output_text, $highlighed_list),
			);
		}

		// если нашли совпадение в содержимом файла
		if ($extended_hit->field_mask & static::_HIT_SPOT_FILE_CONTENT) {
			$output[] = new Struct_Domain_Search_SpotDetail($spot_name_list["file_content"], $extra, null);
		}

		return $output;
	}
}