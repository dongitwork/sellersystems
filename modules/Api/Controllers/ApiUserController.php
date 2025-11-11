<?php

namespace Modules\Api\Controllers;

use App\Core\BaseController;
use Modules\Auth\Models\User;
use Modules\User\Models\Role;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ApiUserController extends BaseController
{
    private User $userModel;
    private Role $roleModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        $this->roleModel = new Role();
    }

    /**
     * List all users - GET /api/users
     * Requires admin role
     */
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // Check if user has admin role
        if (!$this->hasRole($request, 'admin')) {
            return $this->jsonResponse($response, [
                'success' => false,
                'error' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        $queryParams = $request->getQueryParams();
        $page = (int)($queryParams['page'] ?? 1);
        $perPage = (int)($queryParams['per_page'] ?? 20);
        $search = $queryParams['q'] ?? '';
        $status = $queryParams['status'] ?? '';
        $role = $queryParams['role'] ?? '';

        // Build where conditions
        $where = [];

        if (!empty($search)) {
            $where['OR'] = [
                'name[~]' => $search,
                'username[~]' => $search,
                'email[~]' => $search
            ];
        }

        if (!empty($status) && in_array($status, ['active', 'inactive', 'banned'])) {
            $where['status'] = $status;
        }

        // Get total count
        $total = $this->userModel->db()->count('users', $where ?: '*');

        // Get paginated users
        $offset = ($page - 1) * $perPage;
        $users = $this->userModel->db()->select('users', [
            'id',
            'name',
            'username',
            'email',
            'status',
            'created_at',
            'updated_at'
        ], array_merge($where, [
            'LIMIT' => [$offset, $perPage],
            'ORDER' => ['created_at' => 'DESC']
        ]));

        // Get roles for each user
        foreach ($users as &$user) {
            $roles = $this->userModel->getUserRoles($user['id']);
            $user['roles'] = array_column($roles, 'slug');
        }

        // Filter by role if specified
        if (!empty($role)) {
            $users = array_filter($users, function($user) use ($role) {
                return in_array($role, $user['roles']);
            });
            $users = array_values($users); // Re-index array
        }

        return $this->jsonResponse($response, [
            'success' => true,
            'data' => [
                'users' => $users,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'last_page' => ceil($total / $perPage),
                    'from' => $offset + 1,
                    'to' => min($offset + $perPage, $total)
                ]
            ]
        ]);
    }

    /**
     * Get single user - GET /api/users/{id}
     * Requires admin role
     */
    public function show(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        // Check if user has admin role
        if (!$this->hasRole($request, 'admin')) {
            return $this->jsonResponse($response, [
                'success' => false,
                'error' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        $userId = (int)$args['id'];
        $user = $this->userModel->find($userId);

        if (!$user) {
            return $this->jsonResponse($response, [
                'success' => false,
                'error' => 'User not found'
            ], 404);
        }

        // Get user roles
        $roles = $this->userModel->getUserRoles($userId);

        // Remove sensitive data
        unset($user['password'], $user['remember_token']);

        $user['roles'] = array_column($roles, 'slug');

        return $this->jsonResponse($response, [
            'success' => true,
            'data' => ['user' => $user]
        ]);
    }

    /**
     * Create new user - POST /api/users
     * Requires admin role
     */
    public function store(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // Check if user has admin role
        if (!$this->hasRole($request, 'admin')) {
            return $this->jsonResponse($response, [
                'success' => false,
                'error' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        $data = $request->getParsedBody();

        // Validation
        $errors = $this->validateUserData($data);

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
            'status' => $data['status'] ?? 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if (!$userId) {
            return $this->jsonResponse($response, [
                'success' => false,
                'error' => 'Failed to create user'
            ], 500);
        }

        // Assign roles
        if (!empty($data['roles']) && is_array($data['roles'])) {
            foreach ($data['roles'] as $roleSlug) {
                $role = $this->userModel->db()->get('roles', 'id', ['slug' => $roleSlug]);
                if ($role) {
                    $this->userModel->assignRole($userId, $role);
                }
            }
        } else {
            // Assign default customer role
            $customerRole = $this->userModel->db()->get('roles', 'id', ['slug' => 'customer']);
            if ($customerRole) {
                $this->userModel->assignRole($userId, $customerRole);
            }
        }

        // Get created user
        $user = $this->userModel->find($userId);
        $roles = $this->userModel->getUserRoles($userId);

        unset($user['password'], $user['remember_token']);
        $user['roles'] = array_column($roles, 'slug');

        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'User created successfully',
            'data' => ['user' => $user]
        ], 201);
    }

    /**
     * Update user - PUT /api/users/{id}
     * Requires admin role
     */
    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        // Check if user has admin role
        if (!$this->hasRole($request, 'admin')) {
            return $this->jsonResponse($response, [
                'success' => false,
                'error' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        $userId = (int)$args['id'];
        $user = $this->userModel->find($userId);

        if (!$user) {
            return $this->jsonResponse($response, [
                'success' => false,
                'error' => 'User not found'
            ], 404);
        }

        $data = $request->getParsedBody();

        // Validation
        $errors = $this->validateUserData($data, $userId);

        if (!empty($errors)) {
            return $this->jsonResponse($response, [
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $errors
            ], 422);
        }

        // Prepare update data
        $updateData = [
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'status' => $data['status'] ?? $user['status'],
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Update password if provided
        if (!empty($data['password'])) {
            $updateData['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        // Update user
        $updated = $this->userModel->update($userId, $updateData);

        if (!$updated) {
            return $this->jsonResponse($response, [
                'success' => false,
                'error' => 'Failed to update user'
            ], 500);
        }

        // Update roles if provided
        if (isset($data['roles']) && is_array($data['roles'])) {
            // Remove all existing roles
            $this->userModel->db()->delete('user_roles', ['user_id' => $userId]);

            // Assign new roles
            foreach ($data['roles'] as $roleSlug) {
                $role = $this->userModel->db()->get('roles', 'id', ['slug' => $roleSlug]);
                if ($role) {
                    $this->userModel->assignRole($userId, $role);
                }
            }
        }

        // Get updated user
        $user = $this->userModel->find($userId);
        $roles = $this->userModel->getUserRoles($userId);

        unset($user['password'], $user['remember_token']);
        $user['roles'] = array_column($roles, 'slug');

        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'User updated successfully',
            'data' => ['user' => $user]
        ]);
    }

    /**
     * Delete user - DELETE /api/users/{id}
     * Requires admin role
     */
    public function destroy(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        // Check if user has admin role
        if (!$this->hasRole($request, 'admin')) {
            return $this->jsonResponse($response, [
                'success' => false,
                'error' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        $userId = (int)$args['id'];
        $currentUserId = $request->getAttribute('user_id');

        // Prevent self-deletion
        if ($userId === $currentUserId) {
            return $this->jsonResponse($response, [
                'success' => false,
                'error' => 'You cannot delete your own account'
            ], 400);
        }

        $user = $this->userModel->find($userId);

        if (!$user) {
            return $this->jsonResponse($response, [
                'success' => false,
                'error' => 'User not found'
            ], 404);
        }

        // Delete user roles first
        $this->userModel->db()->delete('user_roles', ['user_id' => $userId]);

        // Delete user
        $deleted = $this->userModel->delete($userId);

        if (!$deleted) {
            return $this->jsonResponse($response, [
                'success' => false,
                'error' => 'Failed to delete user'
            ], 500);
        }

        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Validate user data
     */
    private function validateUserData(array $data, ?int $userId = null): array
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors['name'] = 'Name is required';
        }

        if (empty($data['username'])) {
            $errors['username'] = 'Username is required';
        } else {
            $existingUser = $this->userModel->findByUsername($data['username']);
            if ($existingUser && (!$userId || $existingUser['id'] != $userId)) {
                $errors['username'] = 'Username already exists';
            }
        }

        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        } else {
            $existingUser = $this->userModel->findByEmail($data['email']);
            if ($existingUser && (!$userId || $existingUser['id'] != $userId)) {
                $errors['email'] = 'Email already exists';
            }
        }

        if (!$userId && empty($data['password'])) {
            $errors['password'] = 'Password is required';
        } elseif (!empty($data['password']) && strlen($data['password']) < 6) {
            $errors['password'] = 'Password must be at least 6 characters';
        }

        if (isset($data['status']) && !in_array($data['status'], ['active', 'inactive', 'banned'])) {
            $errors['status'] = 'Invalid status. Must be: active, inactive, or banned';
        }

        return $errors;
    }

    /**
     * Check if user has required role
     */
    private function hasRole(ServerRequestInterface $request, string $role): bool
    {
        $userRoles = $request->getAttribute('user_roles', []);
        return in_array($role, $userRoles);
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
