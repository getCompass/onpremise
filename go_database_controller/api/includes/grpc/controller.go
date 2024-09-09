package grpc

import (
	"context"
	pb "github.com/getCompassUtils/pivot_protobuf_schemes/go/database_controller"
	"go_database_controller/api/includes/type/backup"
	"go_database_controller/api/includes/type/keeper"
	"go_database_controller/api/includes/type/manticore"
	"go_database_controller/api/includes/type/migration"
	"go_database_controller/api/includes/type/port_registry"
	"go_database_controller/api/includes/type/relocation"
	"go_database_controller/api/includes/type/routine"
	"google.golang.org/grpc/status"
	"time"
)

// пакет с gRPC контролером — содержит в себе все методы микросервиса
// вынесен отдельно, так как grpc реализует свой роутер запросов и интерфейс работы с request/response

// Server структура контроллера
type Server struct {
	pb.DatabaseControllerServer
}

// GetStatus возвращает статус демона на указанном порте
func (s *Server) GetStatus(_ context.Context, in *pb.GetStatusRequestStruct) (*pb.GetStatusResponseStruct, error) {

	ok := keeper.Report(in.GetPort())
	var databaseStatus string

	if ok {
		databaseStatus = "started"
	} else {
		databaseStatus = "stopped"
	}

	return &pb.GetStatusResponseStruct{
		Status:  databaseStatus,
		Message: "",
	}, nil
}

// CreateVacantCompany создает свободную компанию
// Deprecated: лучше в два шага делать — создание данных + привязка порта
func (s *Server) CreateVacantCompany(ctx context.Context, in *pb.CreateVacantCompanyRequestStruct) (*pb.NullResponseStruct, error) {

	err := keeper.CreateVacantCompany(ctx, in.GetPort(), in.GetCompanyId())

	return &pb.NullResponseStruct{}, err
}

// AddPort добавляет порты на домино
// порты должны быть синхронизованы с pivot-сервером
func (s *Server) AddPort(ctx context.Context, in *pb.AddPortRequestStruct) (*pb.NullResponseStruct, error) {

	err := keeper.AddPort(ctx, in.GetPort(), in.GetStatus(), in.GetType(), in.GetLockedTill(), in.GetCreatedAt(), in.GetUpdatedAt(), in.GetCompanyId(), in.GetExtra())

	return &pb.NullResponseStruct{}, err
}

// SetPortInvalid инвалидирует порт
func (s *Server) SetPortInvalid(ctx context.Context, in *pb.SetPortInvalidRequestStruct) (*pb.NullResponseStruct, error) {

	return &pb.NullResponseStruct{}, keeper.InvalidatePort(ctx, in.GetPort())
}

// BindPort выполняет привязку порта и компании на любой порт
func (s *Server) BindPort(ctx context.Context, in *pb.BindPortRequestStruct) (*pb.BindPortResponseStruct, error) {

	// запускаем рутину и пробуем дождаться выполнения
	// начало процесса мгновенное, туда можно не передавать контекст
	routineKey := keeper.BeginBindPort(in.GetPort(), in.GetCompanyId(), in.NonExistingDataDirPolicy, in.DuplicateDataDirPolicy)
	waitCtx, cancel := context.WithDeadline(ctx, time.Now().Add(5*time.Second))

	// дожидаемся ответа рутины
	result := routine.GetRoutineStatus(waitCtx, routineKey)
	cancel()

	response := pb.BindPortResponseStruct{
		RoutineKey: routineKey,
		Routine: &pb.GetRoutineStatusResponse{
			Status:  result.Status,
			Message: result.Message,
		},
	}

	return &response, nil
}

// BindOnServicePort выполняет привязку порта и компании на сервисный порт
// Deprecated: привязку нужно делать только через BindPort
func (s *Server) BindOnServicePort(ctx context.Context, in *pb.BindOnServicePortRequestStruct) (*pb.BindOnServicePortResponseStruct, error) {

	port, user, pass, err := keeper.BindOnServicePort(ctx, in.GetCompanyId())

	return &pb.BindOnServicePortResponseStruct{
		Port:      port,
		MysqlUser: user,
		MysqlPass: pass,
	}, err
}

// GetCompanyPort возвращает порт и данные  для компании, который связан с указанной компанией
func (s *Server) GetCompanyPort(ctx context.Context, in *pb.GetCompanyPortRequestStruct) (*pb.GetCompanyPortResponseStruct, error) {

	port, user, pass, err := keeper.GetCompanyPort(ctx, in.GetCompanyId())
	if port == 0 && err == nil {
		return nil, status.Errorf(404, "port not found")
	}

	return &pb.GetCompanyPortResponseStruct{
		Port:      port,
		MysqlUser: user,
		MysqlPass: pass,
	}, err
}

// UnbindPort отвязывает порт от любой компании, которая на нем развернута
func (s *Server) UnbindPort(ctx context.Context, in *pb.UnbindPortRequestStruct) (*pb.NullResponseStruct, error) {

	err := keeper.UnbindPort(ctx, in.GetPort())

	return &pb.NullResponseStruct{}, err
}

// CreateMysqlBackup создает бэкап для компании
// возвращает ключ рутины и результат рутины
func (s *Server) CreateMysqlBackup(ctx context.Context, in *pb.CreateMysqlBackupRequestStruct) (*pb.CreateMysqlBackupResponseStruct, error) {

	routineKey, backupName := backup.StartBackupDatabase(in.GetCompanyId())
	waitCtx, cancel := context.WithDeadline(ctx, time.Now().Add(5*time.Second))

	// дожидаемся ответа рутины
	result := routine.GetRoutineStatus(waitCtx, routineKey)
	cancel()

	return &pb.CreateMysqlBackupResponseStruct{
		RoutineKey: routineKey,
		Routine: &pb.GetRoutineStatusResponse{
			Status:  result.Status,
			Message: result.Message,
		},
		BackupName: backupName,
	}, nil
}

// накатить миграции для компании
func (s *Server) MigrateUp(ctx context.Context, in *pb.MigrateRequestStruct) (*pb.MigrateResponseStruct, error) {

	routineKey := migration.StartMigrateUp(in.GetCompanyId())

	waitCtx, cancel := context.WithDeadline(ctx, time.Now().Add(5*time.Second))

	// дожидаемся ответа рутины
	result := routine.GetRoutineStatus(waitCtx, routineKey)
	cancel()

	routineLog := routine.GetRoutineLog(routineKey)

	return &pb.MigrateResponseStruct{
		RoutineKey: routineKey,
		Routine: &pb.GetRoutineStatusResponse{
			Status:  result.Status,
			Message: result.Message,
			Log:     routineLog,
		},
	}, nil
}

// уничтожаем легаси в компании
func (s *Server) MigrateLegacyClean(ctx context.Context, in *pb.MigrateRequestStruct) (*pb.MigrateResponseStruct, error) {

	routineKey := migration.StartMigrateLegacyClean(in.GetCompanyId())

	waitCtx, cancel := context.WithDeadline(ctx, time.Now().Add(5*time.Second))

	// дожидаемся ответа рутины
	result := routine.GetRoutineStatus(waitCtx, routineKey)
	cancel()

	routineLog := routine.GetRoutineLog(routineKey)

	return &pb.MigrateResponseStruct{
		RoutineKey: routineKey,
		Routine: &pb.GetRoutineStatusResponse{
			Status:  result.Status,
			Message: result.Message,
			Log:     routineLog,
		},
	}, nil
}

// запускает процесс копирования данных с одного сервера на другой
// возвращает ключ рутины и результат рутины
func (s *Server) BeginDataCopying(ctx context.Context, in *pb.BeginDataCopyingRequestStruct) (*pb.BeginDataCopyingResponseStruct, error) {

	// запускаем рутину и пробуем дождаться выполнения
	// начало процесса мгновенное, туда можно не передавать контекст
	routineKey, _ := relocation.BeginDataCopying(in.GetCompanyId(), in.GetTargetHost())
	waitCtx, cancel := context.WithDeadline(ctx, time.Now().Add(5*time.Second))

	// дожидаемся ответа рутины
	result := routine.GetRoutineStatus(waitCtx, routineKey)
	cancel()

	response := pb.BeginDataCopyingResponseStruct{
		RoutineKey: routineKey,
		Routine: &pb.GetRoutineStatusResponse{
			Status:  result.Status,
			Message: result.Message,
		},
	}

	return &response, nil
}

// BeginDataApplying запускает процесс применения скопированных данных
// возвращает ключ рутины и результат рутины
func (s *Server) BeginDataApplying(ctx context.Context, in *pb.BeginDataApplyingRequestStruct) (*pb.BeginDataApplyingResponseStruct, error) {

	// запускаем рутину и пробуем дождаться выполнения
	// начало процесса мгновенное, туда можно не передавать контекст
	routineKey := relocation.BeginDataApplying(in.GetCompanyId())
	waitCtx, cancel := context.WithDeadline(ctx, time.Now().Add(5*time.Second))

	// дожидаемся ответа рутины
	result := routine.GetRoutineStatus(waitCtx, routineKey)
	cancel()

	response := pb.BeginDataApplyingResponseStruct{
		RoutineKey: routineKey,
		Routine: &pb.GetRoutineStatusResponse{
			Status:  result.Status,
			Message: result.Message,
		},
	}

	return &response, nil
}

// GetRoutineStatus возвращает состояние рутины для вызова долгого исполнения
func (s *Server) GetRoutineStatus(ctx context.Context, in *pb.GetRoutineStatusRequest) (*pb.GetRoutineStatusResponse, error) {

	// запускаем рутину и пробуем дождаться выполнения
	// начало процесса мгновенное, туда можно не передавать контекст
	waitCtx, cancel := context.WithDeadline(ctx, time.Now().Add(5*time.Second))
	routineResult := routine.GetRoutineStatus(waitCtx, in.GetRoutineKey())

	cancel()

	routineLog := routine.GetRoutineLog(in.RoutineKey)

	response := pb.GetRoutineStatusResponse{
		Status:  routineResult.Status,
		Message: routineResult.Message,
		Log:     routineLog,
	}

	return &response, nil
}

// SyncPortStatus выполняет синхронизацию порта между pivot-сервером и контроллером
func (s *Server) SyncPortStatus(ctx context.Context, in *pb.SyncPortStatusRequestStruct) (*pb.NullResponseStruct, error) {

	return &pb.NullResponseStruct{}, port_registry.SyncPort(ctx, in.GetPort(), in.GetStatus(), in.GetLockedTill(), in.GetCompanyId())
}

// ResetPort выполняет сброс порта
// останавливает демон базы, если дернуть метод неправильно, то компания отвалится
func (s *Server) ResetPort(ctx context.Context, in *pb.ResetPortRequestStruct) (*pb.NullResponseStruct, error) {

	return &pb.NullResponseStruct{}, keeper.ResetPort(ctx, in.GetPort())
}

// InitSearch выполняем инициализацию таблиц поиска
func (s *Server) InitSearch(ctx context.Context, in *pb.InitSearchRequestStruct) (*pb.NullResponseStruct, error) {

	return &pb.NullResponseStruct{}, manticore.InitTable(ctx, in.GetSpaceId())
}

// DropSearchTable удаляем поисковый индекс пространства
func (s *Server) DropSearchTable(_ context.Context, in *pb.DropSearchTableRequestStruct) (*pb.NullResponseStruct, error) {

	return &pb.NullResponseStruct{}, manticore.DropTable(in.GetSpaceId())
}

// UpdateDeployment обновить деплой
func (s *Server) UpdateDeployment(ctx context.Context, _ *pb.NullResponseStruct) (*pb.NullResponseStruct, error) {

	return &pb.NullResponseStruct{}, keeper.UpdateDeployment(ctx)
}
