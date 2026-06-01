#!/bin/bash
# Validate Kubernetes manifests before applying
set -e

GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

ENVIRONMENT="${1:-production}"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
OVERLAY_DIR="${SCRIPT_DIR}/overlays/${ENVIRONMENT}"

print_info()  { echo -e "${GREEN}[INFO]${NC} $1"; }
print_warn()  { echo -e "${YELLOW}[WARN]${NC} $1"; }
print_error() { echo -e "${RED}[ERROR]${NC} $1"; }

print_info "Validating manifests for environment: $ENVIRONMENT"

if [ ! -d "$OVERLAY_DIR" ]; then
    print_error "Overlay directory not found: $OVERLAY_DIR"
    exit 1
fi

# Check kubectl
if ! command -v kubectl &>/dev/null; then
    print_error "kubectl is not installed."
    exit 1
fi

# Build and dry-run
print_info "Running dry-run validation..."
if command -v kustomize &>/dev/null; then
    kustomize build "$OVERLAY_DIR" | kubectl apply --dry-run=client -f - && print_info "Validation passed." || {
        print_error "Validation failed."
        exit 1
    }
else
    kubectl apply -k "$OVERLAY_DIR" --dry-run=client && print_info "Validation passed." || {
        print_error "Validation failed."
        exit 1
    }
fi
