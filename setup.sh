#!/bin/bash
# Setup script for the Liberu Automation project.
#
# Provides installation options: Standalone, Docker, or Kubernetes.
# Handles composer/npm installs with fallback logic and error checking.
#
# Usage: ./setup.sh [--help]

set -euo pipefail

# ---------------------------------------------------------------------------
# Colors
# ---------------------------------------------------------------------------
RED='\e[91m'
GREEN='\e[92m'
YELLOW='\e[93m'
BLUE='\e[94m'
RESET='\e[39m'

print_message() { echo -e "${1}${2}${RESET}"; }
print_header()  { echo ""; echo "=================================="; echo "$1"; echo "=================================="; echo ""; }
print_error()   { print_message "$RED"    "ERROR: $1"; }
print_success() { print_message "$GREEN"  "OK: $1"; }
print_info()    { print_message "$BLUE"   "INFO: $1"; }
print_warning() { print_message "$YELLOW" "WARNING: $1"; }

command_exists() { command -v "$1" >/dev/null 2>&1; }

# ---------------------------------------------------------------------------
# Help
# ---------------------------------------------------------------------------
show_help() {
    cat <<EOF
Liberu Automation Setup Script

Usage: ./setup.sh [OPTIONS]

OPTIONS:
  --help        Show this help message and exit

INSTALLATION MODES:
  1) Standalone  Local/bare-metal install: composer, npm, migrate, seed
  2) Docker      docker compose up with auto .env copy
  3) Kubernetes  kubectl apply via Kustomize overlays (dev/prod/control-panel)

EXAMPLES:
  ./setup.sh                  # Interactive menu
  bash setup.sh               # Same as above

KUBERNETES OVERLAYS:
  k8s/overlays/development/   Single replica, debug logging
  k8s/overlays/production/    3 replicas + HPA, warning logging
  k8s/overlays/control-panel/ hosting-{domain} namespace for liberu-control-panel

REQUIREMENTS:
  PHP 8.5+, Composer, Node.js 20+, npm

EOF
    exit 0
}

# ---------------------------------------------------------------------------
# Prerequisite checks
# ---------------------------------------------------------------------------
check_php_version() {
    if ! command_exists php; then
        print_error "PHP is not installed. Install PHP 8.5+ first."
        exit 1
    fi

    local php_version
    php_version=$(php -r 'echo PHP_VERSION;')

    if php -r "exit(version_compare(PHP_VERSION, '8.5', '>=') ? 0 : 1);"; then
        print_success "PHP ${php_version} meets the minimum requirement (8.5+)"
    else
        print_error "PHP ${php_version} is below the required version (8.5+). Please upgrade."
        exit 1
    fi
}

check_node_version() {
    if ! command_exists node; then
        print_warning "Node.js is not installed — frontend assets will be skipped"
        return 1
    fi

    local node_version
    node_version=$(node --version | sed 's/v//')
    local node_major
    node_major=$(echo "$node_version" | cut -d. -f1)

    if [ "$node_major" -ge 20 ]; then
        print_success "Node.js v${node_version} meets the minimum requirement (20+)"
    else
        print_warning "Node.js v${node_version} is below the recommended version (20+). Consider upgrading."
    fi
}

check_required_env_vars() {
    local missing=0
    for var in APP_KEY DB_DATABASE DB_USERNAME DB_PASSWORD; do
        if ! grep -qE "^${var}=.+" .env 2>/dev/null; then
            print_warning "Missing or empty env var: ${var}"
            missing=1
        fi
    done
    if [ "$missing" -eq 1 ]; then
        print_warning "Some required env vars are not set. Migrations may fail."
        read -rp "Continue anyway? (y/n) " -n 1; echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            exit 1
        fi
    fi
}

# ---------------------------------------------------------------------------
# Composer bootstrap
# ---------------------------------------------------------------------------
ensure_composer() {
    if command_exists composer; then
        print_success "Composer is installed"
        COMPOSER_CMD="composer"
        return 0
    fi

    print_warning "Composer not found — downloading composer.phar..."

    if ! command_exists curl; then
        print_error "curl is required to download Composer. Install curl or Composer manually."
        return 1
    fi

    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php --quiet
    php -r "unlink('composer-setup.php');"

    if [ -f "composer.phar" ]; then
        print_success "composer.phar downloaded"
        COMPOSER_CMD="php composer.phar"
        return 0
    fi

    print_error "Failed to download composer.phar"
    return 1
}

# ---------------------------------------------------------------------------
# Dependency installers
# ---------------------------------------------------------------------------
install_composer_dependencies() {
    print_header "COMPOSER INSTALL"

    if [ -d "vendor" ] && [ -f "vendor/autoload.php" ]; then
        print_info "vendor/ already exists."
        read -rp "Reinstall composer dependencies? (y/n) " -n 1
        echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            print_success "Skipping composer install"
            return 0
        fi
    fi

    ensure_composer || { print_error "Cannot proceed without Composer"; return 1; }

    print_info "Running: $COMPOSER_CMD install"
    eval "$COMPOSER_CMD install --no-interaction --prefer-dist"
    print_success "Composer dependencies installed"
}

install_npm_dependencies() {
    print_header "NPM INSTALL"

    if ! command_exists npm; then
        print_warning "npm is not installed — skipping frontend install"
        return 1
    fi

    if [ -d "node_modules" ]; then
        print_info "node_modules/ already exists."
        read -rp "Reinstall npm dependencies? (y/n) " -n 1
        echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            print_success "Skipping npm install"
            return 0
        fi
    fi

    # Prefer ci (clean install) when lockfile exists for reproducible builds
    if [ -f "package-lock.json" ]; then
        npm ci
    else
        npm install
    fi
    print_success "npm dependencies installed"
}

build_frontend_assets() {
    print_header "NPM BUILD"

    if ! command_exists npm; then
        print_warning "npm is not installed — skipping frontend build"
        return 1
    fi

    npm run build
    print_success "Frontend assets built"
}

# ---------------------------------------------------------------------------
# Standalone
# ---------------------------------------------------------------------------
install_standalone() {
    print_header "STANDALONE INSTALLATION"
    echo "===== USER: [$(whoami)] ====="
    echo "===== PHP: [$(php -r 'echo phpversion();')] ====="
    echo ""

    check_php_version
    check_node_version || true

    # .env setup
    read -rp "Copy .env.example to .env? (y/n) " -n 1; echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        cp .env.example .env
        print_success "Copied .env.example -> .env"
        print_info "Edit .env now and set DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD."
        read -rp "Have you updated the database credentials in .env? (y/n) " -n 1; echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            print_warning "Please configure .env and re-run this script."
            exit 0
        fi
    elif [ ! -f ".env" ]; then
        print_error ".env file not found. Copy .env.example to .env and configure it."
        exit 1
    fi

    install_composer_dependencies || { print_error "Composer step failed"; exit 1; }
    install_npm_dependencies      || print_warning "npm install failed — continuing without frontend assets"
    build_frontend_assets         || print_warning "npm build failed — continuing without built assets"

    print_header "KEY:GENERATE"
    if php artisan key:generate; then
        print_success "Application key generated"
    else
        print_error "Failed to generate application key"
        exit 1
    fi

    check_required_env_vars

    print_header "MIGRATE"
    read -rp "Run migrate:fresh (destroys all data)? (y/n) " -n 1; echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        php artisan migrate:fresh || { print_error "Migration failed"; exit 1; }
        print_success "Database migrated (fresh)"
    else
        php artisan migrate || { print_error "Migration failed"; exit 1; }
        print_success "Database migrated"
    fi

    print_header "DB:SEED"
    read -rp "Seed the database? (y/n) " -n 1; echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        php artisan db:seed || print_warning "Seeding failed — continuing"
        print_success "Database seeded"
    fi

    print_header "STORAGE:LINK"
    if php artisan storage:link 2>/dev/null || true; then
        print_success "Storage link created"
    else
        print_warning "storage:link failed — public uploads may not work"
    fi

    print_header "FILAMENT:SHIELD"
    read -rp "Install Filament Shield (role/permission policies)? (y/n) " -n 1; echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        php artisan shield:install --fresh || print_warning "shield:install failed — continuing"
        print_success "Filament Shield installed"
    fi

    print_header "PHPUNIT TESTS"
    if [ -f "vendor/bin/phpunit" ]; then
        ./vendor/bin/phpunit || print_warning "Some tests failed — review the output above"
    else
        print_warning "phpunit not found in vendor/bin — skipping tests"
    fi

    print_header "OPTIMIZE:CLEAR"
    php artisan optimize:clear
    php artisan route:clear
    php artisan view:clear

    print_success "=================================="
    print_success "============== DONE =============="
    print_success "=================================="
    echo ""

    read -rp "Start the development server? (y/n) " -n 1; echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        print_info "Starting: php artisan serve"
        php artisan serve
    else
        print_success "Run 'php artisan serve' to start the development server."
    fi
}

# ---------------------------------------------------------------------------
# Docker
# ---------------------------------------------------------------------------
install_docker() {
    print_header "DOCKER INSTALLATION"

    if ! command_exists docker; then
        print_error "Docker is not installed. See https://docs.docker.com/get-docker/"
        exit 1
    fi
    print_success "Docker is installed"

    if ! command_exists docker-compose && ! docker compose version >/dev/null 2>&1; then
        print_error "Docker Compose is not installed. See https://docs.docker.com/compose/install/"
        exit 1
    fi
    print_success "Docker Compose is available"

    if [ ! -f ".env" ]; then
        cp .env.example .env
        print_warning "Copied .env.example -> .env. Please review it before continuing."
        read -rp "Press Enter to continue..." || true
    fi

    local compose_cmd="docker compose"
    command_exists docker-compose && compose_cmd="docker-compose"

    $compose_cmd up -d --build

    print_info "Waiting for containers to be ready..."
    sleep 5

    # Run post-up artisan commands inside the app container
    local app_container
    app_container=$($compose_cmd ps -q app 2>/dev/null | head -1 || true)

    if [ -n "$app_container" ]; then
        print_info "Running post-startup artisan commands..."
        docker exec "$app_container" php artisan storage:link 2>/dev/null || true
        docker exec "$app_container" php artisan optimize:clear 2>/dev/null || true
        print_success "Post-startup commands complete"
    fi

    print_success "Docker containers started — app available at http://localhost:8000"
}

# ---------------------------------------------------------------------------
# Kubernetes
# ---------------------------------------------------------------------------

generate_k8s_secret() {
    print_header "GENERATE K8S SECRET"
    print_info "Generating a k8s secret patch from your .env file..."

    if [ ! -f ".env" ]; then
        print_error ".env not found. Cannot generate secret."
        return 1
    fi

    local app_key db_password redis_pass
    app_key=$(grep    "^APP_KEY="        .env | cut -d= -f2-)
    db_password=$(grep "^DB_PASSWORD="   .env | cut -d= -f2-)
    redis_pass=$(grep  "^REDIS_PASSWORD=" .env | cut -d= -f2- || echo "")

    cat <<EOF
# Paste this into k8s/base/secret.yaml before applying:
  APP_KEY: "${app_key}"
  DB_PASSWORD: "${db_password}"
  REDIS_PASSWORD: "${redis_pass}"
EOF
}

install_kubernetes() {
    print_header "KUBERNETES INSTALLATION"

    if ! command_exists kubectl; then
        print_error "kubectl is not installed. See https://kubernetes.io/docs/tasks/tools/"
        exit 1
    fi
    print_success "kubectl is installed"

    K8S_DIR="k8s"
    if [ ! -d "$K8S_DIR" ]; then
        print_error "Kubernetes config directory '$K8S_DIR/' not found."
        exit 1
    fi

    echo "Select environment:"
    echo "  1) development"
    echo "  2) production"
    echo "  3) control-panel  (hosting-{domain} namespace for liberu-control-panel)"
    echo ""
    read -rp "Enter choice (1-3): " env_choice
    case $env_choice in
        1) OVERLAY="development" ;;
        2) OVERLAY="production" ;;
        3) OVERLAY="control-panel" ;;
        *) print_warning "Invalid choice — defaulting to production"; OVERLAY="production" ;;
    esac

    OVERLAY_DIR="${K8S_DIR}/overlays/${OVERLAY}"

    if [ -d "$OVERLAY_DIR" ] && [ -f "${OVERLAY_DIR}/kustomization.yaml" ]; then
        print_info "Kustomize overlay: ${OVERLAY}"

        if [ ! -f ".env" ]; then
            cp .env.example .env
            print_warning "Copied .env.example -> .env. Please review it."
            read -rp "Press Enter to continue..." || true
        fi

        # Offer to echo secret values from .env
        read -rp "Show secret values from .env for k8s/base/secret.yaml? (y/n) " -n 1; echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            generate_k8s_secret
            read -rp "Have you updated k8s/base/secret.yaml? (y/n) " -n 1; echo
            if [[ ! $REPLY =~ ^[Yy]$ ]]; then
                print_warning "Update k8s/base/secret.yaml with real secrets before applying."
            fi
        fi

        # Validate before applying
        if [ -f "${K8S_DIR}/validate.sh" ]; then
            print_info "Validating manifests..."
            if bash "${K8S_DIR}/validate.sh" "$OVERLAY"; then
                print_success "Manifests validated"
            else
                print_error "Manifest validation failed. Aborting."
                exit 1
            fi
        fi

        if command_exists kustomize; then
            kustomize build "$OVERLAY_DIR" | kubectl apply -f -
        else
            kubectl apply -k "$OVERLAY_DIR"
        fi

        local ns
        ns=$(grep "^namespace:" "${OVERLAY_DIR}/kustomization.yaml" | head -1 | awk '{print $2}')
        ns="${ns:-liberu-automation}"

        print_success "Kubernetes resources applied via Kustomize (${OVERLAY})"
        print_info "Check status with: kubectl get pods -n ${ns}"
    else
        # Fallback: apply raw YAML files from k8s/base/
        print_info "No Kustomize overlay found — applying base manifests from ${K8S_DIR}/base/"

        if [ ! -f ".env" ]; then
            cp .env.example .env
            print_warning "Copied .env.example -> .env. Please review it."
            read -rp "Press Enter to continue..." || true
        fi

        kubectl apply -f "${K8S_DIR}/base/" --recursive || {
            print_error "Failed to apply Kubernetes configurations"
            exit 1
        }

        print_success "Kubernetes resources applied"
        print_info "Check status with: kubectl get pods -n liberu-automation"
    fi
}

# ---------------------------------------------------------------------------
# Main menu
# ---------------------------------------------------------------------------
main() {
    # Handle --help flag
    for arg in "$@"; do
        case "$arg" in
            --help|-h) show_help ;;
        esac
    done

    clear
    print_header "LIBERU AUTOMATION - INSTALLER"

    echo "Select installation type:"
    echo ""
    echo "  1) Standalone (local / bare-metal)"
    echo "  2) Docker"
    echo "  3) Kubernetes"
    echo "  4) Exit"
    echo ""

    while true; do
        read -rp "Enter choice (1-4): " choice
        case $choice in
            1) install_standalone; break ;;
            2) install_docker;     break ;;
            3) install_kubernetes; break ;;
            4) print_info "Installation cancelled."; exit 0 ;;
            *) print_warning "Invalid choice. Please enter 1, 2, 3, or 4." ;;
        esac
    done
}

main "$@"
