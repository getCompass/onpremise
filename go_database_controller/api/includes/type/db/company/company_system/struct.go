package company_system

type MigrationDatabaseStruct struct {
	FullDatabaseName string `json:"full_database_name"`
	DatabaseName     string `json:"database_name"`
	IsCompleted      int    `json:"is_completed"`
	CurrentVersion   int    `json:"current_version"`
	PreviousVersion  int    `json:"previous_version"`
	ExpectedVersion  int    `json:"expected_version"`
	HighestVersion   int    `json:"highest_version"`
	LastMigratedType int    `json:"last_migrated_type"`
	LastMigratedAt   int    `json:"last_migrated_at"`
	LastMigratedFile string `json:"last_migrated_file"`
	CreatedAt        int64  `json:"created_at"`
}
