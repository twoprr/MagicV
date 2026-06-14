<?php
declare(strict_types=1);

function server_profiles(array $server): array {
    return !empty($server['profiles']) ? $server['profiles'] : [$server];
}

function build_server_remark(array $server, string $brand = 'MagicVPN'): string {
    $map = [
        'de' => '🇩🇪 Germany', 'de_443' => '🇩🇪 Germany Main', 'de_2053' => '🇩🇪 Germany Stable', 'de_8443' => '🇩🇪 Germany Backup',
        'fr' => '🇫🇷 France', 'fr_443' => '🇫🇷 France',
    ];
    $id = $server['id'] ?? '';
    $pretty = $map[$id] ?? ($server['title'] ?? $id);
    return $pretty . ' | ' . $brand;
}

function build_vless_link(array $server, string $uuid, string $remark): string {
    $base = 'vless://' . $uuid . '@' . $server['domain'] . ':' . $server['port'];
    $params = [
        'type' => 'tcp',
        'encryption' => 'none',
        'security' => $server['transport'] === 'tls' ? 'tls' : 'reality',
    ];
    if (($server['transport'] ?? 'reality') === 'tls') {
        $params['sni'] = $server['sni'] ?? $server['domain'];
        $params['alpn'] = 'h2,http/1.1';
    } else {
        $params['sni'] = $server['reality_server_name'] ?? '';
        $params['fp'] = $server['reality_fp'] ?? 'chrome';
        $params['pbk'] = $server['reality_public_key'] ?? '';
        $params['sid'] = $server['reality_short_id'] ?? '';
        if (!empty($server['reality_spx'])) $params['spx'] = $server['reality_spx'];
        if (!empty($server['reality_flow'])) $params['flow'] = $server['reality_flow'];
    }
    return $base . '?' . http_build_query(array_filter($params, fn($v) => $v !== '' && $v !== null)) . '#' . rawurlencode($remark);
}

function build_all_vless_links(string $uuid): array {
    $links = [];
    foreach (servers_config() as $server) {
        foreach (server_profiles($server) as $profile) {
            $remark = build_server_remark($profile, app_config('brand'));
            $links[] = ['server' => $server['id'], 'remark' => $remark, 'link' => build_vless_link($profile, $uuid, $remark)];
        }
    }
    return $links;
}
