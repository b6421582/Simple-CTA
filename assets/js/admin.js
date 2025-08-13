/**
 * Simple CTA 管理界面JavaScript
 */

(function($) {
    'use strict';
    
    // 当DOM准备就绪时执行
    $(document).ready(function() {
        
        // 初始化管理界面
        initAdminInterface();
        
        // 绑定事件处理器
        bindEventHandlers();
        
        // 初始化样式预览
        initStylePreviews();
        
    });
    
    /**
     * 初始化管理界面
     */
    function initAdminInterface() {
        
        // 标签页切换功能
        $('.nav-tab').on('click', function(e) {
            e.preventDefault();
            
            var target = $(this).attr('href');
            
            // 更新标签页状态
            $('.nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            
            // 显示对应内容
            $('.tab-content').removeClass('active');
            $(target).addClass('active');
            
            // 保存当前标签页到localStorage
            localStorage.setItem('simple_cta_active_tab', target);
        });
        
        // 恢复上次访问的标签页
        var activeTab = localStorage.getItem('simple_cta_active_tab');
        if (activeTab && $(activeTab).length) {
            $('.nav-tab[href="' + activeTab + '"]').trigger('click');
        }
        
        // 初始化代码编辑器（如果需要）
        initCodeEditors();
    }
    
    /**
     * 绑定事件处理器
     */
    function bindEventHandlers() {
        
        // 添加新的匹配规则
        $(document).on('click', '.add-pattern', function() {
            var platform = $(this).data('platform');
            var patternsList = $(this).siblings('.patterns-list');
            
            var html = '<div class="pattern-item">' +
                '<input type="text" name="platforms[' + platform + '][patterns][]" ' +
                'class="large-text" placeholder="' + simpleCTAAdmin.strings.regexPlaceholder + '">' +
                '<button type="button" class="button remove-pattern">' + 
                simpleCTAAdmin.strings.remove + '</button>' +
                '</div>';
            
            patternsList.append(html);
        });
        
        // 删除匹配规则
        $(document).on('click', '.remove-pattern', function() {
            if (confirm(simpleCTAAdmin.strings.confirmDelete)) {
                $(this).closest('.pattern-item').remove();
            }
        });
        
        // 添加新平台
        $('#add-new-platform').on('click', function() {
            var platformKey = 'custom_' + Date.now();
            var html = createPlatformHTML(platformKey, {
                name: '新平台',
                patterns: [''],
                class: 'custom-cta',
                enabled: true
            });
            
            $('.platforms-container').append(html);
        });
        
        // 添加新样式
        $('#add-new-style').on('click', function() {
            var styleKey = 'custom_' + Date.now();
            var html = createStyleHTML(styleKey, {
                name: '新样式',
                css: '.simple-cta.custom {\n    /* 在此添加您的CSS样式 */\n}'
            });
            
            $('.styles-container').append(html);
        });
        
        // 测试链接检测
        $('#test-links').on('click', function() {
            var content = $('#test-content').val().trim();
            
            if (!content) {
                alert(simpleCTAAdmin.strings.noContent);
                return;
            }
            
            testLinkDetection(content);
        });
        
        // 实时预览样式
        $(document).on('input', '.style-item textarea[name*="[css]"]', function() {
            var $textarea = $(this);
            var $preview = $textarea.closest('.style-item').find('.style-preview a');
            var css = $textarea.val();
            
            // 更新预览样式
            updateStylePreview($preview, css);
        });
        
        // 保存设置
        $('form').on('submit', function(e) {
            e.preventDefault();
            saveSettings($(this));
        });
    }
    
    /**
     * 初始化样式预览
     */
    function initStylePreviews() {
        $('.style-item').each(function() {
            var $item = $(this);
            var $textarea = $item.find('textarea[name*="[css]"]');
            var $preview = $item.find('.style-preview a');
            var css = $textarea.val();
            
            updateStylePreview($preview, css);
        });
    }
    
    /**
     * 更新样式预览
     */
    function updateStylePreview($preview, css) {
        // 移除旧的样式
        $('#simple-cta-preview-style').remove();
        
        // 添加新的样式
        if (css.trim()) {
            $('<style id="simple-cta-preview-style">' + css + '</style>').appendTo('head');
        }
    }
    
    /**
     * 初始化代码编辑器
     */
    function initCodeEditors() {
        // 如果有CodeMirror，可以在这里初始化
        $('.style-item textarea.code').each(function() {
            var $textarea = $(this);
            
            // 添加行号和语法高亮（简单版本）
            $textarea.on('input', function() {
                // 这里可以添加简单的语法高亮逻辑
            });
        });
    }
    
    /**
     * 创建平台HTML
     */
    function createPlatformHTML(platformKey, platform) {
        var patternsHTML = '';
        
        if (platform.patterns && platform.patterns.length > 0) {
            platform.patterns.forEach(function(pattern) {
                patternsHTML += '<div class="pattern-item">' +
                    '<input type="text" name="platforms[' + platformKey + '][patterns][]" ' +
                    'value="' + pattern + '" class="large-text">' +
                    '<button type="button" class="button remove-pattern">删除</button>' +
                    '</div>';
            });
        }
        
        return '<div class="platform-item" data-platform="' + platformKey + '">' +
            '<h3>' +
            '<label>' +
            '<input type="checkbox" name="platforms[' + platformKey + '][enabled]" value="1" ' +
            (platform.enabled ? 'checked' : '') + '>' +
            platform.name +
            '</label>' +
            '</h3>' +
            '<table class="form-table">' +
            '<tr>' +
            '<th>平台名称</th>' +
            '<td><input type="text" name="platforms[' + platformKey + '][name]" ' +
            'value="' + platform.name + '" class="regular-text"></td>' +
            '</tr>' +
            '<tr>' +
            '<th>CSS类名</th>' +
            '<td><input type="text" name="platforms[' + platformKey + '][class]" ' +
            'value="' + platform.class + '" class="regular-text"></td>' +
            '</tr>' +
            '<tr>' +
            '<th>匹配规则</th>' +
            '<td>' +
            '<div class="patterns-list">' + patternsHTML + '</div>' +
            '<button type="button" class="button add-pattern" data-platform="' + platformKey + '">添加规则</button>' +
            '</td>' +
            '</tr>' +
            '</table>' +
            '</div>';
    }
    
    /**
     * 创建样式HTML
     */
    function createStyleHTML(styleKey, style) {
        return '<div class="style-item" data-style="' + styleKey + '">' +
            '<h3>' + style.name + '</h3>' +
            '<table class="form-table">' +
            '<tr>' +
            '<th>样式名称</th>' +
            '<td><input type="text" name="styles[' + styleKey + '][name]" ' +
            'value="' + style.name + '" class="regular-text"></td>' +
            '</tr>' +
            '<tr>' +
            '<th>CSS代码</th>' +
            '<td>' +
            '<textarea name="styles[' + styleKey + '][css]" rows="10" cols="80" ' +
            'class="large-text code">' + style.css + '</textarea>' +
            '<p class="description">使用 .simple-cta.样式名 作为选择器。</p>' +
            '</td>' +
            '</tr>' +
            '</table>' +
            '<div class="style-preview">' +
            '<h4>预览</h4>' +
            '<a href="#" class="simple-cta ' + styleKey + '" onclick="return false;">示例CTA按钮</a>' +
            '</div>' +
            '</div>';
    }
    
    /**
     * 测试链接检测
     */
    function testLinkDetection(content) {
        var $results = $('#test-results');
        
        // 显示加载状态
        $results.html('<div class="simple-cta-loading"></div> ' + simpleCTAAdmin.strings.testing);
        
        // 发送AJAX请求
        $.ajax({
            url: simpleCTAAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'simple_cta_test_detection',
                nonce: simpleCTAAdmin.nonce,
                content: content
            },
            success: function(response) {
                if (response.success) {
                    displayTestResults(response.data);
                } else {
                    $results.html('<div class="simple-cta-message error">' + 
                        (response.data || simpleCTAAdmin.strings.error) + '</div>');
                }
            },
            error: function() {
                $results.html('<div class="simple-cta-message error">' + 
                    simpleCTAAdmin.strings.error + '</div>');
            }
        });
    }
    
    /**
     * 显示测试结果
     */
    function displayTestResults(data) {
        var $results = $('#test-results');
        var html = '';
        
        if (data.detected && data.detected.length > 0) {
            html += '<div class="simple-cta-message success">检测到 ' + data.detected.length + ' 个联盟链接</div>';
            html += '<div class="detected-links">';
            
            data.detected.forEach(function(link) {
                html += '<div class="detected-link">' +
                    '<strong>平台:</strong> ' + link.platform + '<br>' +
                    '<strong>链接:</strong> <code>' + link.url + '</code><br>' +
                    '<strong>应用样式:</strong> ' + link.classes + '<br>' +
                    '<strong>预览:</strong> <a href="' + link.url + '" class="' + link.classes + '" onclick="return false;">' + link.text + '</a>' +
                    '</div><hr>';
            });
            
            html += '</div>';
        } else {
            html += '<div class="simple-cta-message">' + simpleCTAAdmin.strings.noResults + '</div>';
        }
        
        if (data.processed_content) {
            html += '<h4>处理后的内容:</h4>';
            html += '<div class="processed-content" style="background: #f9f9f9; padding: 15px; border: 1px solid #ddd;">' + 
                data.processed_content + '</div>';
        }
        
        $results.html(html);
    }
    
    /**
     * 保存设置
     */
    function saveSettings($form) {
        var formData = $form.serialize();
        
        // 显示保存状态
        var $submitButton = $form.find('input[type="submit"]');
        var originalText = $submitButton.val();
        $submitButton.val('保存中...').prop('disabled', true);
        
        // 发送AJAX请求
        $.ajax({
            url: simpleCTAAdmin.ajaxUrl,
            type: 'POST',
            data: formData + '&action=simple_cta_save_settings&nonce=' + simpleCTAAdmin.nonce,
            success: function(response) {
                if (response.success) {
                    showNotice(simpleCTAAdmin.strings.saved, 'success');
                } else {
                    showNotice(response.data || simpleCTAAdmin.strings.error, 'error');
                }
            },
            error: function() {
                showNotice(simpleCTAAdmin.strings.error, 'error');
            },
            complete: function() {
                $submitButton.val(originalText).prop('disabled', false);
            }
        });
    }
    
    /**
     * 显示通知消息
     */
    function showNotice(message, type) {
        var $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
        $('.wrap h1').after($notice);
        
        // 自动隐藏
        setTimeout(function() {
            $notice.fadeOut();
        }, 3000);
    }
    
})(jQuery);
