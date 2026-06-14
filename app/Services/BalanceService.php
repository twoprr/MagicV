<?php

declare(strict_types=1);

class BalanceService
{
    public static function balance(int $userId): int
    {
        self::ensureRow($userId);
        $stmt = db()->prepare('SELECT balance FROM user_balances WHERE user_id=? LIMIT 1');
        $stmt->execute([$userId]);
        return (int)($stmt->fetchColumn() ?: 0);
    }

    public static function ensureRow(int $userId): void
    {
        db()->prepare('INSERT IGNORE INTO user_balances (user_id,balance) VALUES (?,0)')->execute([$userId]);
    }

    public static function history(int $userId, int $limit = 100): array
    {
        $stmt = db()->prepare('SELECT * FROM balance_transactions WHERE user_id=? ORDER BY id DESC LIMIT ' . max(1, min(300, $limit)));
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function topups(int $userId, int $limit = 50): array
    {
        $stmt = db()->prepare('SELECT * FROM balance_topups WHERE user_id=? ORDER BY id DESC LIMIT ' . max(1, min(200, $limit)));
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function createTopup(int $userId, int $amount): int
    {
        if ($amount < 50) throw new RuntimeException('Минимальная сумма пополнения — 50 ₽.');
        if ($amount > 100000) throw new RuntimeException('Слишком большая сумма пополнения.');
        db()->prepare("INSERT INTO balance_topups (user_id,amount,status) VALUES (?,?,'pending')")->execute([$userId, $amount]);
        return (int)db()->lastInsertId();
    }

    public static function attachReceipt(int $topupId, int $userId, string $receiptPath): void
    {
        $stmt = db()->prepare("UPDATE balance_topups SET receipt_path=?, status='pending' WHERE id=? AND user_id=? AND status IN ('pending','rejected')");
        $stmt->execute([$receiptPath, $topupId, $userId]);
    }

    public static function adminTopups(int $limit = 200): array
    {
        $sql = 'SELECT t.*,u.email,u.name FROM balance_topups t JOIN users u ON u.id=t.user_id ORDER BY FIELD(t.status,\'pending\',\'approved\',\'rejected\'), t.id DESC LIMIT ' . max(1, min(500, $limit));
        return db()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function credit(int $userId, int $amount, string $type, string $comment = '', ?int $adminId = null, ?string $relatedType = null, ?int $relatedId = null): void
    {
        if ($amount <= 0) throw new RuntimeException('Сумма должна быть больше нуля.');
        $pdo = db();
        self::ensureRow($userId);
        $pdo->prepare('UPDATE user_balances SET balance=balance+?, updated_at=UTC_TIMESTAMP() WHERE user_id=?')->execute([$amount, $userId]);
        $pdo->prepare('INSERT INTO balance_transactions (user_id,amount,type,comment,related_type,related_id,admin_user_id) VALUES (?,?,?,?,?,?,?)')
            ->execute([$userId, $amount, $type, $comment, $relatedType, $relatedId, $adminId]);
    }

    public static function debit(int $userId, int $amount, string $type, string $comment = '', ?int $adminId = null, ?string $relatedType = null, ?int $relatedId = null): void
    {
        if ($amount <= 0) throw new RuntimeException('Сумма должна быть больше нуля.');
        $pdo = db();
        self::ensureRow($userId);
        $stmt = $pdo->prepare('SELECT balance FROM user_balances WHERE user_id=? FOR UPDATE');
        $stmt->execute([$userId]);
        $balance = (int)($stmt->fetchColumn() ?: 0);
        if ($balance < $amount) throw new RuntimeException('Недостаточно средств на балансе.');
        $pdo->prepare('UPDATE user_balances SET balance=balance-?, updated_at=UTC_TIMESTAMP() WHERE user_id=?')->execute([$amount, $userId]);
        $pdo->prepare('INSERT INTO balance_transactions (user_id,amount,type,comment,related_type,related_id,admin_user_id) VALUES (?,?,?,?,?,?,?)')
            ->execute([$userId, -$amount, $type, $comment, $relatedType, $relatedId, $adminId]);
    }

    public static function approveTopup(int $topupId, int $adminId): void
    {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('SELECT * FROM balance_topups WHERE id=? FOR UPDATE');
            $stmt->execute([$topupId]);
            $topup = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$topup) throw new RuntimeException('Пополнение не найдено.');
            if ($topup['status'] === 'approved') { $pdo->commit(); return; }
            if ($topup['status'] !== 'pending') throw new RuntimeException('Можно подтверждать только pending-пополнения.');
            self::credit((int)$topup['user_id'], (int)$topup['amount'], 'topup', 'Пополнение баланса подтверждено администратором', $adminId, 'balance_topup', $topupId);
            $pdo->prepare("UPDATE balance_topups SET status='approved', processed_by=?, processed_at=UTC_TIMESTAMP() WHERE id=?")->execute([$adminId, $topupId]);
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function rejectTopup(int $topupId, int $adminId, string $comment = ''): void
    {
        db()->prepare("UPDATE balance_topups SET status='rejected', admin_comment=?, processed_by=?, processed_at=UTC_TIMESTAMP() WHERE id=? AND status='pending'")
            ->execute([$comment, $adminId, $topupId]);
    }

    public static function buyPlan(int $userId, array $plan): array
    {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $price = (int)$plan['price'];
            $days = (int)$plan['days'];
            self::debit($userId, $price, 'purchase', 'Покупка тарифа: ' . (string)$plan['title'], null, 'plan', (int)$plan['id']);

            $stmt = $pdo->prepare("INSERT INTO orders (user_id,days,amount,status,plan_id,payment_provider,created_at) VALUES (?,?,?,'paid',?,'balance',UTC_TIMESTAMP())");
            $stmt->execute([$userId, $days, $price, (int)$plan['id']]);
            $orderId = (int)$pdo->lastInsertId();
            $pdo->prepare('UPDATE balance_transactions SET related_type=?, related_id=? WHERE user_id=? AND related_type=? AND related_id=? ORDER BY id DESC LIMIT 1')
                ->execute(['order', $orderId, $userId, 'plan', (int)$plan['id']]);

            $sub = SubscriptionService::activateOrExtend($userId, $days);
            $uuid = (string)($sub['uuid'] ?? '');
            if ($uuid !== '') {
                create_job('provision_user', ['user_id'=>$userId, 'uuid'=>$uuid, 'xray_email'=>'web_' . $userId, 'order_id'=>$orderId], 5);
            }
            maybe_reward_referrer($userId);
            $pdo->commit();
            return ['order_id'=>$orderId, 'subscription'=>$sub];
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function adminAdjust(int $userId, int $amount, string $comment, int $adminId): void
    {
        if ($amount === 0) throw new RuntimeException('Укажите сумму не равную 0.');
        if ($amount > 0) self::credit($userId, $amount, 'admin_adjust', $comment ?: 'Ручное начисление администратором', $adminId);
        else self::debit($userId, abs($amount), 'admin_adjust', $comment ?: 'Ручное списание администратором', $adminId);
    }
}
