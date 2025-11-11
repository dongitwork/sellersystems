<?php

namespace Modules\Api\Controllers;

use App\Core\BaseController;
use Modules\Auth\Models\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ApiAuthController extends BaseController
{
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }

    /**
     * API Login - POST /api/login
     * Returns API token on success
     */
    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = $request->getParsedBody();
        $usernameOrEmail = $data['username'] ?? $data['email'] ?? '';
        $password = $data['password'] ?? '';

        // Validation
        if (empty($usernameOrEmail) || empty($password)) {
            return $this->jsonResponse($response, [
                'success' => false,
                'error' => 'Username/email and password are required'
            ], 400);
        }

        // Find user by username or email
        $user = $this->userModel->findByUsername($usernameOrEmail);
        if (!$user) {
            $user = $this->userModel->findByEmail($usernameOrEmail);
        }

        // Check if user exists
        if (!$user) {
            return $this->jsonResponse($response, [
                'success' => false,
                'error' => 'Invalid credentials'
            ], 401);
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            return $this->jsonResponse($response, [
                'success' => false,
                'error' => 'Invalid credentials'
            ], 401);
        }

        // Check user status
        if ($user['status'] === 'banned') {
            return $this->jsonResponse($response, [
                'success' => false,
                'error' => 'Your account has been banned'
            ], 403);
        }

        if ($user['status'] === 'inactive') {
            return $this->jsonResponse($response, [
                'success' => false,
                'error' => 'Your account is inactive. Please contact administrator'
            ], 403);
        }

        // Generate new API token if not exists
        if (empty($user['api_token'])) {
            $apiToken = bin2hex(random_bytes(40));
            $this->userModel->update($user['id'], ['api_token' => $apiToken]);
            $user['api_token'] = $apiToken;
        }

        // Get user roles
        $roles = $this->userModel->getUserRoles($user['id']);

        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $user['api_token'],
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'status' => $user['status'],
                    'roles' => array_column($roles, 'slug')
                ]
            ]
        ]);
    }

    /**
     * API Register - POST /api/register
     * Creates new user and returns token
     */
    public function register(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = $request->getParsedBody();

        // Validation
        $errors = [];

        if (empty($data['name'])) {
            $errors['name'] = 'Name is required';
        }

        if (empty($data['username'])) {
            $errors['username'] = 'Username is required';
        } elseif ($this->userModel->findByUsername($data['username'])) {
            $errors['username'] = 'Username already exists';
        }

        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        } elseif ($this->userModel->findByEmail($data['email'])) {
            $errors['email'] = 'Email already exists';
        }

        if (empty($data['password'])) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($data['password']) < 6) {
            $errors['password'] = 'Password must be at least 6 characters';
        }

        if (!empty($errors)) {
            return $this->jsonResponse($response, [
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $errors
            ], 422);
        }

        // Generate API token
        $apiToken = bin2hex(random_bytes(40));

        // Create user
        $userId = $this->userModel->create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
            'api_token' => $apiToken,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if (!$userId) {
            return $this->jsonResponse($response, [
                'success' => false,
                'error' => 'Failed to create user'
            ], 500);
        }

        // Assign default customer role
        $customerRole = $this->userModel->db()->get('roles', 'id', ['slug' => 'customer']);
        if ($customerRole) {
            $this->userModel->assignRole($userId, $customerRole);
        }

        // Get created user
        $user = $this->userModel->find($userId);
        $roles = $this->userModel->getUserRoles($userId);

        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Registration successful',
            'data' => [
                'token' => $apiToken,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'status' => $user['status'],
                    'roles' => array_column($roles, 'slug')
                ]
            ]
        ], 201);
    }

    /**
     * API Logout - POST /api/logout
     * Revokes the current API token
     */
    public function logout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $userId = $request->getAttribute('user_id');

        if (!$userId) {
            return $this->jsonResponse($response, [
                'success' => false,
                'error' => 'Unauthorized'
            ], 401);
        }

        // Revoke API token
        $this->userModel->update($userId, ['api_token' => null]);

        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Helper method to return JSON response
     */
    private function jsonResponse(ResponseInterface $response, array $data, int $status = 200): ResponseInterface
    {
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
