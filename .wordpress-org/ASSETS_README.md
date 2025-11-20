# WordPress.org Assets

This directory contains all visual assets required for WordPress.org plugin listing.

## Files Structure

### Icons
- `icon-128x128.png` - Plugin icon (128x128px) - Required
- `icon-256x256.png` - Plugin icon HiDPI (256x256px) - Required for Retina displays

### Banners
- `banner-1544x500.png` - Main banner (1544x500px) - Displayed on plugin page
- `banner-772x250.png` - Low-res banner (772x250px) - Optional, fallback for slower connections

### Screenshots
- `1.jpg` - Main settings page with glassmorphism design
- `2.jpg` - Event monitoring cards with Apple-style toggles
- `3.jpg` - Individual webhook configuration per event
- `4.jpg` - Order status filtering interface
- `5.jpg` - Test buttons for each event with real-time feedback
- `6.jpg` - Custom admin menu with gradient icon

## Specifications

### Icon Requirements
- Format: PNG
- Sizes: 128x128 (required), 256x256 (recommended)
- Background: Can be transparent
- Content: Should be recognizable at small sizes

### Banner Requirements
- Format: PNG or JPG
- Sizes: 1544x500 (required), 772x250 (optional)
- Content: Should showcase plugin features
- Text: Should be readable on all backgrounds

### Screenshot Requirements
- Format: PNG or JPG
- Recommended size: 1280x800 or larger
- Aspect ratio: Maintain consistency across screenshots
- Quality: High quality, no compression artifacts

## Design Notes

### Color Scheme
- Primary Gradient: #667eea → #764ba2 (Purple gradient)
- Glassmorphism: rgba(255, 255, 255, 0.6) with backdrop-filter
- Accents: Green (#34c759) for active states

### Typography
- Main headings: System UI fonts
- Code/URLs: Monaco, Courier New (monospace)

### Icons Used
- 🛒 Cart - Order Created
- ✏️ Pencil - Order Updated  
- 🔃 Arrows - Status Changed
- 📝 Note - Note Added
- 👤 Person - Customer Created
- 👥 People - Customer Updated
- 🗑 Trash - Customer Deleted

## Upload Instructions

1. Commit all files in `.wordpress-org/` to your plugin's SVN repository
2. Place files in the `/assets/` directory of your SVN trunk
3. WordPress.org will automatically use these assets for your plugin listing

## Notes

- All images are optimized for web delivery
- Screenshots demonstrate actual plugin functionality
- Banner showcases key features with clear branding
- Icons are simple, recognizable, and follow WordPress design guidelines
