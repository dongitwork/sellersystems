<?php
namespace App\Core;

use Slim\App;
use DI\Container;
use App\Config\Modules;

class Application {
    private App $app;
    private Container $container;
    
    public function __construct(App $app, Container $container) {
        $this->app = $app;
        $this->container = $container;
    }
    
    public function loadModules(): void {
        $modules = Modules::getActive();
        
        foreach ($modules as $module) {
            $routeFile = __DIR__ . "/../../modules/{$module}/Routes/routes.php";
            if (file_exists($routeFile)) {
                require $routeFile;
                $routeClass = "\\Modules\\{$module}\\Routes\\Routes";
                if (class_exists($routeClass)) {
                    $routes = new $routeClass($this->app, $this->container);
                    $routes->register();
                }
            }
        }
    }
}