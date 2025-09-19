package CompanyConfig

// Пакет описывает использование изоляции как изолированной среды компании.
// Когда компания начинает обслуживаться сервисом, она первым делом проходит через этот пакет
//
// ЭТОТ ПАКЕТ НЕ МОЖЕТ ПОДКЛЮЧАТЬ ДРУГИЕ ПАКЕТЫ С ЛОГИКОЙ КОМПАНИИ КАК ЗАВИСИМОСТИ,
// ИНАЧЕ ЭТО ВЫЗЫВАЕТ ПОРОЧНЫЙ КРУГ ЦИКЛИЧЕСКИХ ИМПОРТОВ

import (
	"errors"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_event/api/conf"
	Isolation "go_event/api/includes/type/isolation"
	"os"
	"strings"
	"sync"
	"time"
)

const vacantCompanyStatus = 1
const activeCompanyStatus = 2

var mx sync.RWMutex
var isolationList = map[int64]*Isolation.Isolation{}

// UpdateWorldConfig получаем конфигурацию custom
// @long
func UpdateWorldConfig() {

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

		if _, err = startEnv(companyId); err != nil {
			log.Errorf("company isolation error %s", err.Error())
		}

		servedCompanyIdMap[companyId] = true
	}

	log.Infof("company configs were refreshed in %dms", time.Now().Sub(startedAt).Milliseconds())
	go invalidateNonExisting(servedCompanyIdMap)
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

// сравниваем существующие изоляции со списком актуальных и удаляем устаревшие
func invalidateNonExisting(servedIdList map[int64]bool) {

	mx.RLock()
	defer mx.RUnlock()

	for _, isolation := range isolationList {

		// изоляция есть в списке
		if _, isExist := servedIdList[isolation.GetCompanyId()]; isExist {
			continue
		}

		toInvalidate := isolation
		go stopEnv(toInvalidate.GetCompanyId())
	}
}

// startEnv инициализирует новую среду для компании
// если сервис больше не может вмещать в себя компании, возвращает ошибку
func startEnv(companyId int64) (*Isolation.Isolation, error) {

	mx.Lock()
	defer mx.Unlock()

	// проверяем, возможно изоляция существует
	isolation, isExist := isolationList[companyId]

	if isExist {
		return isolation, nil
	}

	if len(isolationList) >= conf.GetConfig().CapacityLimit {
		return nil, errors.New("service capacity limit reached")
	}

	log.Info(fmt.Sprintf("инициализирую изоляцю для компании %d", companyId))

	// генерируем новый контекст исполнения для сервиса
	isolation, err := Isolation.MakeIsolation(fmt.Sprintf("%d:%s", companyId, functions.GenerateUuid()), companyId)

	if err != nil {
		return nil, err
	}

	isolationList[companyId] = isolation
	log.Info(fmt.Sprintf("изоляция для компании %d инициализиорована", companyId))
	Isolation.Inc("company-isolation-count")

	return isolation, nil
}

// останавливает среду исполнения компании
// если сервис не обслуживает компанию, то вернет ошибку
func stopEnv(companyId int64) {

	mx.Lock()
	defer mx.Unlock()

	isolation, exists := isolationList[companyId]
	if !exists {
		return
	}

	// говорим всем пакетам, что изоляцию более обслуживать не нужно
	delete(isolationList, companyId)
	if err := isolation.Invalidate(); err != nil {

		log.Errorf("ошибка инвалидации изоляции компании %d — %s", companyId, err.Error())
		return
	}

	log.Errorf("инвалидирую изоляцию для компании %d, компания более не обслуживается", companyId)
	Isolation.Dec("company-isolation-count")
}

// GetEnv возвращает среду исполнения для компании
// если сервис не обслуживает компанию, то вернет ошибку
func GetEnv(companyId int64) *Isolation.Isolation {

	if companyId == 0 {
		return Isolation.Global()
	}

	mx.RLock()
	isolation, exists := isolationList[companyId]
	mx.RUnlock()

	if exists {
		return isolation
	}

	log.Warningf(fmt.Sprintf("isolation for company %d not found", companyId))
	return nil
}

// IterateOverActive пробегает по всем списку изоляция и применят к ним указанную функцию,
// эта функция блокирует список изоляций, ее нельзя вызывать внутри блоков с ожиданием
func IterateOverActive(fn func(isolation *Isolation.Isolation)) {

	mx.RLock()
	defer mx.RUnlock()

	for _, isolation := range isolationList {
		fn(isolation)
	}
}

// ReloadIsolation полностью перезагружает изоляцию для компании
// @long
func ReloadIsolation() {

	globalConfig := conf.GetConfig()

	startedAt := time.Now()
	files, err := os.ReadDir(globalConfig.WorldConfigPath)

	if err != nil {
		panic(err)
	}

	for _, file := range files {

		// пропускаем ненужные файлы и файлы, которые не менялись
		if file.Name() == ".timestamp.json" {
			continue
		}

		companyId, err := getCompanyIdByFileName(file)
		if err != nil {
			continue
		}

		companyConfig, err := conf.GetWorldConfig(conf.ResolveConfigByCompanyId(companyId))
		if err != nil {

			log.Errorf("invalid company config: %v", err)
			continue
		}

		// проверяем, что статус компании в конфиге позволяет нам перезагружать изоляцию
		if companyConfig.Status != activeCompanyStatus && companyConfig.Status != vacantCompanyStatus {
			continue
		}

		stopEnv(companyId)

		if _, err = startEnv(companyId); err != nil {
			log.Errorf("company reload isolation error %s", err.Error())
		}
	}

	log.Infof("company configs were reload in %dms", time.Now().Sub(startedAt).Milliseconds())
}
