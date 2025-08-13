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
        if (!current_user_can('manage_options')) {
            wp_die(__('您没有权限访问此页面。', 'simple-cta'));
        }
        
        // 处理表单提交
        if (isset($_POST['submit']) && wp_verify_nonce($_POST['simple_cta_nonce'], 'simple_cta_save_settings')) {
            self::handleFormSubmission();
        }
        
        // 获取当前设置
        $platforms = get_option('simple_cta_platforms', []);
        $styles = get_option('simple_cta_styles', []);
        $settings = get_option('simple_cta_settings', []);
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="simple-cta-admin-container">
                <div class="simple-cta-tabs">
                    <nav class="nav-tab-wrapper">
                        <a href="#general" class="nav-tab nav-tab-active"><?php _e('基本设置', 'simple-cta'); ?></a>
                        <a href="#platforms" class="nav-tab"><?php _e('平台规则', 'simple-cta'); ?></a>
                        <a href="#styles" class="nav-tab"><?php _e('CTA样式', 'simple-cta'); ?></a>
                        <a href="#preview" class="nav-tab"><?php _e('预览测试', 'simple-cta'); ?></a>
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
                                                        <?php foreach ($platform['patterns'] as $index => $pattern): ?>
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
                        
                        <!-- 预览测试标签页 -->
                        <div id="preview" class="tab-content">
                            <h2><?php _e('预览测试', 'simple-cta'); ?></h2>
                            <p><?php _e('测试链接检测和样式应用效果。', 'simple-cta'); ?></p>
                            
                            <div class="preview-section">
                                <h3><?php _e('链接测试', 'simple-cta'); ?></h3>
                                <textarea id="test-content" rows="5" cols="80" placeholder="<?php _e('在此输入包含链接的HTML内容进行测试...', 'simple-cta'); ?>"></textarea>
                                <br><br>
                                <button type="button" class="button button-primary" id="test-links"><?php _e('测试检测', 'simple-cta'); ?></button>
                                
                                <div id="test-results" style="margin-top: 20px;"></div>
                            </div>
                            
                            <div class="preview-section">
                                <h3><?php _e('样式预览', 'simple-cta'); ?></h3>
                                <div class="style-previews">
                                    <?php foreach ($styles as $style_key => $style): ?>
                                        <div class="preview-item">
                                            <h4><?php echo esc_html($style['name']); ?></h4>
                                            <a href="#" class="simple-cta <?php echo esc_attr($style_key); ?>" onclick="return false;">
                                                <?php _e('示例按钮', 'simple-cta'); ?>
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
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
            
            // 测试链接检测
            $('#test-links').click(function() {
                var content = $('#test-content').val();
                if (!content) {
                    alert('请输入测试内容');
                    return;
                }
                
                // 这里可以添加AJAX调用来测试链接检测
                $('#test-results').html('<p>测试功能开发中...</p>');
            });
        });
        </script>
        <?php
    }
    
    /**
     * 处理表单提交
     */
    private static function handleFormSubmission() {
        // 保存设置
        if (isset($_POST['settings'])) {
            update_option('simple_cta_settings', $_POST['settings']);
        }
        
        // 保存平台规则
        if (isset($_POST['platforms'])) {
            update_option('simple_cta_platforms', $_POST['platforms']);
        }
        
        // 保存样式
        if (isset($_POST['styles'])) {
            update_option('simple_cta_styles', $_POST['styles']);
        }
        
        // 显示成功消息
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>' . __('设置已保存！', 'simple-cta') . '</p></div>';
        });
    }
}
?>
