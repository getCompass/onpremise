<?php declare(strict_types = 1);

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ReturnFatalException;

require_once "/app/src/Compass/Conversation/api/includes/type/script/input_parser.php";
require_once "/app/src/Compass/Conversation/api/includes/type/script/input_helper.php";
require_once "/app/src/Modules/vendor/service/php_base_frame/system/functions.php";

$company_url             = _getCompanyUrl();
$space_id                = _getSpaceId();
$is_dry                  = _getDry();
$group_export_file_path  = _getGroupExportFilePath();
$single_export_file_path = _getSingleExportFilePath();

putenv("COMPANY_ID=$space_id");
$_SERVER["HTTP_HOST"] = $company_url;

require_once __DIR__ . "/../../../../../../start.php";
set_time_limit(0);
ini_set("memory_limit", "4096M");
ini_set("display_errors", "1");

/**
 * Экспортируем сообщения чатов в файл
 */
class Migration_Export_Messages {

	protected bool $_is_dry;

	/** @var int ID пространства */
	private int $space_id;

	public function __construct(bool $is_dry, int $space_id) {

		$this->space_id = $space_id;
		$this->_is_dry  = $is_dry;
	}

	/**
	 * @long
	 */
	public function run(string $group_export_file_path, string $single_export_file_path):void {

		$group_file_dir  = dirname($group_export_file_path);
		$single_file_dir = dirname($single_export_file_path);

		// если директория куда ранее экспортировали чаты отсутствует, то кидаем ошибку
		if (!is_dir($group_file_dir) || !is_dir($single_file_dir)) {
			throw new ReturnFatalException("don't find directory for export");
		}

		console("начинаем экспорт сообщений чатов из Compass Saas");

		// получаем список диалоги Compass
		try {

			if (!file_exists($group_export_file_path)) {

				$group_export_conversation_list = [];
				console(redText("Пропускам групповые чаты. Не смогли найти файл экспорта групповых чатов {$group_export_file_path}"));
			} else {
				$group_export_conversation_list = json_decode(file_get_contents($group_export_file_path), true, flags: JSON_THROW_ON_ERROR);
			}
		} catch (\JsonException) {
			throw new ReturnFatalException("could not get file contents for file path: {$group_export_file_path}");
		}

		// для каждого чата экспортируем блоки сообщений
		foreach ($group_export_conversation_list as $export_conversation) {
			self::_doExportMessageBlocks($export_conversation, $group_file_dir);
		}

		console("экспортированы сообщения для " . count($group_export_conversation_list) . " чатов");

		// получаем список диалоги Compass
		try {

			if (!file_exists($single_export_file_path)) {

				$single_export_conversation_list = [];
				console(redText("Пропускам сингл чаты. Не смогли найти файл экспорта сингл чатов {$single_export_file_path}"));
			} else {
				$single_export_conversation_list = json_decode(file_get_contents($single_export_file_path), true, flags: JSON_THROW_ON_ERROR);
			}
		} catch (\JsonException) {
			throw new ReturnFatalException("could not get file contents for file path: {$single_export_file_path}");
		}

		// для каждого чата экспортируем блоки сообщений
		foreach ($single_export_conversation_list as $export_conversation) {
			self::_doExportMessageBlocks($export_conversation, $single_file_dir);
		}

		console("экспортированы сообщения для " . count($single_export_conversation_list) . " чатов");

		console(greenText("экспортирование сообщений чатов завершено"));
	}

	/**
	 * Выполняем экспорт сообщений
	 *
	 * @param array  $export_conversation
	 * @param string $file_dir
	 *
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \JsonException
	 * @long
	 */
	protected function _doExportMessageBlocks(array $export_conversation, string $file_dir):void {

		$block_id_list          = [];
		$save_message_list      = [];
		$user_hidden_by_message = [];
		do {

			$conversation_map = $export_conversation["id"];

			// получаем блоки сообщений
			[$message_block_list, $current_block_id_list, $prev_block_id_list] = self::_getMessageBlocks(
				$conversation_map, $export_conversation["compass_extra"]["conversation_dynamic"], $block_id_list
			);

			$block_id_list = $prev_block_id_list;

			$block_id_list_by_conversation_map = [$conversation_map => $current_block_id_list];

			if ($this->_is_dry) {

				console("DRY-RUN - будет экспортировано " . count($message_block_list) . " блоки сообщений (" . toJson($current_block_id_list) . ") для чата: {$conversation_map}");
				continue;
			}

			// получаем связь сообщений и тредов
			$thread_rel_list                     = Gateway_Db_CompanyConversation_MessageThreadRel::getSpecifiedList($block_id_list_by_conversation_map);
			$thread_rel_list_by_conversation_map = [];
			foreach ($thread_rel_list as $thread_rel) {

				$thread_rel_list_by_conversation_map[$thread_rel->conversation_map] = Type_Conversation_ThreadRel::prepareThreadRelData(
					$thread_rel_list_by_conversation_map[$thread_rel->conversation_map] ?? [], $thread_rel
				);
			}

			// получаем батчингом реакции для выбранных диалогов
			$reaction_list_by_conversation_map = Domain_Conversation_Feed_Action_GetBatchingReactions::run($block_id_list_by_conversation_map);

			// проходим каждому сообщению из горячего блока
			foreach ($message_block_list as $block_row) {

				$block_id = (int) $block_row["block_id"];

				// получаем реакции для диалога, затем для блока
				$reaction_list_by_block_id_list = $reaction_list_by_conversation_map[$conversation_map] ?? [];
				$prepare_thread_rel_list        = $thread_rel_list_by_conversation_map[$conversation_map] ?? [];
				$messages_reaction_list         = $reaction_list_by_block_id_list[$block_id] ?? [];

				// получаем сообщения, доступные для пользователя
				foreach ($block_row["data"] as $message) {

					// пропускаем системные сообщения, их не переносим
					if ((int) $message["type"] == CONVERSATION_MESSAGE_TYPE_SYSTEM) {
						continue;
					}

					$message_map = $message["message_map"];

					foreach ($export_conversation["members"] as $user_id) {

						if (Type_Conversation_Message_Main::getHandler($message)::isHiddenByUser($message, (int) $user_id)) {
							$user_hidden_by_message[$message_map][] = (int) $user_id;
						}
					}

					// достаём реакции для сообщения
					$reaction_list      = $messages_reaction_list["reaction_list"][$message_map] ?? [];
					$reaction_user_list = $reaction_list["reaction_user_list"] ?? [];

					$save_message_list[] = Type_Conversation_Message_Main::getHandler($message)
						::prepareForFormat($message, 0, $reaction_user_list, $prepare_thread_rel_list, true);
				}
			}

			console("экспортировано " . count($message_block_list) . " блоков сообщений (" . toJson($current_block_id_list) . ") для чата: {$conversation_map}");
		} while (count($prev_block_id_list) > 0);

		// сохраняем сообщения в папки экспортированных чатов
		self::_saveMessageList(
			$save_message_list, $file_dir . "/" . $export_conversation["compass_extra"]["export_file_name"], $user_hidden_by_message
		);
	}

	/**
	 * Получаем блоки сообщений
	 *
	 * @param string $conversation_map
	 * @param array  $dynamic_row
	 * @param array  $block_id_list
	 *
	 * @return array
	 */
	protected function _getMessageBlocks(string $conversation_map, array $dynamic_row, array $block_id_list = []):array {

		$dynamic_row = Struct_Db_CompanyConversation_ConversationDynamic::fromArray($dynamic_row);

		if (count($block_id_list) < 1) {

			$block_id_list = Domain_Conversation_Feed_Action_GetLastBlockIdList::run(
				$dynamic_row, Domain_Conversation_Feed_Action_GetLastBlockIdList::MAX_GET_MESSAGES_BLOCK_COUNT_FOR_EMPTY_BLOCK_ID_LIST
			);
		}
		$block_id_list = Domain_Conversation_Feed_Action_FilterBlockIdList::run($dynamic_row, $block_id_list);

		$previous_block_id_list = self::_getPreviousBlockIdList($dynamic_row, $block_id_list, count($block_id_list));

		return [Gateway_Db_CompanyConversation_MessageBlock::getList($conversation_map, $block_id_list), $block_id_list, $previous_block_id_list];
	}

	/**
	 * Получаем предыдущие id блоков сообщений
	 *
	 * @param Struct_Db_CompanyConversation_ConversationDynamic $dynamic_row
	 * @param array                                             $block_id_list
	 * @param int                                               $block_count
	 *
	 * @return array
	 */
	protected static function _getPreviousBlockIdList(Struct_Db_CompanyConversation_ConversationDynamic $dynamic_row, array $block_id_list, int $block_count):array {

		$previous_block_id_list = [];

		$previous_block_id = min($block_id_list) - 1;

		if ($previous_block_id < 1) {
			return $previous_block_id_list;
		}

		$start_previous_block_id = min($block_id_list) - $block_count;
		if ($start_previous_block_id < $dynamic_row->start_block_id) {
			$start_previous_block_id = $dynamic_row->start_block_id;
		}

		if ($previous_block_id >= $dynamic_row->start_block_id) {
			$previous_block_id_list = range($start_previous_block_id, $previous_block_id);
		}

		$previous_block_id_list[0] != 0 ?: array_shift($previous_block_id_list);

		return $previous_block_id_list;
	}

	/**
	 * Сохраняем список сообщений
	 *
	 * @param array  $message_list
	 * @param string $conversation_file_dir
	 * @param array  $user_hidden_by_message
	 *
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \JsonException
	 */
	protected function _saveMessageList(array $message_list, string $conversation_file_dir, array $user_hidden_by_message):void {

		// проверяем наличие директории экспортированного ранее чата
		if (!is_dir($conversation_file_dir)) {
			return;
		}

		// группируем сообщения по дате
		$messages_by_date = [];
		foreach ($message_list as $message) {

			$date                      = date("Y-m-d", $message["created_at"]);
			$messages_by_date[$date][] = $message;
		}

		foreach ($messages_by_date as $date => $message_list) {

			// сортируем по дате создания сообщения
			uasort($message_list, function(array $a, array $b) {

				return $a["created_at"] <=> $b["created_at"];
			});

			$formatted_message_list = self::_prepareMessageList($message_list, $user_hidden_by_message);

			if (count($formatted_message_list) < 1) {
				return;
			}

			self::_writeToFile($formatted_message_list, $conversation_file_dir . "/{$date}.json");
		}
	}

	/**
	 * Приводим список сообщений к формату
	 *
	 * @param array $message_list
	 * @param array $user_hidden_by_message
	 *
	 * @return array
	 */
	protected function _prepareMessageList(array $message_list, array $user_hidden_by_message):array {

		$formatted_message_list = [];

		foreach ($message_list as $message) {
			$formatted_message_list[] = $this->_formatMessage($message, $user_hidden_by_message[$message["message_map"]] ?? []);
		}

		return $formatted_message_list;
	}

	/**
	 * Форматирование сообщения
	 * @long
	 */
	protected function _formatMessage(array $message, array $hidden_user_list):array {

		$formatted = $this->_formatBaseMessage($message);

		if (count($hidden_user_list) > 0) {
			$formatted["compass_extra"]["hidden_user_list"] = $hidden_user_list;
		}

		if (isset($message["child_thread"])) {
			$formatted = $this->_addThreadInfo($message, $formatted);
		}

		if (isset($message["is_edited"])) {
			$formatted = $this->_addEditInfo($message, $formatted);
		}

		if (isset($message["reaction_list"])) {
			$formatted = $this->_addReactions($message, $formatted);
		}

		if ((isset($message["mention_user_id_list"]) && count($message["mention_user_id_list"]) > 0) || isset($message["text"])) {
			$formatted = $this->_addRichText($message, $formatted);
		}

		if (isset($message["data"]["file_map"]) && mb_strlen($message["data"]["file_map"]) > 0) {
			$formatted = $this->_addFiles($message, $formatted);
		}

		if (isset($message["data"]["quoted_message_list"]) && count($message["data"]["quoted_message_list"]) > 0) {
			$formatted = $this->_addQuotedMessages($message, $formatted);
		}

		if (isset($message["data"]["reposted_message_list"]) && count($message["data"]["reposted_message_list"]) > 0) {
			$formatted = $this->_addRepostedMessages($message, $formatted);
		}

		return array_filter($formatted, fn($value) => !is_null($value));
	}

	/**
	 * Форматирование базовой структуры сообщения
	 */
	protected function _formatBaseMessage(array $message):array {

		return [
			"client_msg_id" => $message["client_message_id"],
			"type"          => "message",
			"user"          => (string) $message["sender_id"],
			"text"          => $message["text"],
			"ts"            => sprintf("%.6f", $message["created_at"] + random_int(0, 999999) / 1000000),
			"team"          => $this->space_id,
			"user_team"     => $this->space_id,
			"source_team"   => $this->space_id,
			"user_profile"  => $this->_formatUserProfile(),
		];
	}

	/**
	 * Форматирование профиля пользователя
	 */
	protected function _formatUserProfile():array {

		return [
			"avatar_hash"         => "default_hash",
			"image_72"            => "",
			"first_name"          => "",
			"real_name"           => "",
			"display_name"        => "",
			"team"                => "T078C13FSH3",
			"name"                => "",
			"is_restricted"       => false,
			"is_ultra_restricted" => false,
		];
	}

	/**
	 * Добавление информации о треде
	 */
	protected function _addThreadInfo(array $message, array $formatted):array {

		if (isset($message["child_thread"])) {

			$formatted["thread_ts"] = \CompassApp\Pack\Thread::doEncrypt($message["child_thread"]["thread_map"]);
			$formatted["replies"]   = [];
		}
		return $formatted;
	}

	/**
	 * Добавление информации о редактировании
	 */
	protected function _addEditInfo(array $message, array $formatted):array {

		$formatted["edited"] = [
			"user" => (string) $message["sender_id"],
			"ts"   => sprintf("%.6f", $message["last_message_edited"] + random_int(0, 999999) / 1000000),

		];
		return $formatted;
	}

	/**
	 * Добавление реакций
	 */
	protected function _addReactions(array $message, array $formatted):array {

		$formatted["reactions"] = array_map(static function($reaction) {

			// :joy: -> joy
			$reaction_name = trim($reaction["reaction_name"], ":");

			return [
				"name"  => $reaction_name,
				"users" => array_map("strval", $reaction["user_id_list"]),
				"count" => $reaction["count"],
			];
		}, $message["reaction_list"]);
		return $formatted;
	}

	/**
	 * Добавление форматированного текста
	 */
	protected function _addRichText(array $message, array $formatted):array {

		$formatted["blocks"] = [
			[
				"type"     => "rich_text",
				"block_id" => "rich_text",
				"elements" => [["type" => "rich_text_section", "elements" => $this->_formatRichTextElements($message)]],
			],
		];
		return $formatted;
	}

	/**
	 * Форматирование элементов форматированного текста
	 */
	protected function _formatRichTextElements(array $message, array $elements = []):array {

		if (isset($message["text"])) {
			$elements[] = $this->_createTextElement($message["text"]);
		}

		return $elements;
	}

	/**
	 * Создание текстового элемента
	 */
	protected function _createTextElement(string $text):array {

		return [
			"type" => "text",
			"text" => $text,
		];
	}

	/**
	 * Добавление информации о файлах
	 */
	protected function _addFiles(array $message, array $formatted):array {

		$file_list = Gateway_Socket_FileBalancer::getFileList([$message["data"]["file_map"]]);
		if (count($file_list) > 0) {
			$formatted["files"][] = $this->_formatFileInfo($message, $file_list[0]);
		}
		return $formatted;
	}

	/**
	 * Форматирование информации о файле
	 * @long
	 */
	protected function _formatFileInfo($message, array $file):array {

		$file_key = \CompassApp\Pack\File::doEncrypt($message["data"]["file_map"]);

		$base = [
			"id"                   => $file_key,
			"created"              => $file["created_at"],
			"timestamp"            => $file["created_at"],
			"name"                 => $file["file_name"],
			"title"                => $file["file_name"],
			"mimetype"             => $file["type"],
			"filetype"             => $file["file_extension"],
			"pretty_type"          => "Файл",
			"user"                 => $message["sender_id"],
			"size"                 => $file["size_kb"] ?? 0,
			"mode"                 => "hosted",
			"is_external"          => false,
			"external_type"        => "",
			"is_public"            => true,
			"public_url_shared"    => false,
			"display_as_bot"       => false,
			"username"             => "",
			"url_private"          => $file["url"] ?? "",
			"url_private_download" => $file["url"] ?? "",
		];

		if ($base["filetype"] == "aac") {
			$base["subtype"] = "slack_audio";
		}

		if (isset($file["data"]["waveform"])) {
			$base = $this->_addAudioFields($file, $base);
		}

		return $base;
	}

	/**
	 * Добавление полей для аудио файлов
	 */
	protected function _addAudioFields(array $file, array $base):array {

		$base["audio_wave_samples"] = $file["data"]["waveform"];
		$base["duration_ms"]        = $file["data"]["duration_ms"] ?? 0;
		$base["media_display_type"] = "audio";
		return $base;
	}

	/**
	 * Добавление цитируемых сообщений
	 */
	protected function _addQuotedMessages(array $message, array $formatted):array {

		$formatted["attachments"] = [
			[
				"ts"             => sprintf("%.6f", $message["created_at"] + random_int(0, 999999) / 1000000),
				"author_id"      => (string) $message["sender_id"],
				"message_blocks" => [
					[
						"team"    => $this->space_id,
						"channel" => "C078EGRLE1M",
						"ts"      => sprintf("%.6f", $message["created_at"] + random_int(0, 999999) / 1000000),
						"message" => [
							"blocks" => [
								[
									"type"                 => "rich_text",
									"block_id"             => "rich_text",
									"elements"             => [
										[
											"type"     => "rich_text_quote",
											"elements" => $this->_formatRepostOrQuoteMessage($message["data"]["quoted_message_list"]),
										],
									],
									"quoted_message_count" => count($message["data"]["quoted_message_list"]),
								],
							],
						],
					],
				],
				"files"          => $this->_getFilesFromMessages($message["data"]["quoted_message_list"]),
			],
		];
		return $formatted;
	}

	/**
	 * Добавление пересланных сообщений
	 * @long
	 */
	protected function _addRepostedMessages(array $message, array $formatted):array {

		$formatted["attachments"][] = [
			"ts"             => sprintf("%.6f", $message["created_at"] + random_int(0, 999999) / 1000000),
			"author_id"      => (string) $message["sender_id"],
			"message_blocks" => [
				[
					"team"    => "T078C13FSH3",
					"channel" => "C078EGRLE1M",
					"ts"      => sprintf("%.6f", $message["created_at"] + random_int(0, 999999) / 1000000),
					"message" => [
						"blocks" => [
							[
								"type"     => "rich_text",
								"block_id" => "rich_text",
								"elements" => [
									[
										"type"     => "rich_text_quote",
										"elements" => $this->_formatRepostOrQuoteMessage($message["data"]["reposted_message_list"]),
									],
								],
							],
						],
					],
				],
			],
			"files"          => $this->_getFilesFromMessages($message["data"]["reposted_message_list"]),
		];
		return $formatted;
	}

	/**
	 * Форматирование сообщения
	 */
	protected function _formatRepostOrQuoteMessage(array $message_list):array {

		$elements = [];

		foreach ($message_list as $message) {

			if ((isset($message["mention_user_id_list"]) && count($message["mention_user_id_list"]) > 0) || isset($message["text"])) {
				$elements = $this->_formatRichTextElements($message, $elements);
			}

			if (isset($message["data"]["quoted_message_list"])) {
				$elements = array_merge($elements, self::_formatRepostOrQuoteMessage($message["data"]["quoted_message_list"]));
			}

			if (isset($message["data"]["quoted_message"])) {
				$elements = array_merge($elements, self::_formatRepostOrQuoteMessage([$message["data"]["quoted_message"]]));
			}

			if (isset($message["data"]["reposted_message_list"])) {
				$elements = array_merge($elements, self::_formatRepostOrQuoteMessage($message["data"]["reposted_message_list"]));
			}
		}

		return $elements;
	}

	/**
	 * Получаем список файлов из сообщений
	 */
	protected function _getFilesFromMessages(array $message_list):array {

		$files = [];
		foreach ($message_list as $message) {

			if (isset($message["data"]["file_map"]) && mb_strlen($message["data"]["file_map"]) > 0) {

				$file_list = Gateway_Socket_FileBalancer::getFileList([$message["data"]["file_map"]]);
				if (count($file_list) > 0) {
					$files[] = $this->_formatFileInfo($message, $file_list[0]);
				}
			}

			// Рекурсивно проверяем вложенные сообщения
			if (isset($message["data"]["quoted_message_list"])) {
				$files = array_merge($files, $this->_getFilesFromMessages($message["data"]["quoted_message_list"]));
			}
			if (isset($message["data"]["quoted_message"])) {
				$files = array_merge($files, $this->_getFilesFromMessages([$message["data"]["quoted_message"]]));
			}
			if (isset($message["data"]["reposted_message_list"])) {
				$files = array_merge($files, $this->_getFilesFromMessages($message["data"]["reposted_message_list"]));
			}
		}
		return $files;
	}

	/**
	 * Записываем в файл.
	 *
	 * @throws ReturnFatalException
	 * @throws \JsonException
	 */
	protected function _writeToFile(array $formatted_message_list, string $message_file_path):void {

		if (!file_exists($message_file_path) && !touch($message_file_path, 0)) {
			throw new ReturnFatalException("can't create export file: {$message_file_path}");
		}

		// получаем содержимое файла
		$file_content = file_get_contents($message_file_path);

		// докидываем в конец файла массив данных
		if (mb_strlen($file_content) > 0) {
			$file_content = json_decode($file_content, true, 512, JSON_THROW_ON_ERROR);
		} else {
			$file_content = [];
		}

		$file_content = array_merge($file_content, $formatted_message_list);

		// дописываем в файл
		file_put_contents($message_file_path, json_encode($file_content, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
	}
}

/**
 * Получаем url компании
 */
function _getCompanyUrl():string {

	try {

		$company_url = Type_Script_InputParser::getArgumentValue("--company_url");
	} catch (\Exception) {

		console("Передайте корректный url компании в которую обращаемся, например: --company_url='c1-d1.company.com'");
		exit;
	}

	return $company_url;
}

/**
 * Получаем id пространства
 */
function _getSpaceId():int {

	try {

		$space_id = Type_Script_InputParser::getArgumentValue("--space_id", Type_Script_InputParser::TYPE_INT);
	} catch (\Exception) {

		console("Передайте корректный id пространства, например: --space_id=1");
		exit;
	}

	return $space_id;
}

/**
 * Получаем флаг is_dry
 */
function _getDry():bool {

	try {

		$is_dry = Type_Script_InputHelper::isDry();
	} catch (\Exception) {

		console("Передайте корректный флаг is_dry");
		exit;
	}

	return $is_dry;
}

/**
 * Получаем путь до директории экспорта групповых чатов
 */
function _getGroupExportFilePath():string {

	try {

		$group_export_file_path = Type_Script_InputParser::getArgumentValue("--group_export_file_path", Type_Script_InputParser::TYPE_STRING, __DIR__ . "/groups.json");
	} catch (\Exception) {

		console("Передайте корректный путь до директории экспорта групповых чатов, например: --group_export_file_path=/app/groups.json");
		exit;
	}

	return $group_export_file_path;
}

/**
 * Получаем путь до директории экспорта сингл чатов
 */
function _getSingleExportFilePath():string {

	try {

		$single_export_file_path = Type_Script_InputParser::getArgumentValue("--single_export_file_path", Type_Script_InputParser::TYPE_STRING, __DIR__ . "/dialogs.json");
	} catch (\Exception) {

		console("Передайте корректный путь до директории экспорта сингл чатов, например: --single_export_file_path=/app/singles.json");
		exit;
	}

	return $single_export_file_path;
}

(new Migration_Export_Messages($is_dry, $space_id))->run($group_export_file_path, $single_export_file_path);