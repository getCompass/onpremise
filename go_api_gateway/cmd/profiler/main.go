package profiler

import (
	"go_api_gateway/internal/config"
	"net/http"
)

func Exec() {

	// инициализируем конфиги. Если какой-то не смогли - сразу падаем, гейтвей не заработает
	mainConfig, err := config.LoadMainConfig()

	if err != nil {
		return
	}

	// врубаем ендпоинт для профайлера на тестовом
	if mainConfig.ServerType == "test-server" {

		go func() {

			err := http.ListenAndServe("0.0.0.0:6060", nil) // nosemgrep
			if err != nil {
				return
			}
		}()
	}

}
