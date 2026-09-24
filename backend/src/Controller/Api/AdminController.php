<?php
declare(strict_types=1);

namespace App\Controller\Api;

class AdminController extends ApiController
{
    public function overview()
    {
        $this->request->allowMethod(['get']);
        if (!$this->requireAdmin()) return $this->json(['message' => 'Admin access is required.'], 403);
        return $this->json(['stats' => [
            'users' => $this->fetchTable('Users')->find()->count(),
            'streamers' => $this->fetchTable('Users')->find()->where(['role IN' => ['streamer', 'admin']])->count(),
            'live_streams' => $this->fetchTable('Streams')->find()->where(['status' => 'live'])->count(),
            'open_reports' => $this->fetchTable('Reports')->find()->where(['status' => 'open'])->count(),
        ]]);
    }
}
