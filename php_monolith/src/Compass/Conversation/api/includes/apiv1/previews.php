<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Request\ParamException;

/**
 * контроллер, отвечающий за взаимодействие с сущностью preview
 */
class Apiv1_Previews extends \BaseFrame\Controller\Api {

	public const ALLOW_METHODS = [
		"getBatching",
	];

	protected const _MAX_PREVIEWS_COUNT = 50; // максимальное количество превью в запросе

	/**
	 * получить информацию о запрошенных превью
	 *
	 * @return mixed
	 *
	 * @throws ParamException
	 */
	public function getBatching():array {

		$preview_key_list = $this->post("?a", "preview_key_list");

		Gateway_Bus_Statholder::inc("previews", "row20");

		// оставляем только уникальные значения в массиве
		$preview_key_list = array_unique($preview_key_list);

		$this->_throwIfPreviewListIsIncorrect($preview_key_list);
		$preview_map_list = $this->_doDecryptPreviewKeyList($preview_key_list);

		// получаем записи с urlPreview из базы
		$preview_list = Type_Preview_Main::getAll($preview_map_list);

		// разделяем все превью на два списка
		$not_deleted_preview_list = [];
		$deleted_preview_map_list = [];

		// пробегаемся по всем превью полученным из базы
		$exist_preview_map_list = [];
		foreach ($preview_list as $item) {

			// заносим preview_map всех превью что лежат в базе
			$exist_preview_map_list[$item["preview_map"]] = $item["preview_map"];

			// если превью было специально удалено
			if ($item["is_deleted"] == 1) {

				$deleted_preview_map_list[] = $item["preview_map"];
				continue;
			}
			$not_deleted_preview_list[] = $item;
		}

		// проверяем, нашлись ли все запрошенные превью в базе
		foreach ($preview_map_list as $item) {

			if (!isset($exist_preview_map_list[$item])) {
				$deleted_preview_map_list[] = $item;
			}
		}

		// форматируем под клиент
		$not_deleted_preview_list = $this->_formatPreviewList($not_deleted_preview_list);

		// формируем массив из найденых и не найденных превью
		$output = $this->_makeGetBatchingOutput($not_deleted_preview_list, $deleted_preview_map_list);
		Gateway_Bus_Statholder::inc("previews", "row25");
		return $this->ok($output);
	}

	// выбрасываем ошибку, если список превью некорректный
	protected function _throwIfPreviewListIsIncorrect(array $preview_list):void {

		// если пришел пустой массив превью
		if (count($preview_list) < 1) {

			Gateway_Bus_Statholder::inc("previews", "row21");
			throw new ParamException("passed empty preview_list");
		}

		// если пришел слишком большой массив
		if (count($preview_list) > self::_MAX_PREVIEWS_COUNT) {

			Gateway_Bus_Statholder::inc("previews", "row22");
			throw new ParamException("passed preview_list biggest than max");
		}
	}

	// преобразуем пришедшие ключи в map
	protected function _doDecryptPreviewKeyList(array $preview_list):array {

		$preview_map_list = [];
		foreach ($preview_list as $item) {

			$key = \CompassApp\Pack\Main::checkCorrectKey($item);

			// преобразуем key в map
			$preview_map = \CompassApp\Pack\Preview::tryDecrypt($key);

			// добавляем превью в массив
			$preview_map_list[] = $preview_map;
		}

		return $preview_map_list;
	}

	// форматируем список превью
	protected function _formatPreviewList(array $preview_list):array {

		$output = [];
		foreach ($preview_list as $item) {

			// приводим превью к формату
			$temp     = Type_Preview_Formatter::prepareForFormat($item, $item["preview_map"]);
			$output[] = Apiv1_Format::urlPreview($temp);
		}

		return $output;
	}

	// формируем ответ для метода previews.getBatching
	protected function _makeGetBatchingOutput(array $not_deleted_preview_list, array $deleted_preview_map_list):array {

		$deleted_preview_key_list = [];

		// если массив map удаленных превью не пустой
		if (count($deleted_preview_map_list) > 0) {

			Gateway_Bus_Statholder::inc("previews", "row24", count($deleted_preview_map_list));

			// преобразуем map в key
			foreach ($deleted_preview_map_list as $item) {
				$deleted_preview_key_list[] = \CompassApp\Pack\Preview::doEncrypt($item);
			}
		}

		return [
			"preview_list"             => (array) $not_deleted_preview_list,
			"deleted_preview_key_list" => (array) $deleted_preview_key_list,
		];
	}
}
