package auth

import (
	"database/sql"
	"encoding/json"
	"fmt"
	"net/http"
	"time"

	"github.com/connerohnesorge/con-nix/internal/models"
	"github.com/gorilla/sessions"
	"github.com/jmoiron/sqlx"
	"github.com/markbates/goth"
	"github.com/markbates/goth/gothic"
)

const SessionKey = "user_session"

func BeginAuthHandler(w http.ResponseWriter, r *http.Request) {
	gothic.BeginAuthHandler(w, r)
}

func CallbackHandler(db *sqlx.DB) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		user, err := gothic.CompleteUserAuth(w, r)
		if err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}

		// Check if user exists
		var dbUser models.User
		err = db.Get(&dbUser, "SELECT * FROM users WHERE github_id = ?", user.UserID)
		
		if err == sql.ErrNoRows {
			// Create new user
			_, err = db.Exec(`
				INSERT INTO users (name, username, email, github_id, github_username, github_avatar, created_at, updated_at)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?)
			`, user.Name, user.NickName, user.Email, user.UserID, user.NickName, user.AvatarURL, time.Now(), time.Now())
			
			if err != nil {
				http.Error(w, "Failed to create user", http.StatusInternalServerError)
				return
			}
			
			// Get the newly created user
			err = db.Get(&dbUser, "SELECT * FROM users WHERE github_id = ?", user.UserID)
			if err != nil {
				http.Error(w, "Failed to retrieve user", http.StatusInternalServerError)
				return
			}
		} else if err != nil {
			http.Error(w, "Database error", http.StatusInternalServerError)
			return
		}

		// Store user in session
		session, _ := gothic.Store.Get(r, SessionKey)
		session.Values["user_id"] = dbUser.ID
		session.Values["username"] = dbUser.Username
		session.Save(r, w)

		http.Redirect(w, r, "/dashboard", http.StatusFound)
	}
}

func LogoutHandler(w http.ResponseWriter, r *http.Request) {
	session, _ := gothic.Store.Get(r, SessionKey)
	session.Options.MaxAge = -1
	session.Save(r, w)
	
	gothic.Logout(w, r)
	http.Redirect(w, r, "/", http.StatusFound)
}

func GetUserFromSession(r *http.Request) (int64, bool) {
	session, _ := gothic.Store.Get(r, SessionKey)
	userID, ok := session.Values["user_id"].(int64)
	return userID, ok
}

func GetCurrentUser(r *http.Request, db *sqlx.DB) (*models.User, error) {
	userID, ok := GetUserFromSession(r)
	if !ok {
		return nil, fmt.Errorf("no user in session")
	}

	var user models.User
	err := db.Get(&user, "SELECT * FROM users WHERE id = ?", userID)
	if err != nil {
		return nil, err
	}

	return &user, nil
}