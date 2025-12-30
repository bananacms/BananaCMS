<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录 - 香蕉CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-yellow-400 to-orange-500">
    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">🍌 香蕉CMS</h1>
            <p class="text-gray-500 mt-2">轻量级影视内容管理系统</p>
        </div>

        <?php if (!empty($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="/admin.php/login">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">用户名</label>
                <input type="text" name="username" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400"
                    placeholder="请输入用户名">
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">密码</label>
                <input type="password" name="password" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400"
                    placeholder="请输入密码">
            </div>

            <button type="submit"
                class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded-lg transition">
                登 录
            </button>
        </form>

        <div class="mt-6 text-center text-sm text-gray-500">
            Powered by <a href="https://xpornkit.com" class="text-yellow-600 hover:underline" target="_blank">香蕉CMS</a>
        </div>
    </div>
</body>
</html>
