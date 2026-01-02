<div class="max-w-2xl">
    <div class="bg-white rounded-lg shadow p-6">
        <form action="/<?= $adminEntry ?>/link/<?= isset($link) ? 'doEdit/' . $link['link_id'] : 'doAdd' ?>" method="post" class="space-y-4">
            <input type="hidden" name="_token" value="<?= $csrfToken ?>">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">网站名称 <span class="text-red-500">*</span></label>
                <input type="text" name="link_name" value="<?= htmlspecialchars($link['link_name'] ?? '') ?>" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">网站地址 <span class="text-red-500">*</span></label>
                <input type="url" name="link_url" value="<?= htmlspecialchars($link['link_url'] ?? '') ?>" required
                       placeholder="https://"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Logo图片</label>
                <input type="url" name="link_logo" value="<?= htmlspecialchars($link['link_logo'] ?? '') ?>"
                       placeholder="https://"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">联系方式</label>
                <input type="text" name="link_contact" value="<?= htmlspecialchars($link['link_contact'] ?? '') ?>"
                       placeholder="QQ/邮箱/Telegram"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">排序</label>
                    <input type="number" name="link_sort" value="<?= $link['link_sort'] ?? 0 ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">数字越小越靠前</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">状态</label>
                    <select name="link_status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="0" <?= ($link['link_status'] ?? 0) == 0 ? 'selected' : '' ?>>待审核</option>
                        <option value="1" <?= ($link['link_status'] ?? 0) == 1 ? 'selected' : '' ?>>已通过</option>
                        <option value="2" <?= ($link['link_status'] ?? 0) == 2 ? 'selected' : '' ?>>已拒绝</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-4 pt-4">
                <button type="submit" class="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">保存</button>
                <a href="/<?= $adminEntry ?>/link" class="px-6 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200">返回</a>
            </div>
        </form>
    </div>
</div>
