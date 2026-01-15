/**
 * 香蕉CMS 评论组件
 * Powered by https://xpornkit.com
 */

class XpkComment {
    constructor(options) {
        this.container = document.querySelector(options.container);
        this.type = options.type || 'vod';
        this.targetId = options.targetId;
        this.page = 1;
        this.loading = false;
        this.userVotes = {};
        
        if (this.container) {
            this.init();
        }
    }

    init() {
        this.render();
        this.loadComments();
        this.bindEvents();
    }

    render() {
        this.container.innerHTML = `
            <div class="xpk-comment-box">
                <h3 class="text-lg font-bold mb-4">💬 评论</h3>
                
                <!-- 发表评论 -->
                <div class="xpk-comment-form bg-gray-50 p-4 rounded mb-6" role="form" aria-labelledby="commentFormTitle">
                    <h4 id="commentFormTitle" class="sr-only">发表评论</h4>
                    <label for="commentContent" class="sr-only">评论内容</label>
                    <textarea id="commentContent" 
                              class="w-full border rounded p-3 resize-none" 
                              rows="3" 
                              placeholder="说点什么吧..." 
                              maxlength="500"
                              aria-describedby="commentHint charCount"
                              aria-label="评论内容"></textarea>
                    <div id="commentHint" class="sr-only">请输入您的评论，最多500个字符</div>
                    <div class="flex justify-between items-center mt-2">
                        <span class="text-sm text-gray-400" aria-live="polite">
                            <span id="charCount">0</span>/500
                        </span>
                        <button id="submitComment" 
                                class="bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700"
                                aria-describedby="submitStatus">
                            发表评论
                        </button>
                    </div>
                    <div id="submitStatus" class="sr-only" aria-live="polite" aria-atomic="true"></div>
                </div>

                <!-- 评论列表 -->
                <div id="commentList" class="space-y-4" role="list" aria-label="评论列表"></div>

                <!-- 加载更多 -->
                <div id="loadMore" class="text-center py-4 hidden">
                    <button class="text-gray-500 hover:text-gray-700" aria-label="加载更多评论">加载更多评论</button>
                </div>

                <!-- 空状态 -->
                <div id="emptyState" class="text-center py-8 text-gray-400 hidden" role="status">
                    暂无评论，快来抢沙发吧~
                </div>
            </div>
        `;
    }

    bindEvents() {
        // 字数统计
        const textarea = this.container.querySelector('#commentContent');
        const charCount = this.container.querySelector('#charCount');
        textarea.addEventListener('input', () => {
            charCount.textContent = textarea.value.length;
        });

        // 发表评论
        this.container.querySelector('#submitComment').addEventListener('click', () => {
            this.submitComment();
        });

        // 加载更多
        this.container.querySelector('#loadMore').addEventListener('click', () => {
            this.page++;
            this.loadComments(true);
        });

        // 事件委托
        this.container.querySelector('#commentList').addEventListener('click', (e) => {
            const target = e.target;
            
            // 点赞/踩
            if (target.closest('.vote-btn')) {
                const btn = target.closest('.vote-btn');
                this.vote(btn.dataset.id, btn.dataset.action);
            }
            
            // 回复
            if (target.closest('.reply-btn')) {
                const btn = target.closest('.reply-btn');
                this.showReplyForm(btn.dataset.id, btn.dataset.parentId || btn.dataset.id);
            }
            
            // 删除
            if (target.closest('.delete-btn')) {
                const btn = target.closest('.delete-btn');
                this.deleteComment(btn.dataset.id);
            }
            
            // 加载更多回复
            if (target.closest('.load-replies')) {
                const btn = target.closest('.load-replies');
                this.loadMoreReplies(btn.dataset.parentId, btn);
            }
            
            // 提交回复
            if (target.closest('.submit-reply')) {
                const btn = target.closest('.submit-reply');
                this.submitReply(btn);
            }
            
            // 取消回复
            if (target.closest('.cancel-reply')) {
                const form = target.closest('.reply-form-box');
                if (form) form.remove();
            }
        });
    }

    async loadComments(append = false) {
        if (this.loading) return;
        this.loading = true;

        try {
            const res = await fetch(`/comment/list?type=${this.type}&id=${this.targetId}&page=${this.page}`);
            
            // 检查HTTP状态
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            
            const data = await res.json();
            
            // 检查响应格式
            if (!data || typeof data !== 'object') {
                throw new Error('Invalid response format');
            }

            if (data.code === 0) {
                this.userVotes = data.data.user_votes || {};
                
                if (append) {
                    this.appendComments(data.data.list);
                } else {
                    this.renderComments(data.data.list, data.data.total);
                }

                // 显示/隐藏加载更多
                const hasMore = this.page * 20 < data.data.total;
                this.container.querySelector('#loadMore').classList.toggle('hidden', !hasMore);
            } else {
                throw new Error(data.msg || '加载评论失败');
            }
        } catch (error) {
            this.showError('加载评论失败，请刷新页面重试');
            
            // 显示错误状态
            const container = this.container.querySelector('#commentList');
            const emptyState = this.container.querySelector('#emptyState');
            
            if (container && emptyState) {
                container.innerHTML = '';
                emptyState.innerHTML = `
                    <div class="text-center py-8">
                        <p class="text-red-500 mb-2">😕 加载失败</p>
                        <p class="text-gray-500 text-sm mb-4">${error.message}</p>
                        <button onclick="location.reload()" class="text-blue-600 hover:underline">刷新页面</button>
                    </div>
                `;
                emptyState.classList.remove('hidden');
            }
        } finally {
            this.loading = false;
        }
    }

    renderComments(list, total) {
        const container = this.container.querySelector('#commentList');
        const emptyState = this.container.querySelector('#emptyState');

        if (list.length === 0) {
            container.innerHTML = '';
            emptyState.classList.remove('hidden');
            return;
        }

        emptyState.classList.add('hidden');
        container.innerHTML = list.map(item => this.renderCommentItem(item)).join('');
    }

    appendComments(list) {
        const container = this.container.querySelector('#commentList');
        container.insertAdjacentHTML('beforeend', list.map(item => this.renderCommentItem(item)).join(''));
    }

    renderCommentItem(item) {
        const userVote = this.userVotes[item.comment_id] || '';
        const replies = item.replies || [];
        const replyCount = item.reply_count || 0;

        return `
            <div class="comment-item border-b pb-4" data-id="${this.escape(item.comment_id)}" role="listitem">
                <div class="flex gap-3">
                    <img src="${this.escape(item.user_pic || '/static/images/avatar.svg')}" 
                         class="w-10 h-10 rounded-full bg-gray-200" 
                         alt="${this.escape(item.user_nick_name || item.user_name || '游客')}的头像">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-medium">${this.escape(item.user_nick_name || item.user_name || '游客')}</span>
                            <time class="text-xs text-gray-400" datetime="${new Date(item.comment_time * 1000).toISOString()}">${this.formatTime(item.comment_time)}</time>
                        </div>
                        <p class="text-gray-700 mb-2">${this.escape(item.comment_content)}</p>
                        <div class="flex items-center gap-4 text-sm text-gray-500" role="group" aria-label="评论操作">
                            <button class="vote-btn flex items-center gap-1 hover:text-red-600 ${userVote === 'up' ? 'text-red-600' : ''}" 
                                    data-id="${this.escape(item.comment_id)}" 
                                    data-action="up"
                                    aria-label="点赞，当前${item.comment_up}个赞"
                                    aria-pressed="${userVote === 'up' ? 'true' : 'false'}">
                                👍 <span class="up-count">${item.comment_up}</span>
                            </button>
                            <button class="vote-btn flex items-center gap-1 hover:text-gray-700 ${userVote === 'down' ? 'text-gray-700' : ''}" 
                                    data-id="${this.escape(item.comment_id)}" 
                                    data-action="down"
                                    aria-label="踩，当前${item.comment_down}个踩"
                                    aria-pressed="${userVote === 'down' ? 'true' : 'false'}">
                                👎 <span class="down-count">${item.comment_down}</span>
                            </button>
                            <button class="reply-btn hover:text-blue-600" 
                                    data-id="${this.escape(item.comment_id)}"
                                    aria-label="回复这条评论">回复</button>
                        </div>

                        <!-- 回复列表 -->
                        ${replies.length > 0 ? `
                        <div class="replies mt-3 pl-4 border-l-2 border-gray-100 space-y-3" role="list" aria-label="回复列表">
                            ${replies.map(reply => this.renderReplyItem(reply, item.comment_id)).join('')}
                            ${replyCount > replies.length ? `
                            <button class="load-replies text-sm text-blue-600 hover:underline" 
                                    data-parent-id="${item.comment_id}" 
                                    data-offset="${replies.length}"
                                    aria-label="查看更多${replyCount - replies.length}条回复">
                                查看更多 ${replyCount - replies.length} 条回复
                            </button>
                            ` : ''}
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
    }

    renderReplyItem(reply, parentId) {
        const userVote = this.userVotes[reply.comment_id] || '';
        const replyTo = reply.reply_to_name ? `<span class="text-blue-600">@${this.escape(reply.reply_to_name)}</span> ` : '';

        return `
            <div class="reply-item" data-id="${this.escape(reply.comment_id)}">
                <div class="flex gap-2">
                    <img src="${this.escape(reply.user_pic || '/static/images/avatar.svg')}" 
                         class="w-8 h-8 rounded-full bg-gray-200" alt="">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-medium text-sm">${this.escape(reply.user_nick_name || reply.user_name || '游客')}</span>
                            <span class="text-xs text-gray-400">${this.formatTime(reply.comment_time)}</span>
                        </div>
                        <p class="text-gray-700 text-sm mb-1">${replyTo}${this.escape(reply.comment_content)}</p>
                        <div class="flex items-center gap-3 text-xs text-gray-500">
                            <button class="vote-btn flex items-center gap-1 hover:text-red-600 ${userVote === 'up' ? 'text-red-600' : ''}" 
                                    data-id="${this.escape(reply.comment_id)}" data-action="up">
                                👍 <span class="up-count">${reply.comment_up}</span>
                            </button>
                            <button class="reply-btn hover:text-blue-600" 
                                    data-id="${this.escape(reply.comment_id)}" data-parent-id="${this.escape(parentId)}">回复</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    showReplyForm(replyId, parentId) {
        // 移除已有的回复框
        this.container.querySelectorAll('.reply-form-box').forEach(el => el.remove());

        const item = this.container.querySelector(`[data-id="${replyId}"]`);
        if (!item) return;

        const replyFormId = `reply-form-${replyId}`;
        const replyTextareaId = `reply-textarea-${replyId}`;

        const form = document.createElement('div');
        form.className = 'reply-form-box mt-3 bg-gray-50 p-3 rounded';
        form.innerHTML = `
            <div class="form-group">
                <label for="${replyTextareaId}" class="sr-only">回复内容</label>
                <textarea id="${replyTextareaId}"
                          class="reply-content w-full border rounded p-2 text-sm resize-none" 
                          rows="2" 
                          placeholder="回复..." 
                          maxlength="500"
                          aria-describedby="${replyFormId}-hint"
                          aria-label="回复内容"></textarea>
                <div id="${replyFormId}-hint" class="sr-only">请输入回复内容，最多500个字符</div>
            </div>
            <div class="flex justify-end gap-2 mt-2">
                <button class="cancel-reply text-sm text-gray-500 hover:text-gray-700 px-3 py-1"
                        aria-label="取消回复">取消</button>
                <button class="submit-reply text-sm bg-red-600 text-white px-4 py-1 rounded hover:bg-red-700"
                        data-parent-id="${parentId}" 
                        data-reply-id="${replyId}"
                        aria-label="提交回复">回复</button>
            </div>
        `;

        item.appendChild(form);
        const textarea = form.querySelector('textarea');
        textarea.focus();
        
        // 设置焦点到文本框末尾
        textarea.setSelectionRange(textarea.value.length, textarea.value.length);
    }

    async submitComment() {
        const textarea = this.container.querySelector('#commentContent');
        const content = textarea.value.trim();
        const submitBtn = this.container.querySelector('#submitComment');
        const statusDiv = this.container.querySelector('#submitStatus');

        if (!content) {
            this.showError('请输入评论内容');
            textarea.focus();
            return;
        }

        // 防止重复提交
        if (submitBtn.disabled) return;
        
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = '发表中...';
        
        if (statusDiv) {
            statusDiv.textContent = '正在发表评论...';
        }

        try {
            const res = await fetch('/comment/post', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `type=${this.type}&target_id=${this.targetId}&content=${encodeURIComponent(content)}`
            });

            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }

            const data = await res.json();
            
            if (!data || typeof data !== 'object') {
                throw new Error('Invalid response format');
            }

            if (data.code === 0) {
                xpk.toast(data.msg || '评论发表成功', 'success');
                textarea.value = '';
                this.container.querySelector('#charCount').textContent = '0';
                this.page = 1;
                await this.loadComments();
                
                if (statusDiv) {
                    statusDiv.textContent = '评论发表成功';
                }
            } else if (data.code === 2) {
                this.showError('请先登录后再发表评论');
                // 可以添加登录跳转逻辑
                setTimeout(() => {
                    if (confirm('需要登录才能发表评论，是否前往登录页面？')) {
                        window.location.href = '/user/login?redirect=' + encodeURIComponent(window.location.href);
                    }
                }, 1000);
            } else {
                throw new Error(data.msg || '发表评论失败');
            }
        } catch (error) {
            this.showError(`发表失败: ${error.message}`);
            
            if (statusDiv) {
                statusDiv.textContent = `发表失败: ${error.message}`;
            }
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    }

    async submitReply(btn) {
        const form = btn.closest('.reply-form-box');
        const content = form.querySelector('.reply-content').value.trim();
        const parentId = btn.dataset.parentId;
        const replyId = btn.dataset.replyId;

        if (!content) {
            xpk.toast('请输入回复内容', 'warning');
            return;
        }

        try {
            const res = await fetch('/comment/post', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `type=${this.type}&target_id=${this.targetId}&content=${encodeURIComponent(content)}&parent_id=${parentId}&reply_id=${replyId}`
            });
            const data = await res.json();

            if (data.code === 0) {
                xpk.toast(data.msg, 'success');
                form.remove();
                this.page = 1;
                this.loadComments();
            } else if (data.code === 2) {
                xpk.toast('请先登录', 'warning');
            } else {
                xpk.toast(data.msg, 'error');
            }
        } catch (e) {
            xpk.toast('回复失败', 'error');
        }
    }

    async vote(commentId, action) {
        try {
            const res = await fetch('/comment/vote', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${commentId}&action=${action}`
            });
            const data = await res.json();

            if (data.code === 0) {
                // 更新UI
                const item = this.container.querySelector(`[data-id="${commentId}"]`);
                if (item) {
                    item.querySelector('.up-count').textContent = data.data.up;
                    const downCount = item.querySelector('.down-count');
                    if (downCount) downCount.textContent = data.data.down;
                    
                    // 更新按钮状态
                    const upBtn = item.querySelector('.vote-btn[data-action="up"]');
                    const downBtn = item.querySelector('.vote-btn[data-action="down"]');
                    
                    upBtn.classList.toggle('text-red-600', data.data.type === 'up' && data.data.action !== 'cancel');
                    downBtn?.classList.toggle('text-gray-700', data.data.type === 'down' && data.data.action !== 'cancel');
                }
            } else if (data.code === 2) {
                xpk.toast('请先登录', 'warning');
            }
        } catch (e) {
            // 静默处理投票失败
        }
    }

    async deleteComment(commentId) {
        if (!confirm('确定删除这条评论？')) return;

        try {
            const res = await fetch('/comment/delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${commentId}`
            });
            const data = await res.json();

            if (data.code === 0) {
                xpk.toast('删除成功', 'success');
                this.page = 1;
                this.loadComments();
            } else {
                xpk.toast(data.msg, 'error');
            }
        } catch (e) {
            xpk.toast('删除失败', 'error');
        }
    }

    async loadMoreReplies(parentId, btn) {
        const offset = parseInt(btn.dataset.offset) || 0;

        try {
            const res = await fetch(`/comment/replies?parent_id=${parentId}&offset=${offset}`);
            const data = await res.json();

            if (data.code === 0 && data.data.list.length > 0) {
                const html = data.data.list.map(reply => this.renderReplyItem(reply, parentId)).join('');
                btn.insertAdjacentHTML('beforebegin', html);
                
                if (data.data.list.length < 10) {
                    btn.remove();
                } else {
                    btn.dataset.offset = offset + data.data.list.length;
                }
            } else {
                btn.remove();
            }
        } catch (e) {
            // 静默处理加载回复失败
        }
    }

    formatTime(timestamp) {
        const date = new Date(timestamp * 1000);
        const now = new Date();
        const diff = Math.floor((now - date) / 1000);

        if (diff < 60) return '刚刚';
        if (diff < 3600) return Math.floor(diff / 60) + '分钟前';
        if (diff < 86400) return Math.floor(diff / 3600) + '小时前';
        if (diff < 604800) return Math.floor(diff / 86400) + '天前';

        const month = (date.getMonth() + 1).toString().padStart(2, '0');
        const day = date.getDate().toString().padStart(2, '0');
        return `${month}-${day}`;
    }

    escape(str) {
        if (typeof str !== 'string') {
            return '';
        }
        
        // 使用更安全的字符映射表进行转义
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
            '/': '&#x2F;',
            '`': '&#x60;',
            '=': '&#x3D;'
        };
        
        return str.replace(/[&<>"'`=\/]/g, function(s) {
            return map[s];
        });
    }
    
    // 显示错误消息
    showError(message) {
        xpk.toast(message, 'error');
    }
    
    // 显示成功消息
    showSuccess(message) {
        xpk.toast(message, 'success');
    }
    
    // 显示警告消息
    showWarning(message) {
        xpk.toast(message, 'warning');
    }
    
    // 网络错误处理
    handleNetworkError(error) {
        if (error.name === 'TypeError' && error.message.includes('fetch')) {
            this.showError('网络连接失败，请检查网络连接');
        } else if (error.message.includes('timeout')) {
            this.showError('请求超时，请重试');
        } else if (error.message.includes('HTTP error')) {
            const status = error.message.match(/status: (\d+)/)?.[1];
            switch (status) {
                case '404':
                    this.showError('请求的资源不存在');
                    break;
                case '500':
                    this.showError('服务器内部错误，请稍后重试');
                    break;
                case '403':
                    this.showError('没有权限执行此操作');
                    break;
                default:
                    this.showError(`服务器错误 (${status})`);
            }
        } else {
            this.showError(error.message || '操作失败，请重试');
        }
    }
    
    // 重试机制
    async retryOperation(operation, maxRetries = 3, delay = 1000) {
        for (let i = 0; i < maxRetries; i++) {
            try {
                return await operation();
            } catch (error) {
                if (i === maxRetries - 1) {
                    throw error;
                }
                
                // 静默重试
                await new Promise(resolve => setTimeout(resolve, delay));
                delay *= 2; // 指数退避
            }
        }
    }
}

// 注册到命名空间
if (typeof window.XPK !== 'undefined') {
    window.XPK.register('Comment', XpkComment);
} else {
    // 降级：直接暴露到全局
    window.XpkComment = XpkComment;
}
