<?php
/**
 * 后台播放器管理控制器
 * Powered by https://xpornkit.com
 */

class AdminPlayerController extends AdminBaseController
{
    private XpkPlayer $playerModel;

    public function __construct()
    {
        parent::__construct();
        require_once MODEL_PATH . 'Player.php';
        $this->playerModel = new XpkPlayer();
    }

    /**
     * 播放器列表
     */
    public function index(): void
    {
        $players = $this->playerModel->getAll();
        $this->assign('players', $players);
        $this->assign('flash', $this->getFlash());
        $this->assign('csrfToken', $this->csrfToken());
        $this->render('player/index', '播放器管理');
    }

    /**
     * 添加播放器
     */
    public function add(): void
    {
        $this->assign('player', null);
        $this->assign('csrfToken', $this->csrfToken());
        $this->render('player/form', '添加播放器');
    }

    /**
     * 处理添加
     */
    public function doAdd(): void
    {
        $this->requireCsrf();

        $code = trim($this->post('player_code', ''));
        $name = trim($this->post('player_name', ''));
        $parse = trim($this->post('player_parse', ''));
        $sort = (int)$this->post('player_sort', 0);
        $status = (int)$this->post('player_status', 1);
        $tip = trim($this->post('player_tip', ''));

        if (empty($code) || empty($name)) {
            $this->error('标识和名称不能为空');
        }

        // 验证code格式
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $code)) {
            $this->error('标识只能包含字母、数字和下划线');
        }

        // 检查code是否已存在
        if ($this->playerModel->codeExists($code)) {
            $this->error('该标识已存在');
        }

        $this->playerModel->insert([
            'player_code' => $code,
            'player_name' => $name,
            'player_parse' => $parse,
            'player_sort' => $sort,
            'player_status' => $status,
            'player_tip' => $tip
        ]);

        $this->log('添加', '播放器', $name);
        $this->success('添加成功');
    }

    /**
     * 编辑播放器
     */
    public function edit(int $id): void
    {
        $player = $this->playerModel->find($id);
        if (!$player) {
            $this->flash('error', '播放器不存在');
            $this->redirect('/' . $this->adminEntry . '?s=player');
            return;
        }

        $this->assign('player', $player);
        $this->assign('csrfToken', $this->csrfToken());
        $this->render('player/form', '编辑 - ' . $player['player_name']);
    }

    /**
     * 处理编辑
     */
    public function doEdit(int $id): void
    {
        $this->requireCsrf();

        $player = $this->playerModel->find($id);
        if (!$player) {
            $this->error('播放器不存在');
        }

        $code = trim($this->post('player_code', ''));
        $name = trim($this->post('player_name', ''));
        $parse = trim($this->post('player_parse', ''));
        $sort = (int)$this->post('player_sort', 0);
        $status = (int)$this->post('player_status', 1);
        $tip = trim($this->post('player_tip', ''));

        if (empty($code) || empty($name)) {
            $this->error('标识和名称不能为空');
        }

        // 验证code格式
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $code)) {
            $this->error('标识只能包含字母、数字和下划线');
        }

        // 检查code是否已存在（排除当前记录）
        if ($this->playerModel->codeExists($code, $id)) {
            $this->error('该标识已存在');
        }

        $this->playerModel->update($id, [
            'player_code' => $code,
            'player_name' => $name,
            'player_parse' => $parse,
            'player_sort' => $sort,
            'player_status' => $status,
            'player_tip' => $tip
        ]);

        $this->log('编辑', '播放器', $name);
        $this->success('保存成功');
    }

    /**
     * 删除播放器
     */
    public function delete(): void
    {
        // CSRF 验证
        $this->requireCsrf();
        
        $id = (int)$this->post('id', 0);
        
        $player = $this->playerModel->find($id);
        if (!$player) {
            $this->error('播放器不存在');
        }

        $this->playerModel->delete($id);
        $this->log('删除', '播放器', $player['player_name']);
        $this->success('删除成功');
    }

    /**
     * 切换状态
     */
    public function toggle(): void
    {
        // CSRF 验证
        $this->requireCsrf();
        
        $id = (int)$this->post('id', 0);
        
        $player = $this->playerModel->find($id);
        if (!$player) {
            $this->error('播放器不存在');
        }

        $newStatus = $player['player_status'] ? 0 : 1;
        $this->playerModel->update($id, ['player_status' => $newStatus]);
        
        $this->log('切换状态', '播放器', $player['player_name']);
        $this->success($newStatus ? '已启用' : '已禁用');
    }
}
