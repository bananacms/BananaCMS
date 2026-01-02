<div class="mb-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold flex items-center">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
            </svg>
            广告管理
        </h2>
        <button onclick="openAdModal()" class="bg-primary text-white px-4 py-2 rounded hover:bg-red-600">
            + 添加广告
        </button>
    </div>

    <!-- 统计卡片 -->
    <div class="grid grid-cols-5 gap-4 mb-6">
        <div class="bg-white p-4 rounded shadow">
            <div class="text-gray-500 text-sm">广告总数</div>
            <div class="text-2xl font-bold"><?= $stats['total'] ?></div>
        </div>
        <div class="bg-white p-4 rounded shadow">
            <div class="text-gray-500 text-sm">启用中</div>
            <div class="text-2xl font-bold text-green-600"><?= $stats['active'] ?></div>
        </div>
        <div class="bg-white p-4 rounded shadow">
            <div class="text-gray-500 text-sm">总展示</div>
            <div class="text-2xl font-bold text-blue-600"><?= number_format($stats['shows']) ?></div>
        </div>
        <div class="bg-white p-4 rounded shadow">
            <div class="text-gray-500 text-sm">总点击</div>
            <div class="text-2xl font-bold text-purple-600"><?= number_format($stats['clicks']) ?></div>
        </div>
        <div class="bg-white p-4 rounded shadow">
            <div class="text-gray-500 text-sm">点击率</div>
            <div class="text-2xl font-bold text-orange-600"><?= $stats['ctr'] ?>%</div>
        </div>
    </div>

    <!-- 筛选 -->
    <div class="bg-white p-4 rounded shadow mb-4">
        <form method="get" class="flex gap-4 items-center">
            <select name="position" class="border rounded px-3 py-2">
                <option value="">全部位置</option>
                <?php foreach ($positions as $key => $name): ?>
                <option value="<?= $key ?>" <?= $position === $key ? 'selected' : '' ?>><?= $name ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">筛选</button>
            <a href="/<?= $adminEntry ?>/ad" class="text-gray-500 hover:text-gray-700">重置</a>
        </form>
    </div>
</div>

<!-- 列表 -->
<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">ID</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">广告名称</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">位置</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">类型</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">展示/点击</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">点击率</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">状态</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">排序</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">操作</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php if (empty($list)): ?>
            <tr>
                <td colspan="9" class="px-4 py-8 text-center text-gray-500">暂无数据</td>
            </tr>
            <?php else: ?>
            <?php foreach ($list as $item): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm"><?= $item['ad_id'] ?></td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                        <?php if ($item['ad_image']): ?>
                        <img src="<?= htmlspecialchars($item['ad_image']) ?>" class="w-16 h-10 object-cover rounded">
                        <?php endif; ?>
                        <span class="font-medium"><?= htmlspecialchars($item['ad_title']) ?></span>
                    </div>
                </td>
                <td class="px-4 py-3 text-sm">
                    <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs">
                        <?= $positions[$item['ad_position']] ?? $item['ad_position'] ?>
                    </span>
                </td>
                <td class="px-4 py-3 text-sm">
                    <?= $types[$item['ad_type']] ?? $item['ad_type'] ?>
                </td>
                <td class="px-4 py-3 text-sm">
                    <?= number_format($item['ad_shows']) ?> / <?= number_format($item['ad_clicks']) ?>
                </td>
                <td class="px-4 py-3 text-sm">
                    <?php 
                    $ctr = $item['ad_shows'] > 0 ? round($item['ad_clicks'] / $item['ad_shows'] * 100, 2) : 0;
                    ?>
                    <span class="<?= $ctr > 1 ? 'text-green-600' : 'text-gray-500' ?>"><?= $ctr ?>%</span>
                </td>
                <td class="px-4 py-3">
                    <?php
                    $now = time();
                    $expired = ($item['ad_end_time'] > 0 && $item['ad_end_time'] < $now);
                    $notStarted = ($item['ad_start_time'] > 0 && $item['ad_start_time'] > $now);
                    ?>
                    <?php if ($expired): ?>
                    <span class="text-gray-400 text-sm">已过期</span>
                    <?php elseif ($notStarted): ?>
                    <span class="text-yellow-600 text-sm">未开始</span>
                    <?php elseif ($item['ad_status']): ?>
                    <span class="text-green-600 text-sm">● 启用</span>
                    <?php else: ?>
                    <span class="text-gray-400 text-sm">○ 禁用</span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-sm text-gray-500"><?= $item['ad_sort'] ?></td>
                <td class="px-4 py-3 text-sm space-x-2">
                    <button onclick="openAdModal(<?= $item['ad_id'] ?>)" class="text-blue-600 hover:underline">编辑</button>
                    <button onclick="toggleAd(<?= $item['ad_id'] ?>)" class="text-yellow-600 hover:underline">
                        <?= $item['ad_status'] ? '禁用' : '启用' ?>
                    </button>
                    <button onclick="deleteAd(<?= $item['ad_id'] ?>)" class="text-red-600 hover:underline">删除</button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- 分页 -->
<?php 
$baseUrl = "/<?= $adminEntry ?>/ad?position=" . urlencode($position);
include __DIR__ . '/../components/pagination.php'; 
?>

<!-- 广告模态框 -->
<div id="adModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center px-6 py-4 border-b sticky top-0 bg-white">
            <h3 id="adModalTitle" class="text-lg font-bold">添加广告</h3>
            <button onclick="closeAdModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="adForm" onsubmit="saveAd(event)" class="p-6">
            <input type="hidden" name="_token" value="<?= $csrfToken ?>">
            <input type="hidden" name="ad_id" id="adId" value="">

            <div class="grid grid-cols-2 gap-6">
                <!-- 左列 -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">广告名称 *</label>
                        <input type="text" name="ad_title" id="adTitle" required class="w-full border rounded px-3 py-2" placeholder="用于后台识别">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">广告位置 *</label>
                        <select name="ad_position" id="adPosition" required class="w-full border rounded px-3 py-2">
                            <?php foreach ($positions as $key => $name): ?>
                            <option value="<?= $key ?>"><?= $name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">广告类型</label>
                        <select name="ad_type" id="adType" class="w-full border rounded px-3 py-2" onchange="toggleTypeFields()">
                            <?php foreach ($types as $key => $name): ?>
                            <option value="<?= $key ?>"><?= $name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="fieldImage" class="type-field">
                        <label class="block text-sm font-medium text-gray-700 mb-1">图片地址</label>
                        <input type="text" name="ad_image" id="adImage" class="w-full border rounded px-3 py-2" placeholder="https://...">
                        <p class="text-xs text-gray-400 mt-1">建议尺寸：横幅 728x90，侧边栏 300x250</p>
                    </div>

                    <div id="fieldLink" class="type-field">
                        <label class="block text-sm font-medium text-gray-700 mb-1">跳转链接</label>
                        <input type="text" name="ad_link" id="adLink" class="w-full border rounded px-3 py-2" placeholder="https://...">
                    </div>

                    <div id="fieldCode" class="type-field" style="display:none;">
                        <label class="block text-sm font-medium text-gray-700 mb-1">广告代码</label>
                        <textarea name="ad_code" id="adCode" rows="4" class="w-full border rounded px-3 py-2 font-mono text-sm" placeholder="粘贴第三方广告代码..."></textarea>
                        <p class="text-xs text-gray-400 mt-1">支持 HTML/JS 代码，如 Google AdSense</p>
                    </div>

                    <div id="fieldVideo" class="type-field" style="display:none;">
                        <label class="block text-sm font-medium text-gray-700 mb-1">视频地址</label>
                        <input type="text" name="ad_video" id="adVideo" class="w-full border rounded px-3 py-2" placeholder="https://...mp4">
                    </div>
                </div>

                <!-- 右列 -->
                <div class="space-y-4">
                    <div id="fieldDuration" class="type-field" style="display:none;">
                        <label class="block text-sm font-medium text-gray-700 mb-1">视频时长（秒）</label>
                        <input type="number" name="ad_duration" id="adDuration" value="15" class="w-full border rounded px-3 py-2" min="0">
                    </div>

                    <div id="fieldSkip" class="type-field" style="display:none;">
                        <label class="block text-sm font-medium text-gray-700 mb-1">跳过时间（秒）</label>
                        <input type="number" name="ad_skip_time" id="adSkipTime" value="5" class="w-full border rounded px-3 py-2" min="0">
                        <p class="text-xs text-gray-400 mt-1">0 表示不可跳过</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">排序</label>
                        <input type="number" name="ad_sort" id="adSort" value="0" class="w-full border rounded px-3 py-2">
                        <p class="text-xs text-gray-400 mt-1">数字越小越靠前</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">状态</label>
                        <select name="ad_status" id="adStatus" class="w-full border rounded px-3 py-2">
                            <option value="1">启用</option>
                            <option value="0">禁用</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">开始时间</label>
                        <input type="datetime-local" name="ad_start_time" id="adStartTime" class="w-full border rounded px-3 py-2">
                        <p class="text-xs text-gray-400 mt-1">留空表示立即生效</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">结束时间</label>
                        <input type="datetime-local" name="ad_end_time" id="adEndTime" class="w-full border rounded px-3 py-2">
                        <p class="text-xs text-gray-400 mt-1">留空表示永不过期</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">备注</label>
                        <textarea name="ad_remark" id="adRemark" rows="2" class="w-full border rounded px-3 py-2" placeholder="内部备注..."></textarea>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t flex justify-end space-x-3">
                <button type="button" onclick="closeAdModal()" class="px-4 py-2 border rounded hover:bg-gray-50">取消</button>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded hover:bg-red-600">保存</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleTypeFields() {
    const type = document.getElementById('adType').value;
    
    document.getElementById('fieldImage').style.display = 'none';
    document.getElementById('fieldLink').style.display = 'none';
    document.getElementById('fieldCode').style.display = 'none';
    document.getElementById('fieldVideo').style.display = 'none';
    document.getElementById('fieldDuration').style.display = 'none';
    document.getElementById('fieldSkip').style.display = 'none';
    
    switch (type) {
        case 'image':
            document.getElementById('fieldImage').style.display = 'block';
            document.getElementById('fieldLink').style.display = 'block';
            break;
        case 'text':
            document.getElementById('fieldLink').style.display = 'block';
            break;
        case 'code':
            document.getElementById('fieldCode').style.display = 'block';
            break;
        case 'video':
            document.getElementById('fieldVideo').style.display = 'block';
            document.getElementById('fieldLink').style.display = 'block';
            document.getElementById('fieldDuration').style.display = 'block';
            document.getElementById('fieldSkip').style.display = 'block';
            break;
    }
}

function openAdModal(id = null) {
    const modal = document.getElementById('adModal');
    const title = document.getElementById('adModalTitle');
    
    // 重置表单
    document.getElementById('adForm').reset();
    document.getElementById('adId').value = '';
    
    // 重置时间字段
    document.getElementById('adStartTime').value = '';
    document.getElementById('adEndTime').value = '';
    
    // 重置类型相关字段显示
    toggleTypeFields();
    
    if (id) {
        title.textContent = '编辑广告';
        // 显示加载状态
        title.textContent = '加载中...';
        
        fetch(adminUrl('/ad/getOne?id=' + id))
            .then(r => {
                if (!r.ok) throw new Error('网络请求失败');
                return r.json();
            })
            .then(data => {
                if (data.code === 0 && data.data) {
                    const ad = data.data;
                    title.textContent = '编辑广告';
                    
                    // 填充表单数据
                    document.getElementById('adId').value = ad.ad_id || '';
                    document.getElementById('adTitle').value = ad.ad_title || '';
                    document.getElementById('adPosition').value = ad.ad_position || 'home_top';
                    document.getElementById('adType').value = ad.ad_type || 'image';
                    document.getElementById('adImage').value = ad.ad_image || '';
                    document.getElementById('adLink').value = ad.ad_link || '';
                    document.getElementById('adCode').value = ad.ad_code || '';
                    document.getElementById('adVideo').value = ad.ad_video || '';
                    document.getElementById('adDuration').value = ad.ad_duration || 15;
                    document.getElementById('adSkipTime').value = ad.ad_skip_time || 5;
                    document.getElementById('adSort').value = ad.ad_sort || 0;
                    document.getElementById('adStatus').value = ad.ad_status !== undefined ? ad.ad_status : 1;
                    document.getElementById('adRemark').value = ad.ad_remark || '';
                    
                    // 处理时间字段
                    if (ad.ad_start_time && parseInt(ad.ad_start_time) > 0) {
                        document.getElementById('adStartTime').value = formatDateTime(parseInt(ad.ad_start_time));
                    }
                    if (ad.ad_end_time && parseInt(ad.ad_end_time) > 0) {
                        document.getElementById('adEndTime').value = formatDateTime(parseInt(ad.ad_end_time));
                    }
                    
                    // 根据类型显示对应字段
                    toggleTypeFields();
                    
                    // 显示模态框
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                } else {
                    xpkToast(data.msg || '获取数据失败', 'error');
                }
            })
            .catch(err => {
                console.error('获取广告数据失败:', err);
                xpkToast('获取数据失败: ' + err.message, 'error');
            });
    } else {
        title.textContent = '添加广告';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function formatDateTime(timestamp) {
    const d = new Date(timestamp * 1000);
    return d.getFullYear() + '-' + 
           String(d.getMonth() + 1).padStart(2, '0') + '-' + 
           String(d.getDate()).padStart(2, '0') + 'T' + 
           String(d.getHours()).padStart(2, '0') + ':' + 
           String(d.getMinutes()).padStart(2, '0');
}

function closeAdModal() {
    const modal = document.getElementById('adModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function saveAd(e) {
    e.preventDefault();
    const form = document.getElementById('adForm');
    const formData = new FormData(form);
    const id = formData.get('ad_id');
    const url = id ? adminUrl('/ad/edit/' + id) : adminUrl('/ad/add');
    
    fetch(url, {
        method: 'POST',
        body: new URLSearchParams(formData)
    })
    .then(r => r.json())
    .then(data => {
        if (data.code === 0) {
            xpkToast(data.msg || '保存成功', 'success');
            closeAdModal();
            location.reload();
        } else {
            xpkToast(data.msg || '保存失败', 'error');
        }
    })
    .catch(() => xpkToast('请求失败', 'error'));
}

document.getElementById('adModal').addEventListener('click', function(e) {
    if (e.target === this) closeAdModal();
});

function toggleAd(id) {
    fetch(adminUrl('/ad/toggle'), {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + id
    })
    .then(r => r.json())
    .then(data => {
        if (data.code === 0) {
            xpkToast(data.msg, 'success');
            location.reload();
        } else {
            xpkToast(data.msg, 'error');
        }
    });
}

function deleteAd(id) {
    xpkConfirm('确定删除该广告？', function() {
        fetch(adminUrl('/ad/delete'), {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id=' + id
        })
        .then(r => r.json())
        .then(data => {
            if (data.code === 0) {
                xpkToast(data.msg, 'success');
                location.reload();
            } else {
                xpkToast(data.msg, 'error');
            }
        });
    });
}
</script>
