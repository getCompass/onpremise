package migration

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"go_database_controller/api/includes/type/db/company"
	"go_database_controller/api/system/sharding"
	"strings"
)

// проверить сделанные миграции
func checkMigration(credentials *sharding.DbCredentials, version int, dbName string, migrationPath string, shardDbName string) error {

	database, err := getDatabase(dbName)
	if err != nil {
		return err
	}

	shardDbList, err := getShardDatabaseNameList(database)
	if err != nil {
		return err
	}

	if len(shardDbName) > 0 {
		shardDbList = []string{dbName}
	}

	migrationFile, isExist, err := getMigrationFile(version, migrationPath, yamlFile)
	if err != nil {
		return err
	}

	// если не существует файл проверки - прерываем миграцию - что то здесь не так
	if !isExist {
		return fmt.Errorf("нет yaml файла для проверки миграции %s", shardDbName)
	}

	databaseStruct, err := parseDatabaseStruct(migrationFile)
	if err != nil {
		return err
	}
	for _, shardDb := range shardDbList {

		err := checkDatabase(credentials, databaseStruct, shardDb)
		if err != nil {
			return err
		}
	}

	return nil
}

// проверить миграцию для отдельной базы данных с шардом
func checkDatabase(credentials *sharding.DbCredentials, databaseStruct *databaseStruct, shardDbName string) error {

	for _, table := range databaseStruct.TableList {

		shardTableNameList, err := getShardTableNameList(&table)
		if err != nil {
			return err
		}

		for _, shardTable := range shardTableNameList {

			err := checkTable(credentials, shardTable, &table, shardDbName)
			if err != nil {
				return err
			}
		}
	}

	return nil
}

// проверить отдельную таблицу с шардом
func checkTable(credentials *sharding.DbCredentials, shardTableName string, tableStruct *tableStruct, shardDbName string) (err error) {

	// получаем запись таблицы в information_schema
	tableSchema, err := company.GetTableInformationSchema(credentials, shardDbName, shardTableName)

	// проверяем. что совпадает engine таблицы
	if strings.ToLower(tableSchema["ENGINE"]) != strings.ToLower(tableStruct.Engine) {
		return fmt.Errorf("engine not equals %s", shardTableName)
	}

	// проверяем, что кодировка совпадает
	if !strings.Contains(tableSchema["TABLE_COLLATION"], tableStruct.Charset) {
		return fmt.Errorf("charset not equals %s", shardTableName)
	}

	// получаем поля таблицы
	fieldSchema, err := company.GetFieldsFromTable(credentials, shardDbName, shardTableName)

	// проверяем, что совпадает количество полей в таблице
	if len(tableStruct.FieldList) != len(fieldSchema) {
		return fmt.Errorf("count field not equals %s", shardTableName)
	}

	// проверяем сортировку полей
	err = checkSortFields(tableStruct.FieldOrder, fieldSchema)
	if err != nil {
		return err
	}

	// каждое поле проверяем на идентичность с предполагаемой структурой
	for _, field := range fieldSchema {

		err := checkField(field, tableStruct)
		if err != nil {
			return err
		}
	}

	indexSchemaStructList, err := getTableIndexes(credentials, shardDbName, shardTableName)

	if len(indexSchemaStructList) != len(tableStruct.IndexList) {
		return fmt.Errorf("count index not equals %s", shardTableName)
	}

	err = checkIndexes(indexSchemaStructList, tableStruct, shardTableName)
	if err != nil {
		return nil
	}

	return nil
}

// проверить индексы в таблице
func checkIndexes(indexSchemaStructList map[string]*indexSchemaStruct, tableStruct *tableStruct, shardTableName string) error {

	for key, index := range indexSchemaStructList {

		if _, exist := tableStruct.IndexList[key]; !exist {
			return fmt.Errorf("index %s doesnt exist %s", key, shardTableName)
		}

		if _, exist := tableStruct.IndexList[key]; exist {

			checkedIndex := ""
			expectedIndex := ""
			for _, field := range index.Fields {
				checkedIndex += field + ","
			}

			checkedIndex = checkedIndex[:len(checkedIndex)-1]

			for _, field := range tableStruct.IndexList[key].Fields {
				expectedIndex += field + ","
			}

			if strings.ToLower(checkedIndex) != strings.ToLower(expectedIndex) {
				return fmt.Errorf("column index %s not equals %s", key, shardTableName)
			}
		}

		if index.Uniq && !tableStruct.IndexList[key].Uniq {

			return fmt.Errorf("index %s not unique %s", key, shardTableName)
		}
	}

	return nil
}

// получить индексы таблицы
func getTableIndexes(credentials *sharding.DbCredentials, shardDbName string, shardTableName string) (map[string]*indexSchemaStruct, error) {

	indexSchema, err := company.GetIndexesFromTable(credentials, shardDbName, shardTableName)

	indexSchemaStructList := map[string]*indexSchemaStruct{}

	if err != nil {
		return nil, err
	}
	for _, index := range indexSchema {

		indexName := strings.ToLower(strings.Trim(index["Key_name"], " "))

		// если еще в слайсе не было объекта индекса - создаем
		if _, exist := indexSchemaStructList[indexName]; !exist {

			indexSchemaStructList[indexName] = &indexSchemaStruct{
				Fields: map[int]string{},
				Uniq:   false,
			}
		}

		// добавляем в индекс поле
		indexSchemaStructList[indexName].Fields[functions.StringToInt(index["Seq_in_index"])] = strings.ToLower(strings.Trim(index["Column_name"], " "))
		indexSchemaStructList[indexName].Uniq = functions.StringToInt(index["Non_uniq"]) == 0
	}

	return indexSchemaStructList, nil

}

// проверить сортировку полей в структуре таблицы
func checkSortFields(expectedFieldSchema []string, fieldSchema map[int]map[string]string) error {

	for key, value := range expectedFieldSchema {
		if value != fieldSchema[key]["Field"] {
			return fmt.Errorf("incorrect field sort %s", value)
		}
	}

	return nil
}

// проверить отдельное поле в таблице на валидность
func checkField(field map[string]string, tableStruct *tableStruct) error {

	if _, exist := tableStruct.FieldList[field["Field"]]; !exist {
		return fmt.Errorf("field %s not found", field["Field"])
	}

	// проверяем, что поле not null
	if field["Null"] != "NO" {
		return fmt.Errorf("field %s null", field["Field"])
	}

	// проверяем, что тип поля совпадает
	if tableStruct.FieldList[field["Field"]].Type != "" && strings.ToLower(tableStruct.FieldList[field["Field"]].Type) != strings.ToLower(field["Type"]) {
		return fmt.Errorf("incorrect field %s type %s", field["Field"], tableStruct.Name)
	}

	// проверяем, что дефолтное значение совпадает
	if tableStruct.FieldList[field["Field"]].Default != "" && strings.ToLower(tableStruct.FieldList[field["Field"]].Default) != strings.ToLower(field["Default"]) {
		return fmt.Errorf("incorrect field %s default %s", field["Field"], tableStruct.Name)
	}

	// проверяем6 что экстра поля совпадает
	if tableStruct.FieldList[field["Field"]].Extra != "" && strings.ToLower(tableStruct.FieldList[field["Field"]].Extra) != strings.ToLower(field["Extra"]) {
		return fmt.Errorf("incorrect field %s extra %s", field["Field"], tableStruct.Name)
	}

	return nil
}
