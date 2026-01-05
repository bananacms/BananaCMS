<h1 class="text-2xl font-bold mb-6"><?= isset($type) ? '编辑分类' : '添加分类' ?></h1>

<form method="POST" class="bg-white rounded-lg shadow p-6 max-w-2xl">
    <input type="hidden" name="_token" value="<?= $csrfToken ?>">

    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">父级分类</label>
            <select name="type_pid" class="w-full border rounded px-3 py-2">
                <option value="0">顶级分类</option>
                <?php foreach ($parentTypes as $pt): ?>
                <option value="<?= $pt['type_id'] ?>" <?= ($type['type_pid'] ?? 0) == $pt['type_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($pt['type_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">分类名称 *</label>
            <input type="text" name="type_name" value="<?= htmlspecialchars($type['type_name'] ?? '') ?>" required
                class="w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">英文名</label>
            <input type="text" name="type_en" value="<?= htmlspecialchars($type['type_en'] ?? '') ?>"
                class="w-full border rounded px-3 py-2" placeholder="用于URL">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">排序</label>
                <input type="number" name="type_sort" value="<?= $type['type_sort'] ?? 0 ?>"
                    class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">状态</label>
                <select name="type_status" class="w-full border rounded px-3 py-2">
                    <option value="1" <?= ($type['type_status'] ?? 1) == 1 ? 'selected' : '' ?>>启用</option>
                    <option value="0" <?= ($type['type_status'] ?? 1) == 0 ? 'selected' : '' ?>>禁用</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">SEO关键词</label>
            <input type="text" name="type_key" value="<?= htmlspecialchars($type['type_key'] ?? '') ?>"
                class="w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">SEO描述</label>
            <textarea name="type_des" rows="3" class="w-full border rounded px-3 py-2"><?= htmlspecialchars($type['type_des'] ?? '') ?></textarea>
        </div>
    </div>

    <div class="mt-6 flex space-x-4">
        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">保存</button>
        <a href="/<?= $adminEntry ?>?s=type" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded">返回</a>
    </div>
</form>
