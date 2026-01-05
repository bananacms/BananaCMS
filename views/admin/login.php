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
            <h1 class="text-3xl font-bold text-gray-800 flex items-center justify-center">
                <svg class="w-8 h-8 mr-2 text-yellow-500" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.5 3C19.43 3 21 4.57 21 6.5C21 8.43 19.43 10 17.5 10C16.37 10 15.37 9.5 14.71 8.71L12 11.41L9.29 8.71C8.63 9.5 7.63 10 6.5 10C4.57 10 3 8.43 3 6.5C3 4.57 4.57 3 6.5 3C7.63 3 8.63 3.5 9.29 4.29L12 7L14.71 4.29C15.37 3.5 16.37 3 17.5 3M6.5 5C5.67 5 5 5.67 5 6.5C5 7.33 5.67 8 6.5 8C7.33 8 8 7.33 8 6.5C8 5.67 7.33 5 6.5 5M17.5 5C16.67 5 16 5.67 16 6.5C16 7.33 16.67 8 17.5 8C18.33 8 19 7.33 19 6.5C19 5.67 18.33 5 17.5 5M12 13.5C10.89 13.5 9.85 13.93 9.06 14.66L6.5 17.22C5.57 18.15 5.57 19.65 6.5 20.58C7.43 21.51 8.93 21.51 9.86 20.58L12 18.44L14.14 20.58C15.07 21.51 16.57 21.51 17.5 20.58C18.43 19.65 18.43 18.15 17.5 17.22L14.94 14.66C14.15 13.93 13.11 13.5 12 13.5Z"/>
                </svg>
                香蕉CMS
            </h1>
            <p class="text-gray-500 mt-2">轻量级影视内容管理系统</p>
        </div>

        <?php if (!empty($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="/<?= $adminEntry ?>?s=login">
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
