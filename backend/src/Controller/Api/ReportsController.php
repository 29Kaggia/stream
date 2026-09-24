<?php
declare(strict_types=1);

namespace App\Controller\Api;

class ReportsController extends ApiController
{
    public function index()
    {
        $this->request->allowMethod(['get']);
        if (!$this->requireAdmin()) return $this->json(['message' => 'Admin access is required.'], 403);
        $reports = $this->fetchTable('Reports')->find()->contain(['Reporter', 'ReportedUsers', 'Streams'])->orderByDesc('Reports.created')->limit(100)->all();
        return $this->json(['reports' => $reports]);
    }

    public function resolve(int $id)
    {
        $this->request->allowMethod(['patch']);
        if (!$this->requireAdmin()) return $this->json(['message' => 'Admin access is required.'], 403);
        $report = $this->fetchTable('Reports')->find()->where(['id' => $id])->first();
        if (!$report) return $this->json(['message' => 'Report not found.'], 404);
        $status = (string)$this->request->getData('status');
        if (!in_array($status, ['open', 'reviewed', 'resolved'], true)) return $this->json(['message' => 'Invalid report status.'], 422);
        $report->status = $status;
        $this->fetchTable('Reports')->saveOrFail($report);
        return $this->json(['report' => $report]);
    }
}
