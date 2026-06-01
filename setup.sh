#!/bin/bash
# Setup script for the Liberu Automation project.
#
# Provides installation options: Standalone, Docker, or Kubernetes.
# Handles composer/npm installs with fallback logic and error checking.

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
    if ! command_exists php; then
        print_error "PHP is required. Install PHP first."
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

    if [ -d "node_modules" ]; then
        print_info "node_modules/ already exists."
        read -rp "Reinstall npm dependencies? (y/n) " -n 1
        echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            print_success "Skipping npm install"
            return 0
        fi
    fi

    if ! command_exists npm; then
        print_error "npm is not installed. Install Node.js from https://nodejs.org/"
        return 1
    fi

    npm install
    print_success "npm dependencies installed"
}

build_frontend_assets() {
    print_header "NPM BUILD"

    if ! command_exists npm; then
        print_error "npm is not installed."
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

    # .env setup
    read -rp "Copy .env.example to .env? (y/n) " -n 1; echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        cp .env.example .env
        print_success "Copied .env.example -> .env"
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

    print_header "MIGRATE:FRESH"
    read -rp "Run migrate:fresh (destroys all data)? (y/n) " -n 1; echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        php artisan migrate:fresh || { print_error "Migration failed"; exit 1; }
        print_success "Database migrated"
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

    print_header "PHPUNIT TESTS"
    if [ -f "vendor/bin/phpunit" ]; then
        ./vendor/bin/phpunit || print_warning "Some tests failed — review the output above"
    else
        print_warning "phpunit not found in vendor/bin — skipping tests"
    fi

    print_header "OPTIMIZE:CLEAR"
    php artisan optimize:clear
    php artisan route:clear

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
        read -rp "Press Enter to continue..."
    fi

    if command_exists docker-compose; then
        docker-compose up -d --build
    else
        docker compose up -d --build
    fi

    print_success "Docker containers started — app available at http://localhost:8000"
}

# ---------------------------------------------------------------------------
# Kubernetes
# ---------------------------------------------------------------------------
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

    # Determine whether to use kustomize or raw manifests
    OVERLAY_DIR="${K8S_DIR}/overlays/production"
    if [ -d "$OVERLAY_DIR" ] && [ -f "${OVERLAY_DIR}/kustomization.yaml" ]; then
        print_info "Kustomize overlays detected — using overlay: production"

        if [ ! -f ".env" ]; then
            cp .env.example .env
            print_warning "Copied .env.example -> .env. Please review it."
            read -rp "Press Enter to continue..."
        fi

        # Validate before applying
        if [ -f "${K8S_DIR}/validate.sh" ]; then
            print_info "Validating manifests..."
            if bash "${K8S_DIR}/validate.sh" production; then
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

        print_success "Kubernetes resources applied via Kustomize"
    else
        # Fallback: apply raw YAML files from k8s/ root
        print_info "No Kustomize overlays found — applying raw manifests from ${K8S_DIR}/"

        if [ ! -f ".env" ]; then
            cp .env.example .env
            print_warning "Copied .env.example -> .env. Please review it."
            read -rp "Press Enter to continue..."
        fi

        kubectl apply -f "${K8S_DIR}/" --recursive || {
            print_error "Failed to apply Kubernetes configurations"
            exit 1
        }

        print_success "Kubernetes resources applied"
    fi

    print_info "Check status with: kubectl get pods -n liberu-automation"
}

# ---------------------------------------------------------------------------
# Main menu
# ---------------------------------------------------------------------------
main() {
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

main
