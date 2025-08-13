/**
 * Simple CTA 前端JavaScript
 */

(function($) {
    'use strict';
    
    // 当DOM准备就绪时执行
    $(document).ready(function() {
        
        // 初始化CTA功能
        initSimpleCTA();
        
        // 添加点击跟踪（可选）
        trackCTAClicks();
        
    });
    
    /**
     * 初始化Simple CTA功能
     */
    function initSimpleCTA() {
        
        // 为CTA按钮添加额外的交互效果
        $('.simple-cta').each(function() {
            var $cta = $(this);
            
            // 添加数据属性用于跟踪
            if (!$cta.attr('data-cta-initialized')) {
                $cta.attr('data-cta-initialized', 'true');
                
                // 添加hover效果增强
                $cta.on('mouseenter', function() {
                    $(this).addClass('cta-hover');
                }).on('mouseleave', function() {
                    $(this).removeClass('cta-hover');
                });
                
                // 添加点击效果
                $cta.on('mousedown', function() {
                    $(this).addClass('cta-active');
                }).on('mouseup mouseleave', function() {
                    $(this).removeClass('cta-active');
                });
            }
        });
        
        // 检查是否有新添加的链接需要处理
        processNewLinks();
    }
    
    /**
     * 处理新添加的链接（用于动态内容）
     */
    function processNewLinks() {
        
        // 监听DOM变化，处理动态添加的内容
        if (window.MutationObserver) {
            var observer = new MutationObserver(function(mutations) {
                var shouldProcess = false;
                
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                        for (var i = 0; i < mutation.addedNodes.length; i++) {
                            var node = mutation.addedNodes[i];
                            if (node.nodeType === 1) { // Element node
                                if (node.tagName === 'A' || $(node).find('a').length > 0) {
                                    shouldProcess = true;
                                    break;
                                }
                            }
                        }
                    }
                });
                
                if (shouldProcess) {
                    // 延迟处理，避免频繁执行
                    setTimeout(function() {
                        initSimpleCTA();
                    }, 100);
                }
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }
    }
    
    /**
     * 跟踪CTA点击事件
     */
    function trackCTAClicks() {
        
        $(document).on('click', '.simple-cta', function(e) {
            var $cta = $(this);
            var href = $cta.attr('href');
            var text = $cta.text().trim();
            var classes = $cta.attr('class');
            
            // 提取平台信息
            var platform = 'unknown';
            var platformClasses = ['amazon-cta', 'ebay-cta', 'walmart-cta', 'cj-cta', 'shareasale-cta', 'impact-cta', 'rakuten-cta'];
            
            for (var i = 0; i < platformClasses.length; i++) {
                if (classes && classes.indexOf(platformClasses[i]) !== -1) {
                    platform = platformClasses[i].replace('-cta', '');
                    break;
                }
            }
            
            // 发送跟踪数据（可以发送到Google Analytics、自定义跟踪等）
            if (typeof gtag !== 'undefined') {
                gtag('event', 'cta_click', {
                    'event_category': 'Simple CTA',
                    'event_label': platform,
                    'value': text,
                    'custom_parameter_1': href
                });
            }
            
            // 控制台日志（开发调试用）
            if (window.console && console.log) {
                console.log('Simple CTA Click:', {
                    platform: platform,
                    text: text,
                    href: href,
                    timestamp: new Date().toISOString()
                });
            }
            
            // 可以在这里添加其他跟踪代码
            // 例如：Facebook Pixel、自定义分析等
            
        });
    }
    
    /**
     * 工具函数：检测链接平台
     */
    function detectLinkPlatform(url) {
        var platforms = {
            'amazon': [
                /amzn\.to\/[a-zA-Z0-9]+/i,
                /amazon\.[a-z.]+\/.*[?&]tag=/i,
                /amazon\.[a-z.]+\/dp\/[A-Z0-9]+/i,
                /amazon\.[a-z.]+\/gp\/product\/[A-Z0-9]+/i
            ],
            'ebay': [
                /rover\.ebay\.[a-z.]+\/rover\//i,
                /ebay\.[a-z.]+\/itm\//i,
                /ebay\.[a-z.]+\/.*[?&]campid=/i
            ],
            'walmart': [
                /linksynergy\.walmart\.com/i,
                /goto\.walmart\.com/i,
                /walmart\.com\/.*[?&]u1=/i
            ]
            // 可以添加更多平台
        };
        
        for (var platform in platforms) {
            for (var i = 0; i < platforms[platform].length; i++) {
                if (platforms[platform][i].test(url)) {
                    return platform;
                }
            }
        }
        
        return null;
    }
    
    /**
     * 手动应用CTA样式到指定链接
     */
    window.SimpleCTA = {
        
        /**
         * 为指定选择器的链接应用CTA样式
         */
        applyStyle: function(selector, style) {
            style = style || 'modern';
            $(selector).addClass('simple-cta ' + style);
            initSimpleCTA();
        },
        
        /**
         * 移除CTA样式
         */
        removeStyle: function(selector) {
            $(selector).removeClass(function(index, className) {
                return (className.match(/(^|\s)simple-cta\S*/g) || []).join(' ');
            });
        },
        
        /**
         * 检测并自动应用样式
         */
        autoDetect: function(container) {
            container = container || document;
            
            $(container).find('a').each(function() {
                var $link = $(this);
                var href = $link.attr('href');
                
                if (href && !$link.hasClass('simple-cta')) {
                    var platform = detectLinkPlatform(href);
                    if (platform) {
                        $link.addClass('simple-cta modern ' + platform + '-cta');
                    }
                }
            });
            
            initSimpleCTA();
        },
        
        /**
         * 获取统计信息
         */
        getStats: function() {
            var stats = {
                total: $('.simple-cta').length,
                platforms: {}
            };
            
            $('.simple-cta').each(function() {
                var classes = $(this).attr('class');
                var platformClasses = ['amazon-cta', 'ebay-cta', 'walmart-cta', 'cj-cta', 'shareasale-cta', 'impact-cta', 'rakuten-cta'];
                
                for (var i = 0; i < platformClasses.length; i++) {
                    if (classes && classes.indexOf(platformClasses[i]) !== -1) {
                        var platform = platformClasses[i].replace('-cta', '');
                        stats.platforms[platform] = (stats.platforms[platform] || 0) + 1;
                        break;
                    }
                }
            });
            
            return stats;
        }
    };
    
})(jQuery);
