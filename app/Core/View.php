<?php
namespace App\Core;

class View {
    private string $layoutPath = __DIR__ . '/../../resources/views/layouts/';
    private string $componentPath = __DIR__ . '/../../resources/views/components/';
    
    public function render(string $template, array $data = []): string {
        extract($data);
        
        // Parse module:view format
        if (strpos($template, ':') !== false) {
            list($module, $view) = explode(':', $template);
            $viewPath = __DIR__ . "/../../modules/{$module}/Views/{$view}.php";
        } else {
            $viewPath = __DIR__ . "/../../resources/views/{$template}.php";
        }
        
        // Determine layout
        $layout = $data['layout'] ?? (isAuth() ? 'app' : 'guest');
        $layoutFile = $this->layoutPath . $layout . '.php';
        
        // Capture view content
        ob_start();
        if (file_exists($viewPath)) {
            include $viewPath;
        }
        $content = ob_get_clean();
        
        // Render with layout
        ob_start();
        include $layoutFile;
        return ob_get_clean();
    }
}