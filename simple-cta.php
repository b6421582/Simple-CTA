<?php
/**
 * Plugin Name: Simple CTA
 * Plugin URI: https://github.com/b6421582/Simple-CTA
 * Description: 轻量、简洁的CTA插件，自动检测联盟链接并应用预设样式，支持自定义平台规则和CTA风格。
 * Version: 1.0.0
 * Author: CatchIdeas
 * Author URI: https://catchideas.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: simple-cta
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.8
 * Requires PHP: 8.0
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

// 定义插件常量
define('SIMPLE_CTA_VERSION', '1.0.0');
define('SIMPLE_CTA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SIMPLE_CTA_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('SIMPLE_CTA_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Simple CTA 主类
 */
class SimpleCTA {
    
    /**
     * 单例实例
     */
    private static $instance = null;
    
    /**
     * 默认平台规则
     */
    private $default_platforms = [
        'amazon' => [
            'name' => 'Amazon',
            'patterns' => [
                'amzn\.to\/[a-zA-Z0-9]+',
                'amazon\.[a-z.]+\/.*[?&]tag=',
                'amazon\.[a-z.]+\/dp\/[A-Z0-9]+',
                'amazon\.[a-z.]+\/gp\/product\/[A-Z0-9]+'
            ],
            'class' => 'amazon-cta',
            'enabled' => true
        ],
        'ebay' => [
            'name' => 'eBay',
            'patterns' => [
                'rover\.ebay\.[a-z.]+\/rover\/',
                'ebay\.[a-z.]+\/itm\/',
                'ebay\.[a-z.]+\/.*[?&]campid='
            ],
            'class' => 'ebay-cta',
            'enabled' => true
        ],
        'walmart' => [
            'name' => 'Walmart',
            'patterns' => [
                'linksynergy\.walmart\.com',
                'goto\.walmart\.com',
                'walmart\.com\/.*[?&]u1='
            ],
            'class' => 'walmart-cta',
            'enabled' => true
        ],
        'cj' => [
            'name' => 'CJ Affiliate',
            'patterns' => [
                'cj\.com\/.*[?&]sid=',
                'anrdoezrs\.net',
                'dpbolvw\.net',
                'jdoqocy\.com'
            ],
            'class' => 'cj-cta',
            'enabled' => true
        ],
        'shareasale' => [
            'name' => 'ShareASale',
            'patterns' => [
                'shareasale\.com\/r\.cfm',
                'shareasale\.com\/.*[?&]afftrack='
            ],
            'class' => 'shareasale-cta',
            'enabled' => true
        ],
        'impact' => [
            'name' => 'Impact Radius',
            'patterns' => [
                'impact\.com\/.*[?&]irclickid=',
                'impactradius-event\.com'
            ],
            'class' => 'impact-cta',
            'enabled' => true
        ],
        'rakuten' => [
            'name' => 'Rakuten',
            'patterns' => [
                'rakuten\.com\/.*[?&]ranMID=',
                'linksynergy\.com\/.*[?&]mid='
            ],
            'class' => 'rakuten-cta',
            'enabled' => true
        ]
    ];
    
    /**
     * 默认CTA样式
     */
    private $default_styles = [
        'modern' => [
            'name' => '现代风格',
            'css' => '.simple-cta.modern { 
                display: inline-block; 
                padding: 12px 24px; 
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                color: white; 
                text-decoration: none; 
                border-radius: 8px; 
                font-weight: 600; 
                transition: all 0.3s ease; 
                box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3); 
            }
            .simple-cta.modern:hover { 
                transform: translateY(-2px); 
                box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4); 
                text-decoration: none; 
            }'
        ],
        'minimal' => [
            'name' => '简约风格',
            'css' => '.simple-cta.minimal { 
                display: inline-block; 
                padding: 10px 20px; 
                background: #2c3e50; 
                color: white; 
                text-decoration: none; 
                border-radius: 4px; 
                font-weight: 500; 
                transition: background 0.3s ease; 
            }
            .simple-cta.minimal:hover { 
                background: #34495e; 
                text-decoration: none; 
            }'
        ],
        'vibrant' => [
            'name' => '活力风格',
            'css' => '.simple-cta.vibrant { 
                display: inline-block; 
                padding: 14px 28px; 
                background: #ff6b6b; 
                color: white; 
                text-decoration: none; 
                border-radius: 50px; 
                font-weight: 700; 
                text-transform: uppercase; 
                letter-spacing: 1px; 
                transition: all 0.3s ease; 
                animation: pulse 2s infinite; 
            }
            .simple-cta.vibrant:hover { 
                background: #ff5252; 
                transform: scale(1.05); 
                text-decoration: none; 
            }
            @keyframes pulse { 
                0% { box-shadow: 0 0 0 0 rgba(255, 107, 107, 0.7); } 
                70% { box-shadow: 0 0 0 10px rgba(255, 107, 107, 0); } 
                100% { box-shadow: 0 0 0 0 rgba(255, 107, 107, 0); } 
            }'
        ],
        'elegant' => [
            'name' => '优雅风格',
            'css' => '.simple-cta.elegant { 
                display: inline-block; 
                padding: 12px 24px; 
                background: transparent; 
                color: #2c3e50; 
                text-decoration: none; 
                border: 2px solid #2c3e50; 
                border-radius: 0; 
                font-weight: 400; 
                position: relative; 
                overflow: hidden; 
                transition: all 0.4s ease; 
            }
            .simple-cta.elegant:before { 
                content: ""; 
                position: absolute; 
                top: 0; 
                left: -100%; 
                width: 100%; 
                height: 100%; 
                background: #2c3e50; 
                transition: left 0.4s ease; 
                z-index: -1; 
            }
            .simple-cta.elegant:hover:before { 
                left: 0; 
            }
            .simple-cta.elegant:hover { 
                color: white; 
                text-decoration: none; 
            }'
        ],
        'gradient' => [
            'name' => '渐变风格',
            'css' => '.simple-cta.gradient { 
                display: inline-block; 
                padding: 14px 30px; 
                background: linear-gradient(45deg, #f093fb 0%, #f5576c 100%); 
                color: white; 
                text-decoration: none; 
                border-radius: 25px; 
                font-weight: 600; 
                position: relative; 
                overflow: hidden; 
                transition: all 0.3s ease; 
            }
            .simple-cta.gradient:before { 
                content: ""; 
                position: absolute; 
                top: 0; 
                left: 0; 
                right: 0; 
                bottom: 0; 
                background: linear-gradient(45deg, #667eea 0%, #764ba2 100%); 
                opacity: 0; 
                transition: opacity 0.3s ease; 
            }
            .simple-cta.gradient:hover:before { 
                opacity: 1; 
            }
            .simple-cta.gradient:hover { 
                transform: translateY(-1px); 
                text-decoration: none; 
            }'
        ]
    ];
    
    /**
     * 获取单例实例
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 构造函数
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * 初始化插件
     */
    private function init() {
        // 激活钩子
        register_activation_hook(__FILE__, [$this, 'activate']);

        // 卸载钩子
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);

        // 初始化钩子
        add_action('init', [$this, 'initPlugin']);

        // 管理界面钩子
        add_action('admin_menu', [$this, 'addAdminMenu']);
        add_action('admin_init', [$this, 'adminInit']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScripts']);

        // 前端钩子
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
        add_filter('the_content', [$this, 'processContent'], 20);

        // AJAX钩子
        add_action('wp_ajax_simple_cta_save_settings', [$this, 'saveSettings']);
        add_action('wp_ajax_simple_cta_preview_style', [$this, 'previewStyle']);
    }
    
    /**
     * 插件激活
     */
    public function activate() {
        // 设置默认选项
        if (!get_option('simple_cta_platforms')) {
            update_option('simple_cta_platforms', $this->default_platforms);
        }
        
        if (!get_option('simple_cta_styles')) {
            update_option('simple_cta_styles', $this->default_styles);
        }
        
        if (!get_option('simple_cta_settings')) {
            update_option('simple_cta_settings', [
                'enabled' => true,
                'default_style' => 'modern',
                'auto_detect' => true,
                'load_css' => true
            ]);
        }
    }
    
    /**
     * 插件停用
     */
    public function deactivate() {
        // 清理临时数据（如果需要）
    }
    
    /**
     * 初始化插件
     */
    public function initPlugin() {
        // 加载文本域
        load_plugin_textdomain('simple-cta', false, dirname(SIMPLE_CTA_PLUGIN_BASENAME) . '/languages');
    }
}

    /**
     * 添加管理菜单
     */
    public function addAdminMenu() {
        add_options_page(
            __('Simple CTA 设置', 'simple-cta'),
            __('Simple CTA', 'simple-cta'),
            'manage_options',
            'simple-cta',
            [$this, 'adminPage']
        );
    }

    /**
     * 管理界面初始化
     */
    public function adminInit() {
        // 注册设置
        register_setting('simple_cta_settings_group', 'simple_cta_platforms');
        register_setting('simple_cta_settings_group', 'simple_cta_styles');
        register_setting('simple_cta_settings_group', 'simple_cta_settings');

        // 添加设置部分
        add_settings_section(
            'simple_cta_general',
            __('基本设置', 'simple-cta'),
            null,
            'simple-cta'
        );

        add_settings_section(
            'simple_cta_platforms_section',
            __('平台规则管理', 'simple-cta'),
            null,
            'simple-cta'
        );

        add_settings_section(
            'simple_cta_styles_section',
            __('CTA样式管理', 'simple-cta'),
            null,
            'simple-cta'
        );
    }

    /**
     * 加载管理界面脚本和样式
     */
    public function enqueueAdminScripts($hook) {
        // 只在插件设置页面加载
        if ($hook !== 'settings_page_simple-cta') {
            return;
        }

        // 加载管理界面CSS
        wp_enqueue_style(
            'simple-cta-admin',
            SIMPLE_CTA_PLUGIN_URL . 'assets/css/admin.css',
            [],
            SIMPLE_CTA_VERSION
        );

        // 加载管理界面JavaScript
        wp_enqueue_script(
            'simple-cta-admin',
            SIMPLE_CTA_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            SIMPLE_CTA_VERSION,
            true
        );

        // 传递数据到JavaScript
        wp_localize_script('simple-cta-admin', 'simpleCTAAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('simple_cta_nonce'),
            'strings' => [
                'confirmDelete' => __('确定要删除这个项目吗？', 'simple-cta'),
                'saved' => __('设置已保存！', 'simple-cta'),
                'error' => __('保存失败，请重试。', 'simple-cta'),
                'testing' => __('正在测试...', 'simple-cta'),
                'noResults' => __('没有检测到联盟链接。', 'simple-cta')
            ]
        ]);
    }

    /**
     * 加载前端脚本和样式
     */
    public function enqueueScripts() {
        $settings = get_option('simple_cta_settings', []);

        if (!empty($settings['load_css']) && $settings['load_css']) {
            // 动态生成CSS
            $this->generateDynamicCSS();
        }

        // 加载前端JavaScript（如果需要）
        wp_enqueue_script(
            'simple-cta-frontend',
            SIMPLE_CTA_PLUGIN_URL . 'assets/js/frontend.js',
            ['jquery'],
            SIMPLE_CTA_VERSION,
            true
        );
    }

    /**
     * 处理内容，自动检测并添加CTA样式
     */
    public function processContent($content) {
        $settings = get_option('simple_cta_settings', []);

        if (empty($settings['enabled']) || !$settings['enabled']) {
            return $content;
        }

        if (empty($settings['auto_detect']) || !$settings['auto_detect']) {
            return $content;
        }

        $platforms = get_option('simple_cta_platforms', $this->default_platforms);
        $default_style = !empty($settings['default_style']) ? $settings['default_style'] : 'modern';

        // 处理链接
        $content = preg_replace_callback(
            '/<a\s+([^>]*href=["\']([^"\']+)["\'][^>]*)>([^<]*)<\/a>/i',
            function($matches) use ($platforms, $default_style) {
                $full_tag = $matches[0];
                $attributes = $matches[1];
                $url = $matches[2];
                $text = $matches[3];

                // 检查是否已经有CTA类
                if (strpos($attributes, 'simple-cta') !== false) {
                    return $full_tag;
                }

                // 检测平台
                $detected_platform = $this->detectPlatform($url, $platforms);

                if ($detected_platform) {
                    $platform_class = $platforms[$detected_platform]['class'];
                    $style_class = 'simple-cta ' . $default_style;

                    // 添加类名
                    if (strpos($attributes, 'class=') !== false) {
                        $attributes = preg_replace(
                            '/class=["\']([^"\']*)["\']/',
                            'class="$1 ' . $style_class . ' ' . $platform_class . '"',
                            $attributes
                        );
                    } else {
                        $attributes .= ' class="' . $style_class . ' ' . $platform_class . '"';
                    }

                    return '<a ' . $attributes . '>' . $text . '</a>';
                }

                return $full_tag;
            },
            $content
        );

        return $content;
    }

    /**
     * 检测链接平台
     */
    private function detectPlatform($url, $platforms) {
        foreach ($platforms as $platform_key => $platform) {
            if (empty($platform['enabled']) || !$platform['enabled']) {
                continue;
            }

            foreach ($platform['patterns'] as $pattern) {
                if (preg_match('/' . $pattern . '/i', $url)) {
                    return $platform_key;
                }
            }
        }

        return false;
    }

    /**
     * 生成动态CSS
     */
    private function generateDynamicCSS() {
        $styles = get_option('simple_cta_styles', $this->default_styles);

        $css = '';
        foreach ($styles as $style_key => $style) {
            if (!empty($style['css'])) {
                $css .= $style['css'] . "\n";
            }
        }

        // 输出CSS到头部
        if (!empty($css)) {
            add_action('wp_head', function() use ($css) {
                echo '<style type="text/css" id="simple-cta-dynamic-css">' . $css . '</style>';
            });
        }
    }

    /**
     * 管理页面
     */
    public function adminPage() {
        require_once SIMPLE_CTA_PLUGIN_PATH . 'admin-page.php';
        SimpleCTAAdminPage::render();
    }

    /**
     * 保存设置 AJAX处理
     */
    public function saveSettings() {
        check_ajax_referer('simple_cta_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(__('权限不足', 'simple-cta'));
        }

        $response = ['success' => false];

        if (isset($_POST['settings'])) {
            update_option('simple_cta_settings', $_POST['settings']);
            $response['success'] = true;
        }

        wp_send_json($response);
    }

    /**
     * 预览样式 AJAX处理
     */
    public function previewStyle() {
        check_ajax_referer('simple_cta_nonce', 'nonce');

        $style_css = sanitize_textarea_field($_POST['css']);
        $style_name = sanitize_text_field($_POST['style_name']);

        $response = [
            'success' => true,
            'html' => '<a href="#" class="simple-cta ' . esc_attr($style_name) . '" onclick="return false;">示例按钮</a>',
            'css' => $style_css
        ];

        wp_send_json($response);
    }
}

// 初始化插件
SimpleCTA::getInstance();
