package main

import (
	"database/sql"
	"log"
	"net/http"
	"os"

	"github.com/connerohnesorge/con-nix/internal/auth"
	"github.com/connerohnesorge/con-nix/internal/database"
	"github.com/connerohnesorge/con-nix/internal/handlers"
	"github.com/connerohnesorge/con-nix/internal/middleware"
	"github.com/gorilla/mux"
	"github.com/gorilla/sessions"
	"github.com/joho/godotenv"
	"github.com/markbates/goth"
	"github.com/markbates/goth/gothic"
	"github.com/markbates/goth/providers/github"
)

func main() {
	// Load environment variables
	if err := godotenv.Load(); err != nil {
		log.Println("No .env file found")
	}

	// Initialize database
	db, err := database.InitDB()
	if err != nil {
		log.Fatal("Failed to initialize database:", err)
	}
	defer db.Close()

	// Run migrations
	if err := database.RunMigrations(db); err != nil {
		log.Fatal("Failed to run migrations:", err)
	}

	// Initialize session store
	store := sessions.NewCookieStore([]byte(getEnv("SESSION_SECRET", "secret-key-change-this")))
	gothic.Store = store

	// Initialize GitHub OAuth
	goth.UseProviders(
		github.New(
			os.Getenv("GITHUB_CLIENT_ID"),
			os.Getenv("GITHUB_CLIENT_SECRET"),
			getEnv("APP_URL", "http://localhost:8000")+"/auth/github/callback",
		),
	)

	// Initialize handlers
	h := handlers.New(db)

	// Setup routes
	r := mux.NewRouter()

	// Static files
	r.PathPrefix("/static/").Handler(http.StripPrefix("/static/", http.FileServer(http.Dir("static"))))

	// Auth routes
	r.HandleFunc("/auth/github", auth.BeginAuthHandler).Methods("GET")
	r.HandleFunc("/auth/github/callback", auth.CallbackHandler(db)).Methods("GET")
	r.HandleFunc("/logout", auth.LogoutHandler).Methods("GET")

	// Public routes
	r.HandleFunc("/", h.HomeHandler).Methods("GET")
	r.HandleFunc("/login", h.LoginHandler).Methods("GET")
	r.HandleFunc("/explore", h.ExploreHandler).Methods("GET")

	// Protected routes
	protected := r.PathPrefix("/").Subrouter()
	protected.Use(middleware.AuthMiddleware)

	// Dashboard
	protected.HandleFunc("/dashboard", h.DashboardHandler).Methods("GET")
	protected.HandleFunc("/feed", h.FeedHandler).Methods("GET")
	protected.HandleFunc("/notifications", h.NotificationsHandler).Methods("GET")

	// User routes
	protected.HandleFunc("/users/{username}", h.UserProfileHandler).Methods("GET")
	protected.HandleFunc("/users/{username}/followers", h.UserFollowersHandler).Methods("GET")
	protected.HandleFunc("/users/{username}/following", h.UserFollowingHandler).Methods("GET")
	protected.HandleFunc("/users/{username}/follow", h.FollowUserHandler).Methods("POST")
	protected.HandleFunc("/users/{username}/unfollow", h.UnfollowUserHandler).Methods("POST")

	// Repository routes
	protected.HandleFunc("/repositories", h.RepositoriesHandler).Methods("GET")
	protected.HandleFunc("/repositories/create", h.CreateRepositoryHandler).Methods("GET", "POST")
	protected.HandleFunc("/{owner}/{repo}", h.RepositoryHandler).Methods("GET")
	protected.HandleFunc("/{owner}/{repo}/edit", h.EditRepositoryHandler).Methods("GET", "POST")
	protected.HandleFunc("/{owner}/{repo}/delete", h.DeleteRepositoryHandler).Methods("POST")
	protected.HandleFunc("/{owner}/{repo}/transfer", h.TransferRepositoryHandler).Methods("GET", "POST")

	// Organization routes
	protected.HandleFunc("/organizations", h.OrganizationsHandler).Methods("GET")
	protected.HandleFunc("/organizations/create", h.CreateOrganizationHandler).Methods("GET", "POST")
	protected.HandleFunc("/orgs/{org}", h.OrganizationHandler).Methods("GET")
	protected.HandleFunc("/orgs/{org}/edit", h.EditOrganizationHandler).Methods("GET", "POST")
	protected.HandleFunc("/orgs/{org}/delete", h.DeleteOrganizationHandler).Methods("POST")
	protected.HandleFunc("/orgs/{org}/members", h.OrganizationMembersHandler).Methods("GET")
	protected.HandleFunc("/orgs/{org}/invite", h.InviteToOrganizationHandler).Methods("GET", "POST")

	// Organization invite routes (public)
	r.HandleFunc("/invites/{token}", h.AcceptInviteHandler).Methods("GET", "POST")

	// Settings routes
	settings := protected.PathPrefix("/settings").Subrouter()
	settings.HandleFunc("/profile", h.ProfileSettingsHandler).Methods("GET", "POST")
	settings.HandleFunc("/password", h.PasswordSettingsHandler).Methods("GET", "POST")
	settings.HandleFunc("/appearance", h.AppearanceSettingsHandler).Methods("GET", "POST")

	// API routes
	api := r.PathPrefix("/api").Subrouter()
	api.Use(middleware.AuthMiddleware)
	api.HandleFunc("/notifications/unread", h.UnreadNotificationsHandler).Methods("GET")

	// Start server
	port := getEnv("PORT", "8000")
	log.Printf("Server starting on port %s", port)
	if err := http.ListenAndServe(":"+port, r); err != nil {
		log.Fatal(err)
	}
}

func getEnv(key, defaultValue string) string {
	if value := os.Getenv(key); value != "" {
		return value
	}
	return defaultValue
}