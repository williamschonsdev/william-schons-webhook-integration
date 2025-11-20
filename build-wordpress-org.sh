#!/bin/bash

# WordPress.org Plugin Package Builder
# This script creates a clean ZIP package ready for WordPress.org submission

echo "🚀 Building WordPress.org plugin package..."

# Variables
PLUGIN_SLUG="william-schons-webhook-integration"
VERSION="1.0.1"
BUILD_DIR="build-wordpress-org"

# Clean previous build
echo "🧹 Cleaning previous builds..."
rm -rf "$BUILD_DIR"
rm -f "$PLUGIN_SLUG.zip"

# Create build directory
echo "📁 Creating build directory..."
mkdir -p "$BUILD_DIR/$PLUGIN_SLUG"

# Copy plugin files
echo "📦 Copying plugin files..."
cp n8nwoo.php "$BUILD_DIR/$PLUGIN_SLUG/"
cp readme.txt "$BUILD_DIR/$PLUGIN_SLUG/"
cp LICENSE "$BUILD_DIR/$PLUGIN_SLUG/"

# Copy languages
echo "🌍 Copying translations..."
mkdir -p "$BUILD_DIR/$PLUGIN_SLUG/languages"
cp languages/*.pot "$BUILD_DIR/$PLUGIN_SLUG/languages/" 2>/dev/null || true
cp languages/*.po "$BUILD_DIR/$PLUGIN_SLUG/languages/" 2>/dev/null || true
cp languages/*.mo "$BUILD_DIR/$PLUGIN_SLUG/languages/" 2>/dev/null || true

# Create assets directory (for reference, not included in ZIP)
echo "🎨 Organizing assets..."
mkdir -p "$BUILD_DIR/assets"
cp .wordpress-org/icon-128x128.png "$BUILD_DIR/assets/"
cp .wordpress-org/icon-256x256.png "$BUILD_DIR/assets/"
cp .wordpress-org/banner-1544x500.png "$BUILD_DIR/assets/"

# Rename screenshots
for i in {1..6}; do
    if [ -f ".wordpress-org/$i.jpg" ]; then
        cp ".wordpress-org/$i.jpg" "$BUILD_DIR/assets/screenshot-$i.jpg"
    fi
done

# Create ZIP
echo "🗜️  Creating ZIP package..."
cd "$BUILD_DIR"
zip -r "../$PLUGIN_SLUG.zip" "$PLUGIN_SLUG"
cd ..

# Show package info
echo ""
echo "✅ Package created successfully!"
echo ""
echo "📦 Package: $PLUGIN_SLUG.zip"
echo "📊 Size: $(du -h "$PLUGIN_SLUG.zip" | cut -f1)"
echo ""
echo "📁 Contents:"
unzip -l "$PLUGIN_SLUG.zip" | tail -n +4 | head -n -2
echo ""
echo "🎯 Next steps:"
echo "   1. Test the plugin by uploading $PLUGIN_SLUG.zip to a WordPress site"
echo "   2. Verify all features work correctly"
echo "   3. Submit to WordPress.org at https://wordpress.org/plugins/developers/add/"
echo ""
echo "📂 Assets for WordPress.org SVN (separate upload):"
echo "   $BUILD_DIR/assets/"
echo ""
echo "   Upload these to your SVN 'assets' directory:"
ls -lh "$BUILD_DIR/assets/"
echo ""
echo "🎉 Ready for WordPress.org submission!"
