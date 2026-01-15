<div class="space-y-6">
    <!-- 页面标题 -->
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800 flex items-center">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            数据统计
        </h2>
        <div class="flex items-center gap-4">
            <span class="text-sm text-gray-500">实时在线: <span class="text-green-500 font-bold"><?= $onlineCount ?></span> 人</span>
            <select id="daysSelect" onchange="changeDays(this.value)" class="border rounded px-3 py-1.5 text-sm">
                <option value="7" <?= $days == 7 ? 'selected' : '' ?>>最近7天</option>
                <option value="14" <?= $days == 14 ? 'selected' : '' ?>>最近14天</option>
                <option value="30" <?= $days == 30 ? 'selected' : '' ?>>最近30天</option>
            </select>
        </div>
    </div>

    <?php 
    // 检查统计数据是否正常
    $totalUvPv = $overview['uv'] + $overview['pv'] + $overview['uv_yesterday'] + $overview['pv_yesterday'];
    if ($totalUvPv == 0): 
    ?>
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <h4 class="font-medium text-yellow-800 mb-2 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            统计数据为空
        </h4>
        <p class="text-sm text-yellow-700 mb-2">可能的原因：</p>
        <ul class="text-sm text-yellow-600 list-disc list-inside space-y-1">
            <li>网站刚部署，还没有访问记录</li>
            <li>统计日志表 (xpk_stats_log) 不存在或结构不正确</li>
            <li>前台页面访问时统计记录失败（检查 runtime/logs/ 目录下的日志）</li>
        </ul>
        <p class="text-sm text-yellow-700 mt-2 flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            提示：访问前台页面后刷新此页面查看是否有数据更新
        </p>
    </div>
    <?php endif; ?>

    <!-- 今日概览 -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">今日UV</p>
                    <p class="text-2xl font-bold text-blue-600"><?= number_format($overview['uv']) ?></p>
                </div>
                <div class="text-3xl">
                    <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-2 text-xs <?= $overview['uv'] >= $overview['uv_yesterday'] ? 'text-green-500' : 'text-red-500' ?>">
                <?php $uvDiff = $overview['uv'] - $overview['uv_yesterday']; ?>
                <?= $uvDiff >= 0 ? '↑' : '↓' ?> <?= abs($uvDiff) ?> 较昨日
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">今日PV</p>
                    <p class="text-2xl font-bold text-green-600"><?= number_format($overview['pv']) ?></p>
                </div>
                <div class="text-3xl">
                    <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-2 text-xs <?= $overview['pv'] >= $overview['pv_yesterday'] ? 'text-green-500' : 'text-red-500' ?>">
                <?php $pvDiff = $overview['pv'] - $overview['pv_yesterday']; ?>
                <?= $pvDiff >= 0 ? '↑' : '↓' ?> <?= abs($pvDiff) ?> 较昨日
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">今日新用户</p>
                    <p class="text-2xl font-bold text-purple-600"><?= number_format($overview['new_users']) ?></p>
                </div>
                <div class="text-3xl">
                    <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">今日播放</p>
                    <p class="text-2xl font-bold text-red-600"><?= number_format($overview['plays']) ?></p>
                </div>
                <div class="text-3xl">
                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293H15M9 10V9a2 2 0 012-2h2a2 2 0 012 2v1M9 10v5a2 2 0 002 2h2a2 2 0 002-2v-5m-6 0h6"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- 内容统计 -->
    <div class="grid grid-cols-3 md:grid-cols-6 gap-4">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow p-4 text-white">
            <p class="text-sm opacity-80">视频总数</p>
            <p class="text-xl font-bold"><?= number_format($contentStats['vods']) ?></p>
        </div>
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow p-4 text-white">
            <p class="text-sm opacity-80">演员总数</p>
            <p class="text-xl font-bold"><?= number_format($contentStats['actors']) ?></p>
        </div>
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow p-4 text-white">
            <p class="text-sm opacity-80">文章总数</p>
            <p class="text-xl font-bold"><?= number_format($contentStats['arts']) ?></p>
        </div>
        <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-lg shadow p-4 text-white">
            <p class="text-sm opacity-80">用户总数</p>
            <p class="text-xl font-bold"><?= number_format($contentStats['users']) ?></p>
        </div>
        <div class="bg-gradient-to-r from-pink-500 to-pink-600 rounded-lg shadow p-4 text-white">
            <p class="text-sm opacity-80">评论总数</p>
            <p class="text-xl font-bold"><?= number_format($contentStats['comments']) ?></p>
        </div>
        <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow p-4 text-white">
            <p class="text-sm opacity-80">短视频</p>
            <p class="text-xl font-bold"><?= number_format($contentStats['shorts']) ?></p>
        </div>
    </div>

    <!-- 图表区域 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- 访问趋势 -->
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-lg font-semibold mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                访问趋势
            </h3>
            <canvas id="trendChart" height="200"></canvas>
        </div>
        <!-- 用户增长 -->
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-lg font-semibold mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                用户增长
            </h3>
            <canvas id="userChart" height="200"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- 来源统计 -->
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-lg font-semibold mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                </svg>
                流量来源
            </h3>
            <canvas id="refererChart" height="200"></canvas>
        </div>
        <!-- 设备统计 -->
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-lg font-semibold mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                设备分布
            </h3>
            <canvas id="deviceChart" height="200"></canvas>
        </div>
        <!-- 热门视频 -->
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-lg font-semibold mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"></path>
                </svg>
                热门视频
            </h3>
            <div class="space-y-2">
                <?php if (!empty($hotVideos)): ?>
                <?php foreach ($hotVideos as $i => $video): ?>
                <div class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded">
                    <span class="w-6 h-6 flex items-center justify-center rounded-full text-xs font-bold <?= $i < 3 ? 'bg-red-500 text-white' : 'bg-gray-200' ?>">
                        <?= $i + 1 ?>
                    </span>
                    <div class="flex-1 truncate text-sm"><?= htmlspecialchars($video['vod_name']) ?></div>
                    <span class="text-xs text-gray-500"><?= number_format($video['period_pv'] ?? 0) ?> 次</span>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <p class="text-gray-400 text-center py-4">暂无数据</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 日志清理 -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="mb-6">
            <h3 class="text-xl font-semibold mb-2 flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                日志清理管理
            </h3>
            <p class="text-sm text-gray-500">定期清理过期日志，释放数据库空间。可以单独清理统计日志，或批量清理所有日志表。</p>
        </div>

        <!-- 日志统计信息 -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 rounded-lg p-4">
                <div class="text-sm text-blue-600 mb-1">统计日志</div>
                <div class="text-2xl font-bold text-blue-700"><?= number_format($logStats['stats_log'] ?? 0) ?></div>
            </div>
            <div class="bg-green-50 rounded-lg p-4">
                <div class="text-sm text-green-600 mb-1">操作日志</div>
                <div class="text-2xl font-bold text-green-700"><?= number_format($logStats['admin_log'] ?? 0) ?></div>
            </div>
            <div class="bg-purple-50 rounded-lg p-4">
                <div class="text-sm text-purple-600 mb-1">搜索日志</div>
                <div class="text-2xl font-bold text-purple-700"><?= number_format($logStats['search_log'] ?? 0) ?></div>
            </div>
            <div class="bg-orange-50 rounded-lg p-4">
                <div class="text-sm text-orange-600 mb-1">采集日志</div>
                <div class="text-2xl font-bold text-orange-700"><?= number_format($logStats['collect_log'] ?? 0) ?></div>
            </div>
            <div class="bg-pink-50 rounded-lg p-4">
                <div class="text-sm text-pink-600 mb-1">评论投票</div>
                <div class="text-2xl font-bold text-pink-700"><?= number_format($logStats['comment_vote'] ?? 0) ?></div>
            </div>
            <div class="bg-indigo-50 rounded-lg p-4">
                <div class="text-sm text-indigo-600 mb-1">评分记录</div>
                <div class="text-2xl font-bold text-indigo-700"><?= number_format($logStats['score'] ?? 0) ?></div>
            </div>
            <div class="bg-yellow-50 rounded-lg p-4">
                <div class="text-sm text-yellow-600 mb-1">观看历史</div>
                <div class="text-2xl font-bold text-yellow-700"><?= number_format($logStats['user_history'] ?? 0) ?></div>
            </div>
            <div class="bg-red-50 rounded-lg p-4">
                <div class="text-sm text-red-600 mb-1">上传分片</div>
                <div class="text-2xl font-bold text-red-700"><?= number_format($logStats['upload_chunk'] ?? 0) ?></div>
            </div>
        </div>

        <!-- 清理选项 -->
        <div class="border-t pt-6">
            <div class="flex flex-col lg:flex-row gap-6">
                <!-- 快速清理 -->
                <div class="flex-1">
                    <h4 class="font-medium mb-3">快速清理</h4>
                    <div class="flex items-center gap-2">
                        <select id="cleanDays" class="border rounded px-3 py-2 text-sm">
                            <option value="30">保留30天</option>
                            <option value="60">保留60天</option>
                            <option value="90" selected>保留90天</option>
                            <option value="180">保留180天</option>
                        </select>
                        <button onclick="cleanLogs()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 text-sm">
                            清理统计日志
                        </button>
                    </div>
                </div>

                <!-- 批量清理 -->
                <div class="flex-1">
                    <h4 class="font-medium mb-3">批量清理所有日志</h4>
                    <button onclick="showAdvancedClean()" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 text-sm">
                        高级清理设置
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 高级清理弹窗 -->
    <div id="advancedCleanModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">高级日志清理设置</h3>
                        <button onclick="hideAdvancedClean()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">统计日志保留天数</label>
                                <select id="statsDays" class="w-full border rounded px-3 py-2 text-sm">
                                    <option value="30">30天</option>
                                    <option value="60">60天</option>
                                    <option value="90" selected>90天</option>
                                    <option value="180">180天</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">操作日志保留天数</label>
                                <select id="adminDays" class="w-full border rounded px-3 py-2 text-sm">
                                    <option value="15">15天</option>
                                    <option value="30" selected>30天</option>
                                    <option value="60">60天</option>
                                    <option value="90">90天</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">搜索日志保留天数</label>
                                <select id="searchDays" class="w-full border rounded px-3 py-2 text-sm">
                                    <option value="30">30天</option>
                                    <option value="60">60天</option>
                                    <option value="90" selected>90天</option>
                                    <option value="180">180天</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">采集日志保留天数</label>
                                <select id="collectDays" class="w-full border rounded px-3 py-2 text-sm">
                                    <option value="15">15天</option>
                                    <option value="30" selected>30天</option>
                                    <option value="60">60天</option>
                                    <option value="90">90天</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">评论投票保留天数</label>
                                <select id="voteDays" class="w-full border rounded px-3 py-2 text-sm">
                                    <option value="90">90天</option>
                                    <option value="180" selected>180天</option>
                                    <option value="365">365天</option>
                                    <option value="730">730天</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">评分记录保留天数</label>
                                <select id="scoreDays" class="w-full border rounded px-3 py-2 text-sm">
                                    <option value="180">180天</option>
                                    <option value="365" selected>365天</option>
                                    <option value="730">730天</option>
                                    <option value="1095">1095天</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">观看历史保留天数</label>
                                <select id="historyDays" class="w-full border rounded px-3 py-2 text-sm">
                                    <option value="180">180天</option>
                                    <option value="365" selected>365天</option>
                                    <option value="730">730天</option>
                                    <option value="1095">1095天</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">上传分片保留天数</label>
                                <select id="chunkDays" class="w-full border rounded px-3 py-2 text-sm">
                                    <option value="1">1天</option>
                                    <option value="3">3天</option>
                                    <option value="7" selected>7天</option>
                                    <option value="15">15天</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="bg-yellow-50 border border-yellow-200 rounded p-3">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        <strong>注意：</strong>此操作将永久删除选定时间范围外的所有日志记录，请谨慎操作！建议先备份重要数据。
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end gap-3 mt-6">
                        <button onclick="hideAdvancedClean()" class="px-4 py-2 text-gray-600 border border-gray-300 rounded hover:bg-gray-50">
                            取消
                        </button>
                        <button onclick="cleanAllLogs()" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                            开始清理
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
// 数据
const trendData = <?= json_encode($trend) ?>;
const userTrendData = <?= json_encode($userTrend) ?>;
const refererData = <?= json_encode($refererStats) ?>;
const deviceData = <?= json_encode($deviceStats) ?>;

// 访问趋势图
new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: trendData.dates,
        datasets: [
            {
                label: 'UV',
                data: trendData.uv,
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.3
            },
            {
                label: 'PV',
                data: trendData.pv,
                borderColor: '#10B981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: true,
                tension: 0.3
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        scales: { y: { beginAtZero: true } }
    }
});

// 用户增长图
new Chart(document.getElementById('userChart'), {
    type: 'bar',
    data: {
        labels: userTrendData.dates,
        datasets: [{
            label: '新增用户',
            data: userTrendData.counts,
            backgroundColor: '#8B5CF6',
            borderRadius: 4
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});

// 来源统计图
new Chart(document.getElementById('refererChart'), {
    type: 'doughnut',
    data: {
        labels: refererData.map(r => r.source),
        datasets: [{
            data: refererData.map(r => r.cnt),
            backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#6B7280']
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } }
    }
});

// 设备统计图
new Chart(document.getElementById('deviceChart'), {
    type: 'pie',
    data: {
        labels: deviceData.map(d => d.name),
        datasets: [{
            data: deviceData.map(d => d.value),
            backgroundColor: ['#3B82F6', '#10B981', '#F59E0B']
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } }
    }
});

// 切换天数
function changeDays(days) {
    location.href = adminUrl('/stats&days=' + days);
}

// 清理统计日志
function cleanLogs() {
    const days = document.getElementById('cleanDays').value;
    xpkConfirm('确定要清理 ' + days + ' 天前的统计日志吗？', function() {
        fetch(adminUrl('/stats/clean'), {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'days=' + days + '&_token=<?= $csrfToken ?>'
        })
        .then(r => r.json())
        .then(data => {
            if (data.code === 0) {
                xpkToast(data.msg, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                xpkToast(data.msg, 'error');
            }
        });
    });
}

// 显示高级清理弹窗
function showAdvancedClean() {
    document.getElementById('advancedCleanModal').classList.remove('hidden');
}

// 隐藏高级清理弹窗
function hideAdvancedClean() {
    document.getElementById('advancedCleanModal').classList.add('hidden');
}

// 清理所有日志
function cleanAllLogs() {
    const formData = new FormData();
    formData.append('_token', '<?= $csrfToken ?>');
    formData.append('stats_days', document.getElementById('statsDays').value);
    formData.append('admin_days', document.getElementById('adminDays').value);
    formData.append('search_days', document.getElementById('searchDays').value);
    formData.append('collect_days', document.getElementById('collectDays').value);
    formData.append('vote_days', document.getElementById('voteDays').value);
    formData.append('score_days', document.getElementById('scoreDays').value);
    formData.append('history_days', document.getElementById('historyDays').value);
    formData.append('chunk_days', document.getElementById('chunkDays').value);

    xpkConfirm('确定要清理所有选定的日志吗？此操作不可恢复！', function() {
        hideAdvancedClean();
        
        fetch(adminUrl('/stats/cleanAll'), {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.code === 0) {
                let message = data.msg;
                if (data.data) {
                    const details = [];
                    for (const [table, count] of Object.entries(data.data)) {
                        if (count > 0) {
                            const tableNames = {
                                'stats_log': '统计日志',
                                'admin_log': '操作日志',
                                'search_log': '搜索日志',
                                'collect_log': '采集日志',
                                'comment_vote': '评论投票',
                                'score': '评分记录',
                                'user_history': '观看历史',
                                'upload_chunk': '上传分片'
                            };
                            details.push(`${tableNames[table] || table}: ${count}条`);
                        }
                    }
                    if (details.length > 0) {
                        message += '\n详情: ' + details.join(', ');
                    }
                }
                xpkToast(message, 'success');
                setTimeout(() => location.reload(), 2000);
            } else {
                xpkToast(data.msg, 'error');
            }
        })
        .catch(err => {
            console.error('清理失败:', err);
            xpkToast('清理失败，请检查网络连接', 'error');
        });
    });
}

// 点击弹窗外部关闭
document.getElementById('advancedCleanModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideAdvancedClean();
    }
});
</script>
