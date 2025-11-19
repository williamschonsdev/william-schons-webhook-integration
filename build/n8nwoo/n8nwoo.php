<?php
/**
 * Plugin Name: N8N Woo Webhook Integration
 * Plugin URI: https://github.com/williamschonsdev/n8n-woocommerce
 * Description: Envia dados completos de pedidos do WooCommerce para webhook do N8N
 * Version: 1.0.1
 * Author: William Schons
 * Author URI: https://williamschons.com.br
 * Text Domain: n8nwoo
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class N8NWoo {
    
    private static $instance = null;
    private $webhook_url = '';
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->webhook_url = get_option('n8nwoo_webhook_url', '');
        
        // Declara compatibilidade com HPOS
        add_action('before_woocommerce_init', array($this, 'declare_hpos_compatibility'));
        
        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        
        // WooCommerce hooks - Orders
        add_action('woocommerce_new_order', array($this, 'on_order_created'), 10, 1);
        add_action('woocommerce_update_order', array($this, 'on_order_updated'), 10, 1);
        add_action('woocommerce_order_status_changed', array($this, 'on_order_status_changed'), 10, 4);
        add_action('woocommerce_order_note_added', array($this, 'on_order_note_added'), 10, 2);
        
        // WooCommerce hooks - Customers
        add_action('woocommerce_created_customer', array($this, 'on_customer_created'), 10, 1);
        add_action('woocommerce_update_customer', array($this, 'on_customer_updated'), 10, 1);
        add_action('profile_update', array($this, 'on_customer_profile_updated'), 10, 1);
        
        // AJAX para teste de webhook
        add_action('wp_ajax_n8nwoo_test_webhook', array($this, 'ajax_test_webhook'));
    }
    
    /**
     * Declara compatibilidade com HPOS (High-Performance Order Storage)
     */
    public function declare_hpos_compatibility() {
        if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
        }
    }
    
    public function add_admin_menu() {
        add_options_page(
            'N8N Webhook Settings',
            'N8N Webhook',
            'manage_options',
            'n8nwoo-settings',
            array($this, 'settings_page')
        );
    }
    
    public function register_settings() {
        register_setting('n8nwoo_settings', 'n8nwoo_webhook_url', array(
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default' => ''
        ));
        
        register_setting('n8nwoo_settings', 'n8nwoo_enabled_events', array(
            'type' => 'array',
            'sanitize_callback' => array($this, 'sanitize_enabled_events'),
            'default' => array('order_created', 'order_updated', 'order_status_changed', 'order_note_added')
        ));
        
        register_setting('n8nwoo_settings', 'n8nwoo_enabled_statuses', array(
            'type' => 'array',
            'sanitize_callback' => array($this, 'sanitize_enabled_statuses'),
            'default' => array()
        ));
        
        register_setting('n8nwoo_settings', 'n8nwoo_individual_webhooks', array(
            'type' => 'array',
            'sanitize_callback' => array($this, 'sanitize_individual_webhooks'),
            'default' => array()
        ));
    }
    
    public function sanitize_enabled_events($input) {
        if (!is_array($input)) {
            return array();
        }
        return array_map('sanitize_text_field', $input);
    }
    
    public function sanitize_enabled_statuses($input) {
        if (!is_array($input)) {
            return array();
        }
        return array_map('sanitize_text_field', $input);
    }
    
    public function sanitize_individual_webhooks($input) {
        if (!is_array($input)) {
            return array();
        }
        $sanitized = array();
        foreach ($input as $key => $url) {
            $sanitized[sanitize_text_field($key)] = esc_url_raw($url);
        }
        return $sanitized;
    }
    
    public function settings_page() {
        $webhook_url = get_option('n8nwoo_webhook_url', '');
        $enabled_events = get_option('n8nwoo_enabled_events', array('order_created', 'order_updated', 'order_status_changed', 'order_note_added'));
        $enabled_statuses = get_option('n8nwoo_enabled_statuses', array());
        $individual_webhooks = get_option('n8nwoo_individual_webhooks', array());
        
        // Pega todos os status de pedidos do WooCommerce (incluindo personalizados)
        $order_statuses = wc_get_order_statuses();
        
        // Eventos disponíveis
        $available_events = array(
            'order_created' => array(
                'label' => 'Pedido Criado',
                'description' => 'Dispara quando um novo pedido é criado',
                'icon' => '🛒',
                'category' => 'Pedidos'
            ),
            'order_updated' => array(
                'label' => 'Pedido Atualizado',
                'description' => 'Dispara quando qualquer dado do pedido é modificado',
                'icon' => '✏️',
                'category' => 'Pedidos'
            ),
            'order_status_changed' => array(
                'label' => 'Status Alterado',
                'description' => 'Dispara quando o status do pedido muda',
                'icon' => '🔄',
                'category' => 'Pedidos'
            ),
            'order_note_added' => array(
                'label' => 'Nota Adicionada',
                'description' => 'Dispara quando uma nota é adicionada ao pedido',
                'icon' => '📝',
                'category' => 'Pedidos'
            ),
            'customer_created' => array(
                'label' => 'Cliente Criado',
                'description' => 'Dispara quando um novo cliente é cadastrado',
                'icon' => '👤',
                'category' => 'Clientes'
            ),
            'customer_updated' => array(
                'label' => 'Cliente Atualizado',
                'description' => 'Dispara quando dados do cliente são modificados',
                'icon' => '👥',
                'category' => 'Clientes'
            ),
            'customer_deleted' => array(
                'label' => 'Cliente Deletado',
                'description' => 'Dispara quando um cliente é removido',
                'icon' => '🗑️',
                'category' => 'Clientes'
            )
        );
        ?>
        <style>
            .n8nwoo-wrapper {
                max-width: 1400px;
                margin: 20px 0;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            }
            .n8nwoo-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 40px;
                border-radius: 20px;
                margin-bottom: 30px;
                box-shadow: 0 20px 60px rgba(102, 126, 234, 0.3);
                position: relative;
                overflow: hidden;
            }
            .n8nwoo-header::before {
                content: '';
                position: absolute;
                top: 0;
                right: 0;
                width: 300px;
                height: 300px;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 50%;
                transform: translate(30%, -30%);
            }
            .n8nwoo-header h1 {
                margin: 0 0 10px 0;
                color: white;
                font-size: 32px;
                font-weight: 700;
                position: relative;
                z-index: 1;
            }
            .n8nwoo-header p {
                margin: 0;
                opacity: 0.95;
                font-size: 16px;
                position: relative;
                z-index: 1;
            }
            .n8nwoo-card {
                background: rgba(255, 255, 255, 0.7);
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
                border: 1px solid rgba(255, 255, 255, 0.5);
                border-radius: 20px;
                padding: 30px;
                margin-bottom: 25px;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
                transition: all 0.3s ease;
            }
            .n8nwoo-card:hover {
                box-shadow: 0 12px 48px rgba(0, 0, 0, 0.15);
                transform: translateY(-2px);
            }
            .n8nwoo-card h2 {
                margin-top: 0;
                color: #1a202c;
                font-size: 20px;
                font-weight: 700;
                border: none;
                padding-bottom: 0;
                margin-bottom: 20px;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .n8nwoo-category-title {
                font-size: 14px;
                font-weight: 600;
                color: #667eea;
                text-transform: uppercase;
                letter-spacing: 1px;
                margin: 25px 0 15px 0;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .n8nwoo-category-title::before {
                content: '';
                width: 4px;
                height: 16px;
                background: linear-gradient(135deg, #667eea, #764ba2);
                border-radius: 2px;
            }
            .n8nwoo-webhook-input {
                width: 100%;
                padding: 15px 20px;
                font-size: 14px;
                border: 2px solid rgba(102, 126, 234, 0.2);
                border-radius: 12px;
                transition: all 0.3s;
                font-family: 'Monaco', 'Courier New', monospace;
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(10px);
            }
            .n8nwoo-webhook-input:focus {
                outline: none;
                border-color: #667eea;
                box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15);
                background: white;
            }
            .n8nwoo-events-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 20px;
                margin-top: 20px;
            }
            .n8nwoo-event-item {
                background: rgba(255, 255, 255, 0.6);
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.8);
                border-radius: 16px;
                padding: 20px;
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                cursor: pointer;
                position: relative;
                overflow: hidden;
            }
            .n8nwoo-event-item::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
                opacity: 0;
                transition: opacity 0.3s;
            }
            .n8nwoo-event-item:hover::before {
                opacity: 1;
            }
            .n8nwoo-event-item:hover {
                transform: translateY(-4px) scale(1.02);
                box-shadow: 0 12px 40px rgba(102, 126, 234, 0.2);
                border-color: rgba(102, 126, 234, 0.3);
            }
            .n8nwoo-event-item.active {
                background: rgba(102, 126, 234, 0.15);
                border-color: #667eea;
                box-shadow: 0 8px 32px rgba(102, 126, 234, 0.25);
            }
            .n8nwoo-event-checkbox {
                display: none;
            }
            .n8nwoo-event-label {
                display: flex;
                align-items: flex-start;
                position: relative;
                z-index: 1;
                pointer-events: none;
            }
            .n8nwoo-event-icon {
                font-size: 32px;
                margin-right: 15px;
                flex-shrink: 0;
                filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
            }
            .n8nwoo-event-content {
                flex: 1;
            }
            .n8nwoo-event-content h3 {
                margin: 0 0 6px 0;
                font-size: 16px;
                color: #1a202c;
                font-weight: 600;
            }
            .n8nwoo-event-content p {
                margin: 0;
                font-size: 13px;
                color: #4a5568;
                line-height: 1.5;
            }
            .n8nwoo-webhook-field {
                margin-top: 15px;
                padding-top: 15px;
                border-top: 1px solid rgba(102, 126, 234, 0.2);
                position: relative;
                z-index: 1;
            }
            .n8nwoo-webhook-individual-input {
                width: 100%;
                padding: 10px 14px;
                font-size: 12px;
                border: 1px solid rgba(102, 126, 234, 0.3);
                border-radius: 8px;
                background: rgba(255, 255, 255, 0.9);
                font-family: 'Monaco', 'Courier New', monospace;
                transition: all 0.3s;
                pointer-events: auto;
            }
            .n8nwoo-webhook-individual-input:focus {
                outline: none;
                border-color: #667eea;
                background: white;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            }
            .n8nwoo-webhook-individual-input::placeholder {
                font-size: 11px;
                color: #a0aec0;
            }
            /* Apple-style Toggle Switch */
            .n8nwoo-toggle-wrapper {
                position: absolute;
                top: 20px;
                right: 20px;
                z-index: 2;
            }
            .n8nwoo-toggle {
                position: relative;
                width: 51px;
                height: 31px;
                background: #e2e8f0;
                border-radius: 31px;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                cursor: pointer;
                box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
            }
            .n8nwoo-event-item.active .n8nwoo-toggle {
                background: #34c759;
            }
            .n8nwoo-toggle::before {
                content: '';
                position: absolute;
                top: 2px;
                left: 2px;
                width: 27px;
                height: 27px;
                background: white;
                border-radius: 50%;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            }
            .n8nwoo-event-item.active .n8nwoo-toggle::before {
                transform: translateX(20px);
            }
            .n8nwoo-status-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
                gap: 12px;
                margin-top: 20px;
            }
            .n8nwoo-status-item {
                background: rgba(255, 255, 255, 0.8);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(102, 126, 234, 0.2);
                border-radius: 12px;
                padding: 14px 18px;
                transition: all 0.3s;
                cursor: pointer;
            }
            .n8nwoo-status-item:hover {
                border-color: #667eea;
                background: rgba(255, 255, 255, 0.95);
                transform: translateX(4px);
            }
            .n8nwoo-status-item.active {
                background: rgba(102, 126, 234, 0.15);
                border-color: #667eea;
            }
            .n8nwoo-status-checkbox {
                margin-right: 10px;
                width: 18px;
                height: 18px;
                cursor: pointer;
            }
            .n8nwoo-status-label {
                display: flex;
                align-items: center;
                cursor: pointer;
                font-size: 14px;
                font-weight: 500;
                color: #2d3748;
            }
            .n8nwoo-badge {
                display: inline-block;
                padding: 6px 14px;
                border-radius: 20px;
                font-size: 11px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                backdrop-filter: blur(10px);
            }
            .n8nwoo-badge-success {
                background: rgba(52, 199, 89, 0.15);
                color: #22543d;
                border: 1px solid rgba(52, 199, 89, 0.3);
            }
            .n8nwoo-badge-info {
                background: rgba(102, 126, 234, 0.15);
                color: #2c5282;
                border: 1px solid rgba(102, 126, 234, 0.3);
            }
            .n8nwoo-button-group {
                display: flex;
                gap: 15px;
                margin-top: 30px;
            }
            .n8nwoo-submit-btn {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                padding: 14px 32px;
                border-radius: 12px;
                font-size: 15px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s;
                box-shadow: 0 8px 20px rgba(102, 126, 234, 0.35);
                flex: 1;
            }
            .n8nwoo-submit-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 12px 28px rgba(102, 126, 234, 0.45);
            }
            .n8nwoo-test-btn {
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(10px);
                color: #667eea;
                border: 2px solid #667eea;
                padding: 14px 32px;
                border-radius: 12px;
                font-size: 15px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s;
                display: flex;
                align-items: center;
                gap: 8px;
                justify-content: center;
            }
            .n8nwoo-test-btn:hover {
                background: #667eea;
                color: white;
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
            }
            .n8nwoo-test-btn:disabled {
                opacity: 0.6;
                cursor: not-allowed;
            }
            .n8nwoo-info-box {
                background: rgba(238, 242, 255, 0.6);
                backdrop-filter: blur(10px);
                border-left: 4px solid #667eea;
                padding: 18px 24px;
                border-radius: 12px;
                margin: 20px 0;
            }
            .n8nwoo-info-box p {
                margin: 0;
                color: #4c51bf;
                font-size: 14px;
                line-height: 1.6;
            }
            .n8nwoo-help-text {
                font-size: 13px;
                color: #718096;
                margin-top: 10px;
                line-height: 1.5;
            }
            .n8nwoo-test-result {
                margin-top: 15px;
                padding: 15px 20px;
                border-radius: 12px;
                backdrop-filter: blur(10px);
                display: none;
                animation: slideIn 0.3s ease;
            }
            .n8nwoo-test-result.success {
                background: rgba(52, 199, 89, 0.15);
                border: 1px solid rgba(52, 199, 89, 0.3);
                color: #22543d;
                display: block;
            }
            .n8nwoo-test-result.error {
                background: rgba(255, 59, 48, 0.15);
                border: 1px solid rgba(255, 59, 48, 0.3);
                color: #742a2a;
                display: block;
            }
            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        </style>
        
        <div class="wrap n8nwoo-wrapper">
            <div class="n8nwoo-header">
                <h1>⚡ N8N WooCommerce Webhook</h1>
                <p>Configure o webhook do N8N e escolha quais eventos você deseja monitorar</p>
            </div>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('n8nwoo_settings');
                do_settings_sections('n8nwoo_settings');
                ?>
                
                <!-- Webhook URL -->
                <div class="n8nwoo-card">
                    <h2>🔗 URL do Webhook N8N</h2>
                    <input 
                        type="url" 
                        id="n8nwoo_webhook_url" 
                        name="n8nwoo_webhook_url" 
                        value="<?php echo esc_attr($webhook_url); ?>" 
                        class="n8nwoo-webhook-input"
                        placeholder="https://seu-n8n.com/webhook/seu-webhook-id"
                        required
                    />
                    <p class="n8nwoo-help-text">
                        Cole a URL completa do webhook do N8N que receberá os dados dos pedidos
                    </p>
                    
                    <?php if (!empty($webhook_url)): ?>
                        <div class="n8nwoo-info-box" style="margin-top: 15px;">
                            <p>✓ Webhook configurado e pronto para enviar dados</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Eventos para Monitorar -->
                <div class="n8nwoo-card">
                    <h2>🎯 Eventos para Monitorar</h2>
                    <p class="n8nwoo-help-text" style="margin-top: 0;">
                        Use os toggles para ativar/desativar eventos do WooCommerce
                    </p>
                    
                    <?php 
                    $events_by_category = array();
                    foreach ($available_events as $event_key => $event) {
                        $events_by_category[$event['category']][$event_key] = $event;
                    }
                    ?>
                    
                    <?php foreach ($events_by_category as $category => $events): ?>
                        <div class="n8nwoo-category-title"><?php echo $category; ?></div>
                        <div class="n8nwoo-events-grid">
                            <?php foreach ($events as $event_key => $event): ?>
                                <div class="n8nwoo-event-item <?php echo in_array($event_key, $enabled_events) ? 'active' : ''; ?>">
                                    <input 
                                        type="checkbox" 
                                        id="event_<?php echo $event_key; ?>"
                                        name="n8nwoo_enabled_events[]" 
                                        value="<?php echo $event_key; ?>"
                                        class="n8nwoo-event-checkbox"
                                        <?php checked(in_array($event_key, $enabled_events)); ?>
                                    />
                                    <div class="n8nwoo-toggle-wrapper" onclick="toggleEventCheckbox('<?php echo $event_key; ?>')">
                                        <div class="n8nwoo-toggle"></div>
                                    </div>
                                    <div class="n8nwoo-event-label">
                                        <span class="n8nwoo-event-icon"><?php echo $event['icon']; ?></span>
                                        <div class="n8nwoo-event-content">
                                            <h3><?php echo $event['label']; ?></h3>
                                            <p><?php echo $event['description']; ?></p>
                                        </div>
                                    </div>
                                    <?php if (in_array($event_key, $enabled_events)): ?>
                                        <div class="n8nwoo-webhook-field">
                                            <input 
                                                type="url" 
                                                name="n8nwoo_individual_webhooks[<?php echo $event_key; ?>]"
                                                value="<?php echo esc_attr($individual_webhooks[$event_key] ?? ''); ?>"
                                                placeholder="Webhook específico ou deixe vazio para usar o principal"
                                                class="n8nwoo-webhook-individual-input"
                                            />
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Status de Pedidos para Monitorar -->
                <div class="n8nwoo-card">
                    <h2>📊 Status de Pedidos para Monitorar</h2>
                    <p class="n8nwoo-help-text" style="margin-top: 0;">
                        Selecione para quais status de pedido o webhook deve ser disparado ao mudar. 
                        <strong>Deixe vazio para monitorar TODOS os status</strong>
                        <span class="n8nwoo-badge n8nwoo-badge-info" style="margin-left: 10px;">
                            <?php echo count($order_statuses); ?> status disponíveis
                        </span>
                    </p>
                    
                    <?php if (empty($enabled_statuses)): ?>
                        <div class="n8nwoo-info-box">
                            <p>ℹ️ Atualmente monitorando <strong>todos os status</strong> de pedidos. Selecione abaixo para filtrar apenas status específicos.</p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="n8nwoo-status-grid">
                        <?php foreach ($order_statuses as $status_key => $status_label): ?>
                            <?php 
                            $clean_key = str_replace('wc-', '', $status_key);
                            $is_checked = in_array($clean_key, $enabled_statuses);
                            ?>
                            <div class="n8nwoo-status-item <?php echo $is_checked ? 'active' : ''; ?>" 
                                 onclick="toggleStatusCheckbox('<?php echo $clean_key; ?>')">
                                <label for="status_<?php echo $clean_key; ?>" class="n8nwoo-status-label">
                                    <input 
                                        type="checkbox" 
                                        id="status_<?php echo $clean_key; ?>"
                                        name="n8nwoo_enabled_statuses[]" 
                                        value="<?php echo $clean_key; ?>"
                                        class="n8nwoo-status-checkbox"
                                        <?php checked($is_checked); ?>
                                    />
                                    <?php echo esc_html($status_label); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Compatibilidade HPOS -->
                <div class="n8nwoo-card" style="background: #f0fdf4; border-color: #86efac;">
                    <h2 style="border-color: #22c55e; color: #15803d;">✓ Informações do Sistema</h2>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                        <div>
                            <span class="n8nwoo-badge n8nwoo-badge-success">✓ HPOS Compatível</span>
                            <p class="n8nwoo-help-text">Armazenamento de Alto Desempenho</p>
                        </div>
                        <div>
                            <span class="n8nwoo-badge n8nwoo-badge-info">Versão 1.0.1</span>
                            <p class="n8nwoo-help-text">Plugin atualizado</p>
                        </div>
                    </div>
                </div>
                
                <div class="n8nwoo-button-group">
                    <button type="submit" class="n8nwoo-submit-btn">
                        💾 Salvar Configurações
                    </button>
                    <button type="button" class="n8nwoo-test-btn" onclick="testWebhook()" id="test-webhook-btn">
                        <span>🚀</span>
                        <span>Testar Webhook</span>
                    </button>
                </div>
                
                <div id="test-result" class="n8nwoo-test-result"></div>
            </form>
        </div>
        
        <script>
            function toggleEventCheckbox(eventKey) {
                const checkbox = document.getElementById('event_' + eventKey);
                const card = checkbox.closest('.n8nwoo-event-item');
                checkbox.checked = !checkbox.checked;
                card.classList.toggle('active', checkbox.checked);
                
                const webhookField = card.querySelector('.n8nwoo-webhook-field');
                if (checkbox.checked) {
                    if (!webhookField) {
                        const newField = document.createElement('div');
                        newField.className = 'n8nwoo-webhook-field';
                        newField.innerHTML = '<input type="url" name="n8nwoo_individual_webhooks[' + eventKey + ']" value="" placeholder="Webhook específico ou deixe vazio para usar o principal" class="n8nwoo-webhook-individual-input" />';
                        card.appendChild(newField);
                    }
                } else {
                    if (webhookField) {
                        webhookField.remove();
                    }
                }
            }
            
            function toggleStatusCheckbox(statusKey) {
                const checkbox = document.getElementById('status_' + statusKey);
                const card = checkbox.closest('.n8nwoo-status-item');
                checkbox.checked = !checkbox.checked;
                card.classList.toggle('active', checkbox.checked);
            }
            
            function testWebhook() {
                const btn = document.getElementById('test-webhook-btn');
                const resultDiv = document.getElementById('test-result');
                const webhookUrl = document.getElementById('n8nwoo_webhook_url').value;
                
                if (!webhookUrl) {
                    resultDiv.className = 'n8nwoo-test-result error';
                    resultDiv.textContent = '❌ Por favor, configure a URL do webhook primeiro.';
                    return;
                }
                
                btn.disabled = true;
                btn.innerHTML = '<span>⏳</span><span>Enviando...</span>';
                resultDiv.style.display = 'none';
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=n8nwoo_test_webhook&webhook_url=' + encodeURIComponent(webhookUrl) + '&nonce=<?php echo wp_create_nonce('n8nwoo_test'); ?>'
                })
                .then(response => response.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = '<span>🚀</span><span>Testar Webhook</span>';
                    
                    if (data.success) {
                        resultDiv.className = 'n8nwoo-test-result success';
                        resultDiv.textContent = '✅ ' + data.data.message;
                    } else {
                        resultDiv.className = 'n8nwoo-test-result error';
                        resultDiv.textContent = '❌ ' + data.data.message;
                    }
                })
                .catch(error => {
                    btn.disabled = false;
                    btn.innerHTML = '<span>🚀</span><span>Testar Webhook</span>';
                    resultDiv.className = 'n8nwoo-test-result error';
                    resultDiv.textContent = '❌ Erro ao testar webhook: ' + error.message;
                });
            }
        </script>
        <?php
    }
    
    private function get_order_data($order_id, $event_type = 'order_created', $old_status = '', $new_status = '', $note_data = null) {
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return null;
        }
        
        // Dados básicos do pedido
        $data = array(
            'event_type' => $event_type,
            'timestamp' => current_time('mysql'),
            'order' => array(
                'id' => $order->get_id(),
                'order_number' => $order->get_order_number(),
                'status' => $order->get_status(),
                'currency' => $order->get_currency(),
                'date_created' => $order->get_date_created() ? $order->get_date_created()->date('Y-m-d H:i:s') : '',
                'date_modified' => $order->get_date_modified() ? $order->get_date_modified()->date('Y-m-d H:i:s') : '',
                'date_completed' => $order->get_date_completed() ? $order->get_date_completed()->date('Y-m-d H:i:s') : '',
            ),
            'customer' => array(
                'id' => $order->get_customer_id(),
                'email' => $order->get_billing_email(),
                'first_name' => $order->get_billing_first_name(),
                'last_name' => $order->get_billing_last_name(),
                'phone' => $order->get_billing_phone(),
                'company' => $order->get_billing_company(),
            ),
            'billing_address' => array(
                'first_name' => $order->get_billing_first_name(),
                'last_name' => $order->get_billing_last_name(),
                'company' => $order->get_billing_company(),
                'address_1' => $order->get_billing_address_1(),
                'address_2' => $order->get_billing_address_2(),
                'city' => $order->get_billing_city(),
                'state' => $order->get_billing_state(),
                'postcode' => $order->get_billing_postcode(),
                'country' => $order->get_billing_country(),
                'email' => $order->get_billing_email(),
                'phone' => $order->get_billing_phone(),
            ),
            'shipping_address' => array(
                'first_name' => $order->get_shipping_first_name(),
                'last_name' => $order->get_shipping_last_name(),
                'company' => $order->get_shipping_company(),
                'address_1' => $order->get_shipping_address_1(),
                'address_2' => $order->get_shipping_address_2(),
                'city' => $order->get_shipping_city(),
                'state' => $order->get_shipping_state(),
                'postcode' => $order->get_shipping_postcode(),
                'country' => $order->get_shipping_country(),
            ),
            'items' => array(),
            'totals' => array(
                'subtotal' => $order->get_subtotal(),
                'discount_total' => $order->get_discount_total(),
                'discount_tax' => $order->get_discount_tax(),
                'shipping_total' => $order->get_shipping_total(),
                'shipping_tax' => $order->get_shipping_tax(),
                'cart_tax' => $order->get_cart_tax(),
                'total' => $order->get_total(),
                'total_tax' => $order->get_total_tax(),
            ),
            'payment' => array(
                'method' => $order->get_payment_method(),
                'method_title' => $order->get_payment_method_title(),
                'transaction_id' => $order->get_transaction_id(),
            ),
            'shipping' => array(
                'method' => $order->get_shipping_method(),
                'total' => $order->get_shipping_total(),
            ),
        );
        
        // Se for mudança de status, incluir status antigo e novo
        if ($event_type === 'order_status_changed') {
            $data['status_change'] = array(
                'old_status' => $old_status,
                'new_status' => $new_status,
            );
        }
        
        // Se houver nota, incluir dados da nota
        if ($note_data && $event_type === 'order_note_added') {
            $data['note'] = $note_data;
        }
        
        // Produtos do pedido
        foreach ($order->get_items() as $item_id => $item) {
            $product = $item->get_product();
            
            $item_data = array(
                'id' => $item_id,
                'name' => $item->get_name(),
                'product_id' => $item->get_product_id(),
                'variation_id' => $item->get_variation_id(),
                'quantity' => $item->get_quantity(),
                'subtotal' => $item->get_subtotal(),
                'subtotal_tax' => $item->get_subtotal_tax(),
                'total' => $item->get_total(),
                'total_tax' => $item->get_total_tax(),
                'sku' => $product ? $product->get_sku() : '',
                'price' => $product ? $product->get_price() : '',
            );
            
            // Metadados do item
            $item_meta = array();
            foreach ($item->get_meta_data() as $meta) {
                $item_meta[$meta->key] = $meta->value;
            }
            $item_data['meta_data'] = $item_meta;
            
            // Dados de variação se for produto variável
            if ($item->get_variation_id()) {
                $item_data['variation'] = array();
                foreach ($item->get_meta_data() as $meta) {
                    if (strpos($meta->key, 'pa_') === 0 || strpos($meta->key, 'attribute_') === 0) {
                        $item_data['variation'][$meta->key] = $meta->value;
                    }
                }
            }
            
            $data['items'][] = $item_data;
        }
        
        // Taxas (se houver)
        $data['fees'] = array();
        foreach ($order->get_fees() as $fee_id => $fee) {
            $data['fees'][] = array(
                'id' => $fee_id,
                'name' => $fee->get_name(),
                'total' => $fee->get_total(),
                'tax' => $fee->get_total_tax(),
            );
        }
        
        // Cupons aplicados
        $data['coupons'] = array();
        foreach ($order->get_coupon_codes() as $coupon_code) {
            $data['coupons'][] = array(
                'code' => $coupon_code,
                'discount' => $order->get_discount_total(),
            );
        }
        
        // Notas do pedido (todas)
        $data['notes'] = array();
        $notes = wc_get_order_notes(array('order_id' => $order_id));
        foreach ($notes as $note) {
            $data['notes'][] = array(
                'id' => $note->id,
                'content' => $note->content,
                'customer_note' => (bool) $note->customer_note,
                'added_by' => $note->added_by,
                'date_created' => $note->date_created->date('Y-m-d H:i:s'),
            );
        }
        
        // Metadados customizados do pedido
        $data['order_meta'] = array();
        $meta_data = $order->get_meta_data();
        foreach ($meta_data as $meta) {
            if (strpos($meta->key, '_') !== 0) { // Ignorar meta keys privadas
                $data['order_meta'][$meta->key] = $meta->value;
            }
        }
        
        return $data;
    }
    
    private function get_webhook_url_for_event($event_type) {
        $individual_webhooks = get_option('n8nwoo_individual_webhooks', array());
        
        if (!empty($individual_webhooks[$event_type])) {
            return $individual_webhooks[$event_type];
        }
        
        return $this->webhook_url;
    }
    
    private function send_to_webhook($data) {
        if (!$data) {
            error_log('N8NWoo: Dados inválidos');
            return false;
        }
        
        $event_type = $data['event_type'] ?? '';
        $webhook_url = $this->get_webhook_url_for_event($event_type);
        
        if (empty($webhook_url)) {
            error_log('N8NWoo: Nenhum webhook configurado para ' . $event_type);
            return false;
        }
        
        $json_data = wp_json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        
        $response = wp_remote_post($webhook_url, array(
            'method' => 'POST',
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json',
                'User-Agent' => 'N8NWoo/1.0',
            ),
            'body' => $json_data,
        ));
        
        if (is_wp_error($response)) {
            error_log('N8NWoo: Erro ao enviar - ' . $response->get_error_message());
            return false;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        
        if ($status_code >= 200 && $status_code < 300) {
            error_log("N8NWoo: Enviado para {$event_type} - Status {$status_code}");
            return true;
        } else {
            error_log("N8NWoo: Falha {$event_type} - Status {$status_code}");
            return false;
        }
    }
    
    private function is_event_enabled($event_type) {
        $enabled_events = get_option('n8nwoo_enabled_events', array('order_created', 'order_updated', 'order_status_changed', 'order_note_added'));
        return in_array($event_type, $enabled_events);
    }
    
    private function is_status_enabled($status) {
        $enabled_statuses = get_option('n8nwoo_enabled_statuses', array());
        // Se nenhum status foi selecionado, monitora todos
        if (empty($enabled_statuses)) {
            return true;
        }
        return in_array($status, $enabled_statuses);
    }
    
    public function on_order_created($order_id) {
        if (!$this->is_event_enabled('order_created')) {
            return;
        }
        
        $data = $this->get_order_data($order_id, 'order_created');
        $this->send_to_webhook($data);
    }
    
    public function on_order_updated($order_id) {
        if (!$this->is_event_enabled('order_updated')) {
            return;
        }
        
        $data = $this->get_order_data($order_id, 'order_updated');
        $this->send_to_webhook($data);
    }
    
    public function on_order_status_changed($order_id, $old_status, $new_status, $order) {
        if (!$this->is_event_enabled('order_status_changed')) {
            return;
        }
        
        // Verifica se o novo status está habilitado
        if (!$this->is_status_enabled($new_status)) {
            return;
        }
        
        $data = $this->get_order_data($order_id, 'order_status_changed', $old_status, $new_status);
        $this->send_to_webhook($data);
    }
    
    public function on_order_note_added($note_id, $order) {
        if (!$this->is_event_enabled('order_note_added')) {
            return;
        }
        
        $order_id = $order->get_id();
        $note = wc_get_order_note($note_id);
        
        $note_data = array(
            'id' => $note_id,
            'content' => $note->content ?? '',
            'customer_note' => $note->customer_note ?? false,
            'added_by' => $note->added_by ?? '',
            'date_created' => $note->date_created ? $note->date_created->date('Y-m-d H:i:s') : '',
        );
        
        $data = $this->get_order_data($order_id, 'order_note_added', '', '', $note_data);
        $this->send_to_webhook($data);
    }
    
    // Customer events
    public function on_customer_created($customer_id) {
        if (!$this->is_event_enabled('customer_created')) {
            return;
        }
        
        $data = $this->get_customer_data($customer_id, 'customer_created');
        $this->send_to_webhook($data);
    }
    
    public function on_customer_updated($customer_id) {
        if (!$this->is_event_enabled('customer_updated')) {
            return;
        }
        
        $data = $this->get_customer_data($customer_id, 'customer_updated');
        $this->send_to_webhook($data);
    }
    
    public function on_customer_profile_updated($user_id) {
        if (!$this->is_event_enabled('customer_updated')) {
            return;
        }
        
        // Verifica se é um cliente WooCommerce
        if (!wc_customer_bought_product('', $user_id, '')) {
            $customer = new WC_Customer($user_id);
            if ($customer->get_id()) {
                $data = $this->get_customer_data($user_id, 'customer_updated');
                $this->send_to_webhook($data);
            }
        }
    }
    
    private function get_customer_data($customer_id, $event_type = 'customer_created') {
        $customer = new WC_Customer($customer_id);
        
        if (!$customer->get_id()) {
            return null;
        }
        
        $data = array(
            'event_type' => $event_type,
            'timestamp' => current_time('mysql'),
            'customer' => array(
                'id' => $customer->get_id(),
                'email' => $customer->get_email(),
                'username' => $customer->get_username(),
                'first_name' => $customer->get_first_name(),
                'last_name' => $customer->get_last_name(),
                'display_name' => $customer->get_display_name(),
                'role' => $customer->get_role(),
                'date_created' => $customer->get_date_created() ? $customer->get_date_created()->date('Y-m-d H:i:s') : '',
                'date_modified' => $customer->get_date_modified() ? $customer->get_date_modified()->date('Y-m-d H:i:s') : '',
            ),
            'billing' => array(
                'first_name' => $customer->get_billing_first_name(),
                'last_name' => $customer->get_billing_last_name(),
                'company' => $customer->get_billing_company(),
                'address_1' => $customer->get_billing_address_1(),
                'address_2' => $customer->get_billing_address_2(),
                'city' => $customer->get_billing_city(),
                'state' => $customer->get_billing_state(),
                'postcode' => $customer->get_billing_postcode(),
                'country' => $customer->get_billing_country(),
                'email' => $customer->get_billing_email(),
                'phone' => $customer->get_billing_phone(),
            ),
            'shipping' => array(
                'first_name' => $customer->get_shipping_first_name(),
                'last_name' => $customer->get_shipping_last_name(),
                'company' => $customer->get_shipping_company(),
                'address_1' => $customer->get_shipping_address_1(),
                'address_2' => $customer->get_shipping_address_2(),
                'city' => $customer->get_shipping_city(),
                'state' => $customer->get_shipping_state(),
                'postcode' => $customer->get_shipping_postcode(),
                'country' => $customer->get_shipping_country(),
            ),
            'stats' => array(
                'orders_count' => $customer->get_order_count(),
                'total_spent' => $customer->get_total_spent(),
            )
        );
        
        return $data;
    }
    
    // AJAX handler for webhook test
    public function ajax_test_webhook() {
        check_ajax_referer('n8nwoo_test', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Sem permissão para testar webhook'));
        }
        
        $webhook_url = isset($_POST['webhook_url']) ? esc_url_raw($_POST['webhook_url']) : '';
        
        if (empty($webhook_url)) {
            wp_send_json_error(array('message' => 'URL do webhook não fornecida'));
        }
        
        $test_data = array(
            'event_type' => 'webhook_test',
            'timestamp' => current_time('mysql'),
            'message' => 'Este é um teste do webhook N8NWoo',
            'test' => true,
            'plugin_version' => '1.0.1',
            'site_url' => get_site_url(),
            'sample_order' => array(
                'id' => 12345,
                'order_number' => 12345,
                'status' => 'processing',
                'total' => '150.00',
                'currency' => 'BRL'
            ),
            'sample_customer' => array(
                'id' => 1,
                'email' => 'teste@exemplo.com',
                'first_name' => 'João',
                'last_name' => 'Silva'
            )
        );
        
        $json_data = wp_json_encode($test_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        
        $response = wp_remote_post($webhook_url, array(
            'method' => 'POST',
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json',
                'User-Agent' => 'N8NWoo/1.0-Test',
            ),
            'body' => $json_data,
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => 'Erro ao conectar: ' . $response->get_error_message()));
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        
        if ($status_code >= 200 && $status_code < 300) {
            wp_send_json_success(array('message' => "Webhook testado com sucesso! Status HTTP: {$status_code}"));
        } else {
            wp_send_json_error(array('message' => "Webhook retornou erro. Status HTTP: {$status_code}"));
        }
    }
}

// Inicializa o plugin
function n8nwoo_init() {
    if (class_exists('WooCommerce')) {
        N8NWoo::get_instance();
    } else {
        add_action('admin_notices', 'n8nwoo_woocommerce_missing_notice');
    }
}
add_action('plugins_loaded', 'n8nwoo_init');

function n8nwoo_woocommerce_missing_notice() {
    ?>
    <div class="notice notice-error">
        <p><strong>N8N WooCommerce Webhook</strong> requer o WooCommerce para funcionar. Por favor, instale e ative o WooCommerce.</p>
    </div>
    <?php
}
