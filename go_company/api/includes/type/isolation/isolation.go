package Isolation

import (
	"context"
	"errors"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_company/api/conf"
	"go_company/api/includes/type/db/company_conversation"
	"go_company/api/includes/type/db/company_data"
	"go_company/api/includes/type/db/company_thread"
	GlobalIsolation "go_company/api/includes/type/global_isolation"
	"go_company/api/includes/type/rating"
	reactionStorage "go_company/api/includes/type/reaction_storage"
	readMessageStorage "go_company/api/includes/type/read_message_storage"
	"go_company/api/includes/type/storage"
	"go_company/api/includes/type/timer"
	"sync"
)

type CompanyEnvList struct {
	mutex   sync.Mutex
	envList map[int64]*Isolation
}

const vacantCompanyStatus = 1
const activeCompanyStatus = 2

// MakeCompanyEnvList создаем env
func MakeCompanyEnvList() *CompanyEnvList {

	return &CompanyEnvList{
		mutex:   sync.Mutex{},
		envList: make(map[int64]*Isolation),
	}
}

// инициализирует новую среду для компании
// если сервис больше не может вмещать в себя компании, возвращает ошибку
func (companyEnv *CompanyEnvList) StartEnv(ctx context.Context, companyId int64, companyConfig *conf.CompanyConfigStruct, capacityLimit int, mysqlMaxConn int,
	globalIsolation *GlobalIsolation.GlobalIsolation) (*Isolation, context.Context, error) {

	companyEnv.mutex.Lock()
	_, exists := companyEnv.envList[companyId]
	companyEnv.mutex.Unlock()

	if companyConfig.Status != activeCompanyStatus && companyConfig.Status != vacantCompanyStatus {

		if exists {
			companyEnv.stopEnv(companyId)
		}

		return nil, nil, nil
	}

	if exists {
		return nil, nil, nil
	}

	if len(companyEnv.envList) >= capacityLimit {

		log.Errorf("service capacity limit reached")
		return nil, nil, nil
	}

	companyContext, cancel := context.WithCancel(ctx)

	// генерируем новый контекст исполнения для сервиса
	isolation, err := MakeIsolation(companyContext, companyId, companyConfig, cancel, mysqlMaxConn, globalIsolation)
	if err != nil {

		log.Errorf("Failed to create isolation for %d. Error: %d", companyId, err)
		return nil, nil, err
	}

	// добавляешь в хранилище
	companyEnv.mutex.Lock()
	companyEnv.envList[companyId] = isolation
	companyEnv.mutex.Unlock()

	log.Infof("added isolation for company %d", companyId)
	return isolation, companyContext, nil
}

// останавливает среду исполнения компании
// если сервис не обслуживает компанию, то вернет ошибку
func (companyEnv *CompanyEnvList) stopEnv(companyId int64) {

	companyEnv.mutex.Lock()
	isolation, exists := companyEnv.envList[companyId]
	delete(companyEnv.envList, companyId)
	companyEnv.mutex.Unlock()
	if !exists {
		return
	}

	// удаляем все observer
	isolation.Cancel()

	// завершаем все подключения к базе
	err := isolation.CompanyDataConn.Conn.Close()
	if err != nil {
		log.Error(err.Error())
	}

	err = isolation.CompanyConversationConn.Conn.Close()
	if err != nil {
		log.Error(err.Error())
	}

	err = isolation.CompanyThreadConn.Conn.Close()
	if err != nil {
		log.Error(err.Error())
	}

	log.Infof("company %d not served", companyId)

}

// GetEnv возвращает среду исполнения для компании
func (companyEnv *CompanyEnvList) GetEnv(companyId int64) *Isolation {

	companyEnv.mutex.Lock()
	isolation, exists := companyEnv.envList[companyId]
	companyEnv.mutex.Unlock()

	if exists {
		return isolation
	}

	log.Debug(fmt.Sprintf("isolation for company %d not found", companyId))
	return nil
}

/** Пакет описывает сущность изоляции исполнения внутри модуля
  Изоляция может быть связана с компанией или быть глобальной для сервиса **/

// структура контекста компании
type Isolation struct {
	companyId               int64
	companyConfig           *conf.CompanyConfigStruct
	RatingStore             *rating.Store
	ReactionStore           *reactionStorage.ReactionQueueStruct
	ReadMessageStore        *readMessageStorage.ReadMessageQueueStruct
	TimerStore              *timer.Store
	Cancel                  context.CancelFunc
	Context                 context.Context
	CompanyConversationConn *company_conversation.DbConn
	CompanyThreadConn       *company_thread.DbConn
	CompanyDataConn         *company_data.DbConn
	SocketKeyToPivot        string
	globalIsolation         *GlobalIsolation.GlobalIsolation
	UserRatingByDays        *rating.UserRatingByDaysStore
	MainStorage             *storage.MainStorage
}

// возвращает ид компании для изоляции
func (i *Isolation) GetCompanyId() int64 {

	return i.companyId
}

// возвращает ид компании для изоляции
func (i *Isolation) GetGlobalIsolation() *GlobalIsolation.GlobalIsolation {

	return i.globalIsolation
}

// возвращает ид компании для изоляции
func (i *Isolation) GetCompanyConfig() *conf.CompanyConfigStruct {

	return i.companyConfig
}

// MakeIsolation возвращает новую локальную изоляцию сервиса
func MakeIsolation(ctx context.Context, companyId int64, companyConfig *conf.CompanyConfigStruct, cancel context.CancelFunc, mysqlMaxConn int, globalIsolation *GlobalIsolation.GlobalIsolation) (*Isolation, error) {

	if companyId == 0 {
		return nil, errors.New("company id must be defined in non-global context")
	}

	host := fmt.Sprintf("%s:%d", companyConfig.Mysql.Host, companyConfig.Mysql.Port)

	companyConversationConn, err := company_conversation.MakeCompanyConversation(ctx, host, companyConfig.Mysql.User, companyConfig.Mysql.Pass, mysqlMaxConn)
	if err != nil {
		return nil, err
	}

	companyThreadConn, err := company_thread.MakeCompanyThread(ctx, host, companyConfig.Mysql.User, companyConfig.Mysql.Pass, mysqlMaxConn)
	if err != nil {
		return nil, err
	}

	companyDataConn, err := company_data.MakeCompanyData(ctx, host, companyConfig.Mysql.User, companyConfig.Mysql.Pass, mysqlMaxConn)
	if err != nil {
		return nil, err
	}

	isolation := Isolation{
		companyId:               companyId,
		companyConfig:           companyConfig,
		Cancel:                  cancel,
		Context:                 ctx,
		CompanyConversationConn: companyConversationConn,
		CompanyThreadConn:       companyThreadConn,
		CompanyDataConn:         companyDataConn,
		globalIsolation:         globalIsolation,
		RatingStore:             rating.MakeStore(),
		UserRatingByDays:        rating.MakeUserRatingByDaysStore(),
		TimerStore:              timer.MakeStore(),
		ReactionStore:           reactionStorage.MakeReactionStore(),
		ReadMessageStore:        readMessageStorage.MakeReadMessageStore(),
		MainStorage:             storage.MakeMainStorage(),
	}

	return &isolation, nil
}
