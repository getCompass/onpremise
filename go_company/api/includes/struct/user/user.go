package structUser

// описания структуры рейтинга для пользователя
type Rating struct {
	UserId          int64          `json:"user_id"`
	GeneralPosition int            `json:"general_position"`
	Year            int            `json:"year"`
	EventCountList  map[string]int `json:"event_count_list"`
	GeneralCount    int            `json:"general_count"`
	UpdatedAt       int64          `json:"updated_at"`
}

// описания структуры рейтинга для пользователя
type RatingPosition struct {
	GeneralPosition             int
	ConversationMessagePosition int
	ThreadMessagePosition       int
	ReactionPosition            int
	FilePosition                int
	CallPosition                int
	VoicePosition               int
	RespectPosition             int
	ExactingnessPosition        int
}
