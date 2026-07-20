package CompanyConfig

import (
	"context"
	"errors"
	"fmt"
	"go_event/api/conf"
	Isolation "go_event/api/includes/type/isolation"
	"os"
	"strings"
	"sync"
	"time"

	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
)

const vacantCompanyStatus = 1
const activeCompanyStatus = 2

// тип хранилища изоляций
type IsolationStorage struct {
	mx           sync.RWMutex
	isolationMap map[int64]*Isolation.Isolation
}

// тип менеджера изоляции
type IsolationManager struct {
	isolationStorage      *IsolationStorage
	invalidateWorkerCount int
	invalidateCh          chan *Isolation.Isolation
}

// экземпляр дефолтного менеджера изоляций
var DefaultManager = &IsolationManager{
	isolationStorage: getIsolationStorage(),
	invalidateCh:     make(chan *Isolation.Isolation, 1000),
}

// getIsolationStorage получить экземпляр хранилища изоляции
func getIsolationStorage() *IsolationStorage {
	return &IsolationStorage{
		isolationMap: map[int64]*Isolation.Isolation{},
	}
}

// InitWorkers инициализировать воркеры для управления изоляциями
func InitWorkers(ctx context.Context, invalidateWorkerCount int) {

	// в конце работы завершаем все изоляции
	defer func() {

		// останавливаем все изоляции
		for _, isolation := range DefaultManager.isolationStorage.isolationMap {

			companyId := isolation.GetCompanyId()

			// останавливаем все, что было привязано к изоляции
			DefaultManager.isolationStorage.stopEnv(companyId)
		}
	}()

	var wg sync.WaitGroup

	DefaultManager.invalidateWorkerCount = invalidateWorkerCount

	// запускаем воркеры для инвалидации изоляций
	wg.Add(DefaultManager.invalidateWorkerCount)
	go DefaultManager.invalidationWorkers(ctx, &wg)

	// запускаем воркер обновления конфигурации изоляций
	wg.Add(1)
	go DefaultManager.updateConfigWorker(ctx, &wg)

	// блокируем функцию, ожидаем завершения всех запущенных воркеров
	wg.Wait()

}

// invalidationWorkers запустить вокреры для инвалидации
func (m *IsolationManager) invalidationWorkers(ctx context.Context, wg *sync.WaitGroup) {

	for w := 0; w < m.invalidateWorkerCount; w++ {
		go m.handleInvalidation(ctx, wg)
	}

}

// handleInvalidation рутина инвалидации изоляций
func (m *IsolationManager) handleInvalidation(ctx context.Context, wg *sync.WaitGroup) {

	defer wg.Done()

	Inc("invalidate-isolation-routine")
	defer Dec("invalidate-isolation-routine")

	for {
		select {
		case <-ctx.Done():

			return
		case i := <-m.invalidateCh:

			companyId := i.GetCompanyId()

			// останавливаем все, что было привязано к изоляции
			m.isolationStorage.stopEnv(companyId)
		}
	}
}

// updateConfigWorker воркер для периодического обновления конфигурации изоляций
func (m *IsolationManager) updateConfigWorker(ctx context.Context, wg *sync.WaitGroup) {

	defer wg.Done()

	Inc("update-isolation-routine")
	defer Dec("update-isolation-routine")

	ticker := time.NewTicker(100 * time.Millisecond)

	for {
		select {
		case <-ctx.Done():
			return
		case <-ticker.C:
			m.UpdateWorldConfig()
		}
	}
}

// invalidateNonExisting сравниваем существующие изоляции со списком актуальных и удаляем устаревшие
func (m *IsolationManager) invalidateNonExisting(servedIdList map[int64]bool) {

	m.isolationStorage.mx.RLock()
	defer m.isolationStorage.mx.RUnlock()

	for _, isolation := range m.isolationStorage.isolationMap {

		// изоляция есть в списке
		if _, isExist := servedIdList[isolation.GetCompanyId()]; isExist {
			continue
		}

		m.invalidateCh <- isolation
	}
}

// UpdateWorldConfig обновляем конфигурацию изоляций
func (m *IsolationManager) UpdateWorldConfig() {

	globalConfig := conf.GetConfig()

	timestampFilename := ".timestamp.json"

	if !checkFileTimeStamp(globalConfig, timestampFilename) {
		return
	}

	startedAt := time.Now()
	files, err := os.ReadDir(globalConfig.WorldConfigPath)

	if err != nil {
		panic(err)
	}

	servedCompanyIdMap := map[int64]bool{}

	for _, file := range files {

		// пропускаем ненужные файлы и файлы, которые не менялись
		if file.Name() == ".timestamp.json" {
			continue
		}

		companyId, err := getCompanyIdByFileName(file)
		if err != nil {
			continue
		}

		// если файл с конфигом нормальный, но не обновился,
		// то ничего не делаем с компанией
		if !checkFileTimeStamp(globalConfig, file.Name()) {

			servedCompanyIdMap[companyId] = true
			continue
		}

		companyConfig, err := conf.GetWorldConfig(conf.ResolveConfigByCompanyId(companyId))
		if err != nil {

			log.Errorf("invalid company config: %v", err)
			continue
		}

		// проверяем, что статус компании в конфиге позволяет нам поднимать изоляцию
		if companyConfig.Status != activeCompanyStatus && companyConfig.Status != vacantCompanyStatus {
			continue
		}

		if _, err = m.isolationStorage.startEnv(companyId); err != nil {

			log.Errorf("company isolation error %s", err.Error())
			continue
		}

		servedCompanyIdMap[companyId] = true
	}

	log.Infof("company configs were refreshed in %dms", time.Since(startedAt).Milliseconds())
	go m.invalidateNonExisting(servedCompanyIdMap)
}

// GetEnv возвращает среду исполнения для компании
// если сервис не обслуживает компанию, то вернет ошибку
func (m *IsolationManager) GetEnv(companyId int64) *Isolation.Isolation {

	if companyId == 0 {
		return Isolation.Global()
	}

	m.isolationStorage.mx.RLock()
	isolation, exists := m.isolationStorage.isolationMap[companyId]
	m.isolationStorage.mx.RUnlock()

	if exists {
		return isolation
	}

	log.Warningf(fmt.Sprintf("isolation for company %d not found", companyId))
	return nil
}

// IterateOverActive пробегает по всем списку изоляция и применят к ним указанную функцию,
// эта функция блокирует список изоляций, ее нельзя вызывать внутри блоков с ожиданием
func (m *IsolationManager) IterateOverActive(fn func(isolation *Isolation.Isolation)) {

	m.isolationStorage.mx.RLock()
	defer m.isolationStorage.mx.RUnlock()

	for _, isolation := range m.isolationStorage.isolationMap {
		fn(isolation)
	}
}

// startEnv инициализирует новую среду для компании
// если сервис больше не может вмещать в себя компании, возвращает ошибку
func (s *IsolationStorage) startEnv(companyId int64) (*Isolation.Isolation, error) {

	s.mx.Lock()
	defer s.mx.Unlock()

	// проверяем, возможно изоляция существует
	isolation, isExist := s.isolationMap[companyId]

	if isExist {
		return isolation, nil
	}

	if len(s.isolationMap) >= conf.GetConfig().CapacityLimit {

		return nil, errors.New("service capacity limit reached")
	}

	log.Info(fmt.Sprintf("инициализирую изоляцю для компании %d", companyId))

	// генерируем новый контекст исполнения для сервиса
	isolation, err := Isolation.MakeIsolation(fmt.Sprintf("%d:%s", companyId, functions.GenerateUuid()), companyId)

	if err != nil {
		return nil, err
	}

	s.isolationMap[companyId] = isolation

	log.Info(fmt.Sprintf("изоляция для компании %d инициализиорована", companyId))
	Inc("company-isolation-count")

	return isolation, nil
}

// останавливает среду исполнения компании
// если сервис не обслуживает компанию, то вернет ошибку
func (s *IsolationStorage) stopEnv(companyId int64) {

	s.mx.Lock()
	defer s.mx.Unlock()

	isolation, exists := s.isolationMap[companyId]
	if !exists {
		return
	}

	// говорим всем пакетам, что изоляцию более обслуживать не нужно
	delete(s.isolationMap, companyId)
	if err := isolation.Invalidate(); err != nil {

		log.Errorf("ошибка инвалидации изоляции компании %d — %s", companyId, err.Error())
		return
	}

	log.Errorf("инвалидирую изоляцию для компании %d, компания более не обслуживается", companyId)
	Dec("company-isolation-count")
}

// получает ид компании из имени файла
func getCompanyIdByFileName(file os.DirEntry) (int64, error) {

	splitString := strings.Split(file.Name(), "_")
	if len(splitString) != 2 || splitString[1] != "company.json" {
		return 0, fmt.Errorf("invalid config file: %v", file.Name())
	}

	companyId := functions.StringToInt64(splitString[0])
	if companyId < 1 {
		return 0, fmt.Errorf("invalid company_id in config file: %v", file.Name())
	}

	return companyId, nil
}
