<?php
/**
 * 香蕉CMS 简单测试脚本
 * 运行: php tests/test.php
 * Powered by https://xpornkit.com
 */

// 加载配置
require_once __DIR__ . '/../config/config.php';
require_once CORE_PATH . 'Database.php';
require_once CORE_PATH . 'Cache.php';
require_once MODEL_PATH . 'Model.php';
require_once MODEL_PATH . 'Vod.php';
require_once MODEL_PATH . 'Type.php';

class XpkTest
{
    private int $passed = 0;
    private int $failed = 0;

    public function run(): void
    {
        echo "========================================\n";
        echo "香蕉CMS 测试开始\n";
        echo "========================================\n\n";

        $this->testDatabase();
        $this->testCache();
        $this->testModels();

        echo "\n========================================\n";
        echo "测试完成: {$this->passed} 通过, {$this->failed} 失败\n";
        echo "========================================\n";
    }

    private function testDatabase(): void
    {
        echo "【数据库测试】\n";
        
        try {
            $db = XpkDatabase::getInstance();
            $this->assert($db !== null, '数据库连接');
            
            $result = $db->queryOne("SELECT 1 as test");
            $this->assert($result['test'] == 1, '数据库查询');
        } catch (Exception $e) {
            $this->assert(false, '数据库连接: ' . $e->getMessage());
        }
        
        echo "\n";
    }

    private function testCache(): void
    {
        echo "【缓存测试】\n";
        
        $cache = xpk_cache();
        
        // 测试设置和获取
        $cache->set('test_key', 'test_value', 60);
        $this->assert($cache->get('test_key') === 'test_value', '缓存设置和获取');
        
        // 测试删除
        $cache->delete('test_key');
        $this->assert($cache->get('test_key') === null, '缓存删除');
        
        // 测试 remember
        $value = $cache->remember('test_remember', 60, fn() => 'remembered');
        $this->assert($value === 'remembered', '缓存 remember');
        $cache->delete('test_remember');
        
        echo "\n";
    }

    private function testModels(): void
    {
        echo "【模型测试】\n";
        
        // 测试 Type 模型
        $typeModel = new XpkType();
        $types = $typeModel->getAll();
        $this->assert(is_array($types), 'Type 模型 getAll');
        
        // 测试 Vod 模型
        $vodModel = new XpkVod();
        $vods = $vodModel->getList(5);
        $this->assert(is_array($vods), 'Vod 模型 getList');
        
        echo "\n";
    }

    private function assert(bool $condition, string $name): void
    {
        if ($condition) {
            echo "  ✓ {$name}\n";
            $this->passed++;
        } else {
            echo "  ✗ {$name}\n";
            $this->failed++;
        }
    }
}

// 运行测试
$test = new XpkTest();
$test->run();
