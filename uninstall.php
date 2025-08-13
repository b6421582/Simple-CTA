<?php
/**
 * Simple CTA 卸载脚本
 * 
 * 当插件被删除时执行此脚本
 */

// 防止直接访问
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * 清理插件数据
 */
function simple_cta_uninstall_cleanup() {
    
    // 删除插件选项
    $options_to_delete = [
        'simple_cta_platforms',
        'simple_cta_styles', 
        'simple_cta_settings',
        'simple_cta_version'
    ];
    
    foreach ($options_to_delete as $option) {
        if (function_exists('delete_option')) {
            delete_option($option);
        }

        // 同时删除多站点的选项
        if (function_exists('delete_site_option')) {
            delete_site_option($option);
        }
    }

    // 清理用户元数据（如果有的话）
    if (function_exists('delete_metadata')) {
        delete_metadata('user', 0, 'simple_cta_user_preferences', '', true);

        // 清理文章元数据（如果有的话）
        delete_metadata('post', 0, 'simple_cta_custom_style', '', true);
        delete_metadata('post', 0, 'simple_cta_disabled', '', true);
    }

    // 清理临时数据
    if (function_exists('wp_cache_delete')) {
        wp_cache_delete('simple_cta_platforms', 'simple_cta');
        wp_cache_delete('simple_cta_styles', 'simple_cta');
        wp_cache_delete('simple_cta_settings', 'simple_cta');
    }
    
    // 注意：这个插件不需要创建额外的数据库表，所以无需清理表
    
    // 清理定时任务（如果有的话）
    if (function_exists('wp_clear_scheduled_hook')) {
        wp_clear_scheduled_hook('simple_cta_daily_cleanup');
        wp_clear_scheduled_hook('simple_cta_weekly_stats');
    }

    // 清理上传的文件（如果有的话）
    if (function_exists('wp_upload_dir')) {
        $upload_dir = wp_upload_dir();
        $simple_cta_dir = $upload_dir['basedir'] . '/simple-cta/';

        if (is_dir($simple_cta_dir)) {
            simple_cta_remove_directory($simple_cta_dir);
        }
    }

}

/**
 * 递归删除目录
 */
function simple_cta_remove_directory($dir) {
    if (!is_dir($dir)) {
        return false;
    }
    
    $files = array_diff(scandir($dir), ['.', '..']);
    
    foreach ($files as $file) {
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        
        if (is_dir($path)) {
            simple_cta_remove_directory($path);
        } else {
            unlink($path);
        }
    }
    
    return rmdir($dir);
}

// 执行卸载清理
simple_cta_uninstall_cleanup();
?>
