/**
 * 颜色对比度增强脚本
 * 自动检测和改进页面中对比度不足的文字颜色
 */
(function() {
    'use strict';
    
    class ContrastEnhancer {
        constructor() {
            this.contrastRatios = {
                // WCAG AA 标准要求普通文字对比度至少 4.5:1，大文字至少 3:1
                minNormal: 4.5,
                minLarge: 3.0,
                // WCAG AAA 标准要求普通文字对比度至少 7:1，大文字至少 4.5:1
                minNormalAAA: 7.0,
                minLargeAAA: 4.5
            };
            
            // 颜色映射表 - 将对比度不足的颜色替换为更好的颜色
            this.colorMappings = {
                // Tailwind 灰色系优化
                'rgb(156, 163, 175)': 'rgb(107, 114, 128)', // text-gray-400 -> text-gray-500
                'rgb(107, 114, 128)': 'rgb(75, 85, 99)',   // text-gray-500 -> text-gray-600
                'rgb(209, 213, 219)': 'rgb(156, 163, 175)', // text-gray-300 -> text-gray-400
                
                // 十六进制格式
                '#9ca3af': '#6b7280', // text-gray-400 -> text-gray-500
                '#6b7280': '#4b5563', // text-gray-500 -> text-gray-600
                '#d1d5db': '#9ca3af', // text-gray-300 -> text-gray-400
                
                // 常见的对比度不足的颜色
                '#999999': '#666666',
                '#aaaaaa': '#777777',
                '#cccccc': '#999999'
            };
            
            this.init();
        }
        
        init() {
            // 等待DOM加载完成
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.enhance());
            } else {
                this.enhance();
            }
            
            // 监听动态内容变化
            this.observeChanges();
        }
        
        enhance() {
            // 检查用户偏好
            const prefersHighContrast = window.matchMedia('(prefers-contrast: high)').matches;
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            
            if (prefersHighContrast) {
                this.applyHighContrastMode();
                return;
            }
            
            // 扫描所有文本元素
            this.scanTextElements();
            
            // 扫描特定的Tailwind类
            this.replaceTailwindClasses();
        }
        
        scanTextElements() {
            const textElements = document.querySelectorAll('*');
            
            textElements.forEach(element => {
                if (this.hasTextContent(element)) {
                    this.checkAndImproveContrast(element);
                }
            });
        }
        
        hasTextContent(element) {
            // 检查元素是否包含文本内容
            const textContent = element.textContent?.trim();
            if (!textContent) return false;
            
            // 排除脚本、样式等元素
            const excludeTags = ['SCRIPT', 'STYLE', 'NOSCRIPT', 'META', 'LINK'];
            if (excludeTags.includes(element.tagName)) return false;
            
            // 检查是否有直接的文本子节点
            for (let node of element.childNodes) {
                if (node.nodeType === Node.TEXT_NODE && node.textContent.trim()) {
                    return true;
                }
            }
            
            return false;
        }
        
        checkAndImproveContrast(element) {
            const styles = window.getComputedStyle(element);
            const color = styles.color;
            const backgroundColor = this.getEffectiveBackgroundColor(element);
            
            if (!color || !backgroundColor) return;
            
            const contrast = this.calculateContrast(color, backgroundColor);
            const fontSize = parseFloat(styles.fontSize);
            const fontWeight = styles.fontWeight;
            
            // 判断是否为大文字（18pt以上或14pt粗体以上）
            const isLargeText = fontSize >= 18 || (fontSize >= 14 && (fontWeight === 'bold' || parseInt(fontWeight) >= 700));
            
            const minContrast = isLargeText ? this.contrastRatios.minLarge : this.contrastRatios.minNormal;
            
            if (contrast < minContrast) {
                this.improveElementContrast(element, color, backgroundColor, isLargeText);
            }
        }
        
        getEffectiveBackgroundColor(element) {
            let current = element;
            
            while (current && current !== document.body) {
                const styles = window.getComputedStyle(current);
                const bgColor = styles.backgroundColor;
                
                if (bgColor && bgColor !== 'rgba(0, 0, 0, 0)' && bgColor !== 'transparent') {
                    return bgColor;
                }
                
                current = current.parentElement;
            }
            
            // 默认返回白色背景
            return 'rgb(255, 255, 255)';
        }
        
        calculateContrast(color1, color2) {
            const rgb1 = this.parseColor(color1);
            const rgb2 = this.parseColor(color2);
            
            if (!rgb1 || !rgb2) return 21; // 假设对比度足够
            
            const l1 = this.getLuminance(rgb1);
            const l2 = this.getLuminance(rgb2);
            
            const lighter = Math.max(l1, l2);
            const darker = Math.min(l1, l2);
            
            return (lighter + 0.05) / (darker + 0.05);
        }
        
        parseColor(color) {
            // 解析 rgb() 格式
            const rgbMatch = color.match(/rgb\((\d+),\s*(\d+),\s*(\d+)\)/);
            if (rgbMatch) {
                return {
                    r: parseInt(rgbMatch[1]),
                    g: parseInt(rgbMatch[2]),
                    b: parseInt(rgbMatch[3])
                };
            }
            
            // 解析 rgba() 格式
            const rgbaMatch = color.match(/rgba\((\d+),\s*(\d+),\s*(\d+),\s*[\d.]+\)/);
            if (rgbaMatch) {
                return {
                    r: parseInt(rgbaMatch[1]),
                    g: parseInt(rgbaMatch[2]),
                    b: parseInt(rgbaMatch[3])
                };
            }
            
            // 解析十六进制格式
            const hexMatch = color.match(/^#([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i);
            if (hexMatch) {
                return {
                    r: parseInt(hexMatch[1], 16),
                    g: parseInt(hexMatch[2], 16),
                    b: parseInt(hexMatch[3], 16)
                };
            }
            
            return null;
        }
        
        getLuminance(rgb) {
            // 计算相对亮度
            const { r, g, b } = rgb;
            
            const [rs, gs, bs] = [r, g, b].map(c => {
                c = c / 255;
                return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
            });
            
            return 0.2126 * rs + 0.7152 * gs + 0.0722 * bs;
        }
        
        improveElementContrast(element, currentColor, backgroundColor, isLargeText) {
            // 尝试从映射表中找到更好的颜色
            const improvedColor = this.colorMappings[currentColor] || this.colorMappings[this.rgbToHex(currentColor)];
            
            if (improvedColor) {
                element.style.color = improvedColor;
                element.setAttribute('data-contrast-improved', 'true');
                return;
            }
            
            // 如果映射表中没有，计算一个更好的颜色
            const newColor = this.calculateBetterColor(currentColor, backgroundColor, isLargeText);
            if (newColor) {
                element.style.color = newColor;
                element.setAttribute('data-contrast-improved', 'true');
            }
        }
        
        rgbToHex(rgb) {
            const parsed = this.parseColor(rgb);
            if (!parsed) return rgb;
            
            const { r, g, b } = parsed;
            return `#${((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1)}`;
        }
        
        calculateBetterColor(currentColor, backgroundColor, isLargeText) {
            const currentRgb = this.parseColor(currentColor);
            const bgRgb = this.parseColor(backgroundColor);
            
            if (!currentRgb || !bgRgb) return null;
            
            const targetContrast = isLargeText ? this.contrastRatios.minLarge : this.contrastRatios.minNormal;
            
            // 简单的方法：使颜色更深或更浅
            const bgLuminance = this.getLuminance(bgRgb);
            
            if (bgLuminance > 0.5) {
                // 浅色背景，使文字更深
                return this.darkenColor(currentRgb, targetContrast, bgRgb);
            } else {
                // 深色背景，使文字更浅
                return this.lightenColor(currentRgb, targetContrast, bgRgb);
            }
        }
        
        darkenColor(rgb, targetContrast, bgRgb) {
            let { r, g, b } = rgb;
            const factor = 0.8; // 每次减少20%
            
            for (let i = 0; i < 10; i++) {
                r = Math.max(0, Math.floor(r * factor));
                g = Math.max(0, Math.floor(g * factor));
                b = Math.max(0, Math.floor(b * factor));
                
                const contrast = this.calculateContrast(`rgb(${r}, ${g}, ${b})`, `rgb(${bgRgb.r}, ${bgRgb.g}, ${bgRgb.b})`);
                
                if (contrast >= targetContrast) {
                    return `rgb(${r}, ${g}, ${b})`;
                }
            }
            
            return `rgb(${r}, ${g}, ${b})`;
        }
        
        lightenColor(rgb, targetContrast, bgRgb) {
            let { r, g, b } = rgb;
            const factor = 1.25; // 每次增加25%
            
            for (let i = 0; i < 10; i++) {
                r = Math.min(255, Math.floor(r * factor));
                g = Math.min(255, Math.floor(g * factor));
                b = Math.min(255, Math.floor(b * factor));
                
                const contrast = this.calculateContrast(`rgb(${r}, ${g}, ${b})`, `rgb(${bgRgb.r}, ${bgRgb.g}, ${bgRgb.b})`);
                
                if (contrast >= targetContrast) {
                    return `rgb(${r}, ${g}, ${b})`;
                }
            }
            
            return `rgb(${r}, ${g}, ${b})`;
        }
        
        replaceTailwindClasses() {
            // 替换常见的对比度不足的Tailwind类
            const classReplacements = {
                'text-gray-400': 'text-gray-500',
                'text-gray-300': 'text-gray-400',
                'text-gray-500': 'text-gray-600'
            };
            
            Object.keys(classReplacements).forEach(oldClass => {
                const elements = document.querySelectorAll(`.${oldClass}`);
                elements.forEach(element => {
                    element.classList.remove(oldClass);
                    element.classList.add(classReplacements[oldClass]);
                    element.setAttribute('data-contrast-improved', 'true');
                });
            });
        }
        
        applyHighContrastMode() {
            // 高对比度模式
            document.body.classList.add('high-contrast-mode');
            
            // 添加高对比度样式
            const style = document.createElement('style');
            style.textContent = `
                .high-contrast-mode * {
                    color: #000000 !important;
                    background-color: #ffffff !important;
                    border-color: #000000 !important;
                }
                .high-contrast-mode a {
                    color: #0000ee !important;
                }
                .high-contrast-mode a:visited {
                    color: #551a8b !important;
                }
                .high-contrast-mode button {
                    background-color: #ffffff !important;
                    color: #000000 !important;
                    border: 2px solid #000000 !important;
                }
            `;
            document.head.appendChild(style);
        }
        
        observeChanges() {
            if (!window.MutationObserver) return;
            
            const observer = new MutationObserver((mutations) => {
                let shouldRecheck = false;
                
                mutations.forEach(mutation => {
                    if (mutation.type === 'childList') {
                        mutation.addedNodes.forEach(node => {
                            if (node.nodeType === Node.ELEMENT_NODE) {
                                shouldRecheck = true;
                            }
                        });
                    }
                });
                
                if (shouldRecheck) {
                    // 延迟执行，避免频繁检查
                    setTimeout(() => this.enhance(), 100);
                }
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }
        
        // 公共API
        checkElement(element) {
            this.checkAndImproveContrast(element);
        }
        
        getContrastRatio(color1, color2) {
            return this.calculateContrast(color1, color2);
        }
    }
    
    // 初始化
    const contrastEnhancer = new ContrastEnhancer();
    
    // 注册到命名空间
    if (typeof window.XPK !== 'undefined') {
        window.XPK.register('ContrastEnhancer', ContrastEnhancer);
        window.XPK.register('contrastEnhancer', contrastEnhancer);
    } else {
        // 降级：暴露到全局
        window.ContrastEnhancer = ContrastEnhancer;
        window.contrastEnhancer = contrastEnhancer;
    }
    
    // 监听用户偏好变化
    if (window.matchMedia) {
        window.matchMedia('(prefers-contrast: high)').addEventListener('change', () => {
            location.reload(); // 重新加载页面以应用新的对比度设置
        });
    }
})();