/**
 * 香蕉CMS 短视频滑动播放组件
 * Powered by https://xpornkit.com
 */

class XpkShortPlayer {
    constructor(options) {
        this.container = document.querySelector(options.container);
        this.videos = options.videos || [];
        this.currentIndex = 0;
        this.loading = false;
        this.page = 1;
        this.touchStartY = 0;
        this.touchEndY = 0;

        if (this.container) {
            this.init();
        }
    }

    async init() {
        if (this.videos.length === 0) {
            await this.loadVideos();
        }
        this.render();
        this.bindEvents();
        this.playCurrentVideo();
    }

    async loadVideos(append = false) {
        if (this.loading) return;
        this.loading = true;

        try {
            const res = await fetch(`/short/api/list?page=${this.page}`);
            const data = await res.json();

            if (data.code === 0) {
                if (append) {
                    this.videos = [...this.videos, ...data.data.list];
                } else {
                    this.videos = data.data.list;
                }
                
                if (data.data.has_more) {
                    this.page++;
                }
            }
        } catch (e) {
            // 静默处理加载视频失败
        }

        this.loading = false;
    }

    render() {
        this.container.innerHTML = `
            <div class="xpk-short-wrapper">
                <div class="xpk-short-slides">
                    ${this.videos.map((v, i) => this.renderSlide(v, i)).join('')}
                </div>
                
                <!-- 侧边操作栏 -->
                <div class="xpk-short-sidebar">
                    <div class="xpk-short-action like-btn" data-id="">
                        <span class="icon">❤️</span>
                        <span class="count">0</span>
                    </div>
                    <div class="xpk-short-action comment-btn">
                        <span class="icon">💬</span>
                        <span class="count">0</span>
                    </div>
                    <div class="xpk-short-action share-btn">
                        <span class="icon">↗️</span>
                        <span class="count">分享</span>
                    </div>
                </div>

                <!-- 底部信息 -->
                <div class="xpk-short-info">
                    <div class="xpk-short-title"></div>
                    <div class="xpk-short-desc"></div>
                    <div class="xpk-short-tags"></div>
                </div>

                <!-- 进度条 -->
                <div class="xpk-short-progress">
                    <div class="xpk-short-progress-bar"></div>
                </div>

                <!-- 滑动提示 -->
                <div class="xpk-short-hint">上滑查看更多</div>
            </div>
        `;

        this.updateInfo();
    }

    renderSlide(video, index) {
        return `
            <div class="xpk-short-slide ${index === this.currentIndex ? 'active' : ''}" data-index="${index}">
                <video 
                    src="${video.short_url}" 
                    poster="${video.short_pic}"
                    loop
                    playsinline
                    webkit-playsinline
                    x5-video-player-type="h5"
                    x5-video-player-fullscreen="true"
                    preload="metadata"
                ></video>
                <div class="xpk-short-play-btn">▶</div>
            </div>
        `;
    }

    bindEvents() {
        // 触摸滑动
        this.container.addEventListener('touchstart', (e) => {
            this.touchStartY = e.touches[0].clientY;
        }, { passive: true });

        this.container.addEventListener('touchend', (e) => {
            this.touchEndY = e.changedTouches[0].clientY;
            this.handleSwipe();
        });

        // 鼠标滚轮
        this.container.addEventListener('wheel', (e) => {
            e.preventDefault();
            if (e.deltaY > 0) {
                this.next();
            } else {
                this.prev();
            }
        }, { passive: false });

        // 键盘
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowDown' || e.key === 'j') {
                this.next();
            } else if (e.key === 'ArrowUp' || e.key === 'k') {
                this.prev();
            } else if (e.key === ' ') {
                e.preventDefault();
                this.togglePlay();
            }
        });

        // 点击播放/暂停
        this.container.addEventListener('click', (e) => {
            if (e.target.closest('.xpk-short-action')) return;
            if (e.target.closest('.xpk-short-info')) return;
            this.togglePlay();
        });

        // 点赞
        this.container.querySelector('.like-btn').addEventListener('click', () => {
            this.like();
        });

        // 视频进度
        const slides = this.container.querySelectorAll('.xpk-short-slide video');
        slides.forEach(video => {
            video.addEventListener('timeupdate', () => {
                if (video.closest('.active')) {
                    const progress = (video.currentTime / video.duration) * 100;
                    this.container.querySelector('.xpk-short-progress-bar').style.width = progress + '%';
                }
            });
        });
    }

    handleSwipe() {
        const diff = this.touchStartY - this.touchEndY;
        const threshold = 50;

        if (diff > threshold) {
            this.next();
        } else if (diff < -threshold) {
            this.prev();
        }
    }

    async next() {
        if (this.currentIndex < this.videos.length - 1) {
            this.pauseCurrentVideo();
            this.currentIndex++;
            this.updateSlides();
            this.playCurrentVideo();
            this.updateInfo();

            // 预加载更多
            if (this.currentIndex >= this.videos.length - 3) {
                await this.loadVideos(true);
                this.appendSlides();
            }
        }
    }

    prev() {
        if (this.currentIndex > 0) {
            this.pauseCurrentVideo();
            this.currentIndex--;
            this.updateSlides();
            this.playCurrentVideo();
            this.updateInfo();
        }
    }

    updateSlides() {
        const slides = this.container.querySelectorAll('.xpk-short-slide');
        slides.forEach((slide, i) => {
            slide.classList.toggle('active', i === this.currentIndex);
            slide.style.transform = `translateY(${(i - this.currentIndex) * 100}%)`;
        });
    }

    appendSlides() {
        const slidesContainer = this.container.querySelector('.xpk-short-slides');
        const existingCount = slidesContainer.children.length;
        
        for (let i = existingCount; i < this.videos.length; i++) {
            const slide = document.createElement('div');
            slide.className = 'xpk-short-slide';
            slide.dataset.index = i;
            slide.style.transform = `translateY(${(i - this.currentIndex) * 100}%)`;
            slide.innerHTML = `
                <video 
                    src="${this.videos[i].short_url}" 
                    poster="${this.videos[i].short_pic}"
                    loop playsinline webkit-playsinline
                    preload="metadata"
                ></video>
                <div class="xpk-short-play-btn">▶</div>
            `;
            slidesContainer.appendChild(slide);
        }
    }

    playCurrentVideo() {
        const slide = this.container.querySelector('.xpk-short-slide.active');
        if (slide) {
            const video = slide.querySelector('video');
            video.play().catch(() => {});
            slide.querySelector('.xpk-short-play-btn').style.display = 'none';
        }
    }

    pauseCurrentVideo() {
        const slide = this.container.querySelector('.xpk-short-slide.active');
        if (slide) {
            const video = slide.querySelector('video');
            video.pause();
        }
    }

    togglePlay() {
        const slide = this.container.querySelector('.xpk-short-slide.active');
        if (slide) {
            const video = slide.querySelector('video');
            const playBtn = slide.querySelector('.xpk-short-play-btn');
            
            if (video.paused) {
                video.play();
                playBtn.style.display = 'none';
            } else {
                video.pause();
                playBtn.style.display = 'flex';
            }
        }
    }

    updateInfo() {
        const video = this.videos[this.currentIndex];
        if (!video) return;

        this.container.querySelector('.xpk-short-title').textContent = video.short_name;
        this.container.querySelector('.xpk-short-desc').textContent = video.short_desc || '';
        
        const tagsEl = this.container.querySelector('.xpk-short-tags');
        if (video.short_tags) {
            tagsEl.innerHTML = video.short_tags.split(',').map(t => `<span class="tag">#${t.trim()}</span>`).join(' ');
        } else {
            tagsEl.innerHTML = '';
        }

        // 更新侧边栏
        this.container.querySelector('.like-btn').dataset.id = video.short_id;
        this.container.querySelector('.like-btn .count').textContent = this.formatNumber(video.short_likes);
        this.container.querySelector('.comment-btn .count').textContent = this.formatNumber(video.short_comments);
    }

    async like() {
        const btn = this.container.querySelector('.like-btn');
        const id = btn.dataset.id;

        try {
            const res = await fetch('/short/api/like', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}`
            });
            const data = await res.json();

            if (data.code === 0) {
                btn.classList.add('liked');
                btn.querySelector('.count').textContent = this.formatNumber(data.data.likes);
                this.videos[this.currentIndex].short_likes = data.data.likes;
            }
        } catch (e) {
            // 静默处理点赞失败
        }
    }

    formatNumber(num) {
        if (num >= 10000) {
            return (num / 10000).toFixed(1) + 'w';
        }
        if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'k';
        }
        return num;
    }
}

// 注册到命名空间
if (typeof window.XPK !== 'undefined') {
    window.XPK.register('ShortPlayer', XpkShortPlayer);
} else {
    // 降级：全局暴露
    window.XpkShortPlayer = XpkShortPlayer;
}
