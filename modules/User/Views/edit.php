<div class="container">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h4><i class="bi bi-pencil"></i> Edit User</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="/users/<?= $user['id'] ?>">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" 
                                       class="form-control" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" value="<?= htmlspecialchars($user['username']) ?>" 
                                       class="form-control" readonly>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" 
                                       class="form-control" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="password" class="form-control">
                                <small class="text-muted">Leave empty to keep current password</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active" <?= $user['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= $user['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                    <option value="banned" <?= $user['status'] == 'banned' ? 'selected' : '' ?>>Banned</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Roles</label>
                                <div>
                                    <?php foreach($roles as $role): ?>
                                    <div class="form-check">
                                        <input type="checkbox" name="roles[]" value="<?= $role['id'] ?>" 
                                               class="form-check-input" id="role_<?= $role['id'] ?>"
                                               <?= in_array($role['id'], $userRoles) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="role_<?= $role['id'] ?>">
                                            <?= $role['name'] ?>
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">API Token</label>
                            <input type="text" value="<?= $user['api_token'] ?>" class="form-control" readonly>
                        </div>
                        
                        <div class="text-end">
                            <a href="/users" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Update User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>