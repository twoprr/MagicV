<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/Services/VpnLinks.php';
require __DIR__ . '/../app/Services/XrayProvision.php';

if (!function_exists('server_profiles')) {
    function server_profiles(array $server): array {
        if (isset($server['profiles']) && is_array($server['profiles'])) {
            $out = [];
            foreach ($server['profiles'] as $profile) {
                if (!is_array($profile)) continue;
                $out[] = array_merge($server, $profile);
            }
            return $out ?: [$server];
        }
        return [$server];
    }
}

$dryRun = in_array('--dry-run', $argv, true);
$restart = !in_array('--no-restart', $argv, true);

function patch_xray_api_on_server(array $server, bool $dryRun, bool $restart): array {
    $path = (string)($server['xray_config_path'] ?? '/usr/local/etc/xray/config.json');
    $api = xray_api_address($server);
    $apiTag = (string)($server['xray_api_tag'] ?? 'api');
    $restartCmd = trim((string)($server['xray_reload_cmd'] ?? 'sudo systemctl restart xray'));
    if ($restartCmd === '' || $restartCmd === 'true') $restartCmd = 'sudo systemctl restart xray';

    $py = "python3 - <<'PY'\n" .
        "import json, shutil, datetime, sys\n" .
        'path=' . var_export($path, true) . "\n" .
        'api_listen=' . var_export($api, true) . "\n" .
        'api_tag=' . var_export($apiTag, true) . "\n" .
        'dry=' . ($dryRun ? 'True' : 'False') . "\n" .
        "with open(path,'r',encoding='utf-8') as f: cfg=json.load(f)\n" .
        "changed=False\n" .
        "api=cfg.setdefault('api', {})\n" .
        "if api.get('tag') != api_tag: api['tag']=api_tag; changed=True\n" .
        "if api.get('listen') != api_listen: api['listen']=api_listen; changed=True\n" .
        "services=set(api.get('services') or [])\n" .
        "need={'HandlerService','LoggerService','StatsService','ReflectionService'}\n" .
        "if not need.issubset(services): api['services']=sorted(services|need); changed=True\n" .
        "if changed:\n" .
        "    backup=path+'.bak-api-'+datetime.datetime.utcnow().strftime('%Y%m%d%H%M%S')\n" .
        "    print('WILL_CHANGE backup='+backup if dry else 'CHANGED backup='+backup)\n" .
        "    if not dry:\n" .
        "        shutil.copy2(path, backup)\n" .
        "        with open(path,'w',encoding='utf-8') as f: json.dump(cfg,f,ensure_ascii=False,indent=2)\n" .
        "else:\n" .
        "    print('NO_CHANGE')\n" .
        "PY";

    $out = ssh_exec_server($server, $py);
    if ($dryRun) return ['ok' => true, 'output' => $out];

    try {
        ssh_exec_server($server, 'xray test -config ' . escapeshellarg($path));
    } catch (Throwable $e) {
        return ['ok' => false, 'output' => $out, 'error' => 'xray test failed: ' . $e->getMessage()];
    }

    if ($restart) {
        try {
            ssh_exec_server($server, $restartCmd);
            $out .= "\nRESTARTED";
        } catch (Throwable $e) {
            return ['ok' => false, 'output' => $out, 'error' => 'restart failed: ' . $e->getMessage()];
        }
    }

    try {
        $apiTest = ssh_exec_server($server, xray_binary($server) . ' api help adu >/dev/null 2>&1 && echo API_CLI_OK || echo API_CLI_MISSING; ss -lnt | grep -q ' . escapeshellarg(':' . substr($api, strrpos($api, ':') + 1)) . ' && echo API_LISTEN_OK || echo API_LISTEN_MISSING');
        $out .= "\n" . $apiTest;
    } catch (Throwable $e) {
        $out .= "\nAPI_TEST_ERROR: " . $e->getMessage();
    }

    return ['ok' => true, 'output' => $out];
}

foreach (servers_config() as $server) {
    foreach (server_profiles($server) as $profile) {
        $name = $profile['id'] ?? $profile['title'] ?? $profile['ssh_host'] ?? 'server';
        echo "=== {$name} ===\n";
        try {
            $res = patch_xray_api_on_server($profile, $dryRun, $restart);
            echo ($res['ok'] ? 'OK' : 'ERROR') . "\n";
            echo trim((string)$res['output']) . "\n";
            if (!empty($res['error'])) echo "ERROR: {$res['error']}\n";
        } catch (Throwable $e) {
            echo "ERROR: " . $e->getMessage() . "\n";
        }
        echo "\n";
    }
}
