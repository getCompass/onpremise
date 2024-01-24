package noticebot

/*
 * пакет для выполнения всех http запросов
 *
 * обязательно надо создавать объект http.NewRequest()
 * один бесконечно важный момент с пакетом net/http - он по умолчанию не ставит таймаут на соединение,
 * поэтому горутины зависают если в них есть такие запросы без таймаутов
 */

import (
	"crypto/hmac"
	"crypto/sha256"
	"encoding/hex"
	"encoding/json"
	"github.com/getCompassUtils/go_base_frame"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	GlobalIsolation "go_company/api/includes/type/global_isolation"
	"net/http"
	"net/url"
)

// -------------------------------------------------------
// FUNCTIONS
// -------------------------------------------------------

// отправляем сообщение в чат боту
func SendGroup(isolation *GlobalIsolation.GlobalIsolation) error {

	config := isolation.GetConfig()

	// формируем текст
	text := config.ServerName + " Error on save stat, check go_company"

	// заполняем запрос
	payload := map[string]string{
		"method":           "messages.addGroup",
		"bot_user_id":      functions.Int64ToString(config.NoticeBotUserId),
		"conversation_key": config.NoticeChannel,
		"text":             text,
	}

	// закидываем в json
	payloadJson, _ := go_base_frame.Json.Marshal(payload)

	// формируем ar post
	data := url.Values{
		"payload":   {string(payloadJson)},
		"signature": {getSignature(payloadJson, config.NoticeBotToken)},
	}

	// отправляем сообщение через бота
	resp, err := http.PostForm(config.NoticeEndpoint, data)
	if err != nil {
		log.Errorf("не удалось отправить сообщение в чат: %v", err)
	}

	var res map[string]interface{}
	err = json.NewDecoder(resp.Body).Decode(&res)
	if err != nil {
		log.Errorf("не удалось отправить сообщение в чат: %v", err)
	}
	return nil
}

// формируем сигнатуру
func getSignature(data []byte, key string) string {

	h := hmac.New(sha256.New, []byte(key))
	_, _ = h.Write(data)
	return hex.EncodeToString(h.Sum(nil))
}
