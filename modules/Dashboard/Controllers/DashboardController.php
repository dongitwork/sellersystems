<?php
namespace Modules\Dashboard\Controllers;

use App\Core\BaseController;
use Modules\Auth\Models\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use DI\Container;

class DashboardController extends BaseController {
    private User $userModel;
    
    public function __construct(Container $container) {
        parent::__construct($container);
        $this->userModel = new User();
    }
    
    public function index(Request $request, Response $response): Response {
        $stats = [
            'total' => $this->db->count('users'),
            'active' => $this->db->count('users', ['status' => 'active']),
            'inactive' => $this->db->count('users', ['status' => 'inactive']),
            'banned' => $this->db->count('users', ['status' => 'banned'])
        ];
        
        $recentUsers = $this->db->select('users', [
            'id', 'name', 'username', 'email', 'status', 'created_at'
        ], [
            'ORDER' => ['created_at' => 'DESC'],
            'LIMIT' => 5
        ]);
        
        return $this->render($response, 'Dashboard:index', [
            'stats' => $stats,
            'recentUsers' => $recentUsers
        ]);
    }
}