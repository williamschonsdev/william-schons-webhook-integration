# Installation Guide

## Quick Start

### Step 1: Download
Download the latest release from the [releases page](https://github.com/williamschonsdev/n8n-woocommerce/releases) or clone the repository:

```bash
git clone https://github.com/williamschonsdev/n8n-woocommerce.git
```

### Step 2: Upload to WordPress
1. Upload the `n8nwoo` folder to `/wp-content/plugins/`
2. Or upload the ZIP file through WordPress admin (Plugins → Add New → Upload Plugin)

### Step 3: Activate
Go to your WordPress admin panel:
- Navigate to **Plugins**
- Find "N8N WooCommerce Webhook"
- Click **Activate**

### Step 4: Configure
1. Go to **Settings → N8N Webhook**
2. Enter your N8N webhook URL
3. Toggle ON the events you want to monitor
4. (Optional) Add specific webhook URLs for individual events
5. (Optional) Select which order statuses to track
6. Click **Save Configuration**

### Step 5: Test
Click the **Test Webhook** button to verify your N8N endpoint is receiving data correctly.

## N8N Setup

### Create Webhook in N8N

1. Open your N8N workflow
2. Add a **Webhook** node
3. Set **HTTP Method** to `POST`
4. Copy the webhook URL
5. Paste it into the plugin settings

### Example N8N Workflow

```
Webhook → Switch (by event_type) → Actions
```

**Switch Node Routes:**
- `order_created` → Send to CRM
- `order_status_changed` → Send email notification
- `customer_created` → Add to mailing list
- `order_note_added` → Log to database

## Troubleshooting

### Webhook not receiving data?
1. Check if the webhook URL is correct
2. Use the Test Webhook button
3. Verify N8N webhook is active
4. Check WordPress error logs

### Events not triggering?
1. Make sure the event toggle is ON
2. Verify WooCommerce is properly installed
3. Check if HPOS is enabled (plugin is compatible)
4. Review order status filters

### Individual webhooks not working?
1. Ensure the event is toggled ON
2. Enter a valid webhook URL in the event field
3. Leave blank to use the main webhook
4. Test with the main webhook first

## Advanced Configuration

### Using Multiple N8N Instances

You can route different events to different N8N instances:

1. Set main webhook to Instance A
2. Toggle ON specific events
3. Enter Instance B webhook URL for those events
4. Events without individual URLs → Instance A
5. Events with individual URLs → Instance B

### Custom Status Monitoring

To monitor only specific order statuses:

1. Scroll to "Order Statuses to Monitor"
2. Check the statuses you want to track
3. Leave all unchecked to monitor ALL statuses
4. Save configuration

## Support

Need help? Check:
- [GitHub Issues](https://github.com/williamschonsdev/n8n-woocommerce/issues)
- [Documentation](https://github.com/williamschonsdev/n8n-woocommerce)
- Plugin Settings page (has helpful info)
