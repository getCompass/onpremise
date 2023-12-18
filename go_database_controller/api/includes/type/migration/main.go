package migration

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/flags"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"go_database_controller/api/includes/type/routine"
	"gopkg.in/yaml.v2"
	"io/ioutil"
	"os"
	"strings"
	"time"
)

const startTime = 1609484461
const sqlPath = "sql/"
const databaseSchemaPath = sqlPath + "database_schema.yaml"
const manticoreSchemaPath = sqlPath + "manticore_schema.yaml"

const migrationTypeUp = 1
const migrationTypeLegacyClean = 2
const migrationTypeManticore = 3

var typeNameList = map[int]string{
	migrationTypeUp:          "release",
	migrationTypeLegacyClean: "legacy_clean",
	migrationTypeManticore:   "manticore",
}

const yamlFile = ".yaml"
const upSqlFile = ".up.sql"
const legacyCleanSqlFile = ".legacy_clean.sql"

const routineStoreName = "migration"

var migrationRoutineStore = routine.MakeStore(routineStoreName)

// получить список баз данных из yaml
func getDatabaseList() (databaseList *databaseListStruct, err error) {

	bs, err := ioutil.ReadFile(flags.ExecutableDir + "/" + databaseSchemaPath)
	if err != nil {
		return nil, err
	}
	if err := yaml.Unmarshal(bs, &databaseList); err != nil {
		return nil, err
	}

	return databaseList, nil
}

// получить список таблиц мантикоры
func getManticoreTableList() (manticoreTableList *manticoreTableListStruct, err error) {

	bs, err := ioutil.ReadFile(flags.ExecutableDir + "/" + manticoreSchemaPath)
	if err != nil {
		return nil, err
	}
	if err := yaml.Unmarshal(bs, &manticoreTableList); err != nil {
		return nil, err
	}

	return manticoreTableList, nil
}

// получить базу данных из yaml
func getDatabase(dbName string) (database *databaseSchemaStruct, err error) {

	databaseList, err := getDatabaseList()
	if err != nil {
		panic(err)
	}

	for _, database := range databaseList.DatabaseList {

		if database.Name == dbName {
			return &database, nil
		}
	}

	return &databaseSchemaStruct{}, fmt.Errorf("cant find database %s in yaml", dbName)
}

// получить таблицу мантикоры из yaml
func getManticoreTable(tableName string) (manticoreTable *manticoreTableSchemaStruct, err error) {

	manticoreTableList, err := getManticoreTableList()
	if err != nil {
		panic(err)
	}

	for _, manticoreTable := range manticoreTableList.ManticoreTableList {

		if manticoreTable.Name == tableName {
			return &manticoreTable, nil
		}
	}

	return &manticoreTableSchemaStruct{}, fmt.Errorf("cant find database in yaml")
}

// получить список всех названий базы данных по шарду
func getShardDatabaseNameList(database *databaseSchemaStruct) ([]string, error) {

	databaseNames := make([]string, 0)

	if database.Sharding == "" {
		return append(databaseNames, database.Name), nil
	}

	if strings.ToLower(database.Sharding) == "year" {

		startYear := time.Unix(startTime, 0).Year()
		lastYear := time.Now().Year()

		if time.Now().Month() == 12 {
			lastYear = time.Now().Year() + 1
		}

		years := makeRange(startYear, lastYear)

		for _, year := range years {
			databaseNames = append(databaseNames, database.Name+"_"+functions.IntToString(year))
		}

		return databaseNames, nil
	}

	return nil, fmt.Errorf("unknown sharding type")
}

// сделать диапазон значений
func makeRange(min, max int) []int {
	a := make([]int, max-min+1)
	for i := range a {
		a[i] = min + i
	}
	return a
}

// сделать слайс с алфавитом sha1
func hashAlphabet() []string {

	return []string{"0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f"}
}

// получить путь до миграций для базы данных
func getMigrationPath(databaseName string, migrationType int) (string, bool, error) {

	database, err := getDatabase(databaseName)
	if err != nil {
		return "", false, err
	}
	migrationPath := fmt.Sprintf("%s%s%s/%s", flags.ExecutableDir+"/", sqlPath, typeNameList[migrationType], database.MigrationPath)

	if _, err := os.Stat(migrationPath); os.IsNotExist(err) {

		return "", false, nil
	}

	return migrationPath, true, nil
}

// получить путь до миграций для таблиц мантикоры
func getManticoreMigrationPath(tableName string, migrationType int) (string, bool, error) {

	manticoreTable, err := getManticoreTable(tableName)
	if err != nil {
		return "", false, err
	}
	migrationPath := fmt.Sprintf("%s%s%s/%s", flags.ExecutableDir+"/", sqlPath, typeNameList[migrationType], manticoreTable.MigrationPath)

	if _, err := os.Stat(migrationPath); os.IsNotExist(err) {

		return "", false, nil
	}

	return migrationPath, true, nil
}

// получить путь до файла миграции (sql или yaml)
func getMigrationFile(version int, migrationPath string, fileType string) (string, bool, error) {

	if _, err := os.Stat(migrationPath); os.IsNotExist(err) {
		return "", false, nil
	}

	files, err := ioutil.ReadDir(migrationPath)

	if err != nil {
		return "", false, err
	}

	migrationFilePath := ""

	// для каждого файла считываем содержимое файла и исполняем из него запросы
	for _, file := range files {

		migrationExplode := strings.Split(file.Name(), "_")
		if len(migrationExplode) < 1 || functions.StringToInt(migrationExplode[0]) != version {
			continue
		}

		if strings.HasSuffix(file.Name(), fileType) {
			migrationFilePath = fmt.Sprintf("%s/%s", migrationPath, file.Name())
			break
		}
	}

	if migrationFilePath == "" {
		return migrationFilePath, false, nil
	}
	return migrationFilePath, true, nil

}

// получить писок названий всех таблиц с шардом
func getShardTableNameList(table *tableStruct) (shardTableNameList []string, err error) {

	if table.Sharding == "" {
		return []string{table.Name}, nil
	}

	if strings.ToLower(table.Sharding) == "month" {

		monthRange := makeRange(1, 12)
		for _, month := range monthRange {
			shardTableNameList = append(shardTableNameList, fmt.Sprintf("%s_%d", table.Name, month))
		}
		return shardTableNameList, nil
	}

	if strings.ToLower(table.Sharding) == "ceil_10" {

		ceilRange := makeRange(0, 9)
		for _, ceil := range ceilRange {
			shardTableNameList = append(shardTableNameList, fmt.Sprintf("%s_%d", table.Name, ceil))
		}
		return shardTableNameList, nil
	}

	if strings.ToLower(table.Sharding) == "hash" {

		shardAlphabet := hashAlphabet()
		for _, char := range shardAlphabet {
			shardTableNameList = append(shardTableNameList, fmt.Sprintf("%s_%s", table.Name, char))
		}
		return shardTableNameList, nil
	}

	return shardTableNameList, fmt.Errorf("unknown table sharding %s", table.Name)
}

// записать структуру базы из yaml
func parseDatabaseStruct(migrationFile string) (*databaseStruct, error) {

	var databaseStruct databaseStruct
	bs, err := ioutil.ReadFile(migrationFile)
	if err != nil {
		return nil, err
	}
	if err := yaml.Unmarshal(bs, &databaseStruct); err != nil {
		return nil, err
	}
	return &databaseStruct, nil
}
