package migration

type databaseListStruct struct {
	DatabaseList []databaseSchemaStruct `yaml:"databaseList"`
}

type manticoreTableListStruct struct {
	ManticoreTableList []manticoreTableSchemaStruct `yaml:"manticoreTableList"`
}

type databaseSchemaStruct struct {
	Name          string `yaml:"name"`
	MigrationPath string `yaml:"migration_path"`
	Sharding      string `yaml:"sharding"`
}

type manticoreTableSchemaStruct struct {
	Name          string `yaml:"name"`
	MigrationPath string `yaml:"migration_path"`
}

type databaseStruct struct {
	DatabaseName          string        `yaml:"databaseName"`
	NeededDatabaseVersion int           `yaml:"neededDatabaseVersion"`
	TableList             []tableStruct `yaml:"tableList"`
	Sharding              string        `yaml:"sharding"`
}

type tableStruct struct {
	Name       string                 `yaml:"name"`
	FieldList  map[string]fieldStruct `yaml:"fieldList"`
	FieldOrder []string               `yaml:"fieldOrder"`
	IndexList  map[string]indexStruct `yaml:"indexList"`
	Engine     string                 `yaml:"engine"`
	Charset    string                 `yaml:"charset"`
	Sharding   string                 `yaml:"sharding"`
}

type fieldStruct struct {
	Type    string `yaml:"type"`
	Default string `yaml:"default,omitempty"`
	Extra   string `yaml:"extra,omitempty"`
}
type indexStruct struct {
	Fields []string `yaml:"fields"`
	Uniq   bool     `yaml:"unique,omitempty"`
}

type indexSchemaStruct struct {
	Fields map[int]string `json:"fields"`
	Uniq   bool           `json:"uniq"`
}
