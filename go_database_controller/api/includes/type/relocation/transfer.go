package relocation

// пакет, отвечающий за переезд копаний между домино
// в этом файле описаны функции, работающие с данными компании

import (
	"context"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_database_controller/api/conf"
	"go_database_controller/api/includes/type/keeper"
	"go_database_controller/api/includes/type/logger"
	"go_database_controller/api/includes/type/port_registry"
	"go_database_controller/api/includes/type/routine"
	"go_database_controller/api/includes/type/sh"
	"golang.org/x/crypto/ssh"
	"io/ioutil"
	"os"
	"time"
)

// таймаут отправки дампа базы на другой сервер
const copyDumpFileTimeout = 5 * time.Second

// название хранилища рутин
const routineStoreName = "relocation"

// хранилище рутин релокации компаний
var relocationRoutineStore = routine.MakeStore(routineStoreName)

// BeginDataCopying запускает рутину копирования данных да другой сервер, используется при релокации компаний
// скопировать данные можно только для компании, к которой привязан сервисный порт
// возвращает ключ рутины и имя файла-дампа (без пути)
func BeginDataCopying(companyId int64, targetHost string) (string, string) {

	routineChan := make(chan *routine.Status)
	routineUniq := functions.GenerateUuid()

	// формируем имя для файла-дампа
	dumpFileName := resolveDatabaseDumpFileName(companyId)

	// добавляем рутину в хранилище рутин
	routineKey := relocationRoutineStore.Push(routineUniq, routineChan, &logger.Log{})

	// запускаем рутину переезда
	go copyData(routineChan, companyId, targetHost, dumpFileName)

	return routineKey, dumpFileName
}

// выполняет копирование и перенос данных с одного сервера на другой
// @long много проверок на ошибки + пуш результата в канал
func copyData(routineChan chan *routine.Status, companyId int64, targetHost, dumpFileName string) {

	log.Infof("начинаю перенос данных компании %d на домино %s", companyId, targetHost)

	ctx := context.Background()

	// получаем порт, на котором развернута компания
	maintenancePort, err := port_registry.GetByCompany(ctx, companyId)

	if err != nil {

		// проверяем, что можно получить запись для компании
		routineChan <- routine.MakeRoutineStatus(routine.StatusError, err.Error())
		return
	} else if maintenancePort.Port == 0 {

		// проверяем, что к компании привязан порт
		routineChan <- routine.MakeRoutineStatus(routine.StatusError, fmt.Sprintf("company %d has no maintenance port", companyId))
		return
	}

	// снимаем дамп с базы данных
	if err = keeper.DumpDatabase(maintenancePort); err != nil {

		routineChan <- routine.MakeRoutineStatus(routine.StatusError, fmt.Sprintf("can't dump database: %s", err.Error()))
		return
	}

	// пытаемся перенести файл на удаленную машину
	sshCredential, err := resolveRemoteHostCredentials()
	if err != nil {

		routineChan <- routine.MakeRoutineStatus(routine.StatusError, fmt.Sprintf("can't dump database: %s", err.Error()))
		return
	}

	backupPath := fmt.Sprintf("%s%s", conf.GetConfig().RelocationSourceDumpPath, "mysql_company_"+functions.Int64ToString(maintenancePort.CompanyId))

	// файл для записи дампа
	outfile, err := os.Open(backupPath + ".zip")
	if err != nil {

		routineChan <- routine.MakeRoutineStatus(routine.StatusError, fmt.Sprintf("can't create database dump file: %s", err.Error()))
		return
	}

	//goland:noinspection GoUnhandledErrorResult
	defer outfile.Close()

	sshTunnel := sh.MakeSshTunnel(targetHost, sh.DefaultSshPort, sshCredential)
	if err = sshTunnel.SendFile(outfile, copyDumpFileTimeout, conf.GetConfig().RelocationTargetDumpPath, "mysql_company_"+functions.Int64ToString(maintenancePort.CompanyId)+".zip"); err != nil {

		routineChan <- routine.MakeRoutineStatus(routine.StatusError, fmt.Sprintf("can't sync database dump file with host %s: %s", err.Error(), targetHost))
		return
	}

	err = os.RemoveAll(backupPath)
	if err != nil {

		routineChan <- routine.MakeRoutineStatus(routine.StatusError, fmt.Sprintf("can't delete backup folder: %s", err.Error()))
		return
	}

	err = os.Remove(backupPath + ".zip")
	if err != nil {

		routineChan <- routine.MakeRoutineStatus(routine.StatusError, fmt.Sprintf("can't delete backup zip-file: %s", err.Error()))
		return
	}

	routineChan <- routine.MakeRoutineStatus(routine.StatusDone, "routine done")
}

// BeginDataApplying запускает рутину применения скопированных данных, используется при релокации компаний
// скопировать данные можно только для компании, к которой привязан сервисный или рабочий порт
func BeginDataApplying(companyId int64) string {

	routineChan := make(chan *routine.Status)
	routineUniq := functions.GenerateUuid()

	// добавляем рутину в хранилище рутин
	routineKey := relocationRoutineStore.Push(routineUniq, routineChan, &logger.Log{})

	// запускаем рутину переезда
	go applyData(routineChan, companyId)

	return routineKey
}

// применяет копированные данные
func applyData(routineChan chan *routine.Status, companyId int64) {

	ctx := context.Background()

	// получаем порт, на котором развернута компания
	maintenancePort, err := port_registry.GetByCompany(ctx, companyId)

	if err != nil {

		// проверяем, что можно получить запись для компании
		routineChan <- routine.MakeRoutineStatus(routine.StatusError, err.Error())
		return
	} else if maintenancePort.Port == 0 {

		// проверяем, что к компании привязан порт
		routineChan <- routine.MakeRoutineStatus(routine.StatusError, fmt.Sprintf("company %d has no maintenance port", companyId))
		return
	}

	if err = keeper.RestoreDatabase(maintenancePort); err != nil {

		// проверяем, что к компании привязан порт
		routineChan <- routine.MakeRoutineStatus(routine.StatusError, err.Error())
		return
	}

	routineChan <- routine.MakeRoutineStatus(routine.StatusDone, "routine done")
}

// формирует имя для файла-дампа
func resolveDatabaseDumpFileName(companyId int64) string {

	return fmt.Sprintf("%s", "mysql_company_"+functions.Int64ToString(companyId)+".zip")
}

// возвращает credentials для доступа к домино по ssh
func resolveRemoteHostCredentials() (*ssh.ClientConfig, error) {

	privateKeyFile, err := os.Open(conf.GetConfig().BackupSshFileKeyFilePath)
	if err != nil {
		return nil, err
	}

	privateKeyBytes, err := ioutil.ReadAll(privateKeyFile)
	if err != nil {
		return nil, err
	}

	signer, err := ssh.ParsePrivateKey(privateKeyBytes)
	if err != nil {
		return nil, err
	}

	return &ssh.ClientConfig{
		User: conf.GetConfig().DominoUser,
		Auth: []ssh.AuthMethod{
			ssh.PublicKeys(signer),
		},
		HostKeyCallback: ssh.InsecureIgnoreHostKey(), // nosemgrep
	}, nil
}
