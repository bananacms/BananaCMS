/**
 * 全局错误处理系统
 * 统一处理JavaScript错误、网络错误和用户操作错误
 */
(function() {
    'use strict';
    
    class ErrorHandler {
        constructor() {
            this.errorQueue = [];
            this.maxErrors = 50; // 最大错误记录数
            this.reportEndpoint = '/api/error-report'; // 错误报告端点
            this.init();
        }
        
        init() {
            this.setupGlobalErrorHandlers();
            this.setupNetworkErrorHandling();
            this.setupPromiseRejectionHandling();
            this.createErrorDisplay();
        }
        
        // 设置全局错误处理
        setupGlobalErrorHandlers() {
            // JavaScript运行时错误
            window.addEventListener('error', (event) => {
                this.handleError({
                    type: 'javascript',
                    message: event.message,
                    filename: event.filename,
                    lineno: event.lineno,
                    colno: event.colno,
                    error: event.error,
                    stack: event.error?.stack,
                    timestamp: new Date().toISOString(),
                    url: window.location.href,
                    userAgent: navigator.userAgent
                });
            });
            
            // 资源加载错误
            window.addEventListener('error', (event) => {
                if (event.target !== window) {
                    this.handleError({
                        type: 'resource',
                        message: `Failed to load resource: ${event.target.src || event.target.href}`,
                        element: event.target.tagName,
                        source: event.target.src || event.target.href,
                        timestamp: new Date().toISOString(),
                        url: window.location.href
                    });
                }
            }, true);
        }
        
        // 设置Promise拒绝处理
        setupPromiseRejectionHandling() {
            window.addEventListener('unhandledrejection', (event) => {
                this.handleError({
                    type: 'promise',
                    message: event.reason?.message || 'Unhandled Promise Rejection',
                    reason: event.reason,
                    stack: event.reason?.stack,
                    timestamp: new Date().toISOString(),
                    url: window.location.href
                });
                
                // 防止错误在控制台显示
                event.preventDefault();
            });
        }
        
        // 设置网络错误处理
        setupNetworkErrorHandling() {
            // 拦截fetch请求
            const originalFetch = window.fetch;
            window.fetch = async (...args) => {
                try {
                    const response = await originalFetch(...args);
                    
                    // 记录HTTP错误状态
                    if (!response.ok) {
                        this.handleError({
                            type: 'network',
                            message: `HTTP ${response.status}: ${response.statusText}`,
                            status: response.status,
                            statusText: response.statusText,
                            url: args[0],
                            method: args[1]?.method || 'GET',
                            timestamp: new Date().toISOString()
                        });
                    }
                    
                    return response;
                } catch (error) {
                    this.handleError({
                        type: 'network',
                        message: error.message,
                        error: error,
                        url: args[0],
                        method: args[1]?.method || 'GET',
                        timestamp: new Date().toISOString()
                    });
                    throw error;
                }
            };
        }
        
        // 处理错误
        handleError(errorInfo) {
            // 添加到错误队列
            this.errorQueue.push(errorInfo);
            
            // 限制队列大小
            if (this.errorQueue.length > this.maxErrors) {
                this.errorQueue.shift();
            }
            
            // 显示用户友好的错误信息
            this.showUserError(errorInfo);
            
            // 报告错误（可选）
            this.reportError(errorInfo);
        }
        
        // 显示用户友好的错误信息
        showUserError(errorInfo) {
            const userMessages = {
                javascript: '页面出现了一个小问题，请刷新页面重试',
                resource: '某些资源加载失败，可能影响页面功能',
                network: '网络请求失败，请检查网络连接',
                promise: '操作失败，请重试'
            };
            
            const message = userMessages[errorInfo.type] || '出现了未知错误';
            
            // 只对严重错误显示提示
            if (this.isCriticalError(errorInfo)) {
                this.showErrorToast(message, errorInfo);
            }
        }
        
        // 判断是否为严重错误
        isCriticalError(errorInfo) {
            // 网络错误中的5xx错误
            if (errorInfo.type === 'network' && errorInfo.status >= 500) {
                return true;
            }
            
            // JavaScript错误中的关键功能错误
            if (errorInfo.type === 'javascript') {
                const criticalKeywords = ['undefined', 'null', 'TypeError', 'ReferenceError'];
                return criticalKeywords.some(keyword => 
                    errorInfo.message?.includes(keyword)
                );
            }
            
            // 资源加载错误中的关键资源
            if (errorInfo.type === 'resource') {
                const criticalResources = ['.js', '.css'];
                return criticalResources.some(ext => 
                    errorInfo.source?.includes(ext)
                );
            }
            
            return false;
        }
        
        // 显示错误提示
        showErrorToast(message, errorInfo) {
            // 避免重复显示相同错误
            const errorKey = `${errorInfo.type}-${errorInfo.message}`;
            if (this.recentErrors?.has(errorKey)) {
                return;
            }
            
            if (!this.recentErrors) {
                this.recentErrors = new Set();
            }
            
            this.recentErrors.add(errorKey);
            
            // 5秒后清除记录
            setTimeout(() => {
                this.recentErrors.delete(errorKey);
            }, 5000);
            
            // 显示错误提示
            if (window.xpk?.toast) {
                window.xpk.toast(message, 'error');
            } else {
                this.showFallbackError(message);
            }
        }
        
        // 备用错误显示
        showFallbackError(message) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-toast';
            errorDiv.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #ef4444;
                color: white;
                padding: 12px 20px;
                border-radius: 6px;
                z-index: 10000;
                max-width: 300px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                animation: slideIn 0.3s ease-out;
            `;
            errorDiv.textContent = message;
            
            // 添加关闭按钮
            const closeBtn = document.createElement('button');
            closeBtn.innerHTML = '×';
            closeBtn.style.cssText = `
                background: none;
                border: none;
                color: white;
                font-size: 18px;
                margin-left: 10px;
                cursor: pointer;
                padding: 0;
                line-height: 1;
            `;
            closeBtn.onclick = () => errorDiv.remove();
            errorDiv.appendChild(closeBtn);
            
            document.body.appendChild(errorDiv);
            
            // 3秒后自动移除
            setTimeout(() => {
                if (errorDiv.parentNode) {
                    errorDiv.remove();
                }
            }, 3000);
        }
        
        // 创建错误显示区域
        createErrorDisplay() {
            // 添加CSS动画
            const style = document.createElement('style');
            style.textContent = `
                @keyframes slideIn {
                    from {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
                
                .error-toast {
                    animation: slideIn 0.3s ease-out;
                }
            `;
            document.head.appendChild(style);
        }
        
        // 报告错误到服务器
        async reportError(errorInfo) {
            // 只在生产环境报告错误
            if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                return;
            }
            
            try {
                await fetch(this.reportEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        ...errorInfo,
                        sessionId: this.getSessionId(),
                        userId: this.getUserId()
                    })
                });
            } catch (e) {
                // 静默失败，避免错误报告本身产生错误
            }
        }
        
        // 获取会话ID
        getSessionId() {
            let sessionId = sessionStorage.getItem('error_session_id');
            if (!sessionId) {
                sessionId = 'sess_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                sessionStorage.setItem('error_session_id', sessionId);
            }
            return sessionId;
        }
        
        // 获取用户ID
        getUserId() {
            // 从全局变量或cookie中获取用户ID
            return window.userId || document.cookie.match(/user_id=([^;]+)/)?.[1] || 'anonymous';
        }
        
        // 公共API
        logError(message, details = {}) {
            this.handleError({
                type: 'manual',
                message: message,
                details: details,
                timestamp: new Date().toISOString(),
                url: window.location.href
            });
        }
        
        getErrorHistory() {
            return [...this.errorQueue];
        }
        
        clearErrorHistory() {
            this.errorQueue = [];
        }
        
        // 网络重试辅助函数
        async retryRequest(requestFn, maxRetries = 3, delay = 1000) {
            for (let i = 0; i < maxRetries; i++) {
                try {
                    return await requestFn();
                } catch (error) {
                    if (i === maxRetries - 1) {
                        this.handleError({
                            type: 'retry_failed',
                            message: `Request failed after ${maxRetries} retries`,
                            originalError: error.message,
                            timestamp: new Date().toISOString()
                        });
                        throw error;
                    }
                    
                    // 静默重试，不输出警告
                    await new Promise(resolve => setTimeout(resolve, delay));
                    delay *= 2; // 指数退避
                }
            }
        }
    }
    
    // 初始化全局错误处理器
    const errorHandler = new ErrorHandler();
    
    // 注册到命名空间
    if (typeof window.XPK !== 'undefined') {
        window.XPK.register('errorHandler', errorHandler);
        window.XPK.registerUtil('logError', (message, details) => errorHandler.logError(message, details));
        window.XPK.registerUtil('retryRequest', (requestFn, maxRetries, delay) => errorHandler.retryRequest(requestFn, maxRetries, delay));
    } else {
        // 降级：使用全局变量
        window.ErrorHandler = ErrorHandler;
        window.errorHandler = errorHandler;
        window.logError = (message, details) => errorHandler.logError(message, details);
        window.retryRequest = (requestFn, maxRetries, delay) => errorHandler.retryRequest(requestFn, maxRetries, delay);
    }
})();