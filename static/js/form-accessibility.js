/**
 * 表单无障碍访问增强脚本
 * 自动为表单元素添加ARIA标签和改进键盘导航
 */
(function() {
    'use strict';
    
    class FormAccessibility {
        constructor() {
            this.init();
        }
        
        init() {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.enhance());
            } else {
                this.enhance();
            }
            
            // 监听动态内容变化
            this.observeChanges();
        }
        
        enhance() {
            this.enhanceForms();
            this.enhanceButtons();
            this.enhanceNavigation();
            this.enhanceInteractiveElements();
            this.addSkipLinks();
        }
        
        enhanceForms() {
            const forms = document.querySelectorAll('form');
            
            forms.forEach(form => {
                this.enhanceFormElements(form);
                this.addFormValidation(form);
            });
        }
        
        enhanceFormElements(form) {
            // 增强输入框
            const inputs = form.querySelectorAll('input, textarea, select');
            
            inputs.forEach(input => {
                this.enhanceInput(input);
            });
            
            // 增强标签关联
            const labels = form.querySelectorAll('label');
            labels.forEach(label => {
                this.enhanceLabel(label);
            });
        }
        
        enhanceInput(input) {
            // 确保输入框有ID
            if (!input.id) {
                input.id = this.generateId('input');
            }
            
            // 查找关联的标签
            let label = document.querySelector(`label[for="${input.id}"]`);
            if (!label) {
                // 查找包含此输入框的标签
                label = input.closest('label');
                if (label) {
                    label.setAttribute('for', input.id);
                }
            }
            
            // 添加ARIA属性
            if (input.required && !input.getAttribute('aria-required')) {
                input.setAttribute('aria-required', 'true');
            }
            
            // 添加描述性文本
            if (input.placeholder && !input.getAttribute('aria-describedby')) {
                const descId = this.generateId('desc');
                const desc = document.createElement('span');
                desc.id = descId;
                desc.className = 'sr-only';
                desc.textContent = input.placeholder;
                input.parentNode.insertBefore(desc, input.nextSibling);
                input.setAttribute('aria-describedby', descId);
            }
            
            // 增强错误提示
            this.enhanceInputErrors(input);
        }
        
        enhanceLabel(label) {
            const forAttr = label.getAttribute('for');
            if (!forAttr) {
                // 查找标签内的输入框
                const input = label.querySelector('input, textarea, select');
                if (input) {
                    if (!input.id) {
                        input.id = this.generateId('input');
                    }
                    label.setAttribute('for', input.id);
                }
            }
            
            // 为必填字段添加视觉指示
            const input = document.getElementById(forAttr) || label.querySelector('input, textarea, select');
            if (input && input.required) {
                if (!label.querySelector('.required-indicator')) {
                    const indicator = document.createElement('span');
                    indicator.className = 'required-indicator text-red-500 ml-1';
                    indicator.setAttribute('aria-label', '必填项');
                    indicator.textContent = '*';
                    label.appendChild(indicator);
                }
            }
        }
        
        enhanceInputErrors(input) {
            // 监听验证事件
            input.addEventListener('invalid', (e) => {
                this.showInputError(input, input.validationMessage);
            });
            
            input.addEventListener('input', () => {
                if (input.checkValidity()) {
                    this.clearInputError(input);
                }
            });
        }
        
        showInputError(input, message) {
            let errorElement = document.getElementById(input.id + '-error');
            
            if (!errorElement) {
                errorElement = document.createElement('div');
                errorElement.id = input.id + '-error';
                errorElement.className = 'text-red-500 text-sm mt-1';
                errorElement.setAttribute('role', 'alert');
                errorElement.setAttribute('aria-live', 'polite');
                input.parentNode.insertBefore(errorElement, input.nextSibling);
            }
            
            errorElement.textContent = message;
            input.setAttribute('aria-describedby', errorElement.id);
            input.setAttribute('aria-invalid', 'true');
        }
        
        clearInputError(input) {
            const errorElement = document.getElementById(input.id + '-error');
            if (errorElement) {
                errorElement.textContent = '';
            }
            input.removeAttribute('aria-invalid');
        }
        
        addFormValidation(form) {
            form.addEventListener('submit', (e) => {
                const firstInvalidInput = form.querySelector(':invalid');
                if (firstInvalidInput) {
                    e.preventDefault();
                    firstInvalidInput.focus();
                    this.showInputError(firstInvalidInput, firstInvalidInput.validationMessage);
                }
            });
        }
        
        enhanceButtons() {
            const buttons = document.querySelectorAll('button, [role="button"]');
            
            buttons.forEach(button => {
                // 确保按钮有可访问的名称
                if (!button.getAttribute('aria-label') && !button.textContent.trim() && !button.getAttribute('aria-labelledby')) {
                    // 尝试从图标或上下文推断标签
                    const icon = button.querySelector('svg, i');
                    if (icon) {
                        const title = icon.getAttribute('title') || icon.getAttribute('aria-label');
                        if (title) {
                            button.setAttribute('aria-label', title);
                        }
                    }
                }
                
                // 为切换按钮添加状态
                if (button.getAttribute('data-toggle') || button.classList.contains('toggle')) {
                    if (!button.getAttribute('aria-pressed')) {
                        button.setAttribute('aria-pressed', 'false');
                    }
                    
                    button.addEventListener('click', () => {
                        const pressed = button.getAttribute('aria-pressed') === 'true';
                        button.setAttribute('aria-pressed', (!pressed).toString());
                    });
                }
                
                // 为下拉菜单按钮添加状态
                if (button.getAttribute('data-dropdown') || button.classList.contains('dropdown-toggle')) {
                    if (!button.getAttribute('aria-expanded')) {
                        button.setAttribute('aria-expanded', 'false');
                    }
                    
                    if (!button.getAttribute('aria-haspopup')) {
                        button.setAttribute('aria-haspopup', 'true');
                    }
                }
            });
        }
        
        enhanceNavigation() {
            const navs = document.querySelectorAll('nav');
            
            navs.forEach(nav => {
                // 确保导航有标签
                if (!nav.getAttribute('aria-label') && !nav.getAttribute('aria-labelledby')) {
                    const heading = nav.querySelector('h1, h2, h3, h4, h5, h6');
                    if (heading) {
                        if (!heading.id) {
                            heading.id = this.generateId('nav-heading');
                        }
                        nav.setAttribute('aria-labelledby', heading.id);
                    } else {
                        nav.setAttribute('aria-label', '导航菜单');
                    }
                }
                
                // 增强导航链接
                const links = nav.querySelectorAll('a');
                links.forEach(link => {
                    // 标记当前页面
                    if (this.isCurrentPage(link.href)) {
                        link.setAttribute('aria-current', 'page');
                    }
                });
            });
        }
        
        enhanceInteractiveElements() {
            // 增强所有可交互元素的键盘访问
            const interactiveElements = document.querySelectorAll('[onclick], [onkeydown], .clickable, .interactive');
            
            interactiveElements.forEach(element => {
                if (element.tagName !== 'BUTTON' && element.tagName !== 'A' && element.tagName !== 'INPUT') {
                    // 为非标准交互元素添加键盘支持
                    if (!element.getAttribute('tabindex')) {
                        element.setAttribute('tabindex', '0');
                    }
                    
                    if (!element.getAttribute('role')) {
                        element.setAttribute('role', 'button');
                    }
                    
                    // 添加键盘事件监听
                    element.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            element.click();
                        }
                    });
                }
            });
        }
        
        addSkipLinks() {
            // 检查是否已有跳转链接
            if (document.querySelector('.skip-link')) {
                return;
            }
            
            // 创建跳转链接容器
            const skipContainer = document.createElement('div');
            skipContainer.className = 'skip-links';
            
            // 添加样式
            const style = document.createElement('style');
            style.textContent = `
                .skip-links {
                    position: absolute;
                    top: -40px;
                    left: 6px;
                    z-index: 1000;
                }
                .skip-link {
                    position: absolute;
                    top: -40px;
                    left: 6px;
                    background: #000;
                    color: #fff;
                    padding: 8px;
                    text-decoration: none;
                    border-radius: 4px;
                    z-index: 1001;
                }
                .skip-link:focus {
                    top: 6px;
                }
            `;
            document.head.appendChild(style);
            
            // 添加跳转到主要内容的链接
            const mainContent = document.querySelector('main, #main, .main-content, [role="main"]');
            if (mainContent) {
                if (!mainContent.id) {
                    mainContent.id = 'main-content';
                }
                
                const skipToMain = document.createElement('a');
                skipToMain.href = '#' + mainContent.id;
                skipToMain.className = 'skip-link';
                skipToMain.textContent = '跳转到主要内容';
                skipContainer.appendChild(skipToMain);
            }
            
            // 添加跳转到导航的链接
            const mainNav = document.querySelector('nav, #nav, .main-nav, [role="navigation"]');
            if (mainNav) {
                if (!mainNav.id) {
                    mainNav.id = 'main-navigation';
                }
                
                const skipToNav = document.createElement('a');
                skipToNav.href = '#' + mainNav.id;
                skipToNav.className = 'skip-link';
                skipToNav.textContent = '跳转到导航菜单';
                skipContainer.appendChild(skipToNav);
            }
            
            // 插入到页面开头
            document.body.insertBefore(skipContainer, document.body.firstChild);
        }
        
        isCurrentPage(href) {
            if (!href) return false;
            
            try {
                const linkUrl = new URL(href, window.location.origin);
                return linkUrl.pathname === window.location.pathname;
            } catch (e) {
                return false;
            }
        }
        
        generateId(prefix = 'element') {
            return prefix + '-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
        }
        
        observeChanges() {
            if (!window.MutationObserver) return;
            
            const observer = new MutationObserver((mutations) => {
                let shouldRecheck = false;
                
                mutations.forEach(mutation => {
                    if (mutation.type === 'childList') {
                        mutation.addedNodes.forEach(node => {
                            if (node.nodeType === Node.ELEMENT_NODE) {
                                // 检查是否添加了表单元素
                                if (node.matches && (node.matches('form, input, button, [role="button"]') || 
                                    node.querySelector('form, input, button, [role="button"]'))) {
                                    shouldRecheck = true;
                                }
                            }
                        });
                    }
                });
                
                if (shouldRecheck) {
                    setTimeout(() => this.enhance(), 100);
                }
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }
        
        // 公共API
        enhanceElement(element) {
            if (element.matches('form')) {
                this.enhanceFormElements(element);
            } else if (element.matches('button, [role="button"]')) {
                this.enhanceButtons();
            } else if (element.matches('input, textarea, select')) {
                this.enhanceInput(element);
            }
        }
    }
    
    // 初始化
    const formAccessibility = new FormAccessibility();
    
    // 注册到命名空间
    if (typeof window.XPK !== 'undefined') {
        window.XPK.register('FormAccessibility', FormAccessibility);
        window.XPK.register('formAccessibility', formAccessibility);
    } else {
        // 降级：暴露到全局
        window.FormAccessibility = FormAccessibility;
        window.formAccessibility = formAccessibility;
    }
})();