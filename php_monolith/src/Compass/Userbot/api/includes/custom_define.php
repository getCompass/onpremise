<?php

namespace Compass\Userbot;

const CURRENT_MODULE = "userbot";
const CODE_UNIQ_VERSION = 18000;

// список системных ошибок при отправке запроса на выполнение (ошибки, которые можно получить перед выполнением запроса внутри приложения)
const CASE_EXCEPTION_CODE_1 = 1; // отсутствуют обязательные поля для запроса
const CASE_EXCEPTION_CODE_2 = 2; // токен запроса не найден
const CASE_EXCEPTION_CODE_3 = 3; // бот выключен или удалён - выполнение запроса невозможно
const CASE_EXCEPTION_CODE_4 = 4; // некорректная подпись для валидации переданных данных
const CASE_EXCEPTION_CODE_5 = 5; // набран лимит ошибок при выполнении запроса
const CASE_EXCEPTION_CODE_6 = 6; // неизвестная ошибка при выполнении внутреннего метода для выполняемого запроса
const CASE_EXCEPTION_CODE_7 = 7; // запрос ещё не выполнен (при получении ответа отправленного ранее запроса)
const CASE_EXCEPTION_CODE_8 = 8; // указаны некорректные параметры для запроса
const CASE_EXCEPTION_CODE_9 = 9; // указан некорректный метод запроса

// список ошибок при выполнении запроса внутри приложения для компании
const CASE_EXCEPTION_CODE_1000 = 1000; // переданы некорректные данные
const CASE_EXCEPTION_CODE_1001 = 1001; // выбранный пользователь не существует в компании
const CASE_EXCEPTION_CODE_1002 = 1002; // выбранный пользователь уволен из компании
const CASE_EXCEPTION_CODE_1003 = 1003; // бот не состоит в групповом диалоге
const CASE_EXCEPTION_CODE_1004 = 1004; // такой групповой диалог не существует
const CASE_EXCEPTION_CODE_1005 = 1005; // у бота отсутствует доступ к сообщению (сообщение удалено или диалог очищен)
const CASE_EXCEPTION_CODE_1006 = 1006; // переданная реакция не найдена
const CASE_EXCEPTION_CODE_1007 = 1007; // переданный id сообщения не существует
const CASE_EXCEPTION_CODE_1008 = 1008; // превышен лимит списка команд
const CASE_EXCEPTION_CODE_1009 = 1009; // некорректная команда в списке
const CASE_EXCEPTION_CODE_1010 = 1010; // ошибка при загрузке файла
const CASE_EXCEPTION_CODE_1011 = 1011; // передана некорректная версия для webhook бота

// идентификатор дефолтного тестового бота для тестов
define("Compass\Userbot\DEFAULT_TEST_USERBOT_ID", isTestServer() ? 21 : 0);