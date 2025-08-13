<?php
/**
 * Simple CTA 管理界面页面
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 管理界面页面类
 */
class SimpleCTAAdminPage {
    
    /**
     * 渲染管理页面
     */
    public static function render() {
        // 检查用户权限
        if (!function_exists('current_user_can') || !current_user_can('manage_options')) {
            if (function_exists('wp_die') && function_exists('__')) {
                wp_die(__('您没有权限访问此页面。', 'simple-cta'));
            } else {
                die('您没有权限访问此页面。');
            }
        }
        
        // 处理表单提交
        if (isset($_POST['submit']) && isset($_POST['simple_cta_nonce']) &&
            function_exists('wp_verify_nonce') &&
            wp_verify_nonce($_POST['simple_cta_nonce'], 'simple_cta_save_settings')) {
            self::handleFormSubmission();
        }
        
        // 获取当前设置
        $platforms = function_exists('get_option') ? get_option('simple_cta_platforms', []) : [];
        $styles = function_exists('get_option') ? get_option('simple_cta_styles', []) : [];
        $settings = function_exists('get_option') ? get_option('simple_cta_settings', []) : [];
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="simple-cta-admin-container">
                <div class="simple-cta-tabs">
                    <nav class="nav-tab-wrapper">
                        <a href="#general" class="nav-tab nav-tab-active"><?php _e('基本设置', 'simple-cta'); ?></a>
                        <a href="#platforms" class="nav-tab"><?php _e('平台规则', 'simple-cta'); ?></a>
                        <a href="#styles" class="nav-tab"><?php _e('CTA样式', 'simple-cta'); ?></a>
                    </nav>
                    
                    <form method="post" action="">
                        <?php wp_nonce_field('simple_cta_save_settings', 'simple_cta_nonce'); ?>
                        
                        <!-- 基本设置标签页 -->
                        <div id="general" class="tab-content active">
                            <h2><?php _e('基本设置', 'simple-cta'); ?></h2>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('启用插件', 'simple-cta'); ?></th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="settings[enabled]" value="1" 
                                                <?php checked(!empty($settings['enabled']), true); ?>>
                                            <?php _e('启用Simple CTA功能', 'simple-cta'); ?>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('自动检测', 'simple-cta'); ?></th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="settings[auto_detect]" value="1" 
                                                <?php checked(!empty($settings['auto_detect']), true); ?>>
                                            <?php _e('自动检测联盟链接并应用CTA样式', 'simple-cta'); ?>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('加载CSS', 'simple-cta'); ?></th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="settings[load_css]" value="1"
                                                <?php checked(!empty($settings['load_css']), true); ?>>
                                            <?php _e('自动加载CTA样式CSS', 'simple-cta'); ?>
                                        </label>
                                        <p class="description"><?php _e('如果您的主题已包含CTA样式，可以关闭此选项。', 'simple-cta'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('添加Nofollow', 'simple-cta'); ?></th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="settings[add_nofollow]" value="1"
                                                <?php checked(!empty($settings['add_nofollow']), true); ?>>
                                            <?php _e('自动为联盟链接添加 rel="nofollow" 属性', 'simple-cta'); ?>
                                        </label>
                                        <p class="description"><?php _e('如果链接已有nofollow属性则不会重复添加。建议开启以符合SEO最佳实践。', 'simple-cta'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('默认样式', 'simple-cta'); ?></th>
                                    <td>
                                        <select name="settings[default_style]">
                                            <?php foreach ($styles as $style_key => $style): ?>
                                                <option value="<?php echo esc_attr($style_key); ?>" 
                                                    <?php selected(!empty($settings['default_style']) ? $settings['default_style'] : 'modern', $style_key); ?>>
                                                    <?php echo esc_html($style['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <p class="description"><?php _e('选择自动检测时使用的默认CTA样式。', 'simple-cta'); ?></p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <!-- 平台规则标签页 -->
                        <div id="platforms" class="tab-content">
                            <h2><?php _e('平台规则管理', 'simple-cta'); ?></h2>
                            <p><?php _e('管理联盟平台的检测规则，支持正则表达式。', 'simple-cta'); ?></p>
                            
                            <div class="platforms-container">
                                <?php foreach ($platforms as $platform_key => $platform): ?>
                                    <div class="platform-item" data-platform="<?php echo esc_attr($platform_key); ?>">
                                        <h3>
                                            <label>
                                                <input type="checkbox" name="platforms[<?php echo esc_attr($platform_key); ?>][enabled]" value="1" 
                                                    <?php checked(!empty($platform['enabled']), true); ?>>
                                                <?php echo esc_html($platform['name']); ?>
                                            </label>
                                        </h3>
                                        
                                        <table class="form-table">
                                            <tr>
                                                <th><?php _e('平台名称', 'simple-cta'); ?></th>
                                                <td>
                                                    <input type="text" name="platforms[<?php echo esc_attr($platform_key); ?>][name]" 
                                                        value="<?php echo esc_attr($platform['name']); ?>" class="regular-text">
                                                </td>
                                            </tr>
                                            <tr>
                                                <th><?php _e('CSS类名', 'simple-cta'); ?></th>
                                                <td>
                                                    <input type="text" name="platforms[<?php echo esc_attr($platform_key); ?>][class]" 
                                                        value="<?php echo esc_attr($platform['class']); ?>" class="regular-text">
                                                </td>
                                            </tr>
                                            <tr>
                                                <th><?php _e('匹配规则', 'simple-cta'); ?></th>
                                                <td>
                                                    <div class="patterns-list">
                                                        <?php foreach ($platform['patterns'] as $pattern): ?>
                                                            <div class="pattern-item">
                                                                <input type="text" 
                                                                    name="platforms[<?php echo esc_attr($platform_key); ?>][patterns][]" 
                                                                    value="<?php echo esc_attr($pattern); ?>" 
                                                                    class="large-text" placeholder="<?php _e('正则表达式', 'simple-cta'); ?>">
                                                                <button type="button" class="button remove-pattern"><?php _e('删除', 'simple-cta'); ?></button>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <button type="button" class="button add-pattern" data-platform="<?php echo esc_attr($platform_key); ?>">
                                                        <?php _e('添加规则', 'simple-cta'); ?>
                                                    </button>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="add-platform-section">
                                <h3><?php _e('添加新平台', 'simple-cta'); ?></h3>
                                <button type="button" class="button button-secondary" id="add-new-platform">
                                    <?php _e('添加新平台', 'simple-cta'); ?>
                                </button>
                            </div>
                        </div>
                        
                        <!-- CTA样式标签页 -->
                        <div id="styles" class="tab-content">
                            <h2><?php _e('CTA样式管理', 'simple-cta'); ?></h2>
                            <p><?php _e('自定义CTA按钮的样式，支持CSS代码编辑。', 'simple-cta'); ?></p>
                            
                            <div class="styles-container">
                                <?php foreach ($styles as $style_key => $style): ?>
                                    <div class="style-item" data-style="<?php echo esc_attr($style_key); ?>">
                                        <h3><?php echo esc_html($style['name']); ?></h3>
                                        
                                        <table class="form-table">
                                            <tr>
                                                <th><?php _e('样式名称', 'simple-cta'); ?></th>
                                                <td>
                                                    <input type="text" name="styles[<?php echo esc_attr($style_key); ?>][name]" 
                                                        value="<?php echo esc_attr($style['name']); ?>" class="regular-text">
                                                </td>
                                            </tr>
                                            <tr>
                                                <th><?php _e('CSS代码', 'simple-cta'); ?></th>
                                                <td>
                                                    <textarea name="styles[<?php echo esc_attr($style_key); ?>][css]" 
                                                        rows="10" cols="80" class="large-text code"><?php echo esc_textarea($style['css']); ?></textarea>
                                                    <p class="description"><?php _e('使用 .simple-cta.样式名 作为选择器。', 'simple-cta'); ?></p>
                                                </td>
                                            </tr>
                                        </table>
                                        
                                        <div class="style-preview">
                                            <h4><?php _e('预览', 'simple-cta'); ?></h4>
                                            <a href="#" class="simple-cta <?php echo esc_attr($style_key); ?>" onclick="return false;">
                                                <?php _e('示例CTA按钮', 'simple-cta'); ?>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="add-style-section">
                                <h3><?php _e('添加新样式', 'simple-cta'); ?></h3>
                                <button type="button" class="button button-secondary" id="add-new-style">
                                    <?php _e('添加新样式', 'simple-cta'); ?>
                                </button>
                            </div>
                        </div>

                        
                        <?php submit_button(__('保存设置', 'simple-cta')); ?>
                    </form>
                </div>
            </div>
        </div>
        
        <style>
            .simple-cta-admin-container { max-width: 1200px; }
            .simple-cta-tabs .tab-content { display: none; padding: 20px 0; }
            .simple-cta-tabs .tab-content.active { display: block; }
            .platform-item, .style-item { border: 1px solid #ddd; padding: 20px; margin: 20px 0; background: #fff; }
            .pattern-item { margin: 5px 0; }
            .pattern-item input { width: 70%; }
            .style-preview { margin-top: 20px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; }
            .preview-section { margin: 30px 0; }
            .style-previews { display: flex; flex-wrap: wrap; gap: 20px; }
            .preview-item { padding: 15px; border: 1px solid #ddd; background: #fff; }

            /* 隐藏WordPress版本信息 */
            body.settings_page_simple-cta #wpfooter,
            body.settings_page_simple-cta .wp-admin #wpfooter {
                display: none !important;
            }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // 标签页切换
            $('.nav-tab').click(function(e) {
                e.preventDefault();
                var target = $(this).attr('href');
                $('.nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                $('.tab-content').removeClass('active');
                $(target).addClass('active');
            });
            
            // 添加/删除规则
            $('.add-pattern').click(function() {
                var platform = $(this).data('platform');
                var html = '<div class="pattern-item">' +
                    '<input type="text" name="platforms[' + platform + '][patterns][]" class="large-text" placeholder="正则表达式">' +
                    '<button type="button" class="button remove-pattern">删除</button>' +
                    '</div>';
                $(this).prev('.patterns-list').append(html);
            });
            
            $(document).on('click', '.remove-pattern', function() {
                $(this).parent().remove();
            });

        });
        </script>
        <?php
    }
    
    /**
     * 处理表单提交
     */
    private static function handleFormSubmission() {
        // 确保WordPress函数可用
        if (!function_exists('update_option') || !function_exists('add_action') || !function_exists('__')) {
            return;
        }

        try {
            // 保存设置
            if (isset($_POST['settings'])) {
                $settings = self::sanitizeSettings($_POST['settings']);
                update_option('simple_cta_settings', $settings);
            }

            // 保存平台规则
            if (isset($_POST['platforms'])) {
                $platforms = self::sanitizePlatforms($_POST['platforms']);
                update_option('simple_cta_platforms', $platforms);
            }

            // 保存样式
            if (isset($_POST['styles'])) {
                $styles = self::sanitizeStyles($_POST['styles']);
                update_option('simple_cta_styles', $styles);
            }

            // 清理插件缓存（如果插件实例存在）
            if (class_exists('SimpleCTA')) {
                $instance = SimpleCTA::getInstance();
                if (method_exists($instance, 'clearCache')) {
                    $instance->clearCache();
                }
            }

            // 显示成功消息
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('设置已保存！', 'simple-cta') . '</p></div>';
            });
        } catch (Exception $e) {
            // 显示错误消息
            add_action('admin_notices', function() use ($e) {
                echo '<div class="notice notice-error is-dismissible"><p>' . __('保存失败：', 'simple-cta') . esc_html($e->getMessage()) . '</p></div>';
            });
        }
    }

    /**
     * 清理设置数据
     */
    private static function sanitizeSettings($settings) {
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
    private static function sanitizePlatforms($platforms) {
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
                    if (!empty($clean_pattern) && self::isValidRegex($clean_pattern)) {
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
    private static function sanitizeStyles($styles) {
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
                'css' => self::sanitizeCSS($style['css'] ?? '')
            ];
        }

        return $clean_styles;
    }

    /**
     * 验证正则表达式
     */
    private static function isValidRegex($pattern) {
        return @preg_match('/' . $pattern . '/', '') !== false;
    }

    /**
     * 清理CSS代码
     */
    private static function sanitizeCSS($css) {
        // 基本的CSS清理，移除潜在的危险内容
        $css = function_exists('wp_strip_all_tags')
            ? wp_strip_all_tags($css)
            : strip_tags($css);
        $css = str_replace(['<script', '</script', 'javascript:', 'expression('], '', $css);
        return $css;
    }
}
?>
