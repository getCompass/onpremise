package auth

import (
	"context"
	"fmt"
	"go_auth/internal/apitoken"
	"go_auth/internal/config"
	"go_auth/internal/database"
	"go_auth/internal/grpc"
	"time"

	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"github.com/getCompassUtils/go_base_frame/api/system/server"
)

func Exec(ctx context.Context) chan error {

	// инициализируем конфиги. Если какой-то не смогли - сразу падаем, модуль не заработает
	mainConfig, err := config.LoadMainConfig()

	if err != nil {
		panic(err)
	}

	// устанавливаем уровень лога и инициализируем сервер
	err = server.Init(mainConfig.ServerTagList, mainConfig.ServiceLabel, "", "")
	log.SetLoggingLevel(mainConfig.LoggingLevel)

	databaseConfig, err := config.LoadDatabaseConfig()

	if err != nil {
		panic(err)
	}

	cacheConfig, err := config.LoadCacheConfig()

	if err != nil {
		panic(err)
	}

	apiKeyTemplatesConfig, err := config.LoadApiKeyTemplatesConfig()

	if err != nil {
		panic(err)
	}

	grpcConfig, err := config.LoadGrpcConfig()

	if err != nil {
		panic(err)
	}
	// инициализируем подключение к базе данных
	database, err := database.InitConnection(ctx, databaseConfig, "auth")

	if err != nil {
		panic(err)
	}

	// инициализируем кеш для токенов
	apiTokenCache := apitoken.InitCache(
		time.Duration(cacheConfig.ItemTTLSec)*time.Second,
		time.Duration(cacheConfig.NegativeItemTTLSec)*time.Second,
	)

	// инициализируем менеджер кеша
	apiTokenCacheManager := apitoken.InitCacheManager(database, apiTokenCache)

	// инициализируем grpc сервер
	serverErr := make(chan (error))
	apiTokenServer := grpc.InitApiTokenServer(apiTokenCacheManager, apiKeyTemplatesConfig, mainConfig.AuthSecretKeyB64)

	go func() {

		// обрабатываем панику
		defer func() {
			if r := recover(); r != nil {
				fmt.Println("Panic at GRPC Server", r)
				serverErr <- fmt.Errorf("panic, need shutdown")
			}
		}()

		// ожидаем grpc подключения
		if err := apiTokenServer.Listen("0.0.0.0", grpcConfig.GrpcPort); err != nil {
			serverErr <- err
		}
	}()

	return serverErr
}
