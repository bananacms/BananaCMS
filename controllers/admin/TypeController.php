<?php
/**
 * 后台分类管理控制器
 * Powered by https://xpornkit.com
 */

class AdminTypeController extends AdminBaseController
{
    private XpkType $typeModel;

    public function __construct()
    {
        parent::__construct();
        $this->typeModel = new XpkType();
    }

    /**
     * 分类列表
     */
    public function index(): void
    {
        $sort = $this->get('sort', 'vod_count'); // 默认按资源数排序
        $order = $this->get('order', 'desc');
        
        $types = $this->typeModel->getTree();
        $parentTypes = $this->typeModel->getAll(['type_pid' => 0]);
        
        // 统计每个分类下的视频数量
        $db = XpkDatabase::getInstance();
        $vodCounts = $db->query(
            "SELECT vod_type_id, COUNT(*) as cnt FROM " . DB_PREFIX . "vod GROUP BY vod_type_id"
        );
        $countMap = [];
        foreach ($vodCounts as $row) {
            $countMap[$row['vod_type_id']] = (int)$row['cnt'];
        }
        
        // 将数量添加到分类数据中
        foreach ($types as &$type) {
            $type['vod_count'] = $countMap[$type['type_id']] ?? 0;
        }
        unset($type);
        
        // 按资源数排序
        if ($sort === 'vod_count') {
            usort($types, function($a, $b) use ($order) {
                if ($order === 'asc') {
                    return $a['vod_count'] - $b['vod_count'];
                }
                return $b['vod_count'] - $a['vod_count'];
            });
        }

        $this->assign('types', $types);
        $this->assign('parentTypes', $parentTypes);
        $this->assign('sort', $sort);
        $this->assign('order', $order);
        $this->assign('csrfToken', $this->csrfToken());
        $this->assign('flash', $this->getFlash());
        $this->render('type/index', '分类管理');
    }

    /**
     * 获取单个分类（AJAX）
     */
    public function getOne(): void
    {
        $id = (int)$this->input('id', 0);
        $type = $this->typeModel->find($id);
        
        if (!$type) {
            $this->error('分类不存在');
        }
        
        $this->success('ok', $type);
    }

    /**
     * 添加分类（AJAX）
     */
    public function add(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // GET请求显示表单页面（兼容旧方式）
            $parentTypes = $this->typeModel->getAll(['type_pid' => 0]);
            $this->assign('parentTypes', $parentTypes);
            $this->assign('csrfToken', $this->csrfToken());
            $this->render('type/form', '添加分类');
            return;
        }

        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $data = [
            'type_pid' => (int)$this->post('type_pid', 0),
            'type_name' => trim($this->post('type_name', '')),
            'type_en' => trim($this->post('type_en', '')),
            'type_sort' => (int)$this->post('type_sort', 0),
            'type_status' => (int)$this->post('type_status', 1),
            'type_key' => trim($this->post('type_key', '')),
            'type_des' => trim($this->post('type_des', '')),
        ];

        if (empty($data['type_name'])) {
            $this->error('分类名称不能为空');
        }

        $id = $this->typeModel->insert($data);

        if ($id) {
            $this->log('添加', '分类', "ID:{$id} {$data['type_name']}");
            $this->success('添加成功');
        } else {
            $this->error('添加失败');
        }
    }

    /**
     * 编辑分类（AJAX）
     */
    public function edit(int $id): void
    {
        $type = $this->typeModel->find($id);
        if (!$type) {
            $this->error('分类不存在');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // GET请求显示表单页面（兼容旧方式）
            $parentTypes = $this->typeModel->getAll(['type_pid' => 0]);
            $this->assign('type', $type);
            $this->assign('parentTypes', $parentTypes);
            $this->assign('csrfToken', $this->csrfToken());
            $this->render('type/form', '编辑分类');
            return;
        }

        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $data = [
            'type_pid' => (int)$this->post('type_pid', 0),
            'type_name' => trim($this->post('type_name', '')),
            'type_en' => trim($this->post('type_en', '')),
            'type_sort' => (int)$this->post('type_sort', 0),
            'type_status' => (int)$this->post('type_status', 1),
            'type_key' => trim($this->post('type_key', '')),
            'type_des' => trim($this->post('type_des', '')),
        ];

        // 不能将自己设为父级
        if ($data['type_pid'] == $id) {
            $this->error('不能将自己设为父级分类');
        }

        // 检查父分类是否变更，需要同步更新视频的一级分类ID
        $oldPid = (int)$type['type_pid'];
        $newPid = $data['type_pid'];
        
        $this->typeModel->update($id, $data);
        
        // 如果父分类变更，同步更新该分类及其子分类下所有视频的 vod_type_id_1
        if ($oldPid !== $newPid) {
            $this->syncVodTopLevelType($id, $newPid);
        }
        
        $this->log('编辑', '分类', "ID:{$id} {$data['type_name']}");

        $this->success('保存成功');
    }
    
    /**
     * 同步更新视频的一级分类ID
     * 当分类的父级变更时，需要更新该分类及其子分类下所有视频的 vod_type_id_1
     */
    private function syncVodTopLevelType(int $typeId, int $newPid): void
    {
        $db = XpkDatabase::getInstance();
        
        // 计算新的一级分类ID
        // 如果 newPid = 0，说明该分类变成了一级分类，vod_type_id_1 = typeId
        // 如果 newPid > 0，说明该分类变成了子分类，vod_type_id_1 = newPid
        $newTopLevelId = $newPid > 0 ? $newPid : $typeId;
        
        // 获取该分类及其所有子分类的ID
        // 注意：此时数据库中的 type_pid 已经更新，所以 getChildIds 会返回正确的子分类
        $typeIds = $this->typeModel->getChildIds($typeId);
        
        if (empty($typeIds)) {
            return;
        }
        
        // 批量更新视频的一级分类ID
        $placeholders = implode(',', array_fill(0, count($typeIds), '?'));
        $params = array_merge([$newTopLevelId], $typeIds);
        
        $affected = $db->execute(
            "UPDATE " . DB_PREFIX . "vod SET vod_type_id_1 = ? WHERE vod_type_id IN ({$placeholders})",
            $params
        );
        
        if ($affected > 0) {
            $this->log('同步', '分类', "更新 {$affected} 个视频的一级分类为 ID:{$newTopLevelId}");
        }
    }

    /**
     * 删除分类
     */
    public function delete(): void
    {
        $id = (int)$this->post('id', 0);

        if ($id <= 0) {
            $this->error('参数错误');
        }

        // 删除前先查询分类详情，用于日志记录
        $type = $this->typeModel->find($id);
        if (!$type) {
            $this->error('分类不存在');
        }

        // 检查是否有子分类
        $children = $this->typeModel->count(['type_pid' => $id]);
        if ($children > 0) {
            $this->error('请先删除子分类');
        }

        // 检查是否有视频
        $vodCount = XpkDatabase::getInstance()->queryOne(
            "SELECT COUNT(*) as cnt FROM " . DB_PREFIX . "vod WHERE vod_type_id = ?",
            [$id]
        )['cnt'] ?? 0;

        if ($vodCount > 0) {
            $this->error('该分类下有视频，无法删除');
        }

        $this->typeModel->delete($id);
        $this->log('删除', '分类', "ID:{$id} 《{$type['type_name']}》");
        $this->success('删除成功');
    }

    /**
     * 批量删除分类
     */
    public function batchDelete(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $ids = $this->post('ids', []);
        
        if (empty($ids) || !is_array($ids)) {
            $this->error('请选择要删除的分类');
        }

        $ids = array_map('intval', $ids);
        $db = XpkDatabase::getInstance();
        
        $success = 0;
        $failed = 0;
        $errors = [];
        $deletedTypes = [];

        foreach ($ids as $id) {
            if ($id <= 0) continue;

            // 先查询分类详情
            $type = $this->typeModel->find($id);
            if (!$type) {
                $failed++;
                $errors[] = "ID:{$id} 不存在";
                continue;
            }

            // 检查是否有子分类
            $children = $this->typeModel->count(['type_pid' => $id]);
            if ($children > 0) {
                $failed++;
                $errors[] = "《{$type['type_name']}》有子分类";
                continue;
            }

            // 检查是否有视频
            $vodCount = $db->queryOne(
                "SELECT COUNT(*) as cnt FROM " . DB_PREFIX . "vod WHERE vod_type_id = ?",
                [$id]
            )['cnt'] ?? 0;

            if ($vodCount > 0) {
                $failed++;
                $errors[] = "《{$type['type_name']}》有视频";
                continue;
            }

            $this->typeModel->delete($id);
            $deletedTypes[] = "ID:{$id}《{$type['type_name']}》";
            $success++;
        }

        // 记录详细日志
        $logContent = "成功删除 {$success} 个: " . implode(', ', $deletedTypes);
        if ($failed > 0) {
            $logContent .= " | 失败 {$failed} 个: " . implode(', ', array_slice($errors, 0, 5));
        }
        $this->log('批量删除', '分类', $logContent);
        
        if ($failed > 0) {
            $this->success("删除完成：成功 {$success} 个，失败 {$failed} 个（" . implode('；', array_slice($errors, 0, 3)) . "）");
        } else {
            $this->success("成功删除 {$success} 个分类");
        }
    }
    
    /**
     * 修复视频一级分类数据（AJAX）
     * 根据当前分类的父子关系，批量更新所有视频的 vod_type_id_1
     */
    public function fixVodTypeId1(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }
        
        $db = XpkDatabase::getInstance();
        
        // 获取所有分类
        $types = $this->typeModel->getAll();
        
        // 构建分类ID到一级分类ID的映射
        $typeMap = [];
        foreach ($types as $type) {
            $typeId = (int)$type['type_id'];
            $typePid = (int)$type['type_pid'];
            
            // 如果是一级分类（pid=0），一级分类ID就是自己
            // 如果是子分类，一级分类ID就是父分类ID
            $typeMap[$typeId] = $typePid > 0 ? $typePid : $typeId;
        }
        
        if (empty($typeMap)) {
            $this->error('没有分类数据');
        }
        
        // 批量更新视频的 vod_type_id_1
        $totalUpdated = 0;
        
        foreach ($typeMap as $typeId => $topLevelId) {
            $affected = $db->execute(
                "UPDATE " . DB_PREFIX . "vod SET vod_type_id_1 = ? WHERE vod_type_id = ? AND (vod_type_id_1 != ? OR vod_type_id_1 IS NULL OR vod_type_id_1 = 0)",
                [$topLevelId, $typeId, $topLevelId]
            );
            $totalUpdated += $affected;
        }
        
        $this->log('修复数据', '分类', "更新 {$totalUpdated} 个视频的一级分类ID");
        $this->success("修复完成，更新了 {$totalUpdated} 个视频的一级分类");
    }
}
