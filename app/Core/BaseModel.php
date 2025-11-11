<?php
namespace App\Core;

use App\Config\Database;
use Medoo\Medoo;

abstract class BaseModel {
    protected Medoo $db;
    protected string $table;
    protected array $fillable = [];
    protected array $hidden = [];
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function find($id) {
        return $this->db->get($this->table, '*', ['id' => $id]);
    }
    
    public function all($columns = '*') {
        return $this->db->select($this->table, $columns);
    }
    
    public function where(array $where, $columns = '*') {
        return $this->db->select($this->table, $columns, $where);
    }
    
    public function create(array $data) {
        $filtered = $this->filterFillable($data);
        $this->db->insert($this->table, $filtered);
        return $this->db->id();
    }
    
    public function update($id, array $data) {
        $filtered = $this->filterFillable($data);
        return $this->db->update($this->table, $filtered, ['id' => $id]);
    }
    
    public function delete($id) {
        return $this->db->delete($this->table, ['id' => $id]);
    }
    
    protected function filterFillable(array $data): array {
        if (empty($this->fillable)) {
            return $data;
        }
        return array_intersect_key($data, array_flip($this->fillable));
    }
    
    public function hideAttributes(array $data): array {
        foreach ($this->hidden as $attr) {
            unset($data[$attr]);
        }
        return $data;
    }
}