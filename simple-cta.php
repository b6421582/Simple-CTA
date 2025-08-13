<?php
/**
 * Plugin Name: Simple CTA
 * Plugin URI: https://github.com/b6421582/Simple-CTA
 * Description: Lightweight and clean CTA plugin that automatically detects affiliate links and applies preset styles, supports custom platform rules and CTA styles.
 * Version: 1.1.0
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

// 定义插件常量 - 在WordPress加载后定义
if (!defined('SIMPLE_CTA_VERSION')) {
    define('SIMPLE_CTA_VERSION', '1.1.0');
}

// 插件初始化函数
function simple_cta_init() {
    // 确保WordPress函数可用
    if (!function_exists('plugin_dir_url') || !function_exists('plugin_dir_path') || !function_exists('plugin_basename')) {
        return;
    }

    // 在WordPress完全加载后定义常量
    if (!defined('SIMPLE_CTA_PLUGIN_URL')) {
        define('SIMPLE_CTA_PLUGIN_URL', plugin_dir_url(__FILE__));
        define('SIMPLE_CTA_PLUGIN_PATH', plugin_dir_path(__FILE__));
        define('SIMPLE_CTA_PLUGIN_BASENAME', plugin_basename(__FILE__));
    }

    // 初始化插件主类
    SimpleCTA::getInstance();
}

// 在WordPress初始化后运行
if (function_exists('add_action')) {
    add_action('plugins_loaded', 'simple_cta_init');
}

// 注册激活和停用钩子（必须在文件级别注册）
if (function_exists('register_activation_hook') && function_exists('register_deactivation_hook')) {
    register_activation_hook(__FILE__, function() {
        // 确保常量已定义
        simple_cta_init();
        $instance = SimpleCTA::getInstance();
        if (method_exists($instance, 'activate')) {
            $instance->activate();
        }
    });

    register_deactivation_hook(__FILE__, function() {
        // 确保常量已定义
        simple_cta_init();
        $instance = SimpleCTA::getInstance();
        if (method_exists($instance, 'deactivate')) {
            $instance->deactivate();
        }
    });
}

/**
 * Simple CTA 主类
 */
class SimpleCTA {

    /**
     * 单例实例
     */
    private static $instance = null;

    /**
     * 缓存的设置数据
     */
    private $cached_settings = null;
    private $cached_platforms = null;
    private $cached_styles = null;
    
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
                'amazon\.[a-z.]+\/gp\/product\/[A-Z0-9]+',
                'www\.amazon\.[a-z.]+\/.*[?&]tag=',
                'www\.amazon\.[a-z.]+\/dp\/[A-Z0-9]+',
                'www\.amazon\.[a-z.]+\/gp\/product\/[A-Z0-9]+'
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
                border-radius: 6px;
                font-weight: 600;
                transition: all 0.3s ease;
                box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
                position: relative;
                overflow: hidden;
            }
            .simple-cta.modern:before {
                content: "";
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
                transition: left 0.5s ease;
            }
            .simple-cta.modern:hover:before {
                left: 100%;
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
                padding: 12px 24px;
                background: #2c3e50;
                color: white;
                text-decoration: none;
                border-radius: 4px;
                font-weight: 500;
                transition: all 0.3s ease;
                position: relative;
            }
            .simple-cta.minimal:after {
                content: "";
                position: absolute;
                bottom: 0;
                left: 50%;
                width: 0;
                height: 2px;
                background: #3498db;
                transition: all 0.3s ease;
                transform: translateX(-50%);
            }
            .simple-cta.minimal:hover:after {
                width: 100%;
            }
            .simple-cta.minimal:hover {
                background: #34495e;
                text-decoration: none;
                transform: translateY(-1px);
            }'
        ],
        'vibrant' => [
            'name' => '活力风格',
            'css' => '.simple-cta.vibrant {
                display: inline-block;
                padding: 12px 24px;
                background: #ff6b6b;
                color: white;
                text-decoration: none;
                border-radius: 8px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 1px;
                transition: all 0.3s ease;
                position: relative;
                overflow: hidden;
            }
            .simple-cta.vibrant:before {
                content: "";
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: repeating-linear-gradient(
                    45deg,
                    transparent,
                    transparent 2px,
                    rgba(255,255,255,0.1) 2px,
                    rgba(255,255,255,0.1) 4px
                );
                opacity: 0;
                transition: opacity 0.3s ease;
            }
            .simple-cta.vibrant:hover:before {
                opacity: 1;
            }
            .simple-cta.vibrant:hover {
                background: #ff5252;
                transform: translateY(-2px);
                text-decoration: none;
                box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4);
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
                border-radius: 2px;
                font-weight: 400;
                transition: all 0.4s ease;
                position: relative;
            }
            .simple-cta.elegant:hover {
                background: #2c3e50;
                color: white;
                text-decoration: none;
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(44, 62, 80, 0.3);
            }'
        ],
        'gradient' => [
            'name' => '渐变风格',
            'css' => '.simple-cta.gradient {
                display: inline-block;
                padding: 12px 24px;
                background: linear-gradient(45deg, #f093fb 0%, #f5576c 100%);
                color: white;
                text-decoration: none;
                border-radius: 25px;
                font-weight: 600;
                position: relative;
                overflow: hidden;
                transition: all 0.3s ease;
                z-index: 1;
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
                z-index: -1;
            }
            .simple-cta.gradient:after {
                content: "";
                position: absolute;
                top: 50%;
                left: 50%;
                width: 0;
                height: 0;
                background: rgba(255,255,255,0.3);
                transition: all 0.3s ease;
                transform: translate(-50%, -50%);
                z-index: -1;
            }
            .simple-cta.gradient:hover:before {
                opacity: 1;
            }
            .simple-cta.gradient:hover:after {
                width: 100%;
                height: 100%;
            }
            .simple-cta.gradient:hover {
                transform: translateY(-1px);
                text-decoration: none;
                color: white;
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
        // 确保WordPress已完全加载
        if (!function_exists('add_action')) {
            return;
        }

        // 激活和停用钩子已在文件级别注册

        // 初始化钩子
        add_action('init', [$this, 'initPlugin']);

        // 管理界面钩子
        add_action('admin_menu', [$this, 'addAdminMenu']);
        add_action('admin_init', [$this, 'adminInit']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScripts']);

        // 前端钩子
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
        add_filter('the_content', [$this, 'processContent'], 15);

        // AJAX钩子
        add_action('wp_ajax_simple_cta_save_settings', [$this, 'saveSettings']);
        add_action('wp_ajax_simple_cta_preview_style', [$this, 'previewStyle']);

        // 插件列表页面的设置链接
        add_filter('plugin_action_links_' . SIMPLE_CTA_PLUGIN_BASENAME, [$this, 'addSettingsLink']);

        // 在插件设置页面隐藏WordPress版本信息
        add_action('admin_head', [$this, 'hideWordPressFooter']);
    }


    
    /**
     * 插件激活
     */
    public function activate() {
        // 确保WordPress函数可用
        if (!function_exists('get_option') || !function_exists('update_option')) {
            return;
        }

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
                'load_css' => true,
                'add_nofollow' => true, // 自动添加nofollow
                'priority' => 15, // 执行优先级
                'preserve_attributes' => true // 保持其他属性
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

    /**
     * 获取缓存的设置数据
     */
    private function getCachedSettings() {
        if ($this->cached_settings === null) {
            if (function_exists('get_option')) {
                $this->cached_settings = get_option('simple_cta_settings', [
                    'enabled' => true,
                    'default_style' => 'modern',
                    'auto_detect' => true,
                    'load_css' => true,
                    'add_nofollow' => true,
                    'priority' => 15,
                    'preserve_attributes' => true
                ]);
            } else {
                // WordPress未加载时的默认值
                $this->cached_settings = [
                    'enabled' => true,
                    'default_style' => 'modern',
                    'auto_detect' => true,
                    'load_css' => true,
                    'add_nofollow' => true,
                    'priority' => 15,
                    'preserve_attributes' => true
                ];
            }
        }
        return $this->cached_settings;
    }

    /**
     * 获取缓存的平台数据
     */
    private function getCachedPlatforms() {
        if ($this->cached_platforms === null) {
            if (function_exists('get_option')) {
                $this->cached_platforms = get_option('simple_cta_platforms', $this->default_platforms);
            } else {
                $this->cached_platforms = $this->default_platforms;
            }
        }
        return $this->cached_platforms;
    }

    /**
     * 获取缓存的样式数据
     */
    private function getCachedStyles() {
        if ($this->cached_styles === null) {
            if (function_exists('get_option')) {
                $this->cached_styles = get_option('simple_cta_styles', $this->default_styles);
            } else {
                $this->cached_styles = $this->default_styles;
            }
        }
        return $this->cached_styles;
    }

    /**
     * 清理缓存
     */
    public function clearCache() {
        $this->cached_settings = null;
        $this->cached_platforms = null;
        $this->cached_styles = null;
    }

    /**
     * 检查是否应该加载前端JavaScript
     */
    private function shouldLoadFrontendJS() {
        // 简单检查：如果启用了自动检测，就加载JS
        $settings = $this->getCachedSettings();
        return !empty($settings['enabled']) && !empty($settings['auto_detect']);
    }

    /**
     * 清理设置数据
     */
    private function sanitizeSettings($settings) {
        $clean_settings = [];

        // 布尔值设置
        $boolean_fields = ['enabled', 'auto_detect', 'load_css', 'add_nofollow', 'preserve_attributes'];
        foreach ($boolean_fields as $field) {
            $clean_settings[$field] = !empty($settings[$field]);
        }

        // 字符串设置
        $default_style = $settings['default_style'] ?? 'modern';
        $clean_settings['default_style'] = function_exists('sanitize_text_field')
            ? sanitize_text_field($default_style)
            : trim(strip_tags($default_style));

        // 数字设置
        $clean_settings['priority'] = intval($settings['priority'] ?? 15);

        return $clean_settings;
    }

    /**
     * 清理平台数据
     */
    private function sanitizePlatforms($platforms) {
        $clean_platforms = [];

        foreach ($platforms as $key => $platform) {
            // 清理键名
            $clean_key = function_exists('sanitize_key')
                ? sanitize_key($key)
                : preg_replace('/[^a-z0-9_\-]/', '', strtolower($key));

            // 清理平台名称
            $name = $platform['name'] ?? '';
            $clean_name = function_exists('sanitize_text_field')
                ? sanitize_text_field($name)
                : trim(strip_tags($name));

            // 清理CSS类名
            $class = $platform['class'] ?? '';
            $clean_class = function_exists('sanitize_html_class')
                ? sanitize_html_class($class)
                : preg_replace('/[^a-z0-9_\-]/', '', strtolower($class));

            $clean_platforms[$clean_key] = [
                'name' => $clean_name,
                'class' => $clean_class,
                'enabled' => !empty($platform['enabled']),
                'patterns' => []
            ];

            // 清理正则表达式模式
            if (!empty($platform['patterns']) && is_array($platform['patterns'])) {
                foreach ($platform['patterns'] as $pattern) {
                    $clean_pattern = function_exists('sanitize_text_field')
                        ? sanitize_text_field($pattern)
                        : trim(strip_tags($pattern));
                    if (!empty($clean_pattern) && $this->isValidRegex($clean_pattern)) {
                        $clean_platforms[$clean_key]['patterns'][] = $clean_pattern;
                    }
                }
            }
        }

        return $clean_platforms;
    }

    /**
     * 清理样式数据
     */
    private function sanitizeStyles($styles) {
        $clean_styles = [];

        foreach ($styles as $key => $style) {
            // 清理键名
            $clean_key = function_exists('sanitize_key')
                ? sanitize_key($key)
                : preg_replace('/[^a-z0-9_\-]/', '', strtolower($key));

            // 清理样式名称
            $name = $style['name'] ?? '';
            $clean_name = function_exists('sanitize_text_field')
                ? sanitize_text_field($name)
                : trim(strip_tags($name));

            $clean_styles[$clean_key] = [
                'name' => $clean_name,
                'css' => $this->sanitizeCSS($style['css'] ?? '')
            ];
        }

        return $clean_styles;
    }

    /**
     * 验证正则表达式
     */
    private function isValidRegex($pattern) {
        return @preg_match('/' . $pattern . '/', '') !== false;
    }

    /**
     * 清理CSS代码
     */
    private function sanitizeCSS($css) {
        // 基本的CSS清理，移除潜在的危险内容
        $css = function_exists('wp_strip_all_tags')
            ? wp_strip_all_tags($css)
            : strip_tags($css);
        $css = str_replace(['<script', '</script', 'javascript:', 'expression('], '', $css);
        return $css;
    }

    /**
     * 添加管理菜单
     */
    public function addAdminMenu() {
        if (function_exists('add_options_page') && function_exists('__')) {
            add_options_page(
                __('Simple CTA 设置', 'simple-cta'),
                __('Simple CTA', 'simple-cta'),
                'manage_options',
                'simple-cta',
                [$this, 'adminPage']
            );
        }
    }

    /**
     * 管理界面初始化
     */
    public function adminInit() {
        // 检查WordPress函数是否可用
        if (!function_exists('register_setting') || !function_exists('add_settings_section') || !function_exists('__')) {
            return;
        }

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

        // 在管理页面也加载CTA样式用于预览
        $this->generateDynamicCSS();
    }

    /**
     * 加载前端脚本和样式
     */
    public function enqueueScripts() {
        $settings = $this->getCachedSettings();

        if (!empty($settings['load_css']) && $settings['load_css']) {
            // 动态生成CSS
            $this->generateDynamicCSS();
        }

        // 只在有CTA链接的页面加载JavaScript
        if ($this->shouldLoadFrontendJS()) {
            wp_enqueue_script(
                'simple-cta-frontend',
                SIMPLE_CTA_PLUGIN_URL . 'assets/js/frontend.js',
                ['jquery'],
                SIMPLE_CTA_VERSION,
                true
            );
        }
    }

    /**
     * 处理内容，自动检测并添加CTA样式
     */
    public function processContent($content) {
        $settings = $this->getCachedSettings();

        if (empty($settings['enabled']) || !$settings['enabled']) {
            return $content;
        }

        if (empty($settings['auto_detect']) || !$settings['auto_detect']) {
            return $content;
        }

        $platforms = $this->getCachedPlatforms();
        $default_style = !empty($settings['default_style']) ? $settings['default_style'] : 'modern';

        // 处理链接 - 改进的兼容性处理
        $content = preg_replace_callback(
            '/<a\s+([^>]*href=["\']([^"\']+)["\'][^>]*)>([^<]*)<\/a>/i',
            function($matches) use ($platforms, $default_style, $settings) {
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

                    // 智能添加类名，保持其他属性不变
                    if (strpos($attributes, 'class=') !== false) {
                        // 如果已有class属性，追加我们的类
                        $attributes = preg_replace(
                            '/class=["\']([^"\']*)["\']/',
                            'class="$1 ' . $style_class . ' ' . $platform_class . '"',
                            $attributes
                        );
                    } else {
                        // 如果没有class属性，添加新的class属性
                        $attributes .= ' class="' . $style_class . ' ' . $platform_class . '"';
                    }

                    // 检查并处理nofollow属性
                    $add_nofollow = !empty($settings['add_nofollow']) ? $settings['add_nofollow'] : true;

                    if ($add_nofollow) {
                        // 检查是否已经有rel属性
                        if (strpos($attributes, 'rel=') !== false) {
                            // 如果已有rel属性，检查是否包含nofollow
                            if (strpos($attributes, 'nofollow') === false) {
                                // 没有nofollow，添加到现有rel属性中
                                $attributes = preg_replace(
                                    '/rel=["\']([^"\']*)["\']/',
                                    'rel="$1 nofollow"',
                                    $attributes
                                );
                            }
                            // 如果已有nofollow，不做处理
                        } else {
                            // 没有rel属性，添加新的rel="nofollow"
                            $attributes .= ' rel="nofollow"';
                        }
                    }

                    // 添加数据属性标记，便于其他插件识别
                    $attributes .= ' data-simple-cta="' . $detected_platform . '"';

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
        // 预处理URL，提取域名进行快速匹配
        $parsed_url = parse_url($url);
        $domain = $parsed_url['host'] ?? '';

        // Amazon特殊处理 - 更强大的Amazon检测
        if ($this->isAmazonLink($url, $domain)) {
            if (!empty($platforms['amazon']['enabled'])) {
                return 'amazon';
            }
        }

        // eBay特殊处理
        if ($this->isEbayLink($url, $domain)) {
            if (!empty($platforms['ebay']['enabled'])) {
                return 'ebay';
            }
        }

        // Walmart特殊处理
        if ($this->isWalmartLink($url, $domain)) {
            if (!empty($platforms['walmart']['enabled'])) {
                return 'walmart';
            }
        }

        // 如果特殊处理失败，使用正则表达式
        foreach ($platforms as $platform_key => $platform) {
            if (empty($platform['enabled']) || !$platform['enabled']) {
                continue;
            }

            foreach ($platform['patterns'] as $pattern) {
                try {
                    if (preg_match('/' . $pattern . '/i', $url)) {
                        return $platform_key;
                    }
                } catch (Exception) {
                    // 忽略无效的正则表达式
                    continue;
                }
            }
        }

        return false;
    }

    /**
     * 检测是否为Amazon链接
     */
    private function isAmazonLink($url, $domain) {
        // Amazon短链接
        if ($domain === 'amzn.to') {
            return true;
        }

        // Amazon域名检测
        $amazon_domains = [
            'amazon.com', 'www.amazon.com',
            'amazon.co.uk', 'www.amazon.co.uk',
            'amazon.de', 'www.amazon.de',
            'amazon.fr', 'www.amazon.fr',
            'amazon.it', 'www.amazon.it',
            'amazon.es', 'www.amazon.es',
            'amazon.ca', 'www.amazon.ca',
            'amazon.com.au', 'www.amazon.com.au',
            'amazon.in', 'www.amazon.in',
            'amazon.com.mx', 'www.amazon.com.mx',
            'amazon.com.br', 'www.amazon.com.br',
            'amazon.co.jp', 'www.amazon.co.jp'
        ];

        if (in_array($domain, $amazon_domains)) {
            // 进一步检查是否为联盟链接
            return (
                strpos($url, 'tag=') !== false ||  // 联盟标签
                strpos($url, '/dp/') !== false ||  // 产品页面
                strpos($url, '/gp/product/') !== false ||  // 产品页面
                strpos($url, 'associate') !== false  // 联盟相关
            );
        }

        return false;
    }

    /**
     * 检测是否为eBay链接
     */
    private function isEbayLink($url, $domain) {
        $ebay_domains = [
            'ebay.com', 'www.ebay.com',
            'ebay.co.uk', 'www.ebay.co.uk',
            'ebay.de', 'www.ebay.de',
            'rover.ebay.com'
        ];

        if (in_array($domain, $ebay_domains)) {
            return (
                strpos($url, 'campid=') !== false ||
                strpos($url, '/itm/') !== false ||
                strpos($url, 'rover') !== false
            );
        }

        return false;
    }

    /**
     * 检测是否为Walmart链接
     */
    private function isWalmartLink($url, $domain) {
        $walmart_domains = [
            'walmart.com', 'www.walmart.com',
            'linksynergy.walmart.com',
            'goto.walmart.com'
        ];

        if (in_array($domain, $walmart_domains)) {
            return (
                strpos($url, 'u1=') !== false ||
                strpos($url, 'linksynergy') !== false ||
                strpos($url, 'goto.walmart') !== false
            );
        }

        return false;
    }

    /**
     * 生成动态CSS
     */
    private function generateDynamicCSS() {
        $styles = $this->getCachedStyles();

        $css = '';
        foreach ($styles as $style) {
            if (!empty($style['css'])) {
                $css .= $style['css'] . "\n";
            }
        }

        // 输出CSS到头部（前端和管理页面）
        if (!empty($css)) {
            add_action('wp_head', function() use ($css) {
                echo '<style type="text/css" id="simple-cta-dynamic-css">' . $css . '</style>';
            });

            // 在管理页面也输出CSS
            add_action('admin_head', function() use ($css) {
                echo '<style type="text/css" id="simple-cta-admin-dynamic-css">' . $css . '</style>';
            });
        }
    }

    /**
     * 管理页面
     */
    public function adminPage() {
        if (!class_exists('SimpleCTAAdminPage')) {
            require_once SIMPLE_CTA_PLUGIN_PATH . 'admin-page.php';
        }
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

        $response = ['success' => false, 'message' => ''];

        try {
            if (isset($_POST['settings'])) {
                $settings = $this->sanitizeSettings($_POST['settings']);
                update_option('simple_cta_settings', $settings);
                $this->clearCache(); // 清理缓存
                $response['success'] = true;
                $response['message'] = __('设置已保存！', 'simple-cta');
            }

            if (isset($_POST['platforms'])) {
                $platforms = $this->sanitizePlatforms($_POST['platforms']);
                update_option('simple_cta_platforms', $platforms);
                $this->clearCache(); // 清理缓存
                $response['success'] = true;
                $response['message'] = __('平台规则已保存！', 'simple-cta');
            }

            if (isset($_POST['styles'])) {
                $styles = $this->sanitizeStyles($_POST['styles']);
                update_option('simple_cta_styles', $styles);
                $this->clearCache(); // 清理缓存
                $response['success'] = true;
                $response['message'] = __('样式已保存！', 'simple-cta');
            }
        } catch (Exception $e) {
            $response['message'] = __('保存失败：', 'simple-cta') . $e->getMessage();
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

    /**
     * 添加设置链接到插件列表页面
     */
    public function addSettingsLink($links) {
        if (function_exists('admin_url')) {
            $settings_link = '<a href="' . admin_url('options-general.php?page=simple-cta') . '">' . __('Settings', 'simple-cta') . '</a>';
            // 使用array_push而不是array_unshift，让Settings在Deactivate后面
            $links[] = $settings_link;
        }
        return $links;
    }

    /**
     * 在插件设置页面隐藏WordPress版本信息
     */
    public function hideWordPressFooter() {
        if (!function_exists('get_current_screen') || !function_exists('add_filter')) {
            return;
        }

        $screen = get_current_screen();
        if ($screen && $screen->id === 'settings_page_simple-cta') {
            // 移除WordPress版本信息
            add_filter('admin_footer_text', '__return_empty_string', 11);
            add_filter('update_footer', '__return_empty_string', 11);
        }
    }
}

// 注意：插件初始化在 simple_cta_init() 函数中进行，不要在此处重复初始化
