# Simple CTA - WordPress插件

一个轻量、简洁的WordPress CTA（Call-to-Action）插件，专门用于自动检测联盟链接并应用预设的CTA样式。

## 功能特性

### 🎯 自动检测联盟链接
- **Amazon联盟**：支持短链接（amzn.to）和长链接格式
- **eBay Partner Network**：支持rover.ebay.com等格式
- **Walmart联盟**：支持linksynergy和goto格式
- **CJ Affiliate**：支持Commission Junction跟踪链接
- **ShareASale**：支持ShareASale联盟链接
- **Impact Radius**：支持Impact联盟平台
- **Rakuten**：支持Rakuten联盟链接

### 🎨 内置5种CTA样式
1. **现代风格**：渐变背景，阴影效果
2. **简约风格**：纯色背景，简洁设计
3. **活力风格**：鲜艳颜色，脉冲动画
4. **优雅风格**：边框设计，悬停填充效果
5. **渐变风格**：多层渐变，悬停变换

### ⚙️ 强大的管理界面
- **平台规则管理**：自定义检测规则，支持正则表达式
- **样式编辑器**：可视化CSS编辑，实时预览
- **全局设置**：启用/禁用功能，选择默认样式
- **预览测试**：测试链接检测和样式效果

## 系统要求

- **WordPress**: 5.0 或更高版本
- **PHP**: 8.0 或更高版本
- **MySQL**: 5.6 或更高版本

## 安装方法

### 方法1：WordPress后台安装
1. 下载插件zip文件
2. 登录WordPress后台
3. 进入"插件" > "安装插件" > "上传插件"
4. 选择zip文件并安装
5. 激活插件

### 方法2：FTP上传
1. 解压插件文件
2. 将`simple-cta`文件夹上传到`/wp-content/plugins/`目录
3. 在WordPress后台激活插件

## 使用方法

### 自动检测模式
插件激活后会自动检测页面中的联盟链接，并应用默认的CTA样式。

### 手动应用样式
```html
<!-- 为链接添加CTA类名 -->
<a href="https://amzn.to/example" class="simple-cta modern amazon-cta">购买链接</a>
```

### JavaScript API
```javascript
// 手动应用样式
SimpleCTA.applyStyle('.my-links', 'vibrant');

// 自动检测并应用样式
SimpleCTA.autoDetect();

// 获取统计信息
var stats = SimpleCTA.getStats();
console.log(stats);
```

## 配置说明

### 基本设置
- **启用插件**：开启/关闭Simple CTA功能
- **自动检测**：自动识别联盟链接并应用样式
- **加载CSS**：是否自动加载插件样式文件
- **默认样式**：选择自动检测时使用的默认样式

### 平台规则管理
支持的链接格式示例：

#### Amazon
```
短链接：https://amzn.to/3JbOalL
长链接：https://www.amazon.com/dp/B07PXHX5K3?tag=XXXXXXX-20
```

#### eBay
```
https://rover.ebay.com/rover/1/711-53200-19255-0/1?campid=123456
https://www.ebay.com/itm/123456789?campid=123456
```

#### Walmart
```
https://linksynergy.walmart.com/link?id=123456
https://goto.walmart.com/c/123456/product
```

### 自定义样式
可以在管理界面编辑CSS代码，使用以下选择器：
```css
.simple-cta.your-style-name {
    /* 你的样式代码 */
}
```

## 文件结构
```
simple-cta/
├── simple-cta.php          # 主插件文件
├── admin-page.php          # 管理界面
├── uninstall.php           # 卸载脚本
├── assets/
│   └── js/
│       └── frontend.js     # 前端JavaScript
├── languages/              # 语言文件目录
└── README.md              # 说明文档
```

## 开发者信息

### 钩子和过滤器
```php
// 修改检测到的平台
add_filter('simple_cta_detected_platform', function($platform, $url) {
    // 自定义逻辑
    return $platform;
}, 10, 2);

// 修改应用的CSS类
add_filter('simple_cta_css_classes', function($classes, $platform, $url) {
    // 自定义逻辑
    return $classes;
}, 10, 3);
```

### 自定义平台规则
```php
// 添加自定义平台
add_filter('simple_cta_platforms', function($platforms) {
    $platforms['custom_platform'] = [
        'name' => '自定义平台',
        'patterns' => ['custom\.com\/.*[?&]ref='],
        'class' => 'custom-cta',
        'enabled' => true
    ];
    return $platforms;
});
```

## 常见问题

### Q: 为什么我的链接没有被自动检测？
A: 请检查以下几点：
1. 确保插件已启用且自动检测功能已开启
2. 检查链接格式是否匹配预设规则
3. 在管理界面的"预览测试"中测试链接

### Q: 如何自定义CTA样式？
A: 在管理界面的"CTA样式"标签页中，可以编辑现有样式或添加新样式。

### Q: 插件会影响网站性能吗？
A: 插件经过优化，对性能影响极小。CSS和JavaScript文件都很轻量，且只在需要时加载。

### Q: 支持多站点吗？
A: 是的，插件完全支持WordPress多站点网络。

## 更新日志

### 1.0.0 (2025-08-13)
- 初始版本发布
- 支持7大主流联盟平台
- 内置5种CTA样式
- 完整的管理界面
- 自动检测和手动应用功能

## 支持与反馈

如果您在使用过程中遇到问题或有改进建议，请通过以下方式联系：

- **GitHub Issues**: [https://github.com/b6421582/Simple-CTA/issues](https://github.com/b6421582/Simple-CTA/issues)
- **项目仓库**: [https://github.com/b6421582/Simple-CTA](https://github.com/b6421582/Simple-CTA)

## 许可证

本插件基于 GPL v2 或更高版本许可证发布。

## 作者

**CatchIdeas**
- 官方网站: [https://catchideas.com/](https://catchideas.com/)
- GitHub: [https://github.com/b6421582](https://github.com/b6421582)

---

感谢使用 Simple CTA 插件！如果觉得有用，请给我们一个 ⭐ Star！
