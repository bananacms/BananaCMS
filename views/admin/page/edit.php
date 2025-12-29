<?php include VIEW_PATH . 'admin/layouts/header.php'; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="/admin.php/page" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <h1 class="text-2xl font-bold"><?= htmlspecialchars($pageTitle) ?></h1>
        </div>
        <a href="/<?= $pageKey ?>" target="_blank" class="text-blue-600 hover:underline text-sm">预览页面 →</a>
    </div>

    <form id="pageForm" class="bg-white rounded-lg shadow p-6">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
        <input type="hidden" name="key" value="<?= htmlspecialchars($pageKey) ?>">
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">页面内容</label>
            <div id="editor" class="border rounded-lg" style="min-height: 400px;"></div>
            <input type="hidden" name="content" id="contentInput">
        </div>

        <div class="flex justify-end space-x-4">
            <a href="/admin.php/page" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">取消</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">保存</button>
        </div>
    </form>
</div>

<!-- Quill Editor -->
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>

<script>
const quill = new Quill('#editor', {
    theme: 'snow',
    placeholder: '请输入页面内容...',
    modules: {
        toolbar: [
            [{ 'header': [1, 2, 3, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            [{ 'align': [] }],
            ['link', 'image'],
            ['clean']
        ]
    }
});

// 加载已有内容
quill.root.innerHTML = <?= json_encode($pageContent) ?>;

// 表单提交
document.getElementById('pageForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    document.getElementById('contentInput').value = quill.root.innerHTML;
    
    const formData = new FormData(this);
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.textContent = '保存中...';
    
    fetch('/admin.php/page/save', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.code === 0) {
            xpk.toast('保存成功', 'success');
            setTimeout(() => location.href = '/admin.php/page', 1000);
        } else {
            xpk.toast(data.msg || '保存失败', 'error');
        }
    })
    .catch(() => xpk.toast('保存失败', 'error'))
    .finally(() => {
        btn.disabled = false;
        btn.textContent = '保存';
    });
});
</script>

<?php include VIEW_PATH . 'admin/layouts/footer.php'; ?>
