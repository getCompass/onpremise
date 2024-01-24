<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ParamException;

/**
 * контроллер, отвечающий за файлы в чате
 */
class Apiv2_Conversations_Files extends \BaseFrame\Controller\Api {

	public const ALLOW_METHODS = [
		"get",
	];

	public const MEMBER_ACTIVITY_METHOD_LIST = [];

	protected const _MAX_FILES_PER_REQUEST = 60;

	/**
	 * Метод для получения чатов
	 *
	 * @return array
	 * @throws CaseException
	 * @throws ParamException
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_UnpackHasFailed
	 */
	public function get():array {

		$conversation_key = $this->post(\Formatter::TYPE_STRING, "conversation_key");
		$count            = $this->post(\Formatter::TYPE_INT, "count", self::_MAX_FILES_PER_REQUEST);
		$below_id         = $this->post(\Formatter::TYPE_INT, "below_id", 0);
		$type_list        = $this->post(\Formatter::TYPE_ARRAY, "type_list", []);
		$filter_self_only = $this->post(\Formatter::TYPE_INT, "filter_self_only", 0);
		$parent_type_list = $this->post(\Formatter::TYPE_ARRAY, "parent_type_list", []);

		$conversation_map = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);

		if ($count < 1 || $below_id < 0 || $count > self::_MAX_FILES_PER_REQUEST) {
			throw new ParamException("invalid parameters");
		}

		$below_id = $below_id > 0 ? $below_id : MAX_TIMESTAMP_VALUE;

		try {

			[$conversation_file_list, $next_below_id] = Domain_Conversation_Scenario_Apiv2::getFiles(
				$this->user_id, $conversation_map, $count, $below_id, $type_list, $filter_self_only, $parent_type_list
			);
		} catch (cs_UserIsNotMember) {
			throw new CaseException(2218001, "user is not member of conversation");
		}

		$this->action->users(array_column($conversation_file_list, "user_id"));

		$output_file_list = Apiv2_Format::conversationFileList($conversation_file_list);

		return $this->ok([
			"file_list"     => (array) $output_file_list,
			"next_below_id" => (int) $next_below_id,
		]);
	}
}
