# N8N Woo Webhook Integration

A WordPress plugin that automatically sends WooCommerce order and customer data to N8N webhooks in real-time. Built with a modern glassmorphism UI and Apple-style toggles.

![Version](https://img.shields.io/badge/version-1.0.1-blue.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)
![WooCommerce](https://img.shields.io/badge/WooCommerce-5.0%2B-purple.svg)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4.svg)

## Features

### Event Monitoring
- **Orders**: Created, Updated, Status Changed, Notes Added
- **Customers**: Created, Updated, Deleted
- **Products**: Support for all WooCommerce product types

### Flexible Webhook Configuration
- Single webhook for all events
- Individual webhooks per event type
- Custom status filtering
- Real-time data transmission

### Modern UI/UX
- Glassmorphism design with backdrop blur effects
- iOS-style toggle switches
- Responsive grid layout
- Real-time webhook testing
- Smooth animations and transitions

### HPOS Compatible
Fully compatible with WooCommerce High-Performance Order Storage (HPOS).

## Screenshots

### Modern Glassmorphism Interface
![Plugin Settings](assets/screenshots/screenshot-1.png)
*Main configuration page with webhook URL and event toggles*

### Event Management
![Event Toggles](assets/screenshots/screenshot-2.png)
*Apple-style toggles for Orders and Customers events with individual webhook fields*

### Order Status Filtering
![Status Filtering](assets/screenshots/screenshot-3.png)
*Select specific order statuses to monitor or leave empty for all*

## Installation

1. Download the plugin files
2. Upload to `/wp-content/plugins/n8nwoo/` directory
3. Activate through WordPress 'Plugins' menu
4. Go to Settings → N8N Webhook to configure

## Configuration

### Main Webhook
Set a default webhook URL that will receive all enabled events:
```
https://your-n8n-instance.com/webhook/your-webhook-id
```

### Event-Specific Webhooks
You can override the main webhook for specific events. Toggle an event ON and enter a custom webhook URL in the field that appears.

### Status Filtering
Select which order statuses should trigger webhooks. Leave empty to monitor all statuses.

## Webhook Data Structure

### Order Events
```json
{
  "event_type": "order_created",
  "timestamp": "2025-11-19 22:30:45",
  "order": {
    "id": 12345,
    "order_number": 12345,
    "status": "processing",
    "currency": "BRL",
    "date_created": "2025-11-19 22:30:45"
  },
  "customer": {
    "id": 1,
    "email": "customer@example.com",
    "first_name": "John",
    "last_name": "Doe",
    "phone": "+5511999999999"
  },
  "billing_address": {
    "address_1": "123 Main St",
    "city": "São Paulo",
    "state": "SP",
    "postcode": "01234-567",
    "country": "BR"
  },
  "shipping_address": { /* same structure */ },
  "items": [
    {
      "id": 1,
      "name": "Product Name",
      "product_id": 456,
      "quantity": 2,
      "total": "150.00",
      "sku": "PROD-123"
    }
  ],
  "totals": {
    "subtotal": "150.00",
    "shipping_total": "10.00",
    "total": "160.00"
  },
  "payment": {
    "method": "bacs",
    "method_title": "Bank Transfer"
  }
}
```

### Customer Events
```json
{
  "event_type": "customer_created",
  "timestamp": "2025-11-19 22:30:45",
  "customer": {
    "id": 1,
    "email": "customer@example.com",
    "username": "johndoe",
    "first_name": "John",
    "last_name": "Doe",
    "date_created": "2025-11-19 22:30:45"
  },
  "billing": { /* address data */ },
  "shipping": { /* address data */ },
  "stats": {
    "orders_count": 5,
    "total_spent": "500.00"
  }
}
```

## Testing

Use the built-in "Test Webhook" button to verify your N8N endpoint is working correctly. It sends sample data and displays the HTTP response status.

## N8N Workflow Example

Create a Webhook node in N8N and use the URL in the plugin settings. Here's a basic workflow structure:

1. **Webhook Trigger** - Receives the data
2. **Switch Node** - Route based on `event_type`
3. **Action Nodes** - Process orders, update CRM, send notifications, etc.

## Requirements

- WordPress 5.0 or higher
- WooCommerce 5.0 or higher
- PHP 7.4 or higher
- Active N8N instance with webhook access

## Frequently Asked Questions

**Q: Can I use different webhooks for different events?**  
A: Yes! Toggle any event ON and you'll see a field where you can enter a specific webhook URL for that event.

**Q: What happens if I don't set an individual webhook?**  
A: The plugin will use the main webhook URL for that event.

**Q: Does this work with custom order statuses?**  
A: Absolutely! The plugin automatically detects all order statuses, including those added by other plugins.

**Q: Is my data secure?**  
A: Data is sent over HTTPS using WordPress's built-in wp_remote_post() function. Make sure your N8N instance uses HTTPS.

## Changelog

### 1.0.1
- Added glassmorphism UI design
- Implemented Apple-style toggle switches
- Added customer event tracking
- Individual webhook support per event
- HPOS compatibility
- Real-time webhook testing

### 1.0.0
- Initial release

## Support

For issues, questions, or contributions, please visit the [GitHub repository](https://github.com/williamschonsdev/n8n-woocommerce).

## License

This plugin is free software released under the GPL v2 license.

## Author

Built by William Schons for seamless WooCommerce and N8N integration.
