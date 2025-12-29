<h1 class="text-2xl font-bold mb-6"><?= isset($art) ? '编辑文章' : '添加文章' ?></h1>

<!-- Quill 编辑器样式 -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">

<form method="POST" class="bg-white rounded-lg shadow p-6">
    <input type="hidden" name="_token" value="<?= $csrfToken ?>">

    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">标题 *</label>
            <input type="text" name="art_name" value="<?= htmlspecialchars($art['art_name'] ?? '') ?>" required
                class="w-full border rounded px-3 py-2">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">分类</label>
                <select name="art_type_id" class="w-full border rounded px-3 py-2">
                    <option value="0">-- 请选择 --</option>
                    <?php foreach ($types as $type): ?>
                    <option value="<?= $type['type_id'] ?>" <?= ($art['art_type_id'] ?? 0) == $type['type_id'] ? 'selected' : '' ?>><?= htmlspecialchars($type['type_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">作者</label>
                <input type="text" name="art_author" value="<?= htmlspecialchars($art['art_author'] ?? '') ?>"
                    class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">来源</label>
                <input type="text" name="art_from" value="<?= htmlspecialchars($art['art_from'] ?? '') ?>"
                    class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">状态</label>
                <select name="art_status" class="w-full border rounded px-3 py-2">
                    <option value="1" <?= ($art['art_status'] ?? 1) == 1 ? 'selected' : '' ?>>发布</option>
                    <option value="0" <?= ($art['art_status'] ?? 1) == 0 ? 'selected' : '' ?>>草稿</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">封面图</label>
            <input type="text" name="art_pic" value="<?= htmlspecialchars($art['art_pic'] ?? '') ?>"
                class="w-full border rounded px-3 py-2" placeholder="图片URL">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">内容</label>
            <div id="editor" style="height: 300px;"><?= $art['art_content'] ?? '' ?></div>
            <input type="hidden" name="art_content" id="art_content">
        </div>
    </div>

    <div class="mt-6 flex space-x-4">
        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">保存</button>
        <a href="/admin.php/art" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded">返回</a>
    </div>
</form>

<!-- Quill 编辑器 -->
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
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

// 表单提交时同步内容
document.querySelector('form').addEventListener('submit', function() {
    document.getElementById('art_content').value = quill.root.innerHTML;
});
</script>
