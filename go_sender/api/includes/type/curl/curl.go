package curl

/*
 * пакет для выполнения всех http запросов
 *
 * обязательно надо создавать объект http.NewRequest()
 * один бесконечно важный момент с пакетом net/http - он по умолчанию не ставит таймаут на соединение,
 * поэтому горутины зависают если в них есть такие запросы без таймаутов
 */

import (
	"bytes"
	"crypto/tls"
	"crypto/x509"
	"encoding/pem"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_sender/api/conf"
	"io/ioutil"
	"net/http"
	"net/url"
	"time"
)

// таймаут по окончанию которого запрос считается проваленным
const curlTimeout = time.Second * 2

// -------------------------------------------------------
// FUNCTIONS
// -------------------------------------------------------

// осуществляем POST запрос
func SimplePost(requestUrl string, requestMap map[string]string) ([]byte, bool) {

	// получаем объект запроса
	requestItem, err := getPostRequestItem(requestUrl, requestMap)
	if err != nil {

		log.Errorf("get POST request item failed, %v", err)
		return []byte{}, false
	}

	// получаем объект клиента
	clientItem, err := getPostClientItem()
	if err != nil {

		log.Errorf("get POST client item failed, %v", err)
		return []byte{}, false
	}

	responseItem, err := clientItem.Do(requestItem)
	if err != nil {

		log.Errorf("failed POST request, %v", err)
		return []byte{}, false
	}
	return prepareResponse(responseItem)
}

// получаем post-объект запроса
func getPostRequestItem(requestUrl string, requestMap map[string]string) (*http.Request, error) {

	// инициализуерм массив параметров
	formMap := url.Values{}

	// заполняем массив параметрами
	for key, value := range requestMap {
		formMap.Set(key, value)
	}

	// создаем объект запроса
	requestItem, err := http.NewRequest("POST", requestUrl, bytes.NewBufferString(formMap.Encode()))
	if err != nil {
		return &http.Request{}, err
	}

	// добавляем заголовок
	requestItem.Header.Add("Content-Type", "application/x-www-form-urlencoded")

	return requestItem, nil
}

// получаем post-объект клиента
func getPostClientItem() (http.Client, error) {

	// формируем объект клиента
	clientItem := http.Client{}

	// устанавливаем таймаут
	clientItem.Timeout = curlTimeout

	// если в конфиге включен TLS
	config := conf.GetTestConfig()
	if config.Tls {

		// добавляем сертификат
		err := addCertificateToPostClient(&clientItem)
		if err != nil {
			return http.Client{}, err
		}
	}

	return clientItem, nil
}

// добавляем сертификат к post-объекту клиента
func addCertificateToPostClient(clientItem *http.Client) error {

	// читаем файл
	config := conf.GetTestConfig()
	certFile, err := ioutil.ReadFile(config.CrtFilePath)
	if err != nil {
		return err
	}

	// инициализируем пул сертификатов
	certPoolItem := x509.NewCertPool()

	// парсим сертификат
	blockItem, _ := pem.Decode(certFile)
	certItem, err := x509.ParseCertificate(blockItem.Bytes)
	if err != nil {
		return err
	}

	// добавляем сертификат в пул и в объект клиента
	certPoolItem.AddCert(certItem)
	clientItem.Transport = &http.Transport{
		TLSClientConfig: &tls.Config{RootCAs: certPoolItem},
	}
	return nil
}

// подготовливаем post-ответ
func prepareResponse(responseItem *http.Response) ([]byte, bool) {

	// переводим ответ в строку
	responseData, err := ioutil.ReadAll(responseItem.Body)
	if err != nil {

		log.Errorf("response read failed, %v", err)
		return []byte{}, false
	}

	// проверяем статус
	if responseItem.StatusCode != 200 {

		log.Errorf("status code != 200. Response: %v", string(responseData))
		return []byte{}, false
	}

	return responseData, true
}
