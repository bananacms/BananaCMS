<h1 class="text-2xl font-bold mb-6">编辑用户</h1>

<form method="POST" class="bg-white rounded-lg shadow p-6 max-w-2xl">
    <input type="hidden" name="_token" value="<?= $csrfToken ?>">

    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">用户名</label>
            <input type="text" value="<?= htmlspecialchars($user['user_name']) ?>" disabled
                class="w-full border rounded px-3 py-2 bg-gray-100">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">新密码</label>
            <input type="password" name="user_pwd" class="w-full border rounded px-3 py-2" placeholder="留空则不修改">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">昵称</label>
            <input type="text" name="user_nick_name" value="<?= htmlspecialchars($user['user_nick_name'] ?? '') ?>"
                class="w-full border rounded px-3 py-2">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">邮箱</label>
                <input type="email" name="user_email" value="<?= htmlspecialchars($user['user_email'] ?? '') ?>"
                    class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">手机</label>
                <input type="text" name="user_phone" value="<?= htmlspecialchars($user['user_phone'] ?? '') ?>"
                    class="w-full border rounded px-3 py-2">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">积分</label>
                <input type="number" name="user_points" value="<?= $user['user_points'] ?? 0 ?>"
                    class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">状态</label>
                <select name="user_status" class="w-full border rounded px-3 py-2">
                    <option value="1" <?= ($user['user_status'] ?? 1) == 1 ? 'selected' : '' ?>>正常</option>
                    <option value="0" <?= ($user['user_status'] ?? 1) == 0 ? 'selected' : '' ?>>禁用</option>
                </select>
            </div>
        </div>

        <div class="text-sm text-gray-500 space-y-1">
            <p>注册时间: <?= date('Y-m-d H:i:s', $user['user_reg_time']) ?></p>
            <p>最后登录: <?= $user['user_login_time'] ? date('Y-m-d H:i:s', $user['user_login_time']) : '-' ?></p>
            <p>登录次数: <?= $user['user_login_num'] ?></p>
        </div>
    </div>

    <div class="mt-6 flex space-x-4">
        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">保存</button>
        <a href="/admin.php/user" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded">返回</a>
    </div>
</form>
