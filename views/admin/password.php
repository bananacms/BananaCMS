<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>修改密码 - <?= htmlspecialchars($admin['name'] ?? '香蕉CMS') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex">
        <!-- 简化侧边栏 -->
        <aside class="w-64 bg-gray-900 min-h-screen text-white fixed left-0 top-0 bottom-0 flex flex-col">
            <div class="p-4 border-b border-gray-800">
                <h1 class="text-xl font-bold text-yellow-400">🍌 香蕉CMS</h1>
                <p class="text-xs text-gray-400 mt-1">轻量级影视CMS</p>
            </div>
            <nav class="flex-1 p-4">
                <a href="/<?= $adminEntry ?>/dashboard" class="block px-4 py-2 rounded hover:bg-gray-800 mb-1">
                    ← 返回后台
                </a>
            </nav>
            <div class="p-4 border-t border-gray-800">
                <div class="text-sm text-gray-400 mb-2">
                    👤 <?= htmlspecialchars($admin['name'] ?? 'Admin') ?>
                </div>
                <a href="/<?= $adminEntry ?>/logout" class="block text-center px-4 py-2 bg-red-600 rounded hover:bg-red-700 text-sm">
                    退出登录
                </a>
            </div>
        </aside>

        <!-- 主内容 -->
        <main class="flex-1 ml-64 p-6">
            <div class="max-w-md mx-auto">
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <h2 class="text-2xl font-bold mb-6 text-center">🔐 修改密码</h2>
                    
                    <?php if (!empty($error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                        <?= htmlspecialchars($error) ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                        <?= htmlspecialchars($success) ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="/<?= $adminEntry ?>/password" class="space-y-4">
                        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">当前用户</label>
                            <input type="text" value="<?= htmlspecialchars($admin['name'] ?? '') ?>" disabled 
                                class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-600">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">原密码 *</label>
                            <input type="password" name="old_password" required 
                                class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                                placeholder="请输入原密码">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">新密码 * (至少6位)</label>
                            <input type="password" name="new_password" required minlength="6"
                                class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                                placeholder="请输入新密码">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">确认新密码 *</label>
                            <input type="password" name="confirm_password" required minlength="6"
                                class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                                placeholder="请再次输入新密码">
                        </div>
                        
                        <button type="submit" class="w-full bg-yellow-500 hover:bg-yellow-600 text-white py-2 rounded font-bold transition">
                            确认修改
                        </button>
                    </form>
                    
                    <div class="mt-6 text-center">
                        <a href="/<?= $adminEntry ?>/dashboard" class="text-gray-500 hover:text-gray-700 text-sm">
                            ← 返回仪表盘
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
