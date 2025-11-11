<?php
namespace Modules\User\Controllers;

use App\Core\BaseController;
use Modules\Auth\Models\User;
use Modules\User\Models\Role;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use DI\Container;

class UserController extends BaseController {
    private User $userModel;
    private Role $roleModel;
    
    public function __construct(Container $container) {
        parent::__construct($container);
        $this->userModel = new User();
        $this->roleModel = new Role();
    }
    
    public function index(Request $request, Response $response): Response {
        $users = $this->db->select('users', [
            'users.id',
            'users.name',
            'users.username',
            'users.email',
            'users.status',
            'users.created_at'
        ]);
        
        // Get roles for each user
        foreach ($users as &$user) {
            $user['roles'] = $this->roleModel->getUserRoles($user['id']);
        }
        
        return $this->render($response, 'User:index', ['users' => $users]);
    }
    
    public function create(Request $request, Response $response): Response {
        $roles = $this->roleModel->all();
        return $this->render($response, 'User:create', ['roles' => $roles]);
    }
    
    public function store(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        
        // Validate
        if (empty($data['name']) || empty($data['username']) || empty($data['email'])) {
            $this->flash('danger', 'Required fields are missing');
            return $this->redirect($response, '/users/create');
        }
        
        // Check existing
        if ($this->userModel->findByUsername($data['username'])) {
            $this->flash('danger', 'Username already exists');
            return $this->redirect($response, '/users/create');
        }
        
        if ($this->userModel->findByEmail($data['email'])) {
            $this->flash('danger', 'Email already exists');
            return $this->redirect($response, '/users/create');
        }
        
        // Create user
        $password = $data['password'] ?? bin2hex(random_bytes(8));
        $userId = $this->userModel->create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'api_token' => bin2hex(random_bytes(32)),
            'status' => $data['status'] ?? 'active',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Assign roles
        if (!empty($data['roles'])) {
            foreach ($data['roles'] as $roleId) {
                $this->userModel->assignRole($userId, $roleId);
            }
        }
        
        $this->flash('success', 'User created successfully');
        return $this->redirect($response, '/users');
    }
    
    public function edit(Request $request, Response $response, array $args): Response {
        $id = $args['id'];
        $user = $this->userModel->find($id);
        
        if (!$user) {
            $this->flash('danger', 'User not found');
            return $this->redirect($response, '/users');
        }
        
        $roles = $this->roleModel->all();
        $userRoles = $this->roleModel->getUserRoleIds($id);
        
        return $this->render($response, 'User:edit', [
            'user' => $user,
            'roles' => $roles,
            'userRoles' => $userRoles
        ]);
    }
    
    public function update(Request $request, Response $response, array $args): Response {
        $id = $args['id'];
        $data = $request->getParsedBody();
        
        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'status' => $data['status'],
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if (!empty($data['password'])) {
            $updateData['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        
        $this->userModel->update($id, $updateData);
        
        // Update roles
        $this->db->delete('user_roles', ['user_id' => $id]);
        if (!empty($data['roles'])) {
            foreach ($data['roles'] as $roleId) {
                $this->userModel->assignRole($id, $roleId);
            }
        }
        
        $this->flash('success', 'User updated successfully');
        return $this->redirect($response, '/users');
    }
    
    public function delete(Request $request, Response $response, array $args): Response {
        $id = $args['id'];
        
        if ($id == $_SESSION['user_id']) {
            $this->flash('danger', 'You cannot delete yourself');
            return $this->redirect($response, '/users');
        }
        
        $this->db->delete('user_roles', ['user_id' => $id]);
        $this->userModel->delete($id);
        
        $this->flash('success', 'User deleted successfully');
        return $this->redirect($response, '/users');
    }
}