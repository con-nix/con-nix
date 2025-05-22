package models

import (
	"database/sql"
	"time"
)

type User struct {
	ID              int64          `db:"id"`
	Name            string         `db:"name"`
	Username        string         `db:"username"`
	Email           string         `db:"email"`
	EmailVerifiedAt sql.NullTime  `db:"email_verified_at"`
	Password        sql.NullString `db:"password"`
	RememberToken   sql.NullString `db:"remember_token"`
	CurrentTeamID   sql.NullInt64  `db:"current_team_id"`
	ProfilePhotoURL sql.NullString `db:"profile_photo_url"`
	CreatedAt       time.Time      `db:"created_at"`
	UpdatedAt       time.Time      `db:"updated_at"`
	GitHubID        sql.NullString `db:"github_id"`
	GitHubUsername  sql.NullString `db:"github_username"`
	GitHubAvatar    sql.NullString `db:"github_avatar"`
}

type Follow struct {
	ID         int64     `db:"id"`
	FollowerID int64     `db:"follower_id"`
	FolloweeID int64     `db:"followee_id"`
	CreatedAt  time.Time `db:"created_at"`
	UpdatedAt  time.Time `db:"updated_at"`
}

type Notification struct {
	ID         int64          `db:"id"`
	UserID     int64          `db:"user_id"`
	Type       string         `db:"type"`
	Data       string         `db:"data"` // JSON
	ReadAt     sql.NullTime  `db:"read_at"`
	RelatedID  sql.NullInt64 `db:"related_id"`
	CreatedAt  time.Time      `db:"created_at"`
	UpdatedAt  time.Time      `db:"updated_at"`
}

type Activity struct {
	ID           int64          `db:"id"`
	UserID       int64          `db:"user_id"`
	Type         string         `db:"type"`
	Description  string         `db:"description"`
	Properties   sql.NullString `db:"properties"` // JSON
	SubjectType  sql.NullString `db:"subject_type"`
	SubjectID    sql.NullInt64  `db:"subject_id"`
	CauserType   sql.NullString `db:"causer_type"`
	CauserID     sql.NullInt64  `db:"causer_id"`
	CreatedAt    time.Time      `db:"created_at"`
	UpdatedAt    time.Time      `db:"updated_at"`
}