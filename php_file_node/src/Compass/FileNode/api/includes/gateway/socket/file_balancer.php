<?php

namespace Compass\FileNode;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Класс-интерфейс для работы с модулей file_balancer
 */
class Gateway_Socket_FileBalancer extends Gateway_Socket_Default
{
	/**
	 * Пометить файлы удаленными
	 *
	 *
	 * @throws Gateway_Socket_Exception_CompanyIsHibernated
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ReturnFatalException
	 * @throws \cs_SocketRequestIsFailed
	 */
	public static function setFileListDeleted(array $file_key_list, int $company_id = 0, string $company_url = ""): void
	{

		// отправляем socket запрос для пометки файлов удаленными
		[$status,] = self::doCall("files.setFileListDeleted", $company_id, $company_url, [
			"file_key_list" => $file_key_list,
		]);

		// если не ок — бросаем экшепшен
		if ($status != "ok") {
			throw new ReturnFatalException("Unhandled error_code from socket call in " . __METHOD__);
		}
	}

	/**
	 * Пометить файлы удаленными
	 *
	 *
	 * @throws Gateway_Socket_Exception_CompanyIsHibernated
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ReturnFatalException
	 * @throws \cs_SocketRequestIsFailed
	 */
	public static function updateFileStatus(string $file_key, int $status, int $company_id = 0, string $company_url = ""): void
	{

		// отправляем socket запрос для пометки файлов удаленными
		[$status,] = self::doCall("files.updateFileStatus", $company_id, $company_url, [
			"file_key" => $file_key,
			"status"   => $status,
		]);

		// если не ок — бросаем экшепшен
		if ($status != "ok") {
			throw new ReturnFatalException("Unhandled error_code from socket call in " . __METHOD__);
		}
	}

	/**
	 * Делаем вызов
	 *
	 *
	 * @throws Gateway_Socket_Exception_CompanyIsHibernated
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \cs_SocketRequestIsFailed
	 */
	public static function doCall(string $method, int $company_id, string $company_url, array $params, int $user_id = 0): array
	{

		// получаем url и подпись
		if ($company_id > 0) {
			$url = self::_getSocketCompanyUrl("file_balancer", $company_url);
		} else {
			$url = self::_getSocketPivotUrl("file_balancer");
		}
		return self::_doCall($url, $method, $params, $user_id, $company_id);
	}
}
