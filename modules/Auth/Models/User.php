<?php
namespace Modules\Auth\Models;

use App\Core\BaseModel;

class User extends BaseModel {
    protected string $table = 'users';
    protected array $fillable = [
        'name', 'username', 'email', 'password', 'api_token', 
        'remember_token', 'status', 'created_at', 'updated_at'
    ];
    protected array $hidden = ['password', 'remember_token'];
    
    public function findByUsername(string $username) {
        $result = $this->db->get($this->table, '*', ['username' => $username]);
        return $result ?: null;
    }
    
    public function findByEmail(string $email) {
        $result = $this->db->get($this->table, '*', ['email' => $email]);
        return $result ?: null;
    }
    
    public function getUserRoles(int $userId): array {
        $roleIds = $this->db->select('user_roles', 'role_id', ['user_id' => $userId]);
        if (empty($roleIds)) return [];
        
        return $this->db->select('roles', 'slug', ['id' => $roleIds]);
    }
    
    public function assignRole(int $userId, int $roleId): void {
        $this->db->insert('user_roles', [
            'user_id' => $userId,
            'role_id' => $roleId
        ]);
    }
    
    public function removeRole(int $userId, int $roleId): void {
        $this->db->delete('user_roles', [
            'user_id' => $userId,
            'role_id' => $roleId
        ]);
    }

    /**
     * Generate a new API token for user
     */
    public function generateApiToken(int $userId): ?string {
        $apiToken = bin2hex(random_bytes(40));
        $updated = $this->update($userId, ['api_token' => $apiToken]);
        return $updated ? $apiToken : null;
    }

    /**
     * Revoke API token for user
     */
    public function revokeApiToken(int $userId): bool {
        return $this->update($userId, ['api_token' => null]);
    }

    /**
     * Find user by API token
     */
    public function findByApiToken(string $token) {
        $result = $this->db->get($this->table, '*', [
            'api_token' => $token,
            'status' => 'active'
        ]);
        return $result ?: null;
    }

    /**
     * Find user by remember token
     */
    public function findByRememberToken(string $token) {
        $result = $this->db->get($this->table, '*', [
            'remember_token' => $token,
            'status' => 'active'
        ]);
        return $result ?: null;
    }
}