package models

import (
	"database/sql"
	"time"
)

type Organization struct {
	ID          int64          `db:"id"`
	Name        string         `db:"name"`
	Slug        string         `db:"slug"`
	Description sql.NullString `db:"description"`
	OwnerID     int64          `db:"owner_id"`
	CreatedAt   time.Time      `db:"created_at"`
	UpdatedAt   time.Time      `db:"updated_at"`
}

type OrganizationMember struct {
	ID             int64     `db:"id"`
	OrganizationID int64     `db:"organization_id"`
	UserID         int64     `db:"user_id"`
	Role           string    `db:"role"` // owner, admin, member
	CreatedAt      time.Time `db:"created_at"`
	UpdatedAt      time.Time `db:"updated_at"`
}

type OrganizationInvite struct {
	ID             int64     `db:"id"`
	OrganizationID int64     `db:"organization_id"`
	Email          string    `db:"email"`
	Token          string    `db:"token"`
	Role           string    `db:"role"`
	InvitedByID    int64     `db:"invited_by_id"`
	AcceptedAt     sql.NullTime `db:"accepted_at"`
	CreatedAt      time.Time `db:"created_at"`
	UpdatedAt      time.Time `db:"updated_at"`
}