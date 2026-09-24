<?php
declare(strict_types=1);

namespace App\Controller\Api;

class CategoriesController extends ApiController
{
    public function index()
    {
        $this->request->allowMethod(['get']);
        $categories = $this->fetchTable('Categories')->find()
            ->select(['id', 'name', 'slug', 'description'])
            ->orderByAsc('name')
            ->all();

        return $this->json(['categories' => $categories]);
    }

    public function manage()
    {
        $this->request->allowMethod(['post']);
        if (!$this->requireAdmin()) return $this->json(['message' => 'Admin access is required.'], 403);
        $categories = $this->fetchTable('Categories');
        $category = $categories->newEntity(['name' => trim((string)$this->request->getData('name')), 'slug' => trim((string)$this->request->getData('slug')), 'description' => trim((string)$this->request->getData('description'))]);
        if (!$categories->save($category)) return $this->json(['message' => 'Unable to create category.', 'errors' => $category->getErrors()], 422);
        return $this->json(['category' => $category], 201);
    }
}
