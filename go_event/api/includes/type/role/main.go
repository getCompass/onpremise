package role

import (
	"context"
	"go_event/api/conf"
	CompanyConfig "go_event/api/includes/type/company_config"
	"strings"
)

// Begin запускает задачи сервиса в зависимости от роли
func Begin(ctx context.Context) {

	if !isDomino() {
		return
	}

	CompanyConfig.DefaultManager.UpdateWorldConfig()
	go CompanyConfig.InitWorkers(ctx, conf.GetConfig().InvalidationWorkerCount)
}

// isDomino проверяет, содержится ли 'domino' в строке role
func isDomino() bool {
	return strings.Contains(conf.GetConfig().ServiceRoleSet, "domino")
}
