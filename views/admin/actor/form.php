<h1 class="text-2xl font-bold mb-6"><?= isset($actor) ? '编辑演员' : '添加演员' ?></h1>

<form method="POST" class="bg-white rounded-lg shadow p-6 max-w-2xl">
    <input type="hidden" name="_token" value="<?= $csrfToken ?>">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">姓名 *</label>
                <input type="text" name="actor_name" value="<?= htmlspecialchars($actor['actor_name'] ?? '') ?>" required
                    class="w-full border rounded px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">英文名</label>
                <input type="text" name="actor_en" value="<?= htmlspecialchars($actor['actor_en'] ?? '') ?>"
                    class="w-full border rounded px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">头像</label>
                <input type="text" name="actor_pic" value="<?= htmlspecialchars($actor['actor_pic'] ?? '') ?>"
                    class="w-full border rounded px-3 py-2" placeholder="图片URL">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">性别</label>
                    <select name="actor_sex" class="w-full border rounded px-3 py-2">
                        <option value="">未知</option>
                        <option value="男" <?= ($actor['actor_sex'] ?? '') === '男' ? 'selected' : '' ?>>男</option>
                        <option value="女" <?= ($actor['actor_sex'] ?? '') === '女' ? 'selected' : '' ?>>女</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">地区</label>
                    <input type="text" name="actor_area" value="<?= htmlspecialchars($actor['actor_area'] ?? '') ?>"
                        class="w-full border rounded px-3 py-2">
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">血型</label>
                    <select name="actor_blood" class="w-full border rounded px-3 py-2">
                        <option value="">未知</option>
                        <option value="A" <?= ($actor['actor_blood'] ?? '') === 'A' ? 'selected' : '' ?>>A型</option>
                        <option value="B" <?= ($actor['actor_blood'] ?? '') === 'B' ? 'selected' : '' ?>>B型</option>
                        <option value="AB" <?= ($actor['actor_blood'] ?? '') === 'AB' ? 'selected' : '' ?>>AB型</option>
                        <option value="O" <?= ($actor['actor_blood'] ?? '') === 'O' ? 'selected' : '' ?>>O型</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">生日</label>
                    <input type="text" name="actor_birthday" value="<?= htmlspecialchars($actor['actor_birthday'] ?? '') ?>"
                        class="w-full border rounded px-3 py-2" placeholder="1990-01-01">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">身高</label>
                    <input type="text" name="actor_height" value="<?= htmlspecialchars($actor['actor_height'] ?? '') ?>"
                        class="w-full border rounded px-3 py-2" placeholder="170cm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">体重</label>
                    <input type="text" name="actor_weight" value="<?= htmlspecialchars($actor['actor_weight'] ?? '') ?>"
                        class="w-full border rounded px-3 py-2" placeholder="60kg">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">状态</label>
                <select name="actor_status" class="w-full border rounded px-3 py-2">
                    <option value="1" <?= ($actor['actor_status'] ?? 1) == 1 ? 'selected' : '' ?>>启用</option>
                    <option value="0" <?= ($actor['actor_status'] ?? 1) == 0 ? 'selected' : '' ?>>禁用</option>
                </select>
            </div>
        </div>
    </div>

    <div class="mt-6">
        <label class="block text-sm font-medium text-gray-700 mb-1">简介</label>
        <textarea name="actor_content" rows="4" class="w-full border rounded px-3 py-2"><?= htmlspecialchars($actor['actor_content'] ?? '') ?></textarea>
    </div>

    <div class="mt-6 flex space-x-4">
        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">保存</button>
        <a href="/<?= $adminEntry ?>?s=actor" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded">返回</a>
    </div>
</form>
