<div class="max-w-2xl">
    <h1 class="text-2xl font-bold mb-6"><?= isset($type) ? '编辑' : '添加' ?>文章分类</h1>

    <form method="POST" action="/admin.php/art_type/<?= isset($type) ? 'edit/' . $type['type_id'] : 'add' ?>" class="bg-white rounded-lg shadow p-6">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">分类名称 *</label>
            <input type="text" name="type_name" value="<?= htmlspecialchars($type['type_name'] ?? '') ?>" required
                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">排序</label>
            <input type="number" name="type_sort" value="<?= $type['type_sort'] ?? 0 ?>"
                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <p class="text-gray-500 text-sm mt-1">数字越小越靠前</p>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">状态</label>
            <select name="type_status" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="1" <?= ($type['type_status'] ?? 1) == 1 ? 'selected' : '' ?>>启用</option>
                <option value="0" <?= ($type['type_status'] ?? 1) == 0 ? 'selected' : '' ?>>禁用</option>
            </select>
        </div>

        <input type="hidden" name="type_pid" value="0">

        <div class="flex space-x-4">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">保存</button>
            <a href="/admin.php/art_type" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded">取消</a>
        </div>
    </form>
</div>
