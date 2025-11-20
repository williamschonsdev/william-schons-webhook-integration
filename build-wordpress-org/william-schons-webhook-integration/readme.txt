=== William Schons Webhook Integration ===
Contributors: williamschons
Tags: webhook, woocommerce, automation, integration, api
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.2
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

Send WooCommerce order and customer data to custom webhooks automatically with a modern glassmorphism interface. Perfect for automation workflows.

== Description ==

**IMPORTANT DISCLAIMER:** This plugin is NOT an official WooCommerce plugin and is NOT an official N8N plugin. It is an independent third-party integration tool developed by William Schons to facilitate webhook integrations.

A powerful WordPress plugin that bridges WooCommerce with automation platforms like N8N, Zapier, Make.com, and others. Monitor orders, customers, and products in real-time and send data to custom webhooks for automated workflows.

= Key Features =

* **Real-time Event Monitoring** - Capture orders, customers, and status changes instantly
* **Flexible Webhook Configuration** - Use one webhook for all events or individual webhooks per event
* **Modern Glassmorphism UI** - Beautiful interface with Apple-style toggle switches
* **Custom Status Filtering** - Choose which order statuses to track
* **Built-in Testing** - Test your webhook connection right from the settings page
* **HPOS Compatible** - Fully supports WooCommerce High-Performance Order Storage

= Monitored Events =

**Orders:**
* Order Created
* Order Updated
* Order Status Changed
* Order Note Added

**Customers:**
* Customer Created
* Customer Updated
* Customer Deleted

= Data Sent =

The plugin sends comprehensive JSON data including:
* Complete order details (items, totals, payment method)
* Customer information (billing, shipping addresses)
* Product details (SKU, variations, metadata)
* Order notes and history
* Custom fields and metadata

= Use Cases =

* Sync orders to external CRM systems
* Send notifications to Slack, Discord, or email
* Update inventory in external systems
* Generate custom reports and analytics
* Trigger marketing automations
* Create backup systems
* Build custom dashboards

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/n8nwoo/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings → N8N Webhook
4. Enter your N8N webhook URL
5. Toggle ON the events you want to monitor
6. Click Save Configuration

= Quick Start =

1. Create a Webhook node in your N8N workflow
2. Copy the webhook URL
3. Paste it in the plugin settings
4. Toggle ON desired events
5. Test using the Test Webhook button

== Frequently Asked Questions ==

= Do I need an N8N account? =

Yes, you need access to an N8N instance (cloud or self-hosted) to receive the webhooks.

= Can I use different webhooks for different events? =

Absolutely! Toggle any event ON and you'll see a field where you can enter a specific webhook URL for that event.

= Does this work with custom order statuses? =

Yes! The plugin automatically detects all order statuses, including those added by other plugins or themes.

= Is my data secure? =

Yes. Data is sent over HTTPS using WordPress's secure wp_remote_post() function. Always use HTTPS for your N8N webhooks.

= Does it work with HPOS? =

Yes, the plugin is fully compatible with WooCommerce's High-Performance Order Storage system.

= How do I test if it's working? =

Use the built-in "Test Webhook" button on the settings page. It sends sample data and shows the response status.

== Screenshots ==

1. Main settings page with glassmorphism design - Configure webhook URL and view system information
2. Event monitoring cards with Apple-style toggles - Enable/disable WooCommerce events individually
3. Individual webhook configuration per event - Set specific webhook URLs for each event type
4. Order status filtering - Choose which order statuses should trigger webhooks
5. Test buttons for each event - Send test data and verify webhook connections in real-time
6. Custom admin menu with gradient icon - Easy access from WordPress sidebar

== Changelog ==

= 1.0.2 =
* Fixed: All WordPress.org coding standards compliance
* Fixed: Added proper escaping for all outputs
* Fixed: Added translators comments for placeholders
* Fixed: Removed debug error_log() calls
* Fixed: Added wp_unslash() before sanitization
* Fixed: Changed text domain to william-schons-webhook-integration

= 1.0.1 =
* Added glassmorphism UI design
* Implemented Apple-style toggle switches
* Added customer event tracking
* Individual webhook support per event
* HPOS compatibility declaration
* Real-time webhook testing
* Improved error logging

= 1.0.0 =
* Initial release
* Order event tracking
* Status filtering
* Main webhook configuration
* WooCommerce integration

== Upgrade Notice ==

= 1.0.2 =
Important update: Full WordPress.org coding standards compliance. Recommended for all users.

= 1.0.1 =
Major UI overhaul with glassmorphism design, customer events, and individual webhook support.

== Additional Information ==

= Requirements =
* WordPress 5.0+
* WooCommerce 5.0+
* PHP 7.4+
* N8N instance (cloud or self-hosted)

= Support =
For issues or feature requests, visit [GitHub repository](https://github.com/williamschonsdev/n8n-woocommerce)

= Documentation =
Full documentation available at [GitHub](https://github.com/williamschonsdev/n8n-woocommerce)
