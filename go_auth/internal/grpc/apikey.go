package grpc

// apikey methods group

import (
	"context"
	"go_auth/internal/apikey"
	"go_auth/internal/apitoken"
	"time"

	"github.com/getCompassUtils/go_base_frame/api/system/log"
	pb "github.com/getCompassUtils/pivot_protobuf_schemes/go/auth"
	"google.golang.org/grpc/status"
)

// ApikeyAuthenticate Проверяем ключ
func (s *ApiTokenServer) ApikeyAuthenticate(ctx context.Context, in *pb.ApiTokenAuthenticateRequestStruct) (*pb.ApiTokenAuthenticateResponseStruct, error) {

	token, err := s.cacheManager.Get(in.GetUserId(), in.GetApiToken())

	if err == apitoken.ErrApiTokenNotFound {

		log.Errorf("api key not found, user_id: %v, token: %v", in.GetUserId(), in.GetApiToken())
		return &pb.ApiTokenAuthenticateResponseStruct{}, status.Error(901, "token not found")
	}

	if err != nil {

		log.Errorf("cant get api key, user_id: %v, token: %v, error %v", in.GetUserId(), in.GetApiToken(), err)
		return &pb.ApiTokenAuthenticateResponseStruct{}, status.Error(500, "unknown error")
	}

	if time.Since(token.ExpiresAt) > 0 {

		log.Errorf("api key expired at, user_id: %v, token: %v", in.GetUserId(), in.GetApiToken())
		return &pb.ApiTokenAuthenticateResponseStruct{}, status.Error(902, "token is expired")
	}

	return &pb.ApiTokenAuthenticateResponseStruct{
		UserId:       token.UserId,
		ScopeListInt: token.ScopeListInt,
	}, err
}

// ApikeyGet Получить информацию по ключу
func (s *ApiTokenServer) ApikeyGet(ctx context.Context, in *pb.ApikeyGetRequestStruct) (*pb.ApiKeyStruct, error) {

	t, err := apikey.Decrypt([]byte(in.GetApiKey()), s.secretKey)

	if err != nil {
		return &pb.ApiKeyStruct{}, status.Error(913, "failed to decrypt api key")
	}

	apiToken, err := s.cacheManager.Get(t.UserId, t.Token)

	if err == apitoken.ErrApiTokenNotFound {

		log.Errorf("token not found, user_id: %v, token: %v", t.UserId, t.Token)
		return &pb.ApiKeyStruct{}, status.Error(901, "token not found")
	}

	if err != nil {

		log.Errorf("cant get token, user_id: %v, token: %v", t.UserId, t.Token)
		return &pb.ApiKeyStruct{}, status.Error(500, "unknown error")
	}

	if time.Since(apiToken.ExpiresAt) >= 0 {

		log.Errorf("token is expired, user_id: %v, token: %v", t.UserId, t.Token)
		return &pb.ApiKeyStruct{}, status.Error(902, "token is expired")
	}

	apiKey, err := apikey.Pack(apiToken.UserId, apiToken.ApiToken).Encrypt(s.secretKey)

	if err != nil {

		log.Errorf("cant encrypt api key, api_token %v, error %v", apiToken, err)
		return &pb.ApiKeyStruct{}, status.Error(500, "cant encrypt api key")
	}

	return &pb.ApiKeyStruct{
		UserId:     apiToken.UserId,
		ApiKey:     string(apiKey),
		ExpiresAt:  apiToken.ExpiresAt.Unix(),
		Name:       apiToken.Name,
		ScopeList:  apiToken.ScopeListInt.ToString(),
		TemplateId: apiToken.Extra.Data.GetTemplateId(),
	}, nil
}

// ApikeyCreate Создаем ключ
func (s *ApiTokenServer) ApikeyCreate(ctx context.Context, in *pb.ApikeyCreateRequestStruct) (*pb.ApiKeyStruct, error) {

	var scopeList apitoken.ScopeList = in.GetScopeList()

	count, err := s.cacheManager.GetActiveCount(in.GetUserId())

	if err != nil {

		log.Errorf("database error %v", err)
		return &pb.ApiKeyStruct{}, status.Error(500, "database error")
	}

	if count > 100 {
		return &pb.ApiKeyStruct{}, status.Error(912, "api key count exceeded")
	}
	token, err := apitoken.GenerateToken()

	if err != nil {

		log.Errorf("generate token error %v", err)
		return &pb.ApiKeyStruct{}, status.Error(500, "cant generate token")
	}

	err = apitoken.ValidateTokenExpiresAt(in.GetExpiresAt())

	if err != nil {
		return &pb.ApiKeyStruct{}, status.Error(911, "invalid expires_at")
	}

	extra, err := apitoken.InitExtra()

	if err != nil {

		log.Errorf("cant init api token extra, error %v", err)
		return &pb.ApiKeyStruct{}, status.Error(500, "cant init extra")
	}

	apiTokenName := in.GetName()

	if in.GetTemplateId() > 0 {

		found := false
		for _, v := range s.apiKeyTemplatesConfig.ApiKeyTemplateList {

			if v.TemplateId == in.GetTemplateId() {

				apiTokenName = v.UniqName
				scopeList = v.ScopeList
				found = true
				break
			}

		}

		if !found {
			return &pb.ApiKeyStruct{}, status.Error(915, "unknown template_id")
		}

		extra.Data.SetTemplateId(in.GetTemplateId())
	}

	apiTokenName, err = apitoken.SanitazeTokenName(apiTokenName)

	if err != nil {
		return &pb.ApiKeyStruct{}, status.Error(910, "invalid token name")
	}

	// проверяем, что scope list передан верно
	scopeListInt := scopeList.ToInt()

	if len(scopeList) != len(scopeListInt) {
		return &pb.ApiKeyStruct{}, status.Error(916, "invalid scope list")
	}

	apiToken := &apitoken.ApiToken{
		UserId:       in.GetUserId(),
		ApiToken:     token,
		CreatedAt:    time.Now(),
		UpdatedAt:    time.Unix(0, 0),
		ExpiresAt:    time.Unix(in.GetExpiresAt(), 0),
		Name:         apiTokenName,
		ScopeListInt: scopeListInt.FilterNone(),
		Extra:        extra,
	}

	apiToken, err = s.cacheManager.Create(apiToken)
	if err != nil {

		log.Errorf("cant create api token, api_token %v, error %v", apiToken, err)
		return &pb.ApiKeyStruct{}, status.Error(500, "cant create token")
	}

	scopeList = apiToken.ScopeListInt.ToString()

	apiKey, err := apikey.Pack(apiToken.UserId, apiToken.ApiToken).Encrypt(s.secretKey)

	if err != nil {

		log.Errorf("cant encrypt api token, api_token %v, error %v", apiToken, err)
		return &pb.ApiKeyStruct{}, status.Error(500, "cant encrypt api key")
	}

	return &pb.ApiKeyStruct{
		UserId:     apiToken.UserId,
		ApiKey:     string(apiKey),
		ExpiresAt:  apiToken.ExpiresAt.Unix(),
		Name:       apiToken.Name,
		ScopeList:  scopeList,
		TemplateId: apiToken.Extra.Data.GetTemplateId(),
	}, nil
}

// ApikeyRefresh Пересоздаем ключ
// @long
func (s *ApiTokenServer) ApikeyRefresh(ctx context.Context, in *pb.ApikeyRefreshRequestStruct) (*pb.ApiKeyStruct, error) {

	t, err := apikey.Decrypt([]byte(in.GetApiKey()), s.secretKey)

	if err != nil {
		return &pb.ApiKeyStruct{}, status.Error(913, "failed to decrypt api key")
	}

	if t.UserId != in.GetUserId() {
		return &pb.ApiKeyStruct{}, status.Error(914, "api key belongs to another user_id")
	}

	oldApiToken, err := s.cacheManager.Get(t.UserId, t.Token)

	if err == apitoken.ErrApiTokenNotFound {
		return &pb.ApiKeyStruct{}, status.Error(901, "token not found")
	}

	if err != nil {

		log.Errorf("cache error, error %v", err)
		return &pb.ApiKeyStruct{}, status.Error(500, "unknown error")
	}

	if time.Since(oldApiToken.ExpiresAt) >= 0 {
		return &pb.ApiKeyStruct{}, status.Error(902, "token is expired")
	}

	token, err := apitoken.GenerateToken()

	if err != nil {

		log.Errorf("cant generate api token, error %v", err)
		return &pb.ApiKeyStruct{}, status.Error(500, "unknown error")
	}

	newApiToken := &apitoken.ApiToken{
		UserId:       in.GetUserId(),
		ApiToken:     token,
		CreatedAt:    time.Now(),
		UpdatedAt:    time.Unix(0, 0),
		ExpiresAt:    oldApiToken.ExpiresAt,
		Name:         oldApiToken.Name,
		ScopeListInt: oldApiToken.ScopeListInt,
		Extra:        oldApiToken.Extra,
	}

	// делаем проэкспайренным старый токен
	oldApiToken.ExpiresAt = time.Now()
	oldApiToken.UpdatedAt = time.Now()
	err = s.cacheManager.Update(oldApiToken)
	if err != nil {
		log.Errorf("cant update old token, api_token %v, error %v", oldApiToken, err)
	}

	// создаём новый ключ
	newApiToken, err = s.cacheManager.Create(newApiToken)

	if err != nil {

		log.Errorf("cant create api token, api_token %v, error %v", newApiToken, err)
		return &pb.ApiKeyStruct{}, status.Error(500, "unknown error")
	}

	scopeList := newApiToken.ScopeListInt.ToString()

	apiKey, err := apikey.Pack(newApiToken.UserId, newApiToken.ApiToken).Encrypt(s.secretKey)

	if err != nil {

		log.Errorf("cant encrypt api token, api_token %v, error %v", newApiToken, err)
		return &pb.ApiKeyStruct{}, status.Error(500, "cant encrypt api key")
	}

	return &pb.ApiKeyStruct{
		UserId:     newApiToken.UserId,
		ApiKey:     string(apiKey),
		ExpiresAt:  newApiToken.ExpiresAt.Unix(),
		Name:       newApiToken.Name,
		ScopeList:  scopeList,
		TemplateId: newApiToken.Extra.Data.GetTemplateId(),
	}, nil
}

// ApikeyEdit Редактируем ключ
func (s *ApiTokenServer) ApikeyEdit(ctx context.Context, in *pb.ApikeyEditRequestStruct) (*pb.ApiKeyStruct, error) {

	t, err := apikey.Decrypt([]byte(in.GetApiKey()), s.secretKey)

	if err != nil {
		return &pb.ApiKeyStruct{}, status.Error(913, "failed to decrypt api key")
	}

	if t.UserId != in.GetUserId() {
		return &pb.ApiKeyStruct{}, status.Error(914, "api key belongs to another user_id")
	}

	apiToken, err := s.cacheManager.Get(t.UserId, t.Token)

	if err == apitoken.ErrApiTokenNotFound {
		return &pb.ApiKeyStruct{}, status.Error(901, "token not found")
	}

	if err != nil {

		log.Errorf("cache error, error %v", err)
		return &pb.ApiKeyStruct{}, status.Error(500, "unknown error")
	}

	if time.Since(apiToken.ExpiresAt) >= 0 {
		return &pb.ApiKeyStruct{}, status.Error(902, "token is expired")
	}

	apiTokenName := apiToken.Name

	if in.GetName() != "" {

		apiTokenName, err = apitoken.SanitazeTokenName(in.GetName())

		if err != nil {
			return &pb.ApiKeyStruct{}, status.Error(910, "invalid token name")
		}

		// если обновился name - обнуляем template_id
		apiToken.Extra.Data.SetTemplateId(0)
	}

	expiresAt := apiToken.ExpiresAt

	if in.GetExpiresAt() > 0 {

		err = apitoken.ValidateTokenExpiresAt(in.GetExpiresAt())

		if err != nil {
			return &pb.ApiKeyStruct{}, status.Error(911, "invalid expires_at")
		}

		expiresAt = time.Unix(in.GetExpiresAt(), 0)
	}

	scopeListInt := apiToken.ScopeListInt
	if len(in.GetScopeList()) > 0 {

		var scopeList apitoken.ScopeList = in.GetScopeList()

		// проверяем, что scope list передан верно
		scopeListInt = scopeList.ToInt()

		if len(scopeList) != len(scopeListInt) {
			return &pb.ApiKeyStruct{}, status.Error(916, "invalid scope list")
		}

		// если обновился scope_list - обнуляем template_id
		apiToken.Extra.Data.SetTemplateId(0)
	}

	apiToken.UpdatedAt = time.Now()
	apiToken.ExpiresAt = expiresAt
	apiToken.Name = apiTokenName
	apiToken.ScopeListInt = scopeListInt.FilterNone()

	err = s.cacheManager.Update(apiToken)
	if err != nil {

		log.Errorf("cant update api token, api_token, %v, error %v", apiToken, err)
		return &pb.ApiKeyStruct{}, status.Error(500, "cant update token")
	}

	return &pb.ApiKeyStruct{
		UserId:     apiToken.UserId,
		ApiKey:     in.GetApiKey(),
		ExpiresAt:  apiToken.ExpiresAt.Unix(),
		Name:       apiToken.Name,
		ScopeList:  apiToken.ScopeListInt.ToString(),
		TemplateId: apiToken.Extra.Data.GetTemplateId(),
	}, nil
}

// ApikeyRemove Удаляем ключ
func (s *ApiTokenServer) ApikeyRemove(ctx context.Context, in *pb.ApikeyRemoveRequestStruct) (*pb.ApikeyRemoveResponseStruct, error) {

	t, err := apikey.Decrypt([]byte(in.GetApiKey()), s.secretKey)

	if err != nil {
		return &pb.ApikeyRemoveResponseStruct{}, status.Error(913, "failed to decrypt api key")
	}

	if t.UserId != in.GetUserId() {
		return &pb.ApikeyRemoveResponseStruct{}, status.Error(914, "api key belongs to another user_id")
	}

	apiToken, err := s.cacheManager.Get(t.UserId, t.Token)

	if err == apitoken.ErrApiTokenNotFound {
		return &pb.ApikeyRemoveResponseStruct{}, status.Error(901, "token not found")
	}

	if err != nil {

		log.Errorf("cache error, error %v", err)
		return &pb.ApikeyRemoveResponseStruct{}, status.Error(500, "unknown error")
	}

	// если уже просрочен, то значит удален уже
	if time.Since(apiToken.ExpiresAt) >= 0 {
		return &pb.ApikeyRemoveResponseStruct{}, nil
	}

	apiToken.ExpiresAt = time.Now()
	apiToken.UpdatedAt = time.Now()

	err = s.cacheManager.Update(apiToken)

	if err != nil {

		log.Errorf("cant delete token, api_token %v, error %v", apiToken, err)
		return &pb.ApikeyRemoveResponseStruct{}, status.Error(500, "cant delete token")
	}

	return &pb.ApikeyRemoveResponseStruct{}, nil
}

// ApikeyRemoveAll Удаляем ключи пользователя
func (s *ApiTokenServer) ApikeyRemoveAll(ctx context.Context, in *pb.ApikeyRemoveAllRequestStruct) (*pb.ApikeyRemoveAllResponseStruct, error) {

	apiTokenList, err := s.cacheManager.GetList(in.GetUserId())
	if err != nil {

		log.Errorf("cant get token list for remove, error %v", err)
		return &pb.ApikeyRemoveAllResponseStruct{}, status.Error(500, "unknown error")
	}

	for _, apiToken := range apiTokenList {

		// если уже просрочен, то значит удален уже
		if time.Since(apiToken.ExpiresAt) >= 0 {
			continue
		}

		apiToken.ExpiresAt = time.Now()
		apiToken.UpdatedAt = time.Now()

		err = s.cacheManager.Update(apiToken)
		if err != nil {

			log.Errorf("cant delete token, api_token %v, error %v", apiToken, err)
			continue
		}
	}

	return &pb.ApikeyRemoveAllResponseStruct{}, nil
}

// ApikeyGetList Получаем массив ключей
func (s *ApiTokenServer) ApikeyGetList(ctx context.Context, in *pb.ApikeyGetListRequestStruct) (*pb.ApiKeyListStruct, error) {

	apikeyInfoList, err := s.cacheManager.GetList(in.GetUserId())
	if err != nil {

		log.Errorf("cant get token list, error %v", err)
		return &pb.ApiKeyListStruct{}, status.Error(500, "unknown error")
	}

	apiKeyList := make([]*pb.ApiKeyStruct, 0, len(apikeyInfoList))

	for _, apiKeyInfo := range apikeyInfoList {

		apiKey, err := prepareApiKeyResponseStruct(apiKeyInfo, s.secretKey)

		if err != nil {

			log.Errorf("invalid scope_list for user_id = %d, api_token = %s", apiKeyInfo.UserId, apiKeyInfo.ApiToken)
			continue
		}

		if time.Since(apiKeyInfo.ExpiresAt) >= 0 {
			continue
		}

		apiKeyList = append(apiKeyList, apiKey)
	}

	return &pb.ApiKeyListStruct{
		ApiKeyList: apiKeyList,
	}, nil
}

// получить темплейты для создания API ключей
func (s *ApiTokenServer) ApikeyGetTemplateList(ctx context.Context, in *pb.ApikeyGetTemplateListRequestStruct) (*pb.ApiKeyTemplateListStruct, error) {

	r := make([]*pb.ApiKeyTemplateStruct, 0, len(s.apiKeyTemplatesConfig.ApiKeyTemplateList))

	for _, template := range s.apiKeyTemplatesConfig.ApiKeyTemplateList {

		t := &pb.ApiKeyTemplateStruct{
			TemplateId:  template.TemplateId,
			Order:       template.Order,
			Title:       template.Title,
			UniqName:    template.UniqName,
			Description: template.Description,
			ScopeList:   template.ScopeList,
		}

		r = append(r, t)
	}

	return &pb.ApiKeyTemplateListStruct{
		ApikeyTemplateList: r,
	}, nil
}

// собираем массив ключей в объект ApikeyGetResponseStruct
func prepareApiKeyResponseStruct(apiToken *apitoken.ApiToken, secretKey []byte) (*pb.ApiKeyStruct, error) {

	scopeList := apiToken.ScopeListInt.ToString()

	apiKey, err := apikey.Pack(apiToken.UserId, apiToken.ApiToken).Encrypt(secretKey)

	if err != nil {

		log.Errorf("cant encrypt api key %v, error %v", apiToken, err)
		return &pb.ApiKeyStruct{}, status.Error(500, "cant encrypt api key")
	}

	return &pb.ApiKeyStruct{
		UserId:     apiToken.UserId,
		ApiKey:     string(apiKey),
		ExpiresAt:  apiToken.ExpiresAt.Unix(),
		Name:       apiToken.Name,
		ScopeList:  scopeList,
		TemplateId: apiToken.Extra.Data.GetTemplateId(),
	}, nil
}
