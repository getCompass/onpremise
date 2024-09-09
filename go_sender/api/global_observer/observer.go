package global_observer

import (
	"context"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_sender/api/conf"
	companyContext "go_sender/api/includes/type/company_context"
	"go_sender/api/includes/type/db/company_data"
	GlobalIsolation "go_sender/api/includes/type/global_isolation"
	Isolation "go_sender/api/includes/type/isolation"
	"go_sender/api/includes/type/pusher"
	"go_sender/api/observer"
	"time"
)

// WorkPivotObserver Work метод для выполнения работы через время
func WorkPivotObserver(ctx context.Context, globalIsolation *GlobalIsolation.GlobalIsolation, companyContextList *Isolation.CompanyEnvList) {

	config := &conf.CompanyConfigStruct{
		Status: Isolation.ActiveCompanyStatus,
	}
	startPivotEnv(ctx, globalIsolation, companyContextList, 0, config)
}

// WorkAnnouncementObserver Work метод для выполнения работы через время
func WorkAnnouncementObserver(ctx context.Context, globalIsolation *GlobalIsolation.GlobalIsolation, companyContextList *Isolation.CompanyEnvList) {

	config := &conf.CompanyConfigStruct{
		Status: Isolation.ActiveCompanyStatus,
	}
	startPivotEnv(ctx, globalIsolation, companyContextList, -1, config)
}

// WorkDominoObserver Work метод для выполнения работы через время
func WorkDominoObserver(ctx context.Context, globalIsolation *GlobalIsolation.GlobalIsolation, companyContextList *Isolation.CompanyEnvList) {

	// делаем сначало синхронно
	updateCompanyList(ctx, globalIsolation, companyContextList)

	go func() {

		for {

			// используем select для выхода по истечении времени жизни контекста
			select {

			case <-time.After(time.Second):

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
		startDominoEnv(ctx, globalIsolation, companyContextList, companyId, config)
	}

	// получим список обслуживаемых компаний
	companyIdList := companyContextList.GetCompanyIdList()

	// пройдемся по компаниям которые остались в списке обслуживания и удалим их
	for companyId := range companyIdList {

		// супер-решение для глобальной изоляции,
		// которая зачем-то идентифицирует себя как изоляция компании
		if companyId < 1 {
			continue
		}

		if _, exist := allCompanyList[companyId]; !exist {

			log.Infof("Конфига больше нет удаляем компанию %d", companyId)
			companyContextList.StopEnv(companyId)
		}
	}
}

// запускаем pivot env
func startPivotEnv(ctx context.Context, globalIsolation *GlobalIsolation.GlobalIsolation, companyContextList *Isolation.CompanyEnvList, companyId int64, config *conf.CompanyConfigStruct) {

	var companyDataConnProvider = func() *company_data.DbConn {
		return nil
	}

	pusherConn := pusher.MakePusherConn(globalIsolation.GetConfig().CurrentServer, globalIsolation.GetConfig().SocketKeyMe, 0)
	isolation, companyCtx := companyContextList.StartEnv(
		ctx, companyId, config, globalIsolation.GetConfig().CapacityLimit, globalIsolation, companyDataConnProvider, pusherConn,
	)

	if isolation == nil {
		return
	}

	// запускаем observer по ней
	observer.WorkCompanyObserver(companyCtx, isolation)

	go shutdownEnv(ctx, isolation)
}

// запускаем domino env
func startDominoEnv(ctx context.Context, globalIsolation *GlobalIsolation.GlobalIsolation, companyContextList *Isolation.CompanyEnvList, companyId int64, config *conf.CompanyConfigStruct) (isSuccess bool) {

	defer func() {

		if err := recover(); err != nil {
			log.Error(fmt.Sprintf("Recovered. Error during isolation %d initialization: %s", companyId, err))
		}
	}()

	// вот этого тут быть не должно, но из-за того, что как-то криво получается сокет ключ
	// для пушера без этой конструкции сервис долго тупит и докер его перезагружает
	if config.Status != Isolation.ActiveCompanyStatus && config.Status != Isolation.VacantCompanyStatus {

		log.Infof("Компания не активная останавливаем %d", companyId)
		companyContextList.StopEnv(companyId)

		return
	}

	var companyDataConnProvider = func() *company_data.DbConn {

		host := fmt.Sprintf("%s:%d", config.Mysql.Host, config.Mysql.Port)
		companyDataConn, err := company_data.MakeConnection(ctx, host, config.Mysql.User, config.Mysql.Pass, globalIsolation.GetShardingConfig().Mysql.MaxConnections)
		if err != nil {
			panic(fmt.Sprintf("no connect to db %s", host))
		}

		return companyDataConn
	}

	pusherConn := pusher.MakePusherConn(globalIsolation.GetConfig().CurrentServer, globalIsolation.GetConfig().SocketKeyMe, companyId)
	isolation, companyCtx := companyContextList.StartEnv(
		ctx, companyId, config, globalIsolation.GetConfig().CapacityLimit, globalIsolation, companyDataConnProvider, pusherConn,
	)

	if isolation == nil {
		return
	}

	// запускаем observer по ней
	observer.WorkCompanyObserver(companyCtx, isolation)

	go shutdownEnv(companyCtx, isolation)
	isSuccess = true

	return isSuccess
}

// shutdown окружение, когда сработает контекст закроются коннекты
func shutdownEnv(ctx context.Context, isolation *Isolation.Isolation) {

	select {

	case <-ctx.Done():

		isolation.UserConnectionStore.CloseAllConnections()
		log.Infof("Закрыли все коннекты к компании %d", isolation.GetCompanyId())
		return

	}
}
