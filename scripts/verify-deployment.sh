#!/bin/bash

################################################################################
# NKHOJ Post-Deployment Verification Script
#
# This script verifies that the deployment was successful
# Usage: bash verify-deployment.sh
################################################################################

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}Post-Deployment Verification${NC}"
echo -e "${BLUE}========================================${NC}\n"

PASS=0
FAIL=0

# Test function
test_pass() {
    echo -e "${GREEN}✅ PASS${NC}: $1"
    ((PASS++))
}

test_fail() {
    echo -e "${RED}❌ FAIL${NC}: $1"
    ((FAIL++))
}

test_warn() {
    echo -e "${YELLOW}⚠️  WARN${NC}: $1"
}

# 1. Git Status
echo -e "${YELLOW}Git Status:${NC}"
if git status | grep -q "nothing to commit"; then
    test_pass "Working tree is clean"
else
    test_fail "Working tree has uncommitted changes"
fi

CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
if [ "$CURRENT_BRANCH" = "master" ]; then
    test_pass "On master branch"
else
    test_fail "Not on master branch (current: $CURRENT_BRANCH)"
fi

LATEST_COMMIT=$(git log --oneline -1 | cut -d' ' -f1)
echo -e "  Latest commit: $LATEST_COMMIT\n"

# 2. PHP & Composer
echo -e "${YELLOW}PHP & Dependencies:${NC}"
if command -v php &> /dev/null; then
    PHP_VERSION=$(php -v | head -1)
    test_pass "PHP installed: $PHP_VERSION"
else
    test_fail "PHP not found"
fi

if [ -d "vendor" ] && [ -f "vendor/autoload.php" ]; then
    test_pass "Composer dependencies installed"
else
    test_fail "Composer dependencies not found"
fi

if [ -f "composer.lock" ]; then
    test_pass "composer.lock exists"
else
    test_warn "composer.lock not found"
fi
echo ""

# 3. Laravel Configuration
echo -e "${YELLOW}Laravel Configuration:${NC}"
if [ -f ".env" ]; then
    test_pass ".env file exists"
    if grep -q "APP_KEY=" .env; then
        test_pass "APP_KEY is configured"
    else
        test_fail "APP_KEY not configured"
    fi
else
    test_fail ".env file not found"
fi

if [ -f "config/app.php" ]; then
    test_pass "config/app.php exists"
else
    test_fail "config/app.php not found"
fi
echo ""

# 4. Database
echo -e "${YELLOW}Database:${NC}"
if php artisan migrate:status &> /dev/null; then
    test_pass "Database connection successful"
    MIGRATIONS=$(php artisan migrate:status 2>/dev/null | grep "^| 2" | wc -l)
    echo -e "  Migrations: $MIGRATIONS completed"
else
    test_fail "Database connection failed"
fi
echo ""

# 5. File Permissions
echo -e "${YELLOW}File Permissions:${NC}"
if [ -w "storage/" ]; then
    test_pass "storage/ is writable"
else
    test_fail "storage/ is not writable"
fi

if [ -w "bootstrap/cache/" ]; then
    test_pass "bootstrap/cache/ is writable"
else
    test_fail "bootstrap/cache/ is not writable"
fi

if [ -w "public/" ]; then
    test_pass "public/ is writable"
else
    test_warn "public/ is not writable (may be OK)"
fi
echo ""

# 6. Key Directories
echo -e "${YELLOW}Directory Structure:${NC}"
DIRS=("app" "bootstrap" "config" "database" "public" "resources" "routes" "storage")
for dir in "${DIRS[@]}"; do
    if [ -d "$dir" ]; then
        test_pass "$dir/ exists"
    else
        test_fail "$dir/ missing"
    fi
done
echo ""

# 7. Assets
echo -e "${YELLOW}Pre-built Assets:${NC}"
if [ -f "public/build/manifest.json" ]; then
    test_pass "Pre-built assets (public/build/manifest.json)"
    ASSETS=$(find public/build/assets -type f 2>/dev/null | wc -l)
    echo -e "  Asset files: $ASSETS"
else
    test_warn "Pre-built assets not found (may need npm build)"
fi

if [ -d "public/tinymce" ]; then
    test_pass "TinyMCE assets found"
else
    test_warn "TinyMCE assets not found"
fi
echo ""

# 8. Laravel Artisan
echo -e "${YELLOW}Laravel Artisan Commands:${NC}"
if php artisan --version &> /dev/null; then
    ARTISAN_VERSION=$(php artisan --version)
    test_pass "Artisan working: $ARTISAN_VERSION"
else
    test_fail "Artisan command failed"
fi

if php artisan config:cache &> /dev/null; then
    test_pass "Configuration can be cached"
else
    test_warn "Configuration caching failed"
fi

if php artisan route:cache &> /dev/null; then
    test_pass "Routes can be cached"
else
    test_warn "Route caching failed"
fi
echo ""

# 9. Critical Files
echo -e "${YELLOW}Critical Files:${NC}"
FILES=(
    "artisan"
    "composer.json"
    "config/app.php"
    "app/Http/Kernel.php"
    "app/Providers/AppServiceProvider.php"
)

for file in "${FILES[@]}"; do
    if [ -f "$file" ]; then
        test_pass "$file exists"
    else
        test_fail "$file missing"
    fi
done
echo ""

# 10. Story Observer (Media Deletion Fix)
echo -e "${YELLOW}Story Media System:${NC}"
if grep -q "StoryObserver" "app/Providers/AppServiceProvider.php"; then
    test_pass "StoryObserver registered in AppServiceProvider"
else
    test_warn "StoryObserver not found (media deletion may not work)"
fi

if [ -f "app/Domains/Blog/Observers/StoryObserver.php" ]; then
    test_pass "StoryObserver.php exists"
else
    test_warn "StoryObserver.php not found"
fi
echo ""

# 11. Documentation
echo -e "${YELLOW}Documentation:${NC}"
if [ -f ".claude/docs/DEPLOYMENT_GUIDE.md" ]; then
    test_pass "DEPLOYMENT_GUIDE.md exists"
else
    test_warn "DEPLOYMENT_GUIDE.md not found"
fi

if [ -f ".claude/docs/VARIENT_NEWSLETTER_SKILLS.md" ]; then
    test_pass "VARIENT_NEWSLETTER_SKILLS.md exists"
else
    test_warn "VARIENT_NEWSLETTER_SKILLS.md not found"
fi

if [ -f ".claude/docs/MAILWIZZ_API_INTEGRATION.md" ]; then
    test_pass "MAILWIZZ_API_INTEGRATION.md exists"
else
    test_warn "MAILWIZZ_API_INTEGRATION.md not found"
fi
echo ""

# Summary
echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}Verification Summary${NC}"
echo -e "${BLUE}========================================${NC}\n"

TOTAL=$((PASS + FAIL))
echo -e "Tests Passed: ${GREEN}${PASS}${NC}"
echo -e "Tests Failed: ${RED}${FAIL}${NC}"
echo -e "Total Tests:  ${TOTAL}\n"

if [ $FAIL -eq 0 ]; then
    echo -e "${GREEN}✅ All critical tests passed!${NC}"
    echo -e "${GREEN}Deployment is ready for production.${NC}\n"
    exit 0
else
    echo -e "${RED}❌ Some tests failed. Please review above.${NC}\n"
    exit 1
fi
