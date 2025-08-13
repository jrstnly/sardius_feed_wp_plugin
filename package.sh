#!/usr/bin/env bash

set -euo pipefail

# Determine project directory (directory containing this script)
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$PROJECT_DIR"

MAIN_FILE="sardius-feed-plugin.php"

if [[ ! -f "$MAIN_FILE" ]]; then
  echo "Error: $MAIN_FILE not found in $PROJECT_DIR" >&2
  exit 1
fi

# Extract version from plugin header (preferred)
VERSION="$(awk -F': ' '/^[[:space:]]*\*+[[:space:]]*Version:/ {print $2; exit}' "$MAIN_FILE" | tr -d '\r' || true)"

# Fallback: extract from SARDIUS_FEED_VERSION define
if [[ -z "${VERSION:-}" ]]; then
  VERSION="$(grep -E "define\(\s*'SARDIUS_FEED_VERSION'\s*,\s*'[^']+'\)" "$MAIN_FILE" | sed -E "s/.*'SARDIUS_FEED_VERSION'\s*,\s*'([^']+)'.*/\1/" | head -n1 || true)"
fi

if [[ -z "${VERSION:-}" ]]; then
  echo "Error: Could not determine plugin version from $MAIN_FILE" >&2
  exit 1
fi

PLUGIN_DIR_NAME="$(basename "$PROJECT_DIR")"
ZIP_NAME="sardius-feed-plugin-${VERSION}.zip"
ZIP_PATH="${PROJECT_DIR}/${ZIP_NAME}"

echo "Packaging version ${VERSION} of ${PLUGIN_DIR_NAME}..."

# Create a temporary staging directory
STAGING_ROOT="$(mktemp -d 2>/dev/null || mktemp -d -t sardius_pkg)"
STAGING_DIR="${STAGING_ROOT}/${PLUGIN_DIR_NAME}"
mkdir -p "$STAGING_DIR"

copy_if_exists() {
  local path="$1"
  if [[ -e "$path" ]]; then
    rsync -a --delete-excluded \
      --exclude ".DS_Store" \
      --exclude "__MACOSX" \
      --exclude ".git" \
      --exclude ".gitignore" \
      --exclude ".idea" \
      --exclude "*.zip" \
      "$path" "$STAGING_DIR/"
  fi
}

# Include only relevant plugin files
copy_if_exists "assets"
copy_if_exists "includes"
copy_if_exists "templates"
copy_if_exists "README.md"
copy_if_exists "$MAIN_FILE"

# Build the zip with the plugin folder at the root
(
  cd "$STAGING_ROOT"
  rm -f "$ZIP_PATH"
  zip -r -q "$ZIP_PATH" "$PLUGIN_DIR_NAME" \
    -x "**/.DS_Store" "**/__MACOSX/**" "**/.git/**" "**/.idea/**"
)

# Cleanup
rm -rf "$STAGING_ROOT"

echo "Created: $ZIP_PATH"


