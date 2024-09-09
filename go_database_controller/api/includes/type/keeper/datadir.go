package keeper

import (
	"context"
	"errors"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/fileutils"
	"github.com/getCompassUtils/go_base_frame/api/system/flags"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"go_database_controller/api/conf"
	"go_database_controller/api/includes/type/port_registry"
	"os"
	"os/user"
	"time"
)

// таймауты консольных команд для копирования данных компании
const makeDumpTimeout = 20 * time.Minute    // таймаут снятия дампа
const compressDumpTimeout = 5 * time.Minute // таймаут сжатия дампа
const encryptDumpTimeout = 5 * time.Minute  // таймаут шифрования дампа

// таймауты консольных команд для применения данных компании
const applyDumpTimeout = 20 * time.Minute      // таймаут накатки дампа
const decompressDumpTimeout = 20 * time.Minute // таймаут распаковки дампа
const decryptDumpTimeout = 20 * time.Minute    // таймаут расшифровывания дампа

// политики поведения при возникновении конфликта имен директорий с данными компании
const duplicateDataDirPolicyReplace = 1 // удалять и замещать директорию
const duplicateDataDirPolicyCopy = 2    // создавать копию директории перед замещение
const duplicateDataDirPolicyIgnore = 3  // игнорировать и использовать существующую
const duplicateDataDirPolicyForbid = 4  // запрещать действия при наличии существующей директории

// политики поведения при существовании директории для компании
const nonExistingDataDirPolicyAllow = 1    // все окей, можно работать
const nonExistingDataDirPolicyDisallow = 2 // работать при отсутствии директории нельзя

var optimizeTableListForDump = []string{
	"company_data.member_list",
}

// CreateDatabase создает файлы для будущей базы данных компании
func CreateDatabase(companyId int64) error {

	initialDatabasePath := flags.ExecutableDir + "/etc/mysql_initial"

	newDir := getDataDirPath(companyId)

	if err := os.MkdirAll(newDir, 0755); err != nil {
		return err
	}

	if err := fileutils.CopyDirectory(initialDatabasePath, newDir); err != nil {
		return err
	}

	mysqlUser, err := user.Lookup("mysql")
	if err != nil {
		return err
	}

	mysqlGroup, err := user.LookupGroup("mysql")
	if err != nil {
		return err
	}

	return fileutils.ChownR(newDir, functions.StringToInt(mysqlUser.Uid), functions.StringToInt(mysqlGroup.Gid))
}

// DumpDatabase снимает дамп базы данных для указанного порта
// записывает данные в переданный файл
// @long
func DumpDatabase(port *port_registry.PortRegistryStruct) error {

	return nil
}

// RestoreDatabase восстанавливает базу данных из указанного файла-дампа
// @long
func RestoreDatabase(port *port_registry.PortRegistryStruct) error {

	return nil
}

// выдаём права пользователя
func chownUser(userName string, path string) error {

	userChown, err := user.Lookup(userName)
	if err != nil {
		return err
	}

	groupChown, err := user.LookupGroup(userName)
	if err != nil {
		return err
	}

	err = fileutils.ChownR(path, functions.StringToInt(userChown.Uid), functions.StringToInt(groupChown.Gid))
	if err != nil {
		return err
	}

	return nil
}

// PrepareDataDir готовит директорию с пустой базой под компанию
// если директория уже существует, то ничего не делает
// возвращает флаг необходимости проинициализировать директорию
func PrepareDataDir(ctx context.Context, companyId int64, nonExistingPolicy, duplicatePolicy int32) (bool, error) {

	dirName := getDataDirPath(companyId)

	// убеждаемся, что с директорией никто не работает
	if port, _ := port_registry.GetByCompany(ctx, companyId); port != nil && isMysqlAlive(port.Port) {
		return false, fmt.Errorf("company datadir %s is used by another daemon on %d port", dirName, port.Port)
	}

	// проверяем наличие директории для компании
	if _, err := os.Stat(dirName); err != nil {

		if os.IsNotExist(err) {

			// если директория не существует,
			// то пытаемся понять, позволяет ли политика нам дальше делать штуки
			switch nonExistingPolicy {

			// не позволяет, выходим
			case nonExistingDataDirPolicyDisallow:
				return false, fmt.Errorf("company %d data directory not exist", companyId)

			// позволяет, тогда создаем новую
			case nonExistingDataDirPolicyAllow:
				return true, CreateDatabase(companyId)

			default:
				return false, fmt.Errorf("passed incorrect none existing policy %d", nonExistingDataDirPolicyAllow)
			}
		} else {

			// любая другая ошибка, связанная с чтением директории
			return false, err
		}

	}

	// директория существует, нужно порешать вопросик в соответствии с политикой
	return prepareDataDirAccordingPolicy(dirName, companyId, duplicatePolicy)
}

// выполняет подготовку директории с базой компании в соответствии с указанной политикой
func prepareDataDirAccordingPolicy(dirName string, companyId int64, duplicateDataDirPolicy int32) (bool, error) {

	switch duplicateDataDirPolicy {

	// удаляем старую и создаем новую
	case duplicateDataDirPolicyReplace:

		if err := os.Remove(dirName); err != nil {
			return false, err
		}

		return true, CreateDatabase(companyId)

	// копируем старую и создаем новую
	case duplicateDataDirPolicyCopy:

		if err := os.Rename(dirName, fmt.Sprintf("%s.copy%s", dirName, time.Now().Format(time.RFC3339))); err != nil {
			return false, err
		}

		return true, CreateDatabase(companyId)

	// игнорим и используем существующую
	case duplicateDataDirPolicyIgnore:

		return false, nil

	// действия над существующими директориями запрещены
	case duplicateDataDirPolicyForbid:

		return false, errors.New("datadir already exists")
	}

	return false, errors.New("passed unknown duplicate datadir policy")
}

// GetDataDirPath получить путь до папки компании
func getDataDirPath(companyId int64) string {

	return conf.GetConfig().CompanyDbPath + "/" + conf.GetConfig().DominoId + "/" + "mysql_company_" + functions.Int64ToString(companyId)

}
