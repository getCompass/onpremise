package httpfileuathz

import (
	"context"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_file_auth/api/includes/type/authz"
	"go_file_auth/api/includes/type/fileauthz"
	"net/http"
)

/**
 * Пакет обработки входящих http-запросов.
 */

// InitFileAuthzController выполняет инициализацию контроллер рассылки пушей
// при добавлении нового метода его нужно добавить здесь
func InitFileAuthzController() (string, *Controller) {

	toRegister := Controller{}
	handler := fileAuthzHandler{}

	// инициализируем методы контроллера, новые методы нужно добавлять сюда
	toRegister.methodList = map[string]*controllerMethod{
		"check": {call: handler.check, auth: &authz.FileServerEntrypointAuthorizer{}},
	}

	return "fileauthz", &toRegister
}

// контроллер авторизации доступа к файлам
type fileAuthzHandler struct{}

// отправить пуш
func (*fileAuthzHandler) check(ctx context.Context, caData *authz.ClientAuthzData, req *http.Request) *AnswerStruct {

	token := req.URL.Query().Get("token")
	original := req.URL.Query().Get("original")

	if err := fileauthz.Authorize(ctx, token, original, caData); err != nil {

		log.Warningf("file access authorization failed %s", err.Error())
		return Error(403, 403, err.Error())
	}

	log.Info("file access authorization successes")
	return Ok()
}
