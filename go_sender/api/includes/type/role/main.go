package role

import (
	"context"
	"go_sender/api/global_observer"
	GlobalIsolation "go_sender/api/includes/type/global_isolation"
	Isolation "go_sender/api/includes/type/isolation"
	"strings"
)

func Begin(ctx context.Context, globalIsolation *GlobalIsolation.GlobalIsolation, companyContextList *Isolation.CompanyEnvList) {

	// запускаем обсерверов
	startPivotObserver(ctx, globalIsolation, companyContextList)
	startAnnouncementObserver(ctx, globalIsolation, companyContextList)
	startDominoObserver(ctx, globalIsolation, companyContextList)
}

// startPivotObserver Work метод для выполнения работы через время
func startPivotObserver(ctx context.Context, globalIsolation *GlobalIsolation.GlobalIsolation, companyContextList *Isolation.CompanyEnvList) {

	if !isPivot(globalIsolation) {
		return
	}

	global_observer.WorkPivotObserver(ctx, globalIsolation, companyContextList)
}

// startDominoObserver Work метод для выполнения работы через время
func startDominoObserver(ctx context.Context, globalIsolation *GlobalIsolation.GlobalIsolation, companyContextList *Isolation.CompanyEnvList) {

	if !isDomino(globalIsolation) {
		return
	}

	global_observer.WorkDominoObserver(ctx, globalIsolation, companyContextList)
}

// startAnnouncementObserver Work метод для выполнения работы через время
func startAnnouncementObserver(ctx context.Context, globalIsolation *GlobalIsolation.GlobalIsolation, companyContextList *Isolation.CompanyEnvList) {

	if !isAnnouncement(globalIsolation) {
		return
	}

	global_observer.WorkPivotObserver(ctx, globalIsolation, companyContextList)
}

// isPivot проверяет, содержится ли 'pivot' в строке role
func isPivot(globalIsolation *GlobalIsolation.GlobalIsolation) bool {
	return strings.Contains(globalIsolation.GetConfig().Role, "pivot")
}

// isDomino проверяет, содержится ли 'domino' в строке role
func isDomino(globalIsolation *GlobalIsolation.GlobalIsolation) bool {
	return strings.Contains(globalIsolation.GetConfig().Role, "domino")
}

// isAnnouncement проверяет, содержится ли 'announcement' в строке role
func isAnnouncement(globalIsolation *GlobalIsolation.GlobalIsolation) bool {
	return strings.Contains(globalIsolation.GetConfig().Role, "announcement")
}
