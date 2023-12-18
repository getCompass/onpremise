package global_observer

import (
	"context"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	companyContext "go_company_cache/api/includes/type/company_context"
	GlobalIsolation "go_company_cache/api/includes/type/global_isolation"
	Isolation "go_company_cache/api/includes/type/isolation"
	"go_company_cache/api/observer"
	"time"
)

// WorkGlobalObserver Work метод для выполнения работы через время
func WorkGlobalObserver(ctx context.Context, globalIsolation *GlobalIsolation.GlobalIsolation, companyContextList *Isolation.CompanyEnvList) {

	// делаем сначало синхронно
	updateCompanyList(ctx, globalIsolation, companyContextList)

	go func() {

		for {

			// используем select для выхода по истечении времени жизни контекста
			select {

			case <-time.After(time.Millisecond):

				updateCompanyList(ctx, globalIsolation, companyContextList)

			case <-ctx.Done():

				log.Infof("Закрыли глобальный обсервер")
				return

			}
		}
	}()
}

// обновляем список компаний
func updateCompanyList(ctx context.Context, globalIsolation *GlobalIsolation.GlobalIsolation, companyContextList *Isolation.CompanyEnvList) {

	configList, allCompanyList, err := companyContext.UpdateWorldConfig(globalIsolation)
	if err != nil {
		log.Error(err.Error())
	}

	// случай когда нам не надо обновлять ничего
	if allCompanyList == nil {
		return
	}

	for companyId, config := range configList {

		isolation, companyCtx := companyContextList.StartEnv(
			ctx, companyId, config, globalIsolation.GetConfig().CapacityLimit, globalIsolation.GetShardingConfig().Mysql.MaxConnections, globalIsolation,
		)

		if isolation != nil {

			// запускаем observer по ней
			observer.WorkCompanyObserver(companyCtx, isolation)
		}
	}

	// получим список обслуживаемых компаний
	companyIdList := companyContextList.GetCompanyIdList()

	// пройдемся по компаниям которые остались в списке обслуживания и удалим их
	for companyId := range companyIdList {

		_, exist := allCompanyList[companyId]
		if !exist {

			log.Infof("Конфига больше нет удаляем компанию %d", companyId)
			companyContextList.StopEnv(companyId)
		}
	}

}
