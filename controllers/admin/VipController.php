<?php
/**
 * VIP套餐管理控制器
 * Powered by https://xpornkit.com
 */

class AdminVipController extends AdminBaseController
{
    /**
     * 套餐列表
     */
    public function index(): void
    {
        $packages = $this->db->query(
            "SELECT * FROM " . DB_PREFIX . "vip_package ORDER BY package_sort ASC, package_id ASC"
        );
        
        // 加载VIP配置
        $configFile = CONFIG_PATH . 'vip.php';
        $vipConfig = file_exists($configFile) ? require $configFile : [];
        
        $this->assign('packages', $packages);
        $this->assign('vipConfig', $vipConfig);
        $this->render('vip/index', 'VIP套餐管理');
    }
    
    /**
     * 添加套餐
     */
    public function add(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->save();
            return;
        }
        
        $this->assign('package', null);
        $this->render('vip/edit', '添加VIP套餐');
    }
    
    /**
     * 编辑套餐
     */
    public function edit(int $id = 0): void
    {
        if ($id <= 0) {
            $id = (int)$this->get('id', 0);
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->save($id);
            return;
        }
        
        $package = $this->db->queryOne(
            "SELECT * FROM " . DB_PREFIX . "vip_package WHERE package_id = ?",
            [$id]
        );
        
        if (!$package) {
            $this->error('套餐不存在');
        }
        
        $this->assign('package', $package);
        $this->render('vip/edit', '编辑VIP套餐');
    }
    
    /**
     * 保存套餐
     */
    private function save(int $id = 0): void
    {
        // CSRF 验证
        $this->requireCsrf();
        
        $data = [
            'package_name' => trim($this->post('package_name', '')),
            'package_code' => trim($this->post('package_code', '')),
            'package_price' => floatval($this->post('price', 0)),
            'package_price_usdt' => floatval($this->post('price_usdt', 0)) ?: null,
            'package_original' => floatval($this->post('original_price', 0)) ?: null,
            'package_days' => (int)$this->post('days', 1),
            'package_daily_limit' => (int)$this->post('daily_limit', 9999),
            'package_bonus_points' => (int)$this->post('bonus_points', 0),
            'package_bonus_days' => (int)$this->post('bonus_days', 0),
            'package_desc' => trim($this->post('description', '')),
            'package_icon' => trim($this->post('icon', '')),
            'package_hot' => (int)$this->post('is_hot', 0),
            'package_status' => (int)$this->post('status', 1),
            'package_sort' => (int)$this->post('sort', 0),
        ];
        
        // 验证
        if (empty($data['package_name']) || empty($data['package_code'])) {
            $this->error('套餐名称和编码不能为空');
        }
        if ($data['package_price'] <= 0) {
            $this->error('价格必须大于0');
        }
        if ($data['package_days'] <= 0) {
            $this->error('有效天数必须大于0');
        }
        
        // 检查编码唯一性
        $exists = $this->db->queryOne(
            "SELECT package_id FROM " . DB_PREFIX . "vip_package WHERE package_code = ? AND package_id != ?",
            [$data['package_code'], $id]
        );
        if ($exists) {
            $this->error('套餐编码已存在');
        }
        
        if ($id > 0) {
            $this->db->update(DB_PREFIX . 'vip_package', $data, ['package_id' => $id]);
            $this->log('edit', 'vip', "编辑VIP套餐: {$data['package_name']}");
            $this->success('更新成功');
        } else {
            $this->db->insert(DB_PREFIX . 'vip_package', $data);
            $this->log('add', 'vip', "添加VIP套餐: {$data['package_name']}");
            $this->success('添加成功');
        }
    }
    
    /**
     * 切换状态
     */
    public function toggle(): void
    {
        // CSRF 验证
        $this->requireCsrf();
        
        $id = (int)$this->post('id', 0);
        
        $package = $this->db->queryOne(
            "SELECT package_name, package_status FROM " . DB_PREFIX . "vip_package WHERE package_id = ?",
            [$id]
        );
        
        if (!$package) {
            $this->error('套餐不存在');
        }
        
        $newStatus = $package['package_status'] ? 0 : 1;
        $this->db->execute(
            "UPDATE " . DB_PREFIX . "vip_package SET package_status = ? WHERE package_id = ?",
            [$newStatus, $id]
        );
        
        $action = $newStatus ? '上架' : '下架';
        $this->log('toggle', 'vip', "{$action}VIP套餐: {$package['package_name']}");
        $this->success('操作成功');
    }
    
    /**
     * 删除套餐
     */
    public function delete(): void
    {
        // CSRF 验证
        $this->requireCsrf();
        
        $id = (int)$this->post('id', 0);
        
        $package = $this->db->queryOne(
            "SELECT package_name FROM " . DB_PREFIX . "vip_package WHERE package_id = ?",
            [$id]
        );
        
        if (!$package) {
            $this->error('套餐不存在');
        }
        
        // 检查是否有关联订单
        $orderCount = $this->db->queryOne(
            "SELECT COUNT(*) as cnt FROM " . DB_PREFIX . "order WHERE product_id = ? AND order_type = 'vip'",
            [$id]
        )['cnt'] ?? 0;
        
        if ($orderCount > 0) {
            $this->error("该套餐有 {$orderCount} 个关联订单，无法删除");
        }
        
        $this->db->execute(
            "DELETE FROM " . DB_PREFIX . "vip_package WHERE package_id = ?",
            [$id]
        );
        
        $this->log('delete', 'vip', "删除VIP套餐: {$package['package_name']}");
        $this->success('删除成功');
    }
    
    /**
     * VIP配置
     */
    public function config(): void
    {
        $configFile = CONFIG_PATH . 'vip.php';
        $config = require $configFile;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // VIP功能开关
            $config['vip_enabled'] = $this->post('vip_enabled', '0') ? true : false;
            
            $config['free_user'] = [
                'daily_limit' => (int)$this->post('free_daily_limit', 3),
                'register_gift' => (int)$this->post('register_gift', 5),
            ];
            $config['points'] = [
                'unlock_cost' => (int)$this->post('unlock_cost', 10),
                'daily_sign' => (int)$this->post('daily_sign', 5),
            ];
            $config['invite'] = [
                'enabled' => true,
                'register_points' => (int)$this->post('invite_register_points', 50),
                'first_pay_rate' => floatval($this->post('first_pay_rate', 0.10)),
                'renew_rate' => floatval($this->post('renew_rate', 0.05)),
            ];
            
            // 写入配置文件
            $content = "<?php\n/**\n * VIP配置\n * Powered by https://xpornkit.com\n */\n\ndefined('XPK_ROOT') or exit('Access Denied');\n\nreturn " . var_export($config, true) . ";\n";
            file_put_contents($configFile, $content);
            
            $this->log('edit', 'vip', 'VIP配置更新');
            $this->success('保存成功');
            return;
        }
        
        $this->assign('config', $config);
        $this->render('vip/config', 'VIP配置');
    }
}
