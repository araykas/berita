<?php
/**
 * Simple PHP Router
 * Maps short action names (c, r, u, d, b) to their full meanings
 * c = create
 * r = read
 * u = update
 * d = delete
 * b = ban
 */

class Router {
    private $basePath;
    private $actionMap = [
        'c' => 'create',
        'r' => 'read',
        'u' => 'update',
        'd' => 'delete',
        'b' => 'ban'
    ];

    public function __construct($basePath) {
        $this->basePath = $basePath;
    }

    /**
     * Get full path untuk file action
     * @param string $module (user, berita, kategori, komentar)
     * @param string $action (c, r, u, d, b)
     * @return string|null
     */
    public function getPath($module, $action) {
        // Validasi module folder exist
        $moduleDir = $this->basePath . '/' . $module;
        if (!is_dir($moduleDir)) {
            return null;
        }

        // Map action
        $actionFile = $action . '.php';
        $filePath = $moduleDir . '/' . $actionFile;

        // Cek file exist
        if (!file_exists($filePath)) {
            return null;
        }

        return $filePath;
    }

    /**
     * Load dan execute action file
     * @param string $module
     * @param string $action
     * @return bool
     */
    public function execute($module, $action) {
        $filePath = $this->getPath($module, $action);
        
        if (!$filePath) {
            return false;
        }

        include $filePath;
        return true;
    }

    /**
     * Get action name dari short code
     * @param string $action
     * @return string
     */
    public function getActionName($action) {
        return $this->actionMap[$action] ?? $action;
    }

    /**
     * Validate module exists
     * @param string $module
     * @return bool
     */
    public function moduleExists($module) {
        return is_dir($this->basePath . '/' . $module);
    }

    /**
     * Validate action exists
     * @param string $module
     * @param string $action
     * @return bool
     */
    public function actionExists($module, $action) {
        return $this->getPath($module, $action) !== null;
    }

    /**
     * Get all available actions untuk module
     * @param string $module
     * @return array
     */
    public function getAvailableActions($module) {
        $moduleDir = $this->basePath . '/' . $module;
        if (!is_dir($moduleDir)) {
            return [];
        }

        $actions = [];
        $files = array_diff(scandir($moduleDir), ['.', '..']);
        
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $action = pathinfo($file, PATHINFO_FILENAME);
                $actions[$action] = $this->getActionName($action);
            }
        }

        return $actions;
    }

    /**
     * Build dashboard URL untuk navigasi
     * @param string $page (home, user, berita, kategori, komentar)
     * @return string
     */
    public function getDashboardUrl($page = 'home') {
        return 'dashboard.php?page=' . $page;
    }
}

// Initialize router
$router = new Router(__DIR__);
?>