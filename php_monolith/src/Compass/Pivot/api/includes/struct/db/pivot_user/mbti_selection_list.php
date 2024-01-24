<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_user_{10m}.mbti_selection_list
 */
class Struct_Db_PivotUser_MbtiSelectionList {

	public int    $user_id;
	public string $mbti_type;
	public string $text_type;
	public array  $color_selection_list;

	public function __construct(
		int    $user_id,
		string $mbti_type,
		string $text_type,
		array  $color_selection_list
	) {

		$this->user_id              = $user_id;
		$this->mbti_type            = $mbti_type;
		$this->text_type            = $text_type;
		$this->color_selection_list = $color_selection_list;
	}
}