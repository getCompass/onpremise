package CompanyConfig

// Пакет описывает использование изоляции как изолированной среды компании.
// Когда компания начинает обслуживаться сервисом, она первым делом проходит через этот пакет
//
// ЭТОТ ПАКЕТ НЕ МОЖЕТ ПОДКЛЮЧАТЬ ДРУГИЕ ПАКЕТЫ С ЛОГИКОЙ КОМПАНИИ КАК ЗАВИСИМОСТИ,
// ИНАЧЕ ЭТО ВЫЗЫВАЕТ ПОРОЧНЫЙ КРУГ ЦИКЛИЧЕСКИХ ИМПОРТОВ

import (
	"go_rating/api/conf"
	Isolation "go_rating/api/includes/type/isolation"
)

// ключ к кредам базы компании
const isolationCompanyDatabaseCredentialKey = "company.database_credential"

// IsolationReg функция для регистрации пакета в изоляциях
// должна вызваться при старте сервиса
func IsolationReg() {

	Isolation.RegPackage("CompanyEnvironment", isolationPreInit, nil, isolationInvalidate)
}

// выполняет первичную настройку изоляции,
func isolationPreInit(isolation *Isolation.Isolation) error {

	// получаем конфигурационный файл для компании
	companyConfig, err := conf.GetCompanyConfig(isolation.GetCompanyId())
	if err != nil {
		return err
	}

	// добавляем в изоляцию компании хранилище
	isolation.Set(isolationCompanyDatabaseCredentialKey, companyConfig)
	return nil
}

// завершает работу изоляции,
func isolationInvalidate(isolation *Isolation.Isolation) error {

	// удаляем ключ кредов из изоляции
	isolation.Set(isolationCompanyDatabaseCredentialKey, nil)
	return nil
}

// --------------------------------------
// функции доступа к изолированным данных
// --------------------------------------

// LeaseCompanyConfig возвращает доступы к базе данных компании
func LeaseCompanyConfig(isolation *Isolation.Isolation) *conf.CompanyConfigStruct {

	isolatedValue := isolation.Get(isolationCompanyDatabaseCredentialKey)
	if isolatedValue == nil {
		return nil
	}

	return isolatedValue.(*conf.CompanyConfigStruct)
}
