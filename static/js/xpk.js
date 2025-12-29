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
        const res = await fetch(url, {
            headers: { 'Content-Type': 'application/json', ...options.headers },
            ...options
        });
        return res.json();
    }
};

// 全局暴露
window.xpk = xpk;

/* ========== 广告功能 ========== */

// 广告点击统计
function xpkAdClick(adId) {
    fetch('/api.php?action=ad.click', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + adId
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
            localStorage.setItem('xpk_ad_closed_' + adId, Date.now());
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
        const closedTime = localStorage.getItem('xpk_ad_closed_' + adId);
        if (closedTime && (Date.now() - parseInt(closedTime)) < 86400000) {
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
