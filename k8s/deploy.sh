#!/bin/bash
# Kubernetes Deployment Script for Liberu Automation
set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

NAMESPACE="${NAMESPACE:-liberu-automation}"
ENVIRONMENT="${ENVIRONMENT:-production}"
DOMAIN="${DOMAIN:-automation.example.com}"
APP_KEY="${APP_KEY:-}"
DB_PASSWORD="${DB_PASSWORD:-}"

print_info()  { echo -e "${GREEN}[INFO]${NC} $1"; }
print_warn()  { echo -e "${YELLOW}[WARN]${NC} $1"; }
print_error() { echo -e "${RED}[ERROR]${NC} $1"; }

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

print_info "=== Liberu Automation Kubernetes Deployment ==="
echo ""

# Prerequisites
print_info "Checking prerequisites..."
if ! command -v kubectl &>/dev/null; then
    print_error "kubectl is not installed. Please install it first."
    exit 1
fi

if ! command -v kustomize &>/dev/null; then
    print_warn "kustomize not found — falling back to: kubectl apply -k"
    KUSTOMIZE_CMD="kubectl apply -k"
else
    KUSTOMIZE_CMD="kustomize build"
fi

if [ -z "$APP_KEY" ]; then
    print_error "APP_KEY is required. Generate one with: php artisan key:generate --show"
    exit 1
fi

if [ -z "$DB_PASSWORD" ]; then
    print_error "DB_PASSWORD is required."
    exit 1
fi

# Validate manifests first
if [ -f "${SCRIPT_DIR}/validate.sh" ]; then
    print_info "Validating manifests..."
    bash "${SCRIPT_DIR}/validate.sh" "$ENVIRONMENT" || {
        print_error "Manifest validation failed. Aborting deployment."
        exit 1
    }
fi

# Create namespace
print_info "Creating namespace: $NAMESPACE"
kubectl create namespace "$NAMESPACE" --dry-run=client -o yaml | kubectl apply -f -

# Apply secrets
print_info "Applying secrets..."
kubectl create secret generic liberu-automation-secret \
    --from-literal=APP_KEY="$APP_KEY" \
    --from-literal=DB_USERNAME="liberu" \
    --from-literal=DB_PASSWORD="$DB_PASSWORD" \
    --from-literal=REDIS_PASSWORD="" \
    --namespace="$NAMESPACE" \
    --dry-run=client -o yaml | kubectl apply -f -

# Patch ingress domain
OVERLAY_DIR="${SCRIPT_DIR}/overlays/${ENVIRONMENT}"
print_info "Configuring ingress for domain: $DOMAIN"
cat > "${OVERLAY_DIR}/ingress-patch.yaml" <<EOF
apiVersion: networking.k8s.io/v1
kind: Ingress
metadata:
  name: liberu-automation
spec:
  tls:
    - hosts:
        - $DOMAIN
      secretName: liberu-automation-tls
  rules:
    - host: $DOMAIN
      http:
        paths:
          - path: /
            pathType: Prefix
            backend:
              service:
                name: liberu-automation
                port:
                  number: 80
EOF

# Deploy
print_info "Deploying environment: $ENVIRONMENT..."
if [ "$KUSTOMIZE_CMD" = "kubectl apply -k" ]; then
    kubectl apply -k "${OVERLAY_DIR}"
else
    kustomize build "${OVERLAY_DIR}" | kubectl apply -f -
fi

# Wait for rollout
print_info "Waiting for rollout to complete..."
kubectl rollout status deployment/liberu-automation -n "$NAMESPACE" --timeout=300s || true

# Run migrations
print_info "Running migrations..."
POD=$(kubectl get pods -n "$NAMESPACE" -l app=liberu-automation,component=application -o jsonpath='{.items[0].metadata.name}' 2>/dev/null || true)
if [ -n "$POD" ]; then
    kubectl exec -n "$NAMESPACE" "$POD" -c php-fpm -- php artisan migrate --force
    print_info "Migrations completed."
else
    print_warn "No pod found — please run migrations manually: kubectl exec -n $NAMESPACE <pod> -- php artisan migrate --force"
fi

echo ""
print_info "Deployment complete!"
echo ""
echo "  Status:  kubectl get pods -n $NAMESPACE"
echo "  Logs:    kubectl logs -n $NAMESPACE -l app=liberu-automation -c php-fpm"
echo "  URL:     https://$DOMAIN"
