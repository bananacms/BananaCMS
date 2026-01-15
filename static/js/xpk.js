/* 香蕉CMS 公共脚本 - Powered by xpornkit.com */

const xpk = {
    // Toast 提示
    toast(msg, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `xpk-toast ${type}`;
        toast.textContent = msg;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    },

    // 确认框
    confirm(msg, onConfirm, onCancel) {
        const modal = document.createElement('div');
        modal.className = 'xpk-modal';
        modal.innerHTML = `
            <div class="xpk-modal-content">
                <p class="text-lg mb-6">${msg}</p>
                <div class="flex justify-end space-x-3">
                    <button class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300" data-action="cancel">取消</button>
                    <button class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700" data-action="confirm">确定</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        
        modal.addEventListener('click', (e) => {
            const action = e.target.dataset.action;
            if (action === 'confirm') {
                onConfirm && onConfirm();
                modal.remove();
            } else if (action === 'cancel' || e.target === modal) {
                onCancel && onCancel();
                modal.remove();
            }
        });
    },

    // AJAX 请求
    async fetch(url, options = {}) {
        // 自动添加CSRF令牌
        const token = document.querySelector('input[name="_token"]')?.value || 
                     document.querySelector('meta[name="csrf-token"]')?.content;
        
        const headers = { 'Content-Type': 'application/json', ...options.headers };
        if (token) {
            headers['X-CSRF-Token'] = token;
        }
        
        // 添加请求标识
        headers['X-Requested-With'] = 'XMLHttpRequest';
        
        const requestOptions = {
            headers,
            ...options
        };
        
        try {
            const res = await fetch(url, requestOptions);
            
            // 检查HTTP状态
            if (!res.ok) {
                const errorData = {
                    status: res.status,
                    statusText: res.statusText,
                    url: url,
                    method: requestOptions.method || 'GET'
                };
                
                // 尝试解析错误响应
                try {
                    const errorBody = await res.text();
                    errorData.body = errorBody;
                } catch (e) {
                    // 忽略解析错误
                }
                
                throw new Error(`HTTP ${res.status}: ${res.statusText}`);
            }
            
            const data = await res.json();
            
            // 检查响应格式
            if (!data || typeof data !== 'object') {
                throw new Error('Invalid response format');
            }
            
            return data;
        } catch (error) {
            // 记录错误
            if (window.XPK) {
                const errorHandler = window.XPK.get('errorHandler');
                if (errorHandler) {
                    errorHandler.logError('XPK Fetch Error', {
                        url: url,
                        options: requestOptions,
                        error: error.message
                    });
                }
            } else if (window.errorHandler) {
                window.errorHandler.logError('XPK Fetch Error', {
                    url: url,
                    options: requestOptions,
                    error: error.message
                });
            }
            
            // 重新抛出错误供调用者处理
            throw error;
        }
    },

    // 安全的Cookie操作
    setCookie(name, value, days = 1) {
        const expires = new Date();
        expires.setTime(expires.getTime() + days * 24 * 60 * 60 * 1000);
        const secure = location.protocol === 'https:' ? '; Secure' : '';
        document.cookie = `${name}=${encodeURIComponent(value)}; expires=${expires.toUTCString()}; path=/; SameSite=Lax${secure}`;
    },

    getCookie(name) {
        const nameEQ = name + '=';
        const ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) {
                return decodeURIComponent(c.substring(nameEQ.length, c.length));
            }
        }
        return null;
    },

    deleteCookie(name) {
        document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; SameSite=Lax`;
    }
};

// 全局暴露 - 使用命名空间
if (typeof window.XPK !== 'undefined') {
    // 注册到XPK命名空间
    window.XPK.registerUtil('toast', xpk.toast);
    window.XPK.registerUtil('confirm', xpk.confirm);
    window.XPK.registerUtil('fetch', xpk.fetch);
    window.XPK.registerUtil('setCookie', xpk.setCookie);
    window.XPK.registerUtil('getCookie', xpk.getCookie);
    window.XPK.registerUtil('deleteCookie', xpk.deleteCookie);
} else {
    // 降级：直接暴露到全局（向后兼容）
    window.xpk = xpk;
}

// 向后兼容：保留原有的全局函数
window.xpkSetCookie = xpk.setCookie.bind(xpk);
window.xpkGetCookie = xpk.getCookie.bind(xpk);
window.xpkDeleteCookie = xpk.deleteCookie.bind(xpk);

/* ========== AJAX 表单处理 ========== */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[data-ajax]').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const btn = form.querySelector('button[type="submit"]');
            const btnText = btn ? btn.textContent : '';
            const statusDiv = form.querySelector('.form-status');
            
            // 防止重复提交
            if (btn?.disabled) return;
            
            if (btn) {
                btn.disabled = true;
                btn.textContent = '处理中...';
            }
            
            if (statusDiv) {
                statusDiv.textContent = '正在提交...';
                statusDiv.className = 'form-status';
            }
            
            try {
                const formData = new FormData(form);
                
                // 确保包含CSRF令牌
                if (!formData.has('_token')) {
                    const token = document.querySelector('input[name="_token"]')?.value || 
                                 document.querySelector('meta[name="csrf-token"]')?.content;
                    if (token) {
                        formData.append('_token', token);
                    }
                }
                
                const res = await fetch(form.action, {
                    method: form.method || 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (!res.ok) {
                    throw new Error(`HTTP ${res.status}: ${res.statusText}`);
                }
                
                const data = await res.json();
                
                if (!data || typeof data !== 'object') {
                    throw new Error('Invalid response format');
                }
                
                if (data.code === 0) {
                    xpk.toast(data.msg || '操作成功', 'success');
                    
                    if (statusDiv) {
                        statusDiv.textContent = data.msg || '操作成功';
                        statusDiv.className = 'form-status text-green-600';
                    }
                    
                    // 如果有跳转URL
                    if (data.data && data.data.url) {
                        setTimeout(() => {
                            window.location.href = data.data.url;
                        }, 1000);
                    }
                    
                    // 重置表单（可选）
                    if (form.dataset.reset !== 'false') {
                        form.reset();
                    }
                } else {
                    const errorMsg = data.msg || '操作失败';
                    xpk.toast(errorMsg, 'error');
                    
                    if (statusDiv) {
                        statusDiv.textContent = errorMsg;
                        statusDiv.className = 'form-status text-red-600';
                    }
                    
                    // 处理字段级错误
                    if (data.errors && typeof data.errors === 'object') {
                        Object.keys(data.errors).forEach(fieldName => {
                            const field = form.querySelector(`[name="${fieldName}"]`);
                            const formAccessibility = window.XPK ? window.XPK.get('FormAccessibility') : window.FormAccessibility;
                            if (field && formAccessibility) {
                                // 使用表单无障碍系统显示错误
                                const event = new CustomEvent('xpk-form-error', {
                                    detail: { form: form, errors: data.errors }
                                });
                                document.dispatchEvent(event);
                            }
                        });
                    }
                }
            } catch (error) {
                let errorMessage = '提交失败，请重试';
                
                // 根据错误类型提供更具体的消息
                if (error.name === 'TypeError' && error.message.includes('fetch')) {
                    errorMessage = '网络连接失败，请检查网络连接';
                } else if (error.message.includes('timeout')) {
                    errorMessage = '请求超时，请重试';
                } else if (error.message.includes('HTTP 500')) {
                    errorMessage = '服务器内部错误，请稍后重试';
                } else if (error.message.includes('HTTP 404')) {
                    errorMessage = '请求的页面不存在';
                } else if (error.message.includes('HTTP 403')) {
                    errorMessage = '没有权限执行此操作';
                } else if (error.message.includes('Invalid response')) {
                    errorMessage = '服务器响应格式错误';
                }
                
                xpk.toast(errorMessage, 'error');
                
                if (statusDiv) {
                    statusDiv.textContent = errorMessage;
                    statusDiv.className = 'form-status text-red-600';
                }
                
                // 记录错误但不输出到控制台
                const errorHandler = window.XPK ? window.XPK.get('errorHandler') : window.errorHandler;
                if (errorHandler) {
                    errorHandler.logError('Form Submission Error', {
                        form: form.action,
                        method: form.method,
                        error: error.message
                    });
                }
            } finally {
                if (btn) {
                    btn.disabled = false;
                    btn.textContent = btnText;
                }
            }
        });
    });
});

/* ========== 广告功能 ========== */

// 广告点击统计
function xpkAdClick(adId) {
    const token = document.querySelector('input[name="_token"]')?.value || 
                 document.querySelector('meta[name="csrf-token"]')?.content;
    
    const body = 'id=' + adId + (token ? '&_token=' + encodeURIComponent(token) : '');
    
    fetch('/api.php?action=ad.click', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body
    });
}

// 关闭悬浮广告
function xpkAdClose(el) {
    const ad = el.closest('.xpk-ad-float');
    if (ad) {
        ad.style.display = 'none';
        // 记录关闭状态，24小时内不再显示
        const adId = ad.dataset.id;
        if (adId) {
            xpkSetCookie('xpk_ad_closed_' + adId, '1', 1); // 1天过期
        }
    }
}

// 关闭弹窗广告
function xpkPopupClose(el) {
    const popup = el.closest('.xpk-ad-popup');
    if (popup) {
        popup.remove();
    }
}

// 视频广告控制
class XpkVideoAd {
    constructor(container) {
        this.container = container;
        this.video = container.querySelector('video');
        this.skipBtn = container.querySelector('.xpk-ad-skip');
        this.skipTime = parseInt(this.video.dataset.skip) || 5;
        this.duration = parseInt(this.video.dataset.duration) || 15;
        this.link = this.video.dataset.link || '';
        this.elapsed = 0;
        
        this.init();
    }
    
    init() {
        // 添加倒计时
        this.countdown = document.createElement('span');
        this.countdown.className = 'xpk-ad-countdown';
        this.container.appendChild(this.countdown);
        
        // 视频播放事件
        this.video.addEventListener('timeupdate', () => this.onTimeUpdate());
        this.video.addEventListener('ended', () => this.onEnded());
        
        // 跳过按钮
        if (this.skipBtn) {
            this.skipBtn.addEventListener('click', () => this.skip());
        }
        
        // 点击跳转
        this.video.addEventListener('click', () => {
            if (this.link) {
                xpkAdClick(this.container.dataset.id);
                window.open(this.link, '_blank');
            }
        });
    }
    
    onTimeUpdate() {
        this.elapsed = Math.floor(this.video.currentTime);
        const remaining = this.duration - this.elapsed;
        
        this.countdown.textContent = `广告 ${remaining}s`;
        
        // 显示跳过按钮
        if (this.skipTime > 0 && this.elapsed >= this.skipTime && this.skipBtn) {
            this.skipBtn.style.display = 'block';
            this.skipBtn.textContent = '跳过广告';
        } else if (this.skipBtn && this.skipTime > 0) {
            this.skipBtn.style.display = 'block';
            this.skipBtn.textContent = `${this.skipTime - this.elapsed}s 后可跳过`;
        }
    }
    
    onEnded() {
        this.container.dispatchEvent(new CustomEvent('adended'));
    }
    
    skip() {
        if (this.elapsed >= this.skipTime) {
            this.video.pause();
            this.container.dispatchEvent(new CustomEvent('adskipped'));
        }
    }
}

// 初始化视频广告
document.querySelectorAll('.xpk-ad-video').forEach(el => {
    new XpkVideoAd(el);
});

// 检查悬浮广告是否应该显示
document.querySelectorAll('.xpk-ad-float').forEach(el => {
    const adId = el.dataset.id;
    if (adId) {
        const isClosed = xpkGetCookie('xpk_ad_closed_' + adId);
        if (isClosed) {
            el.style.display = 'none';
        }
    }
});

// 弹窗广告（页面加载后显示）
document.addEventListener('DOMContentLoaded', () => {
    const popupAds = document.querySelectorAll('.xpk-ad-popup');
    popupAds.forEach((popup, index) => {
        // 延迟显示
        setTimeout(() => {
            popup.style.display = 'flex';
        }, (index + 1) * 2000);
    });
});
/* ========== 图片懒加载 ========== */

// 图片懒加载类
class XpkLazyLoad {
    constructor() {
        this.images = document.querySelectorAll('img.lazy-load');
        this.imageObserver = null;
        this.init();
    }
    
    init() {
        // 检查浏览器是否支持 Intersection Observer
        if ('IntersectionObserver' in window) {
            this.imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.loadImage(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                // 提前100px开始加载
                rootMargin: '100px 0px',
                threshold: 0.01
            });
            
            this.images.forEach(img => this.imageObserver.observe(img));
        } else {
            // 降级处理：直接加载所有图片
            this.images.forEach(img => this.loadImage(img));
        }
    }
    
    loadImage(img) {
        const src = img.dataset.src;
        if (!src) return;
        
        // 创建新的图片对象预加载
        const imageLoader = new Image();
        
        imageLoader.onload = () => {
            // 加载成功后替换src
            img.src = src;
            img.classList.remove('lazy-load');
            img.classList.add('lazy-loaded');
            
            // 添加淡入效果
            img.style.opacity = '0';
            img.style.transition = 'opacity 0.3s ease-in-out';
            setTimeout(() => {
                img.style.opacity = '1';
            }, 10);
        };
        
        imageLoader.onerror = () => {
            // 加载失败时使用默认图片
            img.src = '/static/images/no-image.svg';
            img.classList.remove('lazy-load');
            img.classList.add('lazy-error');
        };
        
        imageLoader.src = src;
    }
    
    // 手动触发加载（用于动态添加的图片）
    refresh() {
        const newImages = document.querySelectorAll('img.lazy-load');
        if (this.imageObserver) {
            newImages.forEach(img => this.imageObserver.observe(img));
        } else {
            newImages.forEach(img => this.loadImage(img));
        }
    }
}

// 初始化懒加载
document.addEventListener('DOMContentLoaded', () => {
    const lazyLoadInstance = new XpkLazyLoad();
    
    // 注册到命名空间
    if (typeof window.XPK !== 'undefined') {
        window.XPK.register('lazyLoad', lazyLoadInstance);
    } else {
        // 降级：使用全局变量
        window.xpkLazyLoad = lazyLoadInstance;
    }
});

// 暴露到xpk工具对象
xpk.lazyLoad = {
    refresh: () => {
        const instance = window.XPK ? window.XPK.get('lazyLoad') : window.xpkLazyLoad;
        return instance?.refresh();
    }
};