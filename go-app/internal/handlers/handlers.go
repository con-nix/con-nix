package handlers

import (
	"database/sql"
	"net/http"

	"github.com/connerohnesorge/con-nix/internal/auth"
	"github.com/connerohnesorge/con-nix/internal/templates"
	"github.com/jmoiron/sqlx"
)

type Handlers struct {
	db *sqlx.DB
}

func New(db *sqlx.DB) *Handlers {
	return &Handlers{db: db}
}

func (h *Handlers) HomeHandler(w http.ResponseWriter, r *http.Request) {
	// Check if user is logged in
	_, ok := auth.GetUserFromSession(r)
	if ok {
		http.Redirect(w, r, "/dashboard", http.StatusFound)
		return
	}

	component := templates.Home()
	component.Render(r.Context(), w)
}

func (h *Handlers) LoginHandler(w http.ResponseWriter, r *http.Request) {
	component := templates.Login()
	component.Render(r.Context(), w)
}

func (h *Handlers) DashboardHandler(w http.ResponseWriter, r *http.Request) {
	user, err := auth.GetCurrentUser(r, h.db)
	if err != nil {
		http.Error(w, "Failed to get user", http.StatusInternalServerError)
		return
	}

	// Get user's repositories
	var repos []templates.Repository
	err = h.db.Select(&repos, `
		SELECT id, name, slug, description, is_private 
		FROM repositories 
		WHERE user_id = ? 
		ORDER BY updated_at DESC 
		LIMIT 10
	`, user.ID)
	if err != nil && err != sql.ErrNoRows {
		http.Error(w, "Failed to get repositories", http.StatusInternalServerError)
		return
	}

	// Get unread notifications count
	var unreadCount int
	h.db.Get(&unreadCount, "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND read_at IS NULL", user.ID)

	templateUser := &templates.User{
		ID:                  user.ID,
		Name:                user.Name,
		Username:            user.Username,
		Email:               user.Email,
		ProfilePhotoURL:     user.GitHubAvatar.String,
		UnreadNotifications: unreadCount,
	}

	component := templates.Dashboard(templateUser, repos)
	templates.Layout(templateUser, component).Render(r.Context(), w)
}

func (h *Handlers) ExploreHandler(w http.ResponseWriter, r *http.Request) {
	user, _ := auth.GetCurrentUser(r, h.db)
	
	// Get public repositories
	var repos []templates.Repository
	err := h.db.Select(&repos, `
		SELECT r.id, r.name, r.slug, r.description, r.is_private,
		       COALESCE(u.username, o.slug) as owner_name
		FROM repositories r
		LEFT JOIN users u ON r.user_id = u.id
		LEFT JOIN organizations o ON r.organization_id = o.id
		WHERE r.is_private = 0
		ORDER BY r.created_at DESC
		LIMIT 50
	`)
	if err != nil {
		http.Error(w, "Failed to get repositories", http.StatusInternalServerError)
		return
	}

	var templateUser *templates.User
	if user != nil {
		var unreadCount int
		h.db.Get(&unreadCount, "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND read_at IS NULL", user.ID)
		
		templateUser = &templates.User{
			ID:                  user.ID,
			Name:                user.Name,
			Username:            user.Username,
			Email:               user.Email,
			ProfilePhotoURL:     user.GitHubAvatar.String,
			UnreadNotifications: unreadCount,
		}
	}

	component := templates.Explore(repos)
	if templateUser != nil {
		templates.Layout(templateUser, component).Render(r.Context(), w)
	} else {
		templates.Base("Explore - ConNix", component).Render(r.Context(), w)
	}
}

// Placeholder handlers - implement these next
func (h *Handlers) FeedHandler(w http.ResponseWriter, r *http.Request) {
	http.Error(w, "Not implemented", http.StatusNotImplemented)
}

func (h *Handlers) NotificationsHandler(w http.ResponseWriter, r *http.Request) {
	http.Error(w, "Not implemented", http.StatusNotImplemented)
}

func (h *Handlers) UserProfileHandler(w http.ResponseWriter, r *http.Request) {
	http.Error(w, "Not implemented", http.StatusNotImplemented)
}

func (h *Handlers) UserFollowersHandler(w http.ResponseWriter, r *http.Request) {
	http.Error(w, "Not implemented", http.StatusNotImplemented)
}

func (h *Handlers) UserFollowingHandler(w http.ResponseWriter, r *http.Request) {
	http.Error(w, "Not implemented", http.StatusNotImplemented)
}

func (h *Handlers) FollowUserHandler(w http.ResponseWriter, r *http.Request) {
	http.Error(w, "Not implemented", http.StatusNotImplemented)
}

func (h *Handlers) UnfollowUserHandler(w http.ResponseWriter, r *http.Request) {
	http.Error(w, "Not implemented", http.StatusNotImplemented)
}

func (h *Handlers) RepositoriesHandler(w http.ResponseWriter, r *http.Request) {
	http.Error(w, "Not implemented", http.StatusNotImplemented)
}

func (h *Handlers) CreateRepositoryHandler(w http.ResponseWriter, r *http.Request) {
	http.Error(w, "Not implemented", http.StatusNotImplemented)
}

func (h *Handlers) RepositoryHandler(w http.ResponseWriter, r *http.Request) {
	http.Error(w, "Not implemented", http.StatusNotImplemented)
}

func (h *Handlers) EditRepositoryHandler(w http.ResponseWriter, r *http.Request) {
	http.Error(w, "Not implemented", http.StatusNotImplemented)
}

func (h *Handlers) DeleteRepositoryHandler(w http.ResponseWriter, r *http.Request) {
	http.Error(w, "Not implemented", http.StatusNotImplemented)
}

func (h *Handlers) TransferRepositoryHandler(w http.ResponseWriter, r *http.Request) {
	http.Error(w, "Not implemented", http.StatusNotImplemented)
}

func (h *Handlers) OrganizationsHandler(w http.ResponseWriter, r *http.Request) {
	http.Error(w, "Not implemented", http.StatusNotImplemented)
}

func (h *Handlers) CreateOrganizationHandler(w http.ResponseWriter, r *http.Request) {
	http.Error(w, "Not implemented", http.StatusNotImplemented)
}

func (h *Handlers) OrganizationHandler(w http.ResponseWriter, r *http.Request) {
	http.Error(w, "Not implemented", http.StatusNotImplemented)
}

func (h *Handlers) EditOrganizationHandler(w http.ResponseWriter, r *http.Request) {
	http.Error(w, "Not implemented", http.StatusNotImplemented)
}

func (h *Handlers) DeleteOrganizationHandler(w http.ResponseWriter, r *http.Request) {
	http.Error(w, "Not implemented", http.StatusNotImplemented)
}

func (h *Handlers) OrganizationMembersHandler(w http.ResponseWriter, r *http.Request) {
	http.Error(w, "Not implemented", http.StatusNotImplemented)
}

func (h *Handlers) InviteToOrganizationHandler(w http.ResponseWriter, r *http.Request) {
	http.Error(w, "Not implemented", http.StatusNotImplemented)
}

func (h *Handlers) AcceptInviteHandler(w http.ResponseWriter, r *http.Request) {
	http.Error(w, "Not implemented", http.StatusNotImplemented)
}

func (h *Handlers) ProfileSettingsHandler(w http.ResponseWriter, r *http.Request) {
	http.Error(w, "Not implemented", http.StatusNotImplemented)
}

func (h *Handlers) PasswordSettingsHandler(w http.ResponseWriter, r *http.Request) {
	http.Error(w, "Not implemented", http.StatusNotImplemented)
}

func (h *Handlers) AppearanceSettingsHandler(w http.ResponseWriter, r *http.Request) {
	http.Error(w, "Not implemented", http.StatusNotImplemented)
}

func (h *Handlers) UnreadNotificationsHandler(w http.ResponseWriter, r *http.Request) {
	http.Error(w, "Not implemented", http.StatusNotImplemented)
}