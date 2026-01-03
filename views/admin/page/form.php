<h1 class="text-2xl font-bold mb-6"><?= isset($page) ? '编辑单页' : '添加单页' ?></h1>

<!-- Quill 编辑器样式 -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">

<form method="POST" class="bg-white rounded-lg shadow p-6" data-no-ajax="true">
    <input type="hidden" name="_token" value="<?= $csrfToken ?>">

    <div class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">页面标题 *</label>
                <input type="text" name="page_title" value="<?= htmlspecialchars($page['page_title'] ?? '') ?>" required
                    class="w-full border rounded px-3 py-2" placeholder="如：关于我们">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">页面标识 *</label>
                <input type="text" name="page_slug" value="<?= htmlspecialchars($page['page_slug'] ?? '') ?>" required
                    class="w-full border rounded px-3 py-2" placeholder="如：about（只能包含字母、数字、下划线、横线）"
                    pattern="[a-zA-Z0-9_-]+" title="只能包含字母、数字、下划线和横线">
                <p class="text-xs text-gray-500 mt-1">访问地址将为：/page/{标识}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">排序</label>
                <input type="number" name="page_sort" value="<?= $page['page_sort'] ?? 0 ?>"
                    class="w-full border rounded px-3 py-2" min="0">
                <p class="text-xs text-gray-500 mt-1">数字越小越靠前</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">状态</label>
                <select name="page_status" class="w-full border rounded px-3 py-2">
                    <option value="1" <?= ($page['page_status'] ?? 1) == 1 ? 'selected' : '' ?>>启用</option>
                    <option value="0" <?= ($page['page_status'] ?? 1) == 0 ? 'selected' : '' ?>>禁用</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">底部显示</label>
                <select name="page_footer" class="w-full border rounded px-3 py-2">
                    <option value="1" <?= ($page['page_footer'] ?? 1) == 1 ? 'selected' : '' ?>>显示在底部导航</option>
                    <option value="0" <?= ($page['page_footer'] ?? 1) == 0 ? 'selected' : '' ?>>不显示</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">页面内容</label>
            <div id="editor" style="height: 350px;"><?= $page['page_content'] ?? '' ?></div>
            <input type="hidden" name="page_content" id="page_content">
        </div>
    </div>

    <div class="mt-6 flex space-x-4">
        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">保存</button>
        <a href="/<?= $adminEntry ?>/page" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded">返回</a>
        <?php if (isset($page)): ?>
        <a href="/page/<?= htmlspecialchars($page['page_slug']) ?>" target="_blank" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded">预览</a>
        <?php endif; ?>
    </div>
</form>

<!-- Quill 编辑器 -->
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const quill = new Quill('#editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'align': [] }],
                ['link', 'image', 'video'],
                ['clean']
            ]
        }
    });

    // 表单AJAX提交
    document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // 同步编辑器内容
        document.getElementById('page_content').value = quill.root.innerHTML;
        
        const form = this;
        const btn = form.querySelector('button[type="submit"]');
        const btnText = btn.textContent;
        btn.disabled = true;
        btn.textContent = '保存中...';
        
        fetch(form.action || location.href, {
            method: 'POST',
            body: new FormData(form)
        })
        .then(r => r.json())
        .then(data => {
            if (data.code === 0) {
                xpkToast(data.msg, 'success');
                setTimeout(() => {
                    location.href = adminUrl('/page');
                }, 1000);
            } else {
                xpkToast(data.msg, 'error');
                btn.disabled = false;
                btn.textContent = btnText;
            }
        })
        .catch(err => {
            xpkToast('请求失败', 'error');
            btn.disabled = false;
            btn.textContent = btnText;
        });
    });
});
</script>
