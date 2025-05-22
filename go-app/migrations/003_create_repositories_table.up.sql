CREATE TABLE IF NOT EXISTS repositories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    slug TEXT NOT NULL,
    description TEXT,
    user_id INTEGER,
    organization_id INTEGER,
    is_private BOOLEAN NOT NULL DEFAULT 0,
    default_branch TEXT NOT NULL DEFAULT 'main',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    CHECK ((user_id IS NOT NULL AND organization_id IS NULL) OR (user_id IS NULL AND organization_id IS NOT NULL))
);

CREATE INDEX idx_repositories_slug ON repositories(slug);
CREATE INDEX idx_repositories_user_id ON repositories(user_id);
CREATE INDEX idx_repositories_organization_id ON repositories(organization_id);
CREATE UNIQUE INDEX idx_repositories_user_slug ON repositories(user_id, slug) WHERE user_id IS NOT NULL;
CREATE UNIQUE INDEX idx_repositories_org_slug ON repositories(organization_id, slug) WHERE organization_id IS NOT NULL;