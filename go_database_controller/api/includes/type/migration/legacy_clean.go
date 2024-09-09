package migration

import (
	"context"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/flags"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"go_database_controller/api/includes/type/db/company"
	"go_database_controller/api/includes/type/db/company/company_system"
	"go_database_controller/api/includes/type/logger"
	"go_database_controller/api/includes/type/port_registry"
	"go_database_controller/api/includes/type/routine"
	"go_database_controller/api/system/sharding"
)

// начинаем процесс очистки от легаси
func StartMigrateLegacyClean(companyId int64) string {

	routineChan := make(chan *routine.Status)
	logItem := &logger.Log{}

	routineUniq := functions.GenerateUuid()

	// добавляем рутину в хранилище рутин
	routineKey := migrationRoutineStore.Push(routineUniq, routineChan, logItem)

	// пишем лог
	logItem.AddLog(fmt.Sprintf("Начинается запись лога для рутины %s, компания %d, задача %s:", routineUniq, companyId, typeNameList[migrationTypeLegacyClean]))

	// запускаем миграцию
	go migrateLegacyClean(routineChan, companyId, logItem)

	return routineKey
}

// поднимает версию миграций для переданных компаний
func migrateLegacyClean(routineChan chan *routine.Status, companyId int64, logItem *logger.Log) {

	ctx := context.Background()

	databaseList, err := getDatabaseList()

	if err != nil {

		routineChan <- routine.MakeRoutineStatus(routine.StatusError, fmt.Sprintf("could no get database list, error: %v", err))
		return
	}

	registry, err := port_registry.GetByCompany(ctx, companyId)

	if err != nil {

		routineChan <- routine.MakeRoutineStatus(routine.StatusError, fmt.Sprintf("could not get company port, error: %v", err))
		return
	}

	user, err := registry.GetDecryptedMysqlUser()
	pass, err := registry.GetDecryptedMysqlPass()

	credentials := &sharding.DbCredentials{
		Host: company.GetCompanyHost(registry.Port),
		User: user,
		Pass: pass,
		Port: registry.Port,
	}

	// проверяем, что база company_system существует, и создаем, если нужно
	isCreated, err := company.CreateIfNotExistDatabase(ctx, credentials, "company_system")

	if err != nil {

		routineChan <- routine.MakeRoutineStatus(routine.StatusError, fmt.Sprintf("could not create company_system database, error: %v", err))
		return
	}

	// если пришлось создать базу, то завершаем работу - как можно чистить ничего? Но перед этим накатываем init миграцию, раз пришли
	if isCreated {

		err = company.ExecSql(ctx, credentials, "company_system", flags.ExecutableDir+"/sql/release/company_system/1_init.up.sql")
		routineChan <- routine.MakeRoutineStatus(routine.StatusDone, "routine done, no database to clean")
	}

	for _, database := range databaseList.DatabaseList {

		err := migrateDatabaseLegacyClean(ctx, &database, credentials, logItem)
		if err != nil {

			routineChan <- routine.MakeRoutineStatus(routine.StatusError, fmt.Sprintf("could not migrated database, error: %v", err))
			return
		}
	}

	logItem.AddLog(fmt.Sprintf("Все базы проверены/избавлены от легаси"))
	routineChan <- routine.MakeRoutineStatus(routine.StatusDone, "routine done")
}

// мигрируем отдельную базу данных
func migrateDatabaseLegacyClean(ctx context.Context, database *databaseSchemaStruct, credentials *sharding.DbCredentials, logItem *logger.Log) error {

	shardDatabaseList, err := getShardDatabaseNameList(database)
	if err != nil {
		return err
	}

	path, isExist, err := getMigrationPath(database.Name, migrationTypeLegacyClean)
	if err != nil {
		return err
	}

	// нечего накатывать, идем дальше
	if !isExist {
		return nil
	}

	// получаем состояние миграции для каждой базы и накатываем новые, если необходимо
	for _, shardDatabaseName := range shardDatabaseList {

		migrationDatabaseStruct, err := company_system.GetMigrationCleaningDatabase(ctx, credentials, shardDatabaseName)
		if err != nil {
			return err
		}

		if migrationDatabaseStruct.CreatedAt > 0 {

			logItem.AddLog(fmt.Sprintf("База %s существует", shardDatabaseName))

			// если вдруг есть незавершенный процесс миграции - останавливаем выполнение
			if migrationDatabaseStruct.IsCompleted == 0 {
				return fmt.Errorf("в прошлый раз некорректно завершилась миграция в базе %s, чините руками и возвращайтесь",
					migrationDatabaseStruct.FullDatabaseName)
			}
		}
		if migrationDatabaseStruct.CreatedAt == 0 {

			migrationDatabaseStruct, err = company_system.InsertMigrationCleaningDatabase(ctx, credentials, shardDatabaseName, database.Name)
			if err != nil {
				return err
			}

			logItem.AddLog(fmt.Sprintf("Добавили запись о применении миграции на удаление легаси %s", shardDatabaseName))
		}

		err = processDatabaseLegacyClean(ctx, credentials, migrationDatabaseStruct, path, logItem)
		if err != nil {
			return err
		}
	}

	return nil
}

// запустить непосредественно процесс миграции на отдельной базе данных
func processDatabaseLegacyClean(ctx context.Context, credentials *sharding.DbCredentials, migrationDatabase *company_system.MigrationDatabaseStruct, migrationPath string, logItem *logger.Log) error {

	migrationSqlPath := "start"
	expectedVersion := 0

	for len(migrationSqlPath) > 0 {

		expectedVersion = migrationDatabase.CurrentVersion + 1

		sqlPath, isExist, err := getMigrationFile(expectedVersion, migrationPath, legacyCleanSqlFile)
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

		releaseRow, err := company_system.GetReleaseMigrationDatabase(ctx, credentials, migrationDatabase.FullDatabaseName)
		if err != nil {
			return err
		}

		yaml, isExist, err := getMigrationFile(expectedVersion, migrationPath, yamlFile)
		if err != nil {
			return err
		}

		// если нет файла проверки миграции - выходим
		if !isExist {
			return fmt.Errorf("отсутствует файл для проверки миграции %s", migrationDatabase.FullDatabaseName)
		}

		// проверяем, что вообще можно накатывать такую версию очистки от легаси
		databaseStruct, err := parseDatabaseStruct(yaml)
		if err != nil {
			return err
		}

		// проверка, что миграцию вообще можно накатить. Не накатится, если версия базы ниже, чем нужно
		if databaseStruct.NeededDatabaseVersion > releaseRow.CurrentVersion {

			logItem.AddLog(fmt.Sprintf("Версия базы данных ниже, чем требуется для очистки от легаси %s", migrationDatabase.FullDatabaseName))
			return nil
		}
		err = company_system.UpdateStartCleaning(
			ctx,
			credentials,
			migrationDatabase.FullDatabaseName,
			0,
			expectedVersion,
			migrationTypeLegacyClean,
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

		err = company_system.UpdateEndCleaning(
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
