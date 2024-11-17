<?php declare(strict_types = 1);

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ReturnFatalException;
use Compass\Conversation\Gateway_Socket_FileBalancer;

require_once "/app/src/Compass/Thread/api/includes/type/script/input_parser.php";
require_once "/app/src/Compass/Thread/api/includes/type/script/input_helper.php";
require_once "/app/src/Modules/vendor/autoload.php";

$company_url      = _getCompanyUrl();
$space_id         = _getSpaceId();
$export_file_path = _getExportDirPath();

putenv("COMPANY_ID=$space_id");
$_SERVER["HTTP_HOST"] = $company_url;

require_once __DIR__ . "/../../../../../../start.php";
set_time_limit(0);
ini_set("memory_limit", "4096M");
ini_set("display_errors", "1");

/**
 * Экспорт тредов (цепочек сообщений) из системы
 */
class Migration_Export_Threads {

	/** @var int ID пространства */
	private int $space_id;

	public function __construct(int $space_id) {

		$this->space_id = $space_id;
	}

	/**
	 * Запуск процесса экспорта
	 *
	 * @param string $export_file_path Путь к директории экспорта
	 *
	 * @throws \JsonException
	 */
	public function run(string $export_file_path):void {

		$channels_list = self::_getChannelsList($export_file_path);
		console(sprintf("Всего чатов для экспорта тредов %d", count($channels_list)));

		foreach ($channels_list as $number => $channel) {
			$this->_processChannel($channel, $number, $export_file_path);
		}
	}

	/**
	 * Обработка отдельного канала
	 */
	protected function _processChannel(array $channel, int $number, string $export_file_path):void {

		console(sprintf("Проходимся по чату %d", $number + 1));

		$export_dir        = dirname($export_file_path);
		$messages_dir_path = "{$export_dir}/{$channel["id"]}";

		if (!is_dir($messages_dir_path)) {

			console(sprintf("Директория %s не существует, пропускаем", $messages_dir_path));
			return;
		}

		$messages_files = $this->_getMessageFiles($messages_dir_path);

		console(sprintf("Всего файлов с сообщениями %d", count($messages_files)));

		foreach ($messages_files as $messages_file) {
			$this->_processMessagesFile($messages_dir_path, $messages_file);
		}
	}

	/**
	 * Получение списка файлов с сообщениями
	 */
	protected function _getMessageFiles(string $messages_dir_path):array {

		return array_filter(
			scandir($messages_dir_path),
			static fn($file) => is_file("{$messages_dir_path}/{$file}")
		);
	}

	/**
	 * Обработка файла с сообщениями
	 */
	protected function _processMessagesFile(string $messages_dir_path, string $messages_file):void {

		$messages_list = json_decode(
			file_get_contents("{$messages_dir_path}/{$messages_file}"),
			true,
			512,
			JSON_THROW_ON_ERROR
		);
		$updated       = false;

		for ($i = 0; $i < count($messages_list); $i++) {
			$message = $messages_list[$i];

			// если это не начало треда, то пропускаем
			if (!$this->_isThreadStart($message)) {
				continue;
			}

			// получаем все сообщения треда
			$thread_messages = $this->_getThreadMessages($message);

			// добавляем поле replies
			$messages_list[$i]["replies"] = array_map(function($thread_message) {

				return [
					"user" => $thread_message["user"],
					"ts"   => $thread_message["ts"],
				];
			}, $thread_messages);

			// вставляем сообщения треда после родительского сообщения
			array_splice(
				$messages_list,
				$i + 1,
				0,
				$thread_messages
			);

			// корректируем индекс с учетом вставленных сообщений
			$i += count($thread_messages);

			$updated = true;
		}

		if ($updated) {
			console(sprintf("Записали треды для файла %s", $messages_file));
			$this->_saveMessagesFile($messages_dir_path, $messages_file, $messages_list);
		}
	}

	/**
	 * Проверка, является ли сообщение началом треда
	 */
	protected function _isThreadStart(array $message):bool {

		return isset($message["thread_ts"]);
	}

	/**
	 * Получение всех сообщений треда
	 */
	protected function _getThreadMessages(array $message):array {

		$thread_map = \CompassApp\Pack\Thread::tryDecrypt($message["thread_ts"]);

		// Получаем все сообщения треда порциями
		$previous_block_id_list = [];
		$thread_messages_list   = [];

		do {
			[$message_list, $previous_block_id_list] = self::_getThreadMessagesList($thread_map, $previous_block_id_list);
			$thread_messages_list = array_merge($thread_messages_list, $message_list);
		} while (count($previous_block_id_list) > 0);

		// Форматируем сообщения треда
		return array_map(
			fn($thread_message) => $this->_formatMessage($thread_message),
			$thread_messages_list
		);
	}

	/**
	 * Сохранение обновленного файла с сообщениями
	 */
	protected function _saveMessagesFile(string $messages_dir_path, string $messages_file, array $messages_list):void {


		file_put_contents(
			"{$messages_dir_path}/{$messages_file}",
			json_encode(
				$messages_list,
				JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
			)
		);
	}

	/**
	 * Форматирование элементов форматированного текста
	 */
	protected function _formatRichTextElements(array $message, array $elements = []):array {

		if ($message["text"]) {
			$elements[] = $this->_createTextElement($message["text"]);
		}

		if (!empty($message["mention_user_id_list"])) {
			$elements = array_merge(
				$elements,
				$this->_createMentionElements($message["mention_user_id_list"])
			);
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
	 * Создание элементов упоминаний
	 */
	protected function _createMentionElements(array $user_id_list):array {

		return array_map(
			static fn($user_id) => [
				"type"    => "user",
				"user_id" => (string) $user_id,
			],
			$user_id_list
		);
	}

	/**
	 * Получаем список всех чатов из файла channels.json
	 *
	 * @throws \JsonException
	 */
	protected function _getChannelsList(string $export_file_path):array {

		if (!file_exists($export_file_path)) {
			console("Не был произведен экспорт чатов");
			exit;
		}

		if (!is_file($export_file_path)) {
			console("Указанный путь не является файлом: {$export_file_path}");
			exit;
		}

		return json_decode(file_get_contents($export_file_path), true, 512, JSON_THROW_ON_ERROR);
	}

	/**
	 * Получаем все сообщения треда
	 *
	 * @throws ReturnFatalException
	 * @throws \parseException
	 */
	protected function _getThreadMessagesList(string $thread_map, array $block_id_list = []):array {

		// получаем все сообщения из треда
		$thread_dynamic = Type_Thread_Dynamic::get($thread_map);
		$block_id_list  = Domain_Thread_Entity_MessageBlock::resolveCorrectBlockIdList($thread_dynamic, $block_id_list);

		// если блоков не осталось, то завершаем исполнение и возвращаем пустоту
		if (count($block_id_list) === 0) {
			return [[], [], [], []];
		}

		// данные для ответа
		$message_list = [];
		$user_list    = [];

		// получаем блоки с сообщениями и список реакций к ним
		$block_list          = Domain_Thread_Entity_MessageBlock::getList($thread_map, $block_id_list);
		$block_reaction_list = Gateway_Db_CompanyThread_MessageBlockReactionList::getList($thread_map, $block_id_list);

		foreach ($block_list as $block) {

			/** @var Struct_Db_CompanyThread_MessageBlockReaction $block_reaction получаем список реакций для блока */
			$block_reaction = $block_reaction_list[$block["block_id"]] ?? null;

			// проходимся по всем сообщениям в блоке и добавляем данные в ответ
			foreach (Domain_Thread_Entity_MessageBlock_Message::iterate($block) as $message) {

				// получаем информацию по реакциям для сообщения из блока реакций
				[$reaction_list, $reaction_last_edited_at] = ($block_reaction !== null)
					? Domain_Thread_Entity_MessageBlock_Reaction::fetchMessageReactionData($block_reaction, $message["message_map"])
					: [[], 0];

				// добавляем сообщение и фиксируем список пользователей для него
				$message_list[] = Type_Thread_Message_Main::getHandler($message)::prepareForFormat($message, $reaction_list, $reaction_last_edited_at);
				array_push($user_list, ...Type_Thread_Message_Main::getHandler($message)::getUsers($message));
			}
		}

		[$previous_block_id_list] = Domain_Thread_Entity_MessageBlock::getAroundNBlocks($thread_dynamic, $block_id_list);

		return [$message_list, $previous_block_id_list];
	}

	/**
	 * Форматирование сообщения
	 */
	protected function _formatMessage(array $message):array {

		$formatted = $this->_formatBaseMessage($message);

		if (isset($message["thread_map"])) {
			$formatted = $this->_addThreadInfo($message, $formatted);
		}

		if ($message["is_edited"]) {
			$formatted = $this->_addEditInfo($message, $formatted);
		}

		if (!empty($message["reaction_list"])) {
			$formatted = $this->_addReactions($message, $formatted);
		}

		if (!empty($message["mention_user_id_list"]) || $message["text"]) {
			$formatted = $this->_addRichText($message, $formatted);
		}

		if (!empty($message["data"]["file_map"])) {
			$formatted = $this->_addFiles($message, $formatted);
		}

		if (!empty($message["data"]["quoted_message_list"])) {
			$formatted = $this->_addQuotedMessages($message, $formatted);
		}

		if (!empty($message["data"]["reposted_message_list"])) {
			$formatted = $this->_addRepostedMessages($message, $formatted);
		}

		return array_filter($formatted, static fn($value) => !is_null($value));
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

		// ~вроде~ нам профиль не нужен, только user_id
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

		$formatted["thread_ts"] = \CompassApp\Pack\Thread::doEncrypt($message["thread_map"]);

		if (isset($message["parent_message_sender_id"])) {
			$formatted["parent_user_id"] = (string) $message["parent_message_sender_id"];
		}
		return $formatted;
	}

	/**
	 * Добавление информации о редактировании
	 */
	protected function _addEditInfo(array $message, array $formatted):array {

		$formatted["edited"] = [
			"user" => (string) $message["sender_id"],
			"ts"   => sprintf("%.6f", $message["last_message_text_edited_at"] + random_int(0, 999999) / 1000000),
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
	 * Добавление информации о файлах
	 */
	protected function _addFiles(array $message, array $formatted):array {

		$formatted["files"][] = $this->_formatFileInfo($message);
		return $formatted;
	}

	/**
	 * Форматирование информации о файле
	 */
	protected function _formatFileInfo($message):array {

		$file_key  = \CompassApp\Pack\File::doEncrypt($message["data"]["file_map"]);
		$file_list = Gateway_Socket_FileBalancer::getFileList([$message["data"]["file_map"]]);
		$file      = $file_list[0];

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
							"blocks"               => [
								[
									"type"     => "rich_text",
									"block_id" => "rich_text",
									"elements" => [
										[
											"type"     => "rich_text_quote",
											"elements" => static::_formatRepostOrQuoteMessage($message["data"]["quoted_message_list"]),
										],
									],
								],
							],
							"quoted_message_count" => count($message["data"]["quoted_message_list"]),
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
										"type"     => "rich_text_section",
										"elements" => static::_formatRepostOrQuoteMessage($message["data"]["reposted_message_list"]),
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

			if (!empty($message["mention_user_id_list"]) || $message["text"]) {
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

			if (!empty($message["data"]["file_map"])) {
				$files[] = $this->_formatFileInfo($message);
			}

			// Рекурсивно проверяем вложенные сообщения
			if (!empty($message["data"]["quoted_message_list"])) {
				$files = array_merge($files, $this->_getFilesFromMessages($message["data"]["quoted_message_list"]));
			}
			if (!empty($message["data"]["quoted_message"])) {
				$files = array_merge($files, $this->_getFilesFromMessages([$message["data"]["quoted_message"]]));
			}
			if (!empty($message["data"]["reposted_message_list"])) {
				$files = array_merge($files, $this->_getFilesFromMessages($message["data"]["reposted_message_list"]));
			}
		}
		return $files;
	}
}

/**
 * Получаем URL компании из аргументов командной строки
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
 * Получаем путь к директории экспорта
 */
function _getExportDirPath():string {

	try {

		$export_dir_path = Type_Script_InputParser::getArgumentValue("--export_dir_path");
	} catch (\Exception) {

		console("Передайте корректный путь к директории экспорта, например: --export_dir_path='/app/src/Compass/Thread/sh/php/migration/exported/dialogs.json'");
		exit;
	}

	return $export_dir_path;
}

(new Migration_Export_Threads($space_id))->run($export_file_path);


