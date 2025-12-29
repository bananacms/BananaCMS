<h1 class="text-2xl font-bold mb-6">分类绑定 - <?= htmlspecialchars($collect['collect_name']) ?></h1>

<form method="POST" action="/admin.php/collect/savebind/<?= $collect['collect_id'] ?>" class="bg-white rounded-lg shadow p-6">
    <input type="hidden" name="_token" value="<?= $csrfToken ?>">

    <div class="mb-4 text-sm text-gray-600">
        将远程分类绑定到本地分类，未绑定的分类将不会采集。
    </div>

    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">远程分类</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">→</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">本地分类</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            <?php foreach ($remoteCategories as $cat): ?>
            <tr>
                <td class="px-4 py-3">
                    <span class="text-sm"><?= htmlspecialchars($cat['name']) ?></span>
                    <span class="text-xs text-gray-400 ml-2">ID: <?= $cat['id'] ?></span>
                </td>
                <td class="px-4 py-3 text-gray-400">→</td>
                <td class="px-4 py-3">
                    <select name="bind[<?= $cat['id'] ?>]" class="border rounded px-3 py-1.5 text-sm w-full max-w-xs">
                        <option value="0">-- 不采集 --</option>
                        <?php foreach ($localTypes as $type): ?>
                        <option value="<?= $type['type_id'] ?>" <?= ($binds[$cat['id']] ?? 0) == $type['type_id'] ? 'selected' : '' ?>>
                            <?= str_repeat('　', $type['level'] ?? 0) ?><?= htmlspecialchars($type['type_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="mt-6 flex space-x-4">
        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">保存绑定</button>
        <a href="/admin.php/collect" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded">返回</a>
    </div>
</form>
