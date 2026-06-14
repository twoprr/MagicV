<?php

class PlanService
{
    public static function activePlans(): array
    {
        $stmt = db()->query("SELECT * FROM plans WHERE is_active = 1 ORDER BY sort_order ASC, price ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function allPlans(): array
    {
        $stmt = db()->query("SELECT * FROM plans ORDER BY sort_order ASC, price ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare("SELECT * FROM plans WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function save(array $data, ?int $id = null): int
    {
        $pdo = db();
        if ($id) {
            $stmt = $pdo->prepare("UPDATE plans SET title=?, days=?, price=?, old_price=?, badge=?, description=?, is_active=?, is_popular=?, sort_order=?, updated_at=NOW() WHERE id=?");
            $stmt->execute([
                trim($data['title'] ?? ''),
                (int)($data['days'] ?? 0),
                (int)($data['price'] ?? 0),
                ($data['old_price'] ?? '') === '' ? null : (int)$data['old_price'],
                trim($data['badge'] ?? ''),
                trim($data['description'] ?? ''),
                !empty($data['is_active']) ? 1 : 0,
                !empty($data['is_popular']) ? 1 : 0,
                (int)($data['sort_order'] ?? 100),
                $id,
            ]);
            return $id;
        }
        $stmt = $pdo->prepare("INSERT INTO plans (title, days, price, old_price, badge, description, is_active, is_popular, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            trim($data['title'] ?? ''),
            (int)($data['days'] ?? 0),
            (int)($data['price'] ?? 0),
            ($data['old_price'] ?? '') === '' ? null : (int)$data['old_price'],
            trim($data['badge'] ?? ''),
            trim($data['description'] ?? ''),
            !empty($data['is_active']) ? 1 : 0,
            !empty($data['is_popular']) ? 1 : 0,
            (int)($data['sort_order'] ?? 100),
        ]);
        return (int)$pdo->lastInsertId();
    }
}
