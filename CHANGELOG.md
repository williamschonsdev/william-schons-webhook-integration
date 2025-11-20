# Changelog

All notable changes to the William Schons Webhook Integration plugin.

## [1.0.2] - 2025-11-20

### Fixed
- WordPress.org coding standards compliance
- Added proper escaping for all outputs
- Added translators comments for placeholders
- Removed debug error_log() calls
- Added wp_unslash() before sanitization
- Text domain changed to n8n-woo-webhook-integration

### Changed
- Added disclaimer: NOT official WooCommerce or N8N plugin
- Updated plugin description to clarify independent third-party status
- Cleaned up unnecessary documentation files

## [1.0.1] - 2025-11-19

### Added
- Modern glassmorphism design with backdrop blur effects
- iOS-style toggle switches for event management
- Customer event tracking (created, updated, deleted)
- Individual webhook URLs for each event type
- Dynamic webhook field display
- Real-time webhook testing functionality
- Organized events by category (Orders/Customers)
- HPOS (High-Performance Order Storage) compatibility declaration
- Improved visual feedback and animations

### Changed
- Redesigned settings page with card-based layout
- Enhanced toggle interaction without visible checkboxes
- Improved error logging for webhook failures
- Better spacing and typography

### Fixed
- Checkbox visibility issue in event cards
- Toggle state synchronization

## [1.0.0] - 2025-11-19

### Added
- Initial plugin release
- Order event tracking (created, updated, status changed, note added)
- Status filtering for order events
- Main webhook URL configuration
- JSON data transmission to N8N
- WooCommerce integration
- Admin settings page
