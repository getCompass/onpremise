package backup

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"go_database_controller/api/conf"
	"go_database_controller/api/includes/type/keeper"
	"go_database_controller/api/includes/type/logger"
	"go_database_controller/api/includes/type/port_registry"
	"go_database_controller/api/includes/type/routine"
	"os"
	"time"
)

// ключ для хранилища рутин
const routineStoreName = "backup"

// хранилище рутин для бекапов
var backupRoutineStore = routine.MakeStore(routineStoreName)

// StartBackupDatabase начинаем бэкапить базу
func StartBackupDatabase(companyId int64) (string, string) {

	routineChan := make(chan *routine.Status)
	routineUniq := functions.GenerateUuid()

	// формируем имя для файла бэкапа
	backupFileName := resolveDatabaseBackupName(companyId)

	// добавляем рутину в хранилище рутин
	routineKey := backupRoutineStore.Push(routineUniq, routineChan, &logger.Log{})

	// запускаем рутину бэкапа
	go backupDatabase(routineChan, companyId, backupFileName)

	return routineKey, backupFileName
}

// бэкапим базу
func backupDatabase(routineChan chan *routine.Status, companyId int64, backupFileName string) {

	// получаем порт, на котором развернута компания
	port, err := port_registry.GetByCompany(companyId)

	if err != nil {

		// проверяем, что можно получить запись для компании
		routineChan <- routine.MakeRoutineStatus(routine.StatusError, err.Error())
		return
	} else if port.Port == 0 {

		// проверяем, что к компании привязан порт
		routineChan <- routine.MakeRoutineStatus(routine.StatusError, fmt.Sprintf("company %d has no port", companyId))
		return
	}

	backupFullName := fmt.Sprintf("%s%s", conf.GetConfig().BackupPath, backupFileName)

	// создаем файл для записи бэкапа
	outfile, err := os.Create(backupFullName)
	if err != nil {

		routineChan <- routine.MakeRoutineStatus(routine.StatusError, fmt.Sprintf("can't create database dump file: %s", err.Error()))
		return
	}

	//goland:noinspection GoUnhandledErrorResult
	defer outfile.Close()

	// снимаем дамп с базы данных
	if err = keeper.DumpDatabase(port); err != nil {

		routineChan <- routine.MakeRoutineStatus(routine.StatusError, fmt.Sprintf("can't dump database: %s", err.Error()))
		return
	}

	routineChan <- routine.MakeRoutineStatus(routine.StatusDone, "routine done")
}

// получаем имя бекапа базы данных
func resolveDatabaseBackupName(companyId int64) (backupName string) {

	dt := time.Now()
	dts := fmt.Sprintf("%d.%d.%d_%d.%d", dt.Day(), dt.Month(), dt.Year(), dt.Hour(), dt.Minute())
	backupName = fmt.Sprintf("company%d_%s.sql.zst.enc", companyId, dts)
	return
}
