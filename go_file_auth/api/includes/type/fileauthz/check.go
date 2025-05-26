package fileauthz

import (
	"context"
	"encoding/json"
	"errors"
	"fmt"
	"go_file_auth/api/conf"
	"go_file_auth/api/includes/type/authz"
	"go_file_auth/api/includes/type/socket"
	"go_file_auth/api/includes/type/socket/auth"
	"strings"
	"sync"
	"time"
)

// Пакет проверки авторизации доступа к файлам.
// Файл содержит основную логику авторизации запроса по токену загрузки.

// CheckRequest данные запроса авторизации в компании
type CheckRequest struct {
	Source string `json:"source,omitempty"`
	Value  string `json:"value,omitempty"`
}

// время жизни кэша доступа по идентификатору сессии
var defaultTtl = time.Second * (time.Duration(conf.GetConfig().SessionCacheTTLSec))

// экземпляр хранилища
var eStorage = makeEntrypointStorage(conf.GetConfig().TrustedEntrypointList)

// экземпляр загрузчика токенов
var dtLoader = makeDownloadTokenLoader([]byte(conf.GetConfig().TokenEncryptKey))

// клиент для общения с точкой входа сервиса авторизации
var authClient = socket.MakeClient()

// Authorize пытается авторизовать запрос от пользователя для получения файла
func Authorize(ctx context.Context, encryptedToken string, originalUrlPath string, caData *authz.ClientAuthzData) error {

	// файлы с пивота просто пропускаем, вне зависимости от того, что там прислал для авторизации клиент
	if strings.HasPrefix(originalUrlPath, "/files/pivot/") || strings.HasPrefix(originalUrlPath, "files/pivot/") {
		return nil
	}

	// наличие токена обязательно
	if encryptedToken == "" {
		return errors.New("token not found in url")
	}

	// наличие исходного url обязательно
	if originalUrlPath == "" {
		return errors.New("original not found in url")
	}

	// загружаем токен из строки
	token, err := dtLoader.Load(encryptedToken)
	if err != nil {
		return err
	}

	// проверим, что знаем такую точку входа
	// если нет, создадим для нее новое хранилище
	caStorage := eStorage.get(token.Entrypoint)
	if caStorage == nil {

		caStorage = &сaDataCache{store: make(map[string]time.Time), mx: sync.RWMutex{}}
		if err := eStorage.set(token.Entrypoint, caStorage); err != nil {
			return err
		}
	}

	// проверим наличие значения в кэше, если оно там есть,
	// значит недавняя проверка завершилась успехом
	validTill, ok := caStorage.get(caData.Value)
	if ok && validTill.After(time.Now()) {
		return nil
	}

	// закинем задачку валидации по точке входа сервиса авторизации
	// если вернет ошибку, значит проверка завершилась ошибкой
	if err := callAuthorizer(ctx, token.Entrypoint, token.CompanyId, caData); err != nil {
		return fmt.Errorf("authorization failed: %s", err.Error())
	}

	// закидываем значение в кэш с временем жизни по умолчанию
	caStorage.set(caData.Value, time.Now().Add(defaultTtl))
	return nil
}

// выполняет проверку на удаленном сервере
func callAuthorizer(ctx context.Context, url string, companyId int64, caData *authz.ClientAuthzData) error {

	// формируем данные запроса
	reqStruct := CheckRequest{Source: caData.Source, Value: caData.Value}

	requestBytes, err := json.Marshal(reqStruct)
	if err != nil {
		return errors.New("can not encode payload")
	}

	// получаем подпись запроса
	signature := socketauth.GetSignature(conf.GetConfig().SocketKeyMe, requestBytes)

	// выполняем запрос
	response, err := authClient.Call(ctx, url, "space.member.checkSession", requestBytes, signature, 0, companyId)

	if err != nil {
		return fmt.Errorf("session request failed: %s", err.Error())
	}

	if response.HttpCode != 200 {
		return fmt.Errorf("session request failed with code %d, message: %s", response.HttpCode, response.Message)
	}

	if response.Status != "ok" {
		return fmt.Errorf("error occurred during callAuthorizer session request: %s", response.Message)
	}

	return nil
}
