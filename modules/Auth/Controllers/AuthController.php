<?php
namespace Modules\Auth\Controllers;

use App\Core\BaseController;
use Modules\Auth\Models\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use DI\Container;

class AuthController extends BaseController {
    private User $userModel;
    
    public function __construct(Container $container) {
        parent::__construct($container);
        $this->userModel = new User();
    }
    
    public function loginForm(Request $request, Response $response): Response {
        return $this->render($response, 'Auth:login');
    }
    
    public function login(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';
        $remember = isset($data['remember']);
        
        $user = $this->userModel->findByUsername($username);
        
        if (!$user || !password_verify($password, $user['password'])) {
            $this->flash('danger', 'Invalid username or password');
            return $this->redirect($response, '/login');
        }
        
        if ($user['status'] === 'banned') {
            $this->flash('danger', 'Your account has been banned');
            return $this->redirect($response, '/login');
        }
        
        if ($user['status'] === 'inactive') {
            $this->flash('warning', 'Your account is inactive');
            return $this->redirect($response, '/login');
        }
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user'] = $this->userModel->hideAttributes($user);
        $_SESSION['user_roles'] = $this->userModel->getUserRoles($user['id']);
        
        // Remember token
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $this->userModel->update($user['id'], ['remember_token' => $token]);
            setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/');
        }
        
        $this->flash('success', 'Welcome back, ' . $user['name'] . '!');
        return $this->redirect($response, '/dashboard');
    }
    
    public function registerForm(Request $request, Response $response): Response {
        return $this->render($response, 'Auth:register');
    }
    
    public function register(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        
        // Validate
        if (empty($data['name']) || empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            $this->flash('danger', 'All fields are required');
            return $this->redirect($response, '/register');
        }
        
        if ($data['password'] !== $data['password_confirmation']) {
            $this->flash('danger', 'Passwords do not match');
            return $this->redirect($response, '/register');
        }
        
        // Check existing
        if ($this->userModel->findByUsername($data['username'])) {
            $this->flash('danger', 'Username already exists');
            return $this->redirect($response, '/register');
        }
        
        if ($this->userModel->findByEmail($data['email'])) {
            $this->flash('danger', 'Email already exists');
            return $this->redirect($response, '/register');
        }
        
        // Create user
        $userId = $this->userModel->create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
            'api_token' => bin2hex(random_bytes(32)),
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Assign default role
        $this->userModel->assignRole($userId, 3); // customer role
        
        $this->flash('success', 'Registration successful! Please login.');
        return $this->redirect($response, '/login');
    }
    
    public function logout(Request $request, Response $response): Response {
        session_destroy();
        setcookie('remember_token', '', time() - 3600, '/');
        return $this->redirect($response, '/login');
    }
}