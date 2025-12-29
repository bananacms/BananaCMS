<?php
/**
 * 分类控制器
 * Powered by https://xpornkit.com
 */

class TypeController extends BaseController
{
    private XpkVod $vodModel;
    private XpkType $typeModel;

    public function __construct()
    {
        parent::__construct();
        $this->vodModel = new XpkVod();
        $this->typeModel = new XpkType();
    }

    /**
     * 分类页
     */
    public function index(int $id, int $page = 1): void
    {
        $type = $this->typeModel->getById($id);
        if (!$type) {
            $this->redirect(xpk_url());
            return;
        }

        // 获取父分类信息
        if ($type['type_pid'] > 0) {
            $parent = $this->typeModel->getById($type['type_pid']);
            if ($parent) {
                $type['parent_name'] = $parent['type_name'];
                $type['parent_en'] = $parent['type_en'];
            }
        }

        $result = $this->vodModel->getByType($id, $page, PAGE_SIZE);
        
        // 子分类
        $subTypes = $this->typeModel->getList($id);
        
        $this->assign('type', $type);
        $this->assign('subTypes', $subTypes);
        $this->assign('vodList', $result['list']);
        $this->assign('page', $result['page']);
        $this->assign('totalPages', $result['totalPages']);
        $this->assign('total', $result['total']);
        $this->assign('baseUrl', '/type/' . $id);
        
        // SEO
        $seoVars = ['name' => $type['type_name']];
        $this->assign('title', $this->seoTitle('type', $seoVars));
        $this->assign('keywords', $this->seoKeywords('type', $seoVars));
        
        $this->render('type/index');
    }

    /**
     * 通过 slug 访问分类
     */
    public function indexBySlug(string $slug, int $page = 1): void
    {
        $type = $this->typeModel->findBySlug($slug);
        
        // 调试信息
        if (isset($_GET['debug'])) {
            header('Content-Type: application/json');
            $typeIds = $type ? $this->typeModel->getChildIds($type['type_id']) : [];
            echo json_encode([
                'slug' => $slug,
                'type' => $type,
                'childIds' => $typeIds,
                'allTypes' => $this->typeModel->getAll()
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }
        
        if (!$type) {
            $this->redirect(xpk_url());
            return;
        }
        $this->index($type['type_id'], $page);
    }
}
