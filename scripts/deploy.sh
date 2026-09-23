#!/bin/bash

################################################################################
# NKHOJ Deployment Script for dewanlabung.com.np
#
# This script automates the deployment of NKHOJ to production
# Usage: bash deploy.sh
################################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
WEB_ROOT="${WEB_ROOT:-.}"
ENVIRONMENT="${ENVIRONMENT:-production}"
BRANCH="${BRANCH:-master}"

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}NKHOJ Deployment Script${NC}"
echo -e "${BLUE}Environment: ${ENVIRONMENT}${NC}"
echo -e "${BLUE}Branch: ${BRANCH}${NC}"
echo -e "${BLUE}========================================${NC}\n"

# Step 1: Verify Git Repository
echo -e "${YELLOW}[1/12]${NC} Verifying git repository..."
if [ ! -d ".git" ]; then
    echo -e "${RED}❌ Not in a git repository!${NC}"
    exit 1
fi
echo -e "${GREEN}✅ Git repository found${NC}\n"

# Step 2: Fetch Latest Code
echo -e "${YELLOW}[2/12]${NC} Fetching latest code from origin..."
git fetch origin ${BRANCH}
echo -e "${GREEN}✅ Fetched from origin${NC}\n"

# Step 3: Checkout Branch
echo -e "${YELLOW}[3/12]${NC} Checking out ${BRANCH} branch..."
git checkout ${BRANCH}
echo -e "${GREEN}✅ Checked out ${BRANCH}${NC}\n"

# Step 4: Pull Latest Changes
echo -e "${YELLOW}[4/12]${NC} Pulling latest changes..."
git pull origin ${BRANCH}
LATEST_COMMIT=$(git log --oneline -1)
echo -e "${GREEN}✅ Latest commit: ${LATEST_COMMIT}${NC}\n"

# Step 5: Verify Working Tree is Clean
echo -e "${YELLOW}[5/12]${NC} Verifying working tree is clean..."
if ! git diff-index --quiet HEAD --; then
    echo -e "${RED}❌ Working tree is dirty!${NC}"
    echo -e "${YELLOW}Uncommitted changes:${NC}"
    git status --short
    exit 1
fi
echo -e "${GREEN}✅ Working tree is clean${NC}\n"

# Step 6: Install Composer Dependencies
echo -e "${YELLOW}[6/12]${NC} Installing composer dependencies..."
if command -v composer &> /dev/null; then
    composer install --no-dev --optimize-autoloader
    echo -e "${GREEN}✅ Composer dependencies installed${NC}\n"
else
    echo -e "${RED}❌ Composer not found!${NC}"
    exit 1
fi

# Step 7: Clear Laravel Caches
echo -e "${YELLOW}[7/12]${NC} Clearing Laravel caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo -e "${GREEN}✅ Caches cleared${NC}\n"

# Step 8: Run Database Migrations
echo -e "${YELLOW}[8/12]${NC} Running database migrations..."
php artisan migrate --force
echo -e "${GREEN}✅ Migrations completed${NC}\n"

# Step 9: Set File Permissions
echo -e "${YELLOW}[9/12]${NC} Setting file permissions..."
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
echo -e "${GREEN}✅ File permissions set${NC}\n"

# Step 10: Verify Assets
echo -e "${YELLOW}[10/12]${NC} Verifying pre-built assets..."
if [ -f "public/build/manifest.json" ]; then
    echo -e "${GREEN}✅ Pre-built assets found${NC}\n"
else
    echo -e "${YELLOW}⚠️  Pre-built assets not found. This may be expected.${NC}\n"
fi

# Step 11: Cache Configuration
echo -e "${YELLOW}[11/12]${NC} Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo -e "${GREEN}✅ Configuration cached${NC}\n"

# Step 12: Verification Tests
echo -e "${YELLOW}[12/12]${NC} Running verification tests..."

# Test Laravel artisan
if php artisan tinker --version &> /dev/null; then
    echo -e "${GREEN}✅ Laravel artisan is working${NC}"
else
    echo -e "${YELLOW}⚠️  Could not verify Laravel${NC}"
fi

# Test storage directory writable
if touch storage/.write-test && rm storage/.write-test; then
    echo -e "${GREEN}✅ Storage directory is writable${NC}"
else
    echo -e "${RED}❌ Storage directory is not writable${NC}"
    exit 1
fi

# Test database connection
if php artisan migrate:status &> /dev/null; then
    echo -e "${GREEN}✅ Database connection is working${NC}"
else
    echo -e "${YELLOW}⚠️  Could not verify database connection${NC}"
fi

echo -e "\n${GREEN}========================================${NC}"
echo -e "${GREEN}✅ Deployment Successful!${NC}"
echo -e "${GREEN}========================================${NC}\n"

echo -e "${BLUE}Summary:${NC}"
echo -e "  Branch: ${BRANCH}"
echo -e "  Latest Commit: ${LATEST_COMMIT}"
echo -e "  Environment: ${ENVIRONMENT}"
echo -e "  PHP Version: $(php -v | head -1)"
echo -e "\n${YELLOW}Next Steps:${NC}"
echo -e "  1. Verify website: curl -I https://dewanlabung.com.np"
echo -e "  2. Check admin panel: https://dewanlabung.com.np/admin"
echo -e "  3. Test newsletter: /admin/newsletter"
echo -e "  4. Monitor logs: tail -f storage/logs/laravel.log"
echo -e "\n${GREEN}Deployment completed at $(date)${NC}\n"
