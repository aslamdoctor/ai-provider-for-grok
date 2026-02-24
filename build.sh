#!/bin/bash
#
# Build a distributable zip for WordPress.org
# Usage: bash build.sh

PLUGIN_SLUG="ai-provider-for-grok"
PLUGIN_DIR="$(cd "$(dirname "$0")" && pwd)"
BUILD_DIR="$(mktemp -d)"
DEST="$BUILD_DIR/$PLUGIN_SLUG"

echo "Building $PLUGIN_SLUG.zip ..."

# Copy everything into a temp directory
mkdir -p "$DEST"
rsync -a "$PLUGIN_DIR/" "$DEST/" --exclude='.git'

# Remove files listed in .distignore
if [ -f "$DEST/.distignore" ]; then
  while IFS= read -r pattern || [ -n "$pattern" ]; do
    # Skip empty lines and comments
    pattern="$(echo "$pattern" | sed 's/^[[:space:]]*//;s/[[:space:]]*$//')"
    [ -z "$pattern" ] && continue
    [[ "$pattern" == \#* ]] && continue

    # Remove matching files/dirs from the build
    cd "$DEST" && rm -rf $pattern 2>/dev/null
  done < "$DEST/.distignore"
fi

# Create the zip
cd "$BUILD_DIR"
zip -rq "$PLUGIN_DIR/$PLUGIN_SLUG.zip" "$PLUGIN_SLUG"

# Cleanup
rm -rf "$BUILD_DIR"

echo "Done: $PLUGIN_SLUG.zip"
