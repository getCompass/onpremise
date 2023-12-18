package role

import (
	"context"
	"go_event/api/conf"
	CompanyConfig "go_event/api/includes/type/company_config"
	"go_event/api/observer"
	"strings"
)

// Begin запускает задачи сервиса в зависимости от роли
func Begin(ctx context.Context) {

	if !isDomino() {
		return
	}

	CompanyConfig.UpdateWorldConfig()
	observer.Work(ctx)
}

// isDomino проверяет, содержится ли 'domino' в строке role
func isDomino() bool {
	return strings.Contains(conf.GetConfig().ServiceRoleSet, "domino")
}
