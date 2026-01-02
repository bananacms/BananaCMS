/**
 * 浏览器兼容性检测和提示
 * 用于检测旧版浏览器并显示升级提示
 */
(function() {
    'use strict';
    
    // 检测是否为旧版浏览器
    function isOldBrowser() {
        // 检测IE11及以下
        if (navigator.userAgent.indexOf('MSIE') !== -1 || 
            navigator.userAgent.indexOf('Trident/') !== -1) {
            return true;
        }
        
        // 检测CSS Grid支持
        if (!window.CSS || !CSS.supports || !CSS.supports('display', 'grid')) {
            return true;
        }
        
        // 检测Flexbox支持
        if (!CSS.supports('display', 'flex')) {
            return true;
        }
        
        // 检测CSS变量支持
        if (!CSS.supports('color', 'var(--test-var)')) {
            return true;
        }
        
        // 检测ES6基本支持
        try {
            new Function('() => {}');
        } catch (e) {
            return true;
        }
        
        return false;
    }
    
    // 显示浏览器升级提示
    function showBrowserWarning() {
        if (sessionStorage.getItem('browserWarningShown')) {
            return;
        }
        
        var warning = document.createElement('div');
        warning.className = 'browser-warning';
        warning.style.cssText = [
            'background: #fef2f2',
            'border: 1px solid #fecaca',
            'color: #dc2626',
            'padding: 12px 16px',
            'margin: 10px',
            'border-radius: 6px',
            'text-align: center',
            'font-size: 14px',
            'position: relative',
            'z-index: 9999',
            'animation: slideDown 0.3s ease-out'
        ].join(';');
        
        warning.innerHTML = [
            '<strong>兼容性提示：</strong>',
            '检测到您的浏览器可能不支持某些现代功能，建议升级浏览器以获得最佳体验。',
            '<br><small>推荐使用：',
            '<a href="https://www.microsoft.com/edge" target="_blank" style="color:#dc2626;text-decoration:underline;">Edge</a>、',
            '<a href="https://www.google.com/chrome" target="_blank" style="color:#dc2626;text-decoration:underline;">Chrome</a>、',
            '<a href="https://www.mozilla.org/firefox" target="_blank" style="color:#dc2626;text-decoration:underline;">Firefox</a>',
            '</small>',
            '<button onclick="this.parentNode.remove(); sessionStorage.setItem(\'browserWarningShown\', \'1\');" ',
            'style="float:right; background:none; border:none; color:#dc2626; cursor:pointer; font-size:18px; line-height:1; margin-top:-2px;">×</button>'
        ].join('');
        
        // 插入到页面顶部
        if (document.body) {
            document.body.insertBefore(warning, document.body.firstChild);
        } else {
            document.addEventListener('DOMContentLoaded', function() {
                document.body.insertBefore(warning, document.body.firstChild);
            });
        }
    }
    
    // 添加CSS降级样式
    function addFallbackStyles() {
        var style = document.createElement('style');
        style.textContent = [
            '/* 浏览器兼容性降级样式 */',
            '@keyframes slideDown {',
            '  from { opacity: 0; transform: translateY(-20px); }',
            '  to { opacity: 1; transform: translateY(0); }',
            '}',
            
            // IE11专用样式
            '@media screen and (-ms-high-contrast: active), (-ms-high-contrast: none) {',
            '  .grid { display: -ms-flexbox; display: flex; -ms-flex-wrap: wrap; flex-wrap: wrap; }',
            '  .grid-cols-2 > * { -ms-flex: 0 0 50%; flex: 0 0 50%; }',
            '  .grid-cols-3 > * { -ms-flex: 0 0 33.333333%; flex: 0 0 33.333333%; }',
            '  .grid-cols-4 > * { -ms-flex: 0 0 25%; flex: 0 0 25%; }',
            '  .flex { display: -ms-flexbox; display: flex; }',
            '  .items-center { -ms-flex-align: center; align-items: center; }',
            '  .justify-between { -ms-flex-pack: justify; justify-content: space-between; }',
            '}',
            
            // 不支持Grid的降级
            '@supports not (display: grid) {',
            '  .grid { display: flex; flex-wrap: wrap; }',
            '  .grid-cols-2 > * { flex: 0 0 50%; }',
            '  .grid-cols-3 > * { flex: 0 0 33.333333%; }',
            '  .grid-cols-4 > * { flex: 0 0 25%; }',
            '  .gap-4 > * { margin: 0.5rem; }',
            '}'
        ].join('\n');
        
        document.head.appendChild(style);
    }
    
    // 初始化
    function init() {
        addFallbackStyles();
        
        if (isOldBrowser()) {
            showBrowserWarning();
        }
    }
    
    // 页面加载完成后执行
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();