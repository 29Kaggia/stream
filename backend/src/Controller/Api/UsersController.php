<?php
declare(strict_types=1);

namespace App\Controller\Api;

class UsersController extends ApiController
{
    public function index()
    {
        $this->request->allowMethod(['get']);
        if (!$this->requireAdmin()) return $this->json(['message' => 'Admin access is required.'], 403);
        $users = $this->fetchTable('Users')->find()->select(['id', 'username', 'email', 'role', 'status', 'created'])->orderByDesc('created')->limit(100)->all();
        return $this->json(['users' => $users]);
    }

    public function status(int $id)
    {
        $this->request->allowMethod(['patch']);
        $admin = $this->requireAdmin();
        if (!$admin) return $this->json(['message' => 'Admin access is required.'], 403);
        $status = (string)$this->request->getData('status');
        if (!in_array($status, ['active', 'banned'], true)) return $this->json(['message' => 'Status must be active or banned.'], 422);
        $user = $this->fetchTable('Users')->find()->where(['id' => $id])->first();
        if (!$user) return $this->json(['message' => 'User not found.'], 404);
        if ($user->id === $admin->id) return $this->json(['message' => 'You cannot change your own account status.'], 422);
        $user->status = $status;
        $this->fetchTable('Users')->saveOrFail($user);
        return $this->json(['user' => ['id' => $user->id, 'status' => $user->status]]);
    }

    public function role(int $id)
    {
        $this->request->allowMethod(['patch']);
        if (!$this->requireAdmin()) {
            return $this->json(['message' => 'Admin access is required.'], 403);
        }
        $role = (string)$this->request->getData('role');
        if (!in_array($role, ['viewer', 'streamer', 'admin'], true)) {
            return $this->json(['message' => 'Role must be viewer, streamer, or admin.'], 422);
        }
        $users = $this->fetchTable('Users');
        $user = $users->find()->where(['id' => $id])->first();
        if (!$user) return $this->json(['message' => 'User not found.'], 404);
        $user->role = $role;
        $users->saveOrFail($user);
        return $this->json(['user' => ['id' => $user->id, 'username' => $user->username, 'role' => $user->role]]);
    }
}
