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
        delete_option($option);
        
        // 同时删除多站点的选项
        delete_site_option($option);
    }
    
    // 清理用户元数据（如果有的话）
    delete_metadata('user', 0, 'simple_cta_user_preferences', '', true);
    
    // 清理文章元数据（如果有的话）
    delete_metadata('post', 0, 'simple_cta_custom_style', '', true);
    delete_metadata('post', 0, 'simple_cta_disabled', '', true);
    
    // 清理临时数据
    wp_cache_delete('simple_cta_platforms', 'simple_cta');
    wp_cache_delete('simple_cta_styles', 'simple_cta');
    wp_cache_delete('simple_cta_settings', 'simple_cta');
    
    // 清理数据库中的临时表（如果创建了的话）
    global $wpdb;
    
    // 注意：这里只是示例，实际上这个插件不需要创建额外的表
    // $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}simple_cta_stats");
    
    // 清理定时任务（如果有的话）
    wp_clear_scheduled_hook('simple_cta_daily_cleanup');
    wp_clear_scheduled_hook('simple_cta_weekly_stats');
    
    // 清理上传的文件（如果有的话）
    $upload_dir = wp_upload_dir();
    $simple_cta_dir = $upload_dir['basedir'] . '/simple-cta/';
    
    if (is_dir($simple_cta_dir)) {
        simple_cta_remove_directory($simple_cta_dir);
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

/**
 * 记录卸载日志（可选）
 */
function simple_cta_log_uninstall() {
    $log_data = [
        'timestamp' => current_time('mysql'),
        'site_url' => get_site_url(),
        'wp_version' => get_bloginfo('version'),
        'php_version' => PHP_VERSION,
        'plugin_version' => get_option('simple_cta_version', '1.0.0')
    ];
    
    // 可以发送到远程服务器进行统计（可选）
    // wp_remote_post('https://your-stats-server.com/uninstall', [
    //     'body' => $log_data,
    //     'timeout' => 5
    // ]);
}

// 执行卸载清理
simple_cta_uninstall_cleanup();

// 记录卸载（可选）
// simple_cta_log_uninstall();
?>
