package migration

import (
	"context"
	"database/sql"
	"encoding/json"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/flags"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_database_controller/api/conf"
	"go_database_controller/api/includes/type/db/company"
	"go_database_controller/api/includes/type/db/company/company_system"
	"go_database_controller/api/includes/type/logger"
	"go_database_controller/api/includes/type/port_registry"
	"go_database_controller/api/includes/type/routine"
	"go_database_controller/api/system/sharding"
	"io/ioutil"
	"strings"
)

// начинаем процесс миграции
func StartMigrateUp(companyId int64) string {

	routineChan := make(chan *routine.Status)
	logItem := &logger.Log{}

	routineUniq := functions.GenerateUuid()

	// добавляем рутину в хранилище рутин
	routineKey := migrationRoutineStore.Push(routineUniq, routineChan, logItem)

	// пишем лог
	logItem.AddLog(fmt.Sprintf("Начинается запись лога для рутины %s, компания %d, задача %s:", routineUniq, companyId, typeNameList[migrationTypeUp]))

	// запускаем миграцию
	go migrateUp(routineChan, companyId, logItem)

	return routineKey
}

// поднимает версию миграций для переданных компаний
// @long
func migrateUp(routineChan chan *routine.Status, spaceId int64, logItem *logger.Log) {

	ctx := context.Background()

	registry, err := port_registry.GetByCompany(ctx, spaceId)
	if err != nil {

		// если не нашли порт у пространства - возвращаем ошибку
		routineChan <- routine.MakeRoutineStatus(routine.StatusError, fmt.Sprintf("could not get space port, error: %v", err))
		return
	}
	if registry == nil {

		// если не нашли регистри у пространства - возвращаем ошибку
		routineChan <- routine.MakeRoutineStatus(routine.StatusError, fmt.Sprintf("could not get space registry, error: %v", err))
		return
	}

	// расшифровываем пользователя и пароль от базы
	user, err := registry.GetDecryptedMysqlUser()
	if err != nil {
		routineChan <- routine.MakeRoutineStatus(routine.StatusError, fmt.Sprintf("could not decrypt MySQL user, error: %v", err))
		return
	}

	pass, err := registry.GetDecryptedMysqlPass()
	if err != nil {
		routineChan <- routine.MakeRoutineStatus(routine.StatusError, fmt.Sprintf("could not decrypt MySQL password, error: %v", err))
		return
	}

	// проверяем, что учетные данные не пустые
	if user == "" || pass == "" || registry.Port == 0 {
		routineChan <- routine.MakeRoutineStatus(routine.StatusError, "invalid database credentials (empty user, password or port)")
		return
	}

	credentials := &sharding.DbCredentials{
		Host: company.GetCompanyHost(registry),
		User: user,
		Pass: pass,
		Port: registry.Port,
	}

	rootPass, err := company.GetRootPassword(registry)

	if err != nil {

		routineChan <- routine.MakeRoutineStatus(routine.StatusError, err.Error())
		return
	}

	rootCredentials := &sharding.DbCredentials{
		Host: company.GetCompanyHost(registry),
		User: "root",
		Pass: rootPass,
		Port: registry.Port,
	}

	// пишем информацию о подключении для отладки
	logItem.AddLog(fmt.Sprintf("attempting to connect to MySQL with Host=%s, User=%s, Port=%d",
		credentials.Host, credentials.User, credentials.Port))

	// выполняем миграцию для баз данных
	errMessage := doMigrateDb(ctx, credentials, rootCredentials, logItem)
	if errMessage != "" {

		routineChan <- routine.MakeRoutineStatus(routine.StatusError, errMessage)
		return
	}

	// устанавливаем соединение с мантикорой
	connection, err := sql.Open("mysql", fmt.Sprintf("tcp(%s:%s)/", conf.GetShardingConfig().Manticore.Host, conf.GetShardingConfig().Manticore.Port))
	if err != nil {

		routineChan <- routine.MakeRoutineStatus(routine.StatusError, fmt.Sprintf("could not connect to manticore, error: %v", err))
		return
	}

	// после выполнения закрываем соединение
	defer func() {

		err = connection.Close()
		if err != nil {
			log.Errorf("%v", err)
		}
	}()

	// выполняем миграцию для таблиц мантикторы
	errMessage = DoMigrateManticore(ctx, credentials, connection, spaceId, logItem)
	if errMessage != "" {

		routineChan <- routine.MakeRoutineStatus(routine.StatusError, fmt.Sprintf("could not migrated database, error: %s", errMessage))
		return
	}

	// успешно мигрировали
	routineChan <- routine.MakeRoutineStatus(routine.StatusDone, "routine done")
}

// выполнить миграцию для баз данных
// @long
func doMigrateDb(ctx context.Context, credentials *sharding.DbCredentials, rootCredentials *sharding.DbCredentials, logItem *logger.Log) string {

	// проверяем валидность учетных данных
	if credentials == nil {
		return "database credentials are nil"
	}

	if credentials.Host == "" || credentials.User == "" || credentials.Pass == "" || credentials.Port == 0 {
		return fmt.Sprintf("invalid database credentials: Host=%s, User=%s, Port=%d",
			credentials.Host, credentials.User, credentials.Port)
	}

	// получаем список баз данных для миграции
	databaseList, err := getDatabaseList()
	b, _ := json.Marshal(databaseList)
	fmt.Println(string(b))

	if err != nil {

		// если не нашли файл с базами - завершаем выполнение с ошибкой
		return fmt.Sprintf("could no get database list, error: %v", err)
	}

	// проверяем, что база company_system существует. Если нет - создаем
	isCreated, err := company.CreateIfNotExistDatabase(ctx, credentials, rootCredentials, "company_system")

	if err != nil {

		return fmt.Sprintf("could not create company_system database, error: %v", err)
	}

	// если пришлось создать базу, пишем об этом лог
	if isCreated {
		logItem.AddLog(fmt.Sprintf("Создали базу company_system"))
	}

	// исполняем изначальную миграцию - чтобы появились необходимые таблицы для работы миграций
	err = company.ExecSql(ctx, credentials, "company_system", flags.ExecutableDir+"/sql/release/company_system/1_init.up.sql")

	if err != nil {

		return fmt.Sprintf("could not create company_system database, error: %v", err)
	}

	// поочередено накатываем миграции на базы компании
	for _, database := range databaseList.DatabaseList {

		b, _ := json.Marshal(database)
		fmt.Println(string(b))

		err = migrateDatabaseUp(ctx, &database, credentials, rootCredentials, logItem)
		if err != nil {

			return fmt.Sprintf("could not migrated database, error: %v", err)
		}
	}

	logItem.AddLog(fmt.Sprintf("Все базы проверены/актуализированы"))

	return ""
}

// мигрируем отдельную базу данных
// @long
func migrateDatabaseUp(ctx context.Context, database *databaseSchemaStruct, credentials *sharding.DbCredentials, rootCredentials *sharding.DbCredentials, logItem *logger.Log) error {

	// для базы получаем все названия с шардингом
	shardDatabaseList, err := getShardDatabaseNameList(database)
	if err != nil {
		return err
	}

	// получаем путь, где находятся файлы для миграций
	path, isExist, err := getMigrationPath(database.Name, migrationTypeUp)
	if err != nil {
		return err
	}

	// нечего накатывать, идем дальше
	if !isExist {
		return nil
	}

	// для каждой базы проверяем версию миграций
	for _, shardDatabaseName := range shardDatabaseList {

		migrationDatabaseStruct, err := company_system.GetReleaseMigrationDatabase(ctx, credentials, shardDatabaseName)
		if err != nil {
			return err
		}

		if migrationDatabaseStruct.CreatedAt > 0 {

			logItem.AddLog(fmt.Sprintf("База %s существует", shardDatabaseName))
			if migrationDatabaseStruct.IsCompleted == 0 {
				return fmt.Errorf("некорректно завершилась миграция в базе %s, чините руками и возвращайтесь", migrationDatabaseStruct.FullDatabaseName)
			}
		}

		// если накатываемой базы нет - создаем
		if migrationDatabaseStruct.CreatedAt == 0 {
			err := company.CreateDatabase(ctx, credentials, rootCredentials, shardDatabaseName)
			if err != nil {
				return err
			}

			migrationDatabaseStruct, err = company_system.InsertReleaseMigrationDatabase(ctx, credentials, shardDatabaseName, database.Name)
			if err != nil {
				return err
			}

			logItem.AddLog(fmt.Sprintf("Создали базу %s", shardDatabaseName))
		}

		// исполняем sql файлы миграции на базу
		err = processDatabaseUp(ctx, credentials, migrationDatabaseStruct, path, logItem)
		if err != nil {
			return err
		}

		log.Infof("Накатили миграцию для базы %s", shardDatabaseName)
	}

	return nil
}

// запустить непосредественно процесс миграции на отдельной базе данных
// @long
func processDatabaseUp(ctx context.Context, credentials *sharding.DbCredentials, migrationDatabase *company_system.MigrationDatabaseStruct, migrationPath string, logItem *logger.Log) error {

	migrationSqlPath := "start"
	expectedVersion := 0

	for len(migrationSqlPath) > 0 {

		expectedVersion = migrationDatabase.CurrentVersion + 1

		sqlPath, isExist, err := getMigrationFile(expectedVersion, migrationPath, upSqlFile)
		if err != nil {
			return err
		}

		if !isExist {

			err := checkMigration(ctx, credentials, migrationDatabase.CurrentVersion, migrationDatabase.DatabaseName, migrationPath, migrationDatabase.FullDatabaseName)
			if err != nil {
				return err
			}
			break
		}

		migrationSqlPath = sqlPath

		err = company_system.UpdateStartRelease(
			ctx,
			credentials,
			migrationDatabase.FullDatabaseName,
			0,
			expectedVersion,
			migrationTypeUp,
			functions.GetCurrentTimeStamp(),
			migrationSqlPath)
		if err != nil {
			return err
		}

		err = company.ExecSql(ctx, credentials, migrationDatabase.FullDatabaseName, migrationSqlPath)
		if err != nil {
			return err
		}

		err = checkMigration(ctx, credentials, expectedVersion, migrationDatabase.DatabaseName, migrationPath, migrationDatabase.FullDatabaseName)
		if err != nil {
			return err
		}

		if migrationDatabase.HighestVersion < expectedVersion {
			migrationDatabase.HighestVersion = expectedVersion
		}

		migrationDatabase.PreviousVersion = migrationDatabase.CurrentVersion
		migrationDatabase.CurrentVersion = expectedVersion

		err = company_system.UpdateEndRelease(
			ctx,
			credentials,
			migrationDatabase.FullDatabaseName,
			1,
			migrationDatabase.PreviousVersion,
			migrationDatabase.CurrentVersion,
			migrationDatabase.HighestVersion)
		if err != nil {
			return err
		}

		logItem.AddLog(fmt.Sprintf("Для базы данных %s накатили версию: %d", migrationDatabase.FullDatabaseName, expectedVersion))
	}

	return nil
}

// выполнить миграцию таблиц мантикоры
func DoMigrateManticore(ctx context.Context, credentials *sharding.DbCredentials, connection *sql.DB, spaceId int64, logItem *logger.Log) string {

	// получаем таблицы мантикоры
	manticoreTableList, err := getManticoreTableList()

	if err != nil {

		// если не нашли файл с таблицами мантикоры - завершаем выполнение с ошибкой
		return fmt.Sprintf("could no get manticore table list, error: %v", err)
	}

	// поочередено накатываем миграции на базы компании
	for _, manticoreTable := range manticoreTableList.ManticoreTableList {

		err = migrateManticoreTableUp(ctx, &manticoreTable, credentials, connection, spaceId, logItem)
		if err != nil {

			return fmt.Sprintf("could not migrated manticore table, error: %v", err)
		}
	}

	logItem.AddLog(fmt.Sprintf("Все таблицы мантикоры проверены/актуализированы"))

	return ""
}

// мигрируем отдельную таблицу мантикоры
// @long
func migrateManticoreTableUp(ctx context.Context, manticoreTable *manticoreTableSchemaStruct, credentials *sharding.DbCredentials, connection *sql.DB, spaceId int64, logItem *logger.Log) error {

	// получаем путь, где находятся файлы для миграций
	path, isExist, err := getManticoreMigrationPath(manticoreTable.Name, migrationTypeManticore)
	if err != nil {
		return err
	}

	// нечего накатывать, идем дальше
	if !isExist {
		return nil
	}

	// проверяем версию миграций
	migrationDatabaseStruct, err := company_system.GetReleaseMigrationDatabase(ctx, credentials, manticoreTable.Name)
	if err != nil {
		return err
	}

	if migrationDatabaseStruct.CreatedAt > 0 {

		logItem.AddLog(fmt.Sprintf("Таблица мантикоры %s существует", manticoreTable.Name))
		if migrationDatabaseStruct.IsCompleted == 0 {
			return fmt.Errorf("некорректно завершилась миграция в таблице мантикоры %s, чините руками и возвращайтесь", migrationDatabaseStruct.FullDatabaseName)
		}
	}

	// если накатываемой таблицы нет - создаём
	if migrationDatabaseStruct.CreatedAt == 0 {

		migrationDatabaseStruct, err = company_system.InsertReleaseMigrationDatabase(ctx, credentials, manticoreTable.Name, manticoreTable.Name)
		if err != nil {
			return err
		}

		logItem.AddLog(fmt.Sprintf("Создали таблицу для мантикоры %s", manticoreTable.Name))
	}

	// исполняем sql файлы миграции на таблицу
	err = processManticoreUp(ctx, credentials, connection, spaceId, migrationDatabaseStruct, path, logItem)
	if err != nil {
		return err
	}

	return nil
}

// запустить непосредественно процесс миграции на отдельной таблице мантикоры
// @long
func processManticoreUp(ctx context.Context, credentials *sharding.DbCredentials, connection *sql.DB, spaceId int64, migrationDatabase *company_system.MigrationDatabaseStruct, migrationPath string, logItem *logger.Log) error {

	migrationSqlPath := "start"
	expectedVersion := 0

	for len(migrationSqlPath) > 0 {

		expectedVersion = migrationDatabase.CurrentVersion + 1

		sqlPath, isExist, err := getMigrationFile(expectedVersion, migrationPath, upSqlFile)
		if err != nil {
			return err
		}

		if !isExist {
			break
		}

		migrationSqlPath = sqlPath

		err = company_system.UpdateStartRelease(
			ctx,
			credentials,
			migrationDatabase.FullDatabaseName,
			0,
			expectedVersion,
			migrationTypeManticore,
			functions.GetCurrentTimeStamp(),
			migrationSqlPath)
		if err != nil {
			return err
		}

		sc, err := ioutil.ReadFile(migrationSqlPath)
		sqlContent := string(sc)

		// делим запросы и убираем последнйи элемент - он пустой
		sqlQueries := strings.Split(sqlContent, ";")

		if sqlQueries[len(sqlQueries)-1] == "" {
			sqlQueries = sqlQueries[:len(sqlQueries)-1]
		}

		// выполняем запросы
		for _, query := range sqlQueries {

			// подставляем space_id в запрос
			query = strings.Replace(query, "{space_id}", functions.Int64ToString(spaceId), 1)

			_, err = connection.Query(query)
			if err != nil {
				return err
			}
		}

		if migrationDatabase.HighestVersion < expectedVersion {
			migrationDatabase.HighestVersion = expectedVersion
		}

		migrationDatabase.PreviousVersion = migrationDatabase.CurrentVersion
		migrationDatabase.CurrentVersion = expectedVersion

		err = company_system.UpdateEndRelease(
			ctx,
			credentials,
			migrationDatabase.FullDatabaseName,
			1,
			migrationDatabase.PreviousVersion,
			migrationDatabase.CurrentVersion,
			migrationDatabase.HighestVersion)
		if err != nil {
			return err
		}

		logItem.AddLog(fmt.Sprintf("Для таблицы мантикоры %s накатили версию: %d", migrationDatabase.FullDatabaseName, expectedVersion))
	}

	return nil
}
