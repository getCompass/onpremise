package proxy

import (
	"go_api_gateway/internal/grpc"
	"net/http"
	"strings"

	"google.golang.org/grpc/status"
)

// получить информацию о текущем API ключе в хедере
func (ph *ProxyHandler) GetCurrent(r *http.Request) (*ResponseStruct, *RequestError) {

	authHeader := r.Header.Get("Authorization")
	apiKeyB58, found := strings.CutPrefix(authHeader, "Api-Key ")

	if !found {
		return nil, &RequestError{http.StatusUnauthorized, "api key not found in header"}
	}
	authResponse, err := ph.authClient.ApikeyGet(apiKeyB58)

	if err != nil {
		if e, ok := status.FromError(err); ok {
			switch e.Code() {
			case grpc.ErrTokenExpired, grpc.ErrTokenNotFound, grpc.ErrTokenInvalid:
				return nil, &RequestError{http.StatusUnauthorized, "api key not found in auth service"}
			default:
				return nil, &RequestError{http.StatusInternalServerError, "unknown error while getting apikey data by grpc"}
			}

		} else {
			return nil, &RequestError{http.StatusInternalServerError, "unknown error while getting apikey data by grpc"}
		}
	}

	response := &ResponseStruct{
		Status: "ok",
		Response: map[string]any{
			"api_key_data": authResponse,
		},
	}

	return response, nil
}
