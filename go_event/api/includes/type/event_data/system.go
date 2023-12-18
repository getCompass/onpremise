package EventData

// список событий, чтобы не использовать строки при создании подписок
var SystemEventList = struct {
	SubscriptionRefreshingRequired string
}{
	SubscriptionRefreshingRequired: "system.subscriptions_refreshing_requested", // переподписка на события
}
