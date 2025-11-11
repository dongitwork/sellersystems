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
}