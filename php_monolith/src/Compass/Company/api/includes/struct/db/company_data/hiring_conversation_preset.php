<?php

namespace Compass\Company;

/**
 * Класс-структура для таблицы company_data.hiring_conversation_preset
 */
class Struct_Db_CompanyData_HiringConversationPreset {

	public int    $hiring_conversation_preset_id;
	public int    $status;
	public int    $creator_user_id;
	public int    $created_at;
	public int    $updated_at;
	public string $title;
	public array  $conversation_list;

	/**
	 * Struct_Db_CompanyData_HiringConversationPreset constructor.
	 *
	 * @param int    $hiring_conversation_preset_id
	 * @param int    $status
	 * @param int    $creator_user_id
	 * @param int    $created_at
	 * @param int    $updated_at
	 * @param string $title
	 * @param array  $conversation_list
	 */
	public function __construct(int    $hiring_conversation_preset_id,
					    int    $status,
					    int    $creator_user_id,
					    int    $created_at,
					    int    $updated_at,
					    string $title,
					    array  $conversation_list) {

		$this->hiring_conversation_preset_id = $hiring_conversation_preset_id;
		$this->status                        = $status;
		$this->creator_user_id               = $creator_user_id;
		$this->created_at                    = $created_at;
		$this->updated_at                    = $updated_at;
		$this->title                         = $title;
		$this->conversation_list             = $conversation_list;
	}
}