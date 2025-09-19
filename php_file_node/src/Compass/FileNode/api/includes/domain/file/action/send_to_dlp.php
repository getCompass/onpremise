<?php

namespace Compass\FileNode;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Request\InappropriateContentException;
use BaseFrame\Icap\Client\HttpRequestBuilder;
use BaseFrame\Icap\Client\IcapClient;
use BaseFrame\Icap\Client\IcapMockClient;
use BaseFrame\System\File;
use BaseFrame\Url\UrlProvider;

/**
 * Экшн отправки файла на проверку в dlp
 */
class Domain_File_Action_SendToDlp
{
	/**
	 * Выполняем
	 */
	public static function do(int $user_id, string $file_name, string $file_extension, string $file_path): void
	{

		$icap_config = Domain_Config_Entity_Icap::instance($user_id);

		// проверяем, включен ли клиент
		if (
			!$icap_config->isEnabled()
			|| !$icap_config->isFileControlled()
			|| !$icap_config->isFileExtensionControlled(mb_strtolower($file_extension))) {

			return;
		}

		// формируем болванку http запроса для ICAP
		$http_request_builder = (new HttpRequestBuilder())
			->method("POST")
			->url("/files/upload")
			->addHeader("Host", UrlProvider::pivotDomain())
			->bodyFromMultipartFile(
				"file",
				$file_path,
				$file_name,
			);

		// получаем ICAP клиент
		$icap_client = self::_getIcapClient($user_id);

		// отправляем REQMOD запрос
		try {
			$icap_response = $icap_client->reqmod($http_request_builder->build());
		} catch (\Throwable $t) {
			throw new ReturnFatalException("icap request error: {$t->getMessage()}");
		}

		// если изменился запрос - значит отдаем ошибку
		if ($icap_response->isRequestModified($icap_client->getLastRequest())) {

			// файл нужно удалить, чтобы не захламлять место
			self::_removeRestrictedFile($file_path);
			throw new InappropriateContentException("icap request changed");
		}

		return;
	}

	/**
	 * Получить ICAP клиент
	 */
	protected static function _getIcapClient(int $user_id): IcapClient
	{

		$icap_config = Domain_Config_Entity_Icap::instance($user_id);

		// если включен мок - активируем мок клиент
		if ($icap_config->isMock()) {
			return IcapMockClient::instance(ShardingGateway::class, CURRENT_MODULE . "_$user_id");
		}

		return new IcapClient(
			sprintf(
				"icap://%s:%d/%s",
				$icap_config->host(),
				$icap_config->port(),
				$icap_config->service()
			)
		);
	}

	/**
	 * Удалить непрошедший проверку файл
	 *
	 *
	 * @throws ReturnFatalException
	 */
	protected static function _removeRestrictedFile(string $file_path): void
	{

		if (!str_starts_with($file_path, PATH_WWW)) {
			throw new ReturnFatalException("file was not in www dir");
		}
		$file_subpath = substr($file_path, strlen(PATH_WWW));

		$file = File::init(PATH_WWW, $file_subpath);
		$file->delete();
	}
}
