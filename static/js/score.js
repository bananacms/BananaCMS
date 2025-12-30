/**
 * 香蕉CMS 评分组件
 * Powered by https://xpornkit.com
 */

class XpkScore {
    constructor(options) {
        this.container = document.querySelector(options.container);
        this.type = options.type || 'vod';
        this.targetId = options.targetId;
        this.readonly = options.readonly || false;
        this.size = options.size || 'normal'; // small, normal, large
        
        this.stats = { count: 0, average: 0, distribution: {} };
        this.userScore = null;
        this.hasRated = false;
        this.hoverScore = 0;

        if (this.container) {
            this.init();
        }
    }

    async init() {
        await this.loadStats();
        this.render();
        if (!this.readonly) {
            this.bindEvents();
        }
    }

    async loadStats() {
        try {
            const res = await fetch(`/score/stats?type=${this.type}&target_id=${this.targetId}`);
            const data = await res.json();
            if (data.code === 0) {
                this.stats = data.data.stats;
                this.userScore = data.data.user_score;
                this.hasRated = data.data.has_rated;
            }
        } catch (e) {
            console.error('加载评分失败', e);
        }
    }

    render() {
        const sizeClass = {
            small: 'text-sm',
            normal: 'text-base',
            large: 'text-lg'
        }[this.size];

        const starSize = {
            small: 'w-4 h-4',
            normal: 'w-6 h-6',
            large: 'w-8 h-8'
        }[this.size];

        this.container.innerHTML = `
            <div class="xpk-score-box ${sizeClass}">
                <!-- 评分显示 -->
                <div class="flex items-center gap-4 mb-3">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-yellow-500">${this.stats.average || '-'}</div>
                        <div class="text-xs text-gray-400">${this.stats.count} 人评分</div>
                    </div>
                    <div class="flex-1">
                        <div class="xpk-stars flex gap-1" data-score="${this.userScore || 0}">
                            ${this.renderStars(this.stats.average, starSize)}
                        </div>
                        ${this.hasRated ? `<div class="text-xs text-gray-400 mt-1">您的评分: ${this.userScore}分</div>` : ''}
                    </div>
                </div>

                ${!this.readonly ? `
                <!-- 评分操作 -->
                <div class="xpk-score-action ${this.hasRated ? 'rated' : ''}">
                    <div class="text-sm text-gray-500 mb-2">${this.hasRated ? '修改评分:' : '点击评分:'}</div>
                    <div class="xpk-score-stars flex gap-1 cursor-pointer">
                        ${this.renderInteractiveStars(starSize)}
                    </div>
                    <div class="xpk-score-text text-sm text-gray-500 mt-1 h-5"></div>
                </div>
                ` : ''}

                ${this.stats.count > 0 ? `
                <!-- 评分分布 -->
                <div class="xpk-score-dist mt-4 pt-4 border-t">
                    <div class="text-xs text-gray-400 mb-2">评分分布</div>
                    ${this.renderDistribution()}
                </div>
                ` : ''}
            </div>
        `;
    }

    renderStars(score, sizeClass) {
        const fullStars = Math.floor(score / 2);
        const halfStar = (score % 2) >= 1;
        const emptyStars = 5 - fullStars - (halfStar ? 1 : 0);

        let html = '';
        
        // 满星
        for (let i = 0; i < fullStars; i++) {
            html += `<span class="star ${sizeClass} text-yellow-400">★</span>`;
        }
        
        // 半星
        if (halfStar) {
            html += `<span class="star ${sizeClass} text-yellow-400 relative">
                <span class="absolute overflow-hidden w-1/2">★</span>
                <span class="text-gray-300">★</span>
            </span>`;
        }
        
        // 空星
        for (let i = 0; i < emptyStars; i++) {
            html += `<span class="star ${sizeClass} text-gray-300">★</span>`;
        }

        return html;
    }

    renderInteractiveStars(sizeClass) {
        let html = '';
        for (let i = 1; i <= 10; i++) {
            const isHalf = i % 2 === 1;
            const starNum = Math.ceil(i / 2);
            html += `
                <span class="score-star ${sizeClass} ${isHalf ? 'half' : 'full'} text-gray-300 transition-colors" 
                      data-score="${i}" data-star="${starNum}">
                    ${isHalf ? '★' : ''}
                </span>
            `;
        }
        // 简化为5颗星
        html = '';
        for (let i = 1; i <= 5; i++) {
            html += `
                <span class="score-star text-2xl text-gray-300 hover:text-yellow-400 transition-colors cursor-pointer" 
                      data-score="${i * 2}">★</span>
            `;
        }
        return html;
    }

    renderDistribution() {
        const dist = this.stats.distribution || {};
        const max = Math.max(...Object.values(dist), 1);
        
        let html = '<div class="space-y-1">';
        for (let i = 10; i >= 1; i--) {
            const count = dist[i] || 0;
            const percent = max > 0 ? (count / max * 100) : 0;
            html += `
                <div class="flex items-center gap-2 text-xs">
                    <span class="w-6 text-right text-gray-500">${i}分</span>
                    <div class="flex-1 h-2 bg-gray-100 rounded overflow-hidden">
                        <div class="h-full bg-yellow-400 rounded" style="width: ${percent}%"></div>
                    </div>
                    <span class="w-8 text-gray-400">${count}</span>
                </div>
            `;
        }
        html += '</div>';
        return html;
    }

    bindEvents() {
        const stars = this.container.querySelectorAll('.score-star');
        const textEl = this.container.querySelector('.xpk-score-text');
        
        const scoreTexts = {
            2: '很差',
            4: '较差', 
            6: '还行',
            8: '推荐',
            10: '力荐'
        };

        stars.forEach(star => {
            // 悬停效果
            star.addEventListener('mouseenter', () => {
                const score = parseInt(star.dataset.score);
                this.highlightStars(score);
                if (textEl) textEl.textContent = `${score}分 - ${scoreTexts[score] || ''}`;
            });

            // 移出恢复
            star.addEventListener('mouseleave', () => {
                this.highlightStars(this.userScore || 0);
                if (textEl) textEl.textContent = '';
            });

            // 点击评分
            star.addEventListener('click', () => {
                const score = parseInt(star.dataset.score);
                this.submitScore(score);
            });
        });
    }

    highlightStars(score) {
        const stars = this.container.querySelectorAll('.score-star');
        const targetStar = Math.ceil(score / 2);
        
        stars.forEach((star, index) => {
            if (index < targetStar) {
                star.classList.remove('text-gray-300');
                star.classList.add('text-yellow-400');
            } else {
                star.classList.remove('text-yellow-400');
                star.classList.add('text-gray-300');
            }
        });
    }

    async submitScore(score) {
        try {
            const res = await fetch('/score/rate', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `type=${this.type}&target_id=${this.targetId}&score=${score}`
            });
            const data = await res.json();

            if (data.code === 0) {
                xpk.toast(data.msg, 'success');
                this.stats = data.data.stats;
                this.userScore = score;
                this.hasRated = true;
                this.render();
                this.bindEvents();
            } else if (data.code === 2) {
                xpk.toast('请先登录', 'warning');
            } else {
                xpk.toast(data.msg, 'error');
            }
        } catch (e) {
            xpk.toast('评分失败', 'error');
        }
    }
}

// 简化版评分显示（只读）
function xpkScoreDisplay(container, score, count = 0) {
    const el = document.querySelector(container);
    if (!el) return;

    const fullStars = Math.floor(score / 2);
    const halfStar = (score % 2) >= 1;
    
    let stars = '★'.repeat(fullStars);
    if (halfStar) stars += '½';
    stars += '☆'.repeat(5 - fullStars - (halfStar ? 1 : 0));

    el.innerHTML = `
        <span class="text-yellow-400">${stars}</span>
        <span class="text-yellow-500 font-bold ml-1">${score}</span>
        ${count > 0 ? `<span class="text-gray-400 text-xs ml-1">(${count}人)</span>` : ''}
    `;
}

// 全局暴露
window.XpkScore = XpkScore;
window.xpkScoreDisplay = xpkScoreDisplay;
