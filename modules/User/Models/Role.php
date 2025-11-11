<?php
namespace Modules\User\Models;

use App\Core\BaseModel;

class Role extends BaseModel {
    protected string $table = 'roles';
    protected array $fillable = ['name', 'slug', 'description'];
    
    public function getUserRoles(int $userId): array {
        return $this->db->select('roles', [
            '[>]user_roles' => ['id' => 'role_id']
        ], [
            'roles.name'
        ], [
            'user_roles.user_id' => $userId
        ]);
    }
    
    public function getUserRoleIds(int $userId): array {
        return $this->db->select('user_roles', 'role_id', ['user_id' => $userId]);
    }
}