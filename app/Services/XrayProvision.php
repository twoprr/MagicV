<?php
declare(strict_types=1);

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

function ssh_exec_server(array $server, string $command): string {
    $key = (string)($server['ssh_key_path'] ?? '');
    $user = (string)($server['ssh_user'] ?? 'root');
    $host = (string)($server['ssh_host'] ?? '');
    if ($key === '' || $host === '') {
        throw new RuntimeException('SSH key/host is not configured');
    }

    $knownHosts = dirname($key) . '/known_hosts';
    $cmd = sprintf(
        'ssh -i %s -o IdentitiesOnly=yes -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o UserKnownHostsFile=%s -o GlobalKnownHostsFile=/dev/null -o ConnectTimeout=15 %s@%s %s 2>&1',
        escapeshellarg($key),
        escapeshellarg($knownHosts),
        escapeshellarg($user),
        escapeshellarg($host),
        escapeshellarg($command)
    );
    exec($cmd, $out, $code);
    $text = implode("\n", $out);
    if ($code !== 0) throw new RuntimeException($text ?: 'SSH failed: ' . $code);
    return $text;
}

function xray_api_address(array $server): string {
    return (string)($server['xray_api_server'] ?? $server['api_server'] ?? '127.0.0.1:10085');
}

function xray_binary(array $server): string {
    return (string)($server['xray_binary'] ?? 'xray');
}

function xray_provision_mode(array $server): string {
    return strtolower(trim((string)($server['provision_mode'] ?? 'api')));
}

function xray_profile_id(array $server): string {
    return (string)($server['id'] ?? $server['inbound_tag'] ?? $server['ssh_host'] ?? 'xray');
}

/**
 * Writes UUID to config.json as a persistent reserve copy, but DOES NOT restart Xray.
 * Runtime add/remove is done by Xray API below.
 */
function ensure_vless_user_in_config(array $server, string $uuid, string $email): void {
    $tag = $server['inbound_tag'] ?? 'vless-in';
    $flow = trim((string)($server['reality_flow'] ?? ''));
    $path = $server['xray_config_path'];
    $py = "python3 - <<'PYX'\n" .
        "import json\n" .
        'path=' . var_export($path, true) . "\n" .
        'uuid=' . var_export($uuid, true) . "\n" .
        'email=' . var_export($email, true) . "\n" .
        'tag=' . var_export($tag, true) . "\n" .
        'flow=' . var_export($flow, true) . "\n" .
        "with open(path,'r',encoding='utf-8') as f: cfg=json.load(f)\n" .
        "target=None\n" .
        "for inbound in cfg.get('inbounds',[]):\n" .
        "    if inbound.get('protocol')=='vless' and inbound.get('tag')==tag:\n" .
        "        target=inbound; break\n" .
        "if not target: raise SystemExit(f'No vless inbound with tag {tag} found')\n" .
        "clients=target.setdefault('settings',{}).setdefault('clients',[])\n" .
        "existing=next((c for c in clients if c.get('id')==uuid or c.get('email')==email), None)\n" .
        "changed=False\n" .
        "if existing is None:\n" .
        "    c={'id':uuid,'email':email}\n" .
        "    if flow: c['flow']=flow\n" .
        "    clients.append(c); changed=True; print('CONFIG_ADDED')\n" .
        "else:\n" .
        "    if existing.get('id')!=uuid: existing['id']=uuid; changed=True\n" .
        "    if existing.get('email')!=email: existing['email']=email; changed=True\n" .
        "    if flow and existing.get('flow')!=flow: existing['flow']=flow; changed=True\n" .
        "    print('CONFIG_UPDATED' if changed else 'CONFIG_EXISTS')\n" .
        "if changed:\n" .
        "    with open(path,'w',encoding='utf-8') as f: json.dump(cfg,f,ensure_ascii=False,indent=2)\n" .
        "PYX";
    ssh_exec_server($server, $py);
}

function remove_vless_user_from_config(array $server, string $uuid, ?string $email = null): void {
    $tag = $server['inbound_tag'] ?? 'vless-in';
    $path = $server['xray_config_path'];
    $py = "python3 - <<'PYX'\n" .
        "import json\n" .
        'path=' . var_export($path, true) . "\n" .
        'uuid=' . var_export($uuid, true) . "\n" .
        'email=' . var_export($email ?? '', true) . "\n" .
        'tag=' . var_export($tag, true) . "\n" .
        "with open(path,'r',encoding='utf-8') as f: cfg=json.load(f)\n" .
        "changed=False\n" .
        "for inbound in cfg.get('inbounds',[]):\n" .
        "    if inbound.get('protocol')=='vless' and inbound.get('tag')==tag:\n" .
        "        settings=inbound.setdefault('settings',{})\n" .
        "        clients=settings.setdefault('clients',[])\n" .
        "        new_clients=[c for c in clients if c.get('id')!=uuid and (not email or c.get('email')!=email)]\n" .
        "        if len(new_clients)!=len(clients):\n" .
        "            settings['clients']=new_clients; changed=True\n" .
        "if changed:\n" .
        "    with open(path,'w',encoding='utf-8') as f: json.dump(cfg,f,ensure_ascii=False,indent=2)\n" .
        "    print('CONFIG_REMOVED')\n" .
        "else:\n" .
        "    print('CONFIG_NOT_FOUND')\n" .
        "PYX";
    ssh_exec_server($server, $py);
}

function xray_api_add_vless_user(array $server, string $uuid, string $email): void {
    $tag = (string)($server['inbound_tag'] ?? 'vless-in');
    $flow = trim((string)($server['reality_flow'] ?? ''));
    $api = xray_api_address($server);
    $bin = xray_binary($server);

    $payload = [
        'inbounds' => [[
            'tag' => $tag,
            'protocol' => 'vless',
            'settings' => [
                'clients' => [[
                    'id' => $uuid,
                    'email' => $email,
                ]],
            ],
        ]],
    ];
    if ($flow !== '') {
        $payload['inbounds'][0]['settings']['clients'][0]['flow'] = $flow;
    }

    $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $remote = "set -e\n" .
        "tmp=\$(mktemp /tmp/magicvpn-add-user.XXXXXX.json)\n" .
        "cat > \"\$tmp\" <<'JSON'\n" . $json . "\nJSON\n" .
        "if ! command -v " . escapeshellarg($bin) . " >/dev/null 2>&1; then echo 'xray binary not found: " . addslashes($bin) . "' >&2; rm -f \"\$tmp\"; exit 20; fi\n" .
        "if ! " . escapeshellarg($bin) . " api help adu >/dev/null 2>&1; then echo 'This Xray does not support: xray api adu. Update Xray-core to a recent version.' >&2; rm -f \"\$tmp\"; exit 21; fi\n" .
        "out=\$(" . escapeshellarg($bin) . " api adu --server=" . escapeshellarg($api) . " \"\$tmp\" 2>&1) || { code=\$?; echo \"\$out\" >&2; rm -f \"\$tmp\"; exit \$code; }\n" .
        "rm -f \"\$tmp\"\n" .
        "echo \"API_ADDED\"\n";
    ssh_exec_server($server, $remote);
}

function xray_api_remove_vless_user(array $server, string $uuid, string $email): void {
    $tag = (string)($server['inbound_tag'] ?? 'vless-in');
    $api = xray_api_address($server);
    $bin = xray_binary($server);

    $payload = [
        'inbounds' => [[
            'tag' => $tag,
            'protocol' => 'vless',
            'settings' => [
                'clients' => [[
                    'id' => $uuid,
                    'email' => $email,
                ]],
            ],
        ]],
    ];
    $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $remote = "set -e\n" .
        "tmp=\$(mktemp /tmp/magicvpn-remove-user.XXXXXX.json)\n" .
        "cat > \"\$tmp\" <<'JSON'\n" . $json . "\nJSON\n" .
        "if ! command -v " . escapeshellarg($bin) . " >/dev/null 2>&1; then echo 'xray binary not found: " . addslashes($bin) . "' >&2; rm -f \"\$tmp\"; exit 20; fi\n" .
        "if ! " . escapeshellarg($bin) . " api help rmu >/dev/null 2>&1; then echo 'This Xray does not support: xray api rmu. Update Xray-core to a recent version.' >&2; rm -f \"\$tmp\"; exit 21; fi\n" .
        "out=\$(" . escapeshellarg($bin) . " api rmu --server=" . escapeshellarg($api) . " --tag=" . escapeshellarg((string)($server['inbound_tag'] ?? 'vless-reality')) . " \"\$tmp\" 2>&1) || { code=\$?; echo \"\$out\" >&2; rm -f \"\$tmp\"; exit \$code; }\n" .
        "rm -f \"\$tmp\"\n" .
        "echo \"API_REMOVED\"\n";
    ssh_exec_server($server, $remote);
}

function ensure_vless_user(array $server, string $uuid, string $email, bool $reload = false): void {
    // Always persist to config.json so users survive a future manual/night restart.
    ensure_vless_user_in_config($server, $uuid, $email);

    $mode = xray_provision_mode($server);
    if ($mode === 'config_restart' || $mode === 'restart') {
        $reloadCmd = trim((string)($server['xray_reload_cmd'] ?? 'sudo systemctl restart xray'));
        if ($reloadCmd !== '' && $reloadCmd !== 'true') {
            ssh_exec_server($server, $reloadCmd);
        }
        return;
    }

    // Default mode: no restart, runtime update via Xray API.
    xray_api_add_vless_user($server, $uuid, $email);
}

function remove_vless_user(array $server, string $uuid, bool $reload = false): void {
    $email = (string)($server['remove_email'] ?? '');
    // If caller does not pass email, infer usual MagicVPN email from uuid is impossible.
    // Therefore we remove from config by uuid, and for API removal we prefer xray_email from payload-aware wrapper below.
    remove_vless_user_from_config($server, $uuid, $email !== '' ? $email : null);

    $mode = xray_provision_mode($server);
    if ($mode === 'config_restart' || $mode === 'restart') {
        $reloadCmd = trim((string)($server['xray_reload_cmd'] ?? 'sudo systemctl restart xray'));
        if ($reloadCmd !== '' && $reloadCmd !== 'true') {
            ssh_exec_server($server, $reloadCmd);
        }
        return;
    }

    if ($email === '') {
        // Keep backward compatibility: old callers may only know uuid. Config reserve is removed now;
        // live API removal should be done through remove_vless_user_everywhere_by_email().
        throw new RuntimeException('API remove requires user email. Use remove_vless_user_everywhere_by_email($uuid, $email).');
    }
    xray_api_remove_vless_user($server, $uuid, $email);
}

function ensure_vless_user_everywhere(string $uuid, string $email, bool $reload = false): array {
    $results = [];
    foreach (servers_config() as $server) {
        foreach (server_profiles($server) as $profile) {
            try {
                ensure_vless_user($profile, $uuid, $email, false);
                $results[] = ['ok' => true, 'server' => $profile['id'] ?? $server['id'] ?? 'xray', 'mode' => xray_provision_mode($profile)];
            } catch (Throwable $e) {
                $results[] = ['ok' => false, 'server' => $profile['id'] ?? $server['id'] ?? 'xray', 'mode' => xray_provision_mode($profile), 'error' => $e->getMessage()];
            }
        }
    }
    return $results;
}

function remove_vless_user_everywhere_by_email(string $uuid, string $email, bool $reload = false): array {
    $results = [];
    foreach (servers_config() as $server) {
        foreach (server_profiles($server) as $profile) {
            $profile['remove_email'] = $email;
            try {
                remove_vless_user($profile, $uuid, false);
                $results[] = ['ok' => true, 'server' => $profile['id'] ?? $server['id'] ?? 'xray', 'mode' => xray_provision_mode($profile)];
            } catch (Throwable $e) {
                $results[] = ['ok' => false, 'server' => $profile['id'] ?? $server['id'] ?? 'xray', 'mode' => xray_provision_mode($profile), 'error' => $e->getMessage()];
            }
        }
    }
    return $results;
}

function remove_vless_user_everywhere(string $uuid, bool $reload = false): array {
    // Backward-compatible reserve cleanup without live API removal, because old signature has no email.
    $results = [];
    foreach (servers_config() as $server) {
        foreach (server_profiles($server) as $profile) {
            try {
                remove_vless_user_from_config($profile, $uuid, null);
                $results[] = ['ok' => true, 'server' => $profile['id'] ?? $server['id'] ?? 'xray', 'note' => 'config removed only; live API removal requires email'];
            } catch (Throwable $e) {
                $results[] = ['ok' => false, 'server' => $profile['id'] ?? $server['id'] ?? 'xray', 'error' => $e->getMessage()];
            }
        }
    }
    return $results;
}

function check_xray_api_status(array $profile): array {
    $api = xray_api_address($profile);
    $bin = xray_binary($profile);
    try {
        $raw = ssh_exec_server($profile, "set -e; " . escapeshellarg($bin) . " api help adu >/dev/null 2>&1 && echo API_CLI_OK; ss -lnt 2>/dev/null | grep -q '" . addslashes(substr($api, strrpos($api, ':') + 1)) . "' && echo API_PORT_LISTEN || true");
        return ['api_server' => $api, 'api_cli' => str_contains($raw, 'API_CLI_OK'), 'api_listen' => str_contains($raw, 'API_PORT_LISTEN')];
    } catch (Throwable $e) {
        return ['api_server' => $api, 'api_cli' => false, 'api_listen' => false, 'api_error' => $e->getMessage()];
    }
}

function check_xray_profile_status(array $profile): array {
    $port = (int)($profile['port'] ?? 0);
    $tag = (string)($profile['inbound_tag'] ?? '');
    $remote = "python3 - <<'PY'\n" .
        "import json, subprocess, socket, shutil, os\n" .
        'path=' . var_export($profile['xray_config_path'] ?? '/usr/local/etc/xray/config.json', true) . "\n" .
        'port=' . var_export($port, true) . "\n" .
        'tag=' . var_export($tag, true) . "\n" .
        "out={'active': False, 'listening': False, 'clients': 0, 'tag_found': False, 'actual_tag': '', 'match_method': '', 'internet': False, 'disk_free_gb': None}\n" .
        "try:\n" .
        "    r=subprocess.run(['systemctl','is-active','xray'], capture_output=True, text=True, timeout=5)\n" .
        "    out['active']=(r.stdout.strip()=='active')\n" .
        "except Exception as e: out['active_error']=str(e)\n" .
        "try:\n" .
        "    s=socket.socket(); s.settimeout(1); s.connect(('127.0.0.1', port)); s.close(); out['listening']=True\n" .
        "except Exception: out['listening']=False\n" .
        "try:\n" .
        "    with open(path,'r',encoding='utf-8') as f: cfg=json.load(f)\n" .
        "    inbounds=[i for i in cfg.get('inbounds',[]) if i.get('protocol')=='vless']\n" .
        "    target=None\n" .
        "    for inbound in inbounds:\n" .
        "        if inbound.get('tag')==tag:\n" .
        "            target=inbound; out['tag_found']=True; out['match_method']='tag'; break\n" .
        "    if target is None:\n" .
        "        for inbound in inbounds:\n" .
        "            if int(inbound.get('port') or 0)==int(port):\n" .
        "                target=inbound; out['match_method']='port'; break\n" .
        "    if target is not None:\n" .
        "        out['actual_tag']=target.get('tag','')\n" .
        "        out['actual_port']=target.get('port')\n" .
        "        out['clients']=len(target.get('settings',{}).get('clients',[]))\n" .
        "except Exception as e: out['config_error']=str(e)\n" .
        "try:\n" .
        "    r=subprocess.run(['curl','-4','-I','https://www.cloudflare.com','--connect-timeout','4','--max-time','8'], capture_output=True, text=True, timeout=10)\n" .
        "    out['internet']=(r.returncode==0 and ('HTTP/' in (r.stdout+r.stderr)))\n" .
        "except Exception as e: out['internet_error']=str(e)\n" .
        "try:\n" .
        "    du=shutil.disk_usage('/')\n" .
        "    out['disk_free_gb']=round(du.free/1024/1024/1024, 1)\n" .
        "except Exception: pass\n" .
        "try:\n" .
        "    with open('/proc/loadavg') as f: out['loadavg']=f.read().split()[0]\n" .
        "except Exception: pass\n" .
        "import json as _j; print(_j.dumps(out, ensure_ascii=False))\n" .
        "PY";
    try {
        $raw = ssh_exec_server($profile, $remote);
        $data = json_decode(trim($raw), true) ?: [];
        return ['ok' => true, 'profile' => $profile['id'] ?? $tag, 'title' => $profile['title'] ?? ($profile['id'] ?? $tag), 'expected_tag' => $tag, 'port' => $port] + $data + check_xray_api_status($profile);
    } catch (Throwable $e) {
        return ['ok' => false, 'profile' => $profile['id'] ?? $tag, 'title' => $profile['title'] ?? ($profile['id'] ?? $tag), 'expected_tag' => $tag, 'port' => $port, 'error' => $e->getMessage()] + check_xray_api_status($profile);
    }
}

function check_all_xray_statuses(): array {
    $rows = [];
    foreach (servers_config() as $server) {
        foreach (server_profiles($server) as $profile) {
            $rows[] = check_xray_profile_status($profile);
        }
    }
    return $rows;
}
