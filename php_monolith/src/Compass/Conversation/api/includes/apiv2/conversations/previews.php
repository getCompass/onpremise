<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ParamException;

/**
 * контроллер, отвечающий за превью в чате
 */
class Apiv2_Conversations_Previews extends \BaseFrame\Controller\Api {

	public const ALLOW_METHODS = [
		"get",
	];

	public const MEMBER_ACTIVITY_METHOD_LIST = [];

	protected const _MAX_PREVIEWS_PER_REQUEST = 60;

	/**
	 * Метод для получения превью
	 *
	 * @return array
	 * @throws CaseException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_UnpackHasFailed
	 */
	public function get():array {

		$conversation_key = $this->post(\Formatter::TYPE_STRING, "conversation_key");
		$count            = $this->post(\Formatter::TYPE_INT, "count", self::_MAX_PREVIEWS_PER_REQUEST);
		$offset           = $this->post(\Formatter::TYPE_INT, "offset", 0);
		$filter_self_only = $this->post(\Formatter::TYPE_INT, "filter_self_only", 0);

		$conversation_map = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);

		if ($count < 1 || $offset < 0 || $count > self::_MAX_PREVIEWS_PER_REQUEST) {
			throw new ParamException("invalid parameters");
		}

		try {

			[$conversation_preview_list, $has_next] = Domain_Conversation_Scenario_Apiv2::getPreviews($this->method_version,
				$this->user_id, $conversation_map, $count, $offset, $filter_self_only);
		} catch (cs_UserIsNotMember) {
			throw new CaseException(2218001, "user is not member of conversation");
		}

		$this->action->users(array_column($conversation_preview_list, "user_id"));

		$output_preview_list = Apiv2_Format::conversationPreviewList($conversation_preview_list);

		return $this->ok([
			"preview_list" => (array) $output_preview_list,
			"has_next"     => (int) $has_next,
		]);
	}
}
