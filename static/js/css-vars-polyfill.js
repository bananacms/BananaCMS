/**
 * CSS变量Polyfill - 为不支持CSS变量的浏览器提供支持
 * 轻量级实现，仅处理基本的CSS变量功能
 */
(function() {
    'use strict';
    
    // 检测CSS变量支持
    function supportsCSSVars() {
        if (!window.CSS || !CSS.supports) {
            return false;
        }
        return CSS.supports('color', 'var(--test-var)');
    }
    
    // 如果支持CSS变量，则不需要polyfill
    if (supportsCSSVars()) {
        return;
    }
    
    // CSS变量存储
    var cssVars = {};
    
    // 解析CSS变量定义
    function parseCSSVars() {
        var rootVars = {
            // 主色调
            '--color-primary': '#ef4444',
            '--color-primary-dark': '#dc2626',
            '--color-primary-light': '#f87171',
            
            // 中性色
            '--color-gray-50': '#f9fafb',
            '--color-gray-100': '#f3f4f6',
            '--color-gray-200': '#e5e7eb',
            '--color-gray-300': '#d1d5db',
            '--color-gray-400': '#9ca3af',
            '--color-gray-500': '#6b7280',
            '--color-gray-600': '#4b5563',
            '--color-gray-700': '#374151',
            '--color-gray-800': '#1f2937',
            '--color-gray-900': '#111827',
            
            // 功能色
            '--color-success': '#10b981',
            '--color-warning': '#f59e0b',
            '--color-error': '#ef4444',
            '--color-info': '#3b82f6',
            '--color-white': '#ffffff',
            
            // 间距
            '--spacing-xs': '0.25rem',
            '--spacing-sm': '0.5rem',
            '--spacing-md': '1rem',
            '--spacing-lg': '1.5rem',
            '--spacing-xl': '2rem',
            
            // 字体大小
            '--font-size-xs': '0.75rem',
            '--font-size-sm': '0.875rem',
            '--font-size-base': '1rem',
            '--font-size-lg': '1.125rem',
            '--font-size-xl': '1.25rem',
            '--font-size-2xl': '1.5rem',
            
            // 阴影
            '--shadow-sm': '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
            '--shadow-md': '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
            '--shadow-lg': '0 10px 15px -3px rgba(0, 0, 0, 0.1)',
            
            // 圆角
            '--border-radius-sm': '0.25rem',
            '--border-radius-md': '0.375rem',
            '--border-radius-lg': '0.5rem',
            '--border-radius-xl': '0.75rem'
        };
        
        cssVars = rootVars;
    }
    
    // 替换CSS变量
    function replaceCSSVars(cssText) {
        return cssText.replace(/var\(([^,)]+)(?:,\s*([^)]+))?\)/g, function(match, varName, fallback) {
            var value = cssVars[varName.trim()];
            return value || fallback || match;
        });
    }
    
    // 处理样式表
    function processStyleSheets() {
        var styleSheets = document.styleSheets;
        
        for (var i = 0; i < styleSheets.length; i++) {
            try {
                var styleSheet = styleSheets[i];
                var rules = styleSheet.cssRules || styleSheet.rules;
                
                if (!rules) continue;
                
                for (var j = 0; j < rules.length; j++) {
                    var rule = rules[j];
                    
                    if (rule.style) {
                        var cssText = rule.style.cssText;
                        if (cssText.indexOf('var(') !== -1) {
                            var newCssText = replaceCSSVars(cssText);
                            if (newCssText !== cssText) {
                                rule.style.cssText = newCssText;
                            }
                        }
                    }
                }
            } catch (e) {
                // 跨域样式表或其他错误，静默忽略
            }
        }
    }
    
    // 处理内联样式
    function processInlineStyles() {
        var elements = document.querySelectorAll('[style*="var("]');
        
        for (var i = 0; i < elements.length; i++) {
            var element = elements[i];
            var style = element.getAttribute('style');
            
            if (style && style.indexOf('var(') !== -1) {
                var newStyle = replaceCSSVars(style);
                if (newStyle !== style) {
                    element.setAttribute('style', newStyle);
                }
            }
        }
    }
    
    // 监听动态添加的样式
    function observeStyleChanges() {
        if (!window.MutationObserver) {
            return;
        }
        
        var observer = new MutationObserver(function(mutations) {
            var needsUpdate = false;
            
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1) { // Element node
                            if (node.tagName === 'STYLE' || node.tagName === 'LINK') {
                                needsUpdate = true;
                            }
                            if (node.getAttribute && node.getAttribute('style') && 
                                node.getAttribute('style').indexOf('var(') !== -1) {
                                needsUpdate = true;
                            }
                        }
                    });
                } else if (mutation.type === 'attributes' && 
                          mutation.attributeName === 'style' &&
                          mutation.target.getAttribute('style') &&
                          mutation.target.getAttribute('style').indexOf('var(') !== -1) {
                    needsUpdate = true;
                }
            });
            
            if (needsUpdate) {
                setTimeout(function() {
                    processInlineStyles();
                }, 10);
            }
        });
        
        observer.observe(document, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['style']
        });
    }
    
    // 初始化polyfill
    function init() {
        parseCSSVars();
        
        // 等待DOM和样式表加载完成
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    processStyleSheets();
                    processInlineStyles();
                    observeStyleChanges();
                }, 100);
            });
        } else {
            setTimeout(function() {
                processStyleSheets();
                processInlineStyles();
                observeStyleChanges();
            }, 100);
        }
    }
    
    // 注册到命名空间
    if (typeof window.XPK !== 'undefined') {
        window.XPK.registerUtil('CSSVarsPolyfill', {
            process: function() {
                processStyleSheets();
                processInlineStyles();
            },
            setVar: function(name, value) {
                cssVars[name] = value;
                this.process();
            },
            getVar: function(name) {
                return cssVars[name];
            }
        });
    } else {
        // 降级：暴露到全局
        window.CSSVarsPolyfill = {
            process: function() {
                processStyleSheets();
                processInlineStyles();
            },
            setVar: function(name, value) {
                cssVars[name] = value;
                this.process();
            },
            getVar: function(name) {
                return cssVars[name];
            }
        };
    }
    
    init();
})();