<?php

class SubscriptionService
{
    public static function activateOrExtend(int $userId, int $days): array
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE user_id=? AND server_id='global' ORDER BY expires_at DESC LIMIT 1");
        $stmt->execute([$userId]);
        $sub = $stmt->fetch(PDO::FETCH_ASSOC);
        $now = new DateTimeImmutable('now');
        if ($sub) {
            $base = new DateTimeImmutable($sub['expires_at']);
            if ($base < $now) $base = $now;
            $expires = $base->modify('+' . $days . ' days')->format('Y-m-d H:i:s');
            $pdo->prepare("UPDATE subscriptions SET active=1, expires_at=?, reminded_3=0, reminded_1=0 WHERE id=?")->execute([$expires, $sub['id']]);
            $sub['expires_at'] = $expires;
            $sub['active'] = 1;
            return $sub;
        }
        $uuid = self::uuid4();
        $expires = $now->modify('+' . $days . ' days')->format('Y-m-d H:i:s');
        $pdo->prepare("INSERT INTO subscriptions (user_id, server_id, uuid, active, expires_at, created_at) VALUES (?, 'global', ?, 1, ?, NOW())")->execute([$userId, $uuid, $expires]);
        return ['id' => (int)$pdo->lastInsertId(), 'user_id' => $userId, 'server_id' => 'global', 'uuid' => $uuid, 'active' => 1, 'expires_at' => $expires];
    }

    private static function uuid4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
