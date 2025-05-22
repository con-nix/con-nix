package models

import (
	"database/sql"
	"time"
)

type Repository struct {
	ID             int64          `db:"id"`
	Name           string         `db:"name"`
	Slug           string         `db:"slug"`
	Description    sql.NullString `db:"description"`
	UserID         sql.NullInt64  `db:"user_id"`
	OrganizationID sql.NullInt64  `db:"organization_id"`
	IsPrivate      bool           `db:"is_private"`
	DefaultBranch  string         `db:"default_branch"`
	CreatedAt      time.Time      `db:"created_at"`
	UpdatedAt      time.Time      `db:"updated_at"`
}