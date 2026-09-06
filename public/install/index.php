<?php
/**
 * nkhoj — Web Installer
 * Place this file at: public/install/index.php
 * Visit: https://yourdomain.com/install/
 * DELETE this directory after installation!
 */

session_start();

define('APP_ROOT', realpath(__DIR__ . '/../../'));
define('INSTALLER_VERSION', '1.0.0');

// ── Helpers ──────────────────────────────────────────────────────────────────

function env_path(): string { return APP_ROOT . '/.env'; }
function env_example_path(): string { return APP_ROOT . '/.env.example'; }

function step(): int { return (int)($_SESSION['install_step'] ?? 1); }
function set_step(int $s): void { $_SESSION['install_step'] = $s; }
function data(): array { return $_SESSION['install_data'] ?? []; }
function set_data(array $d): void { $_SESSION['install_data'] = $d; }
function merge_data(array $d): void { $_SESSION['install_data'] = array_merge(data(), $d); }

function already_installed(): bool {
    return file_exists(env_path()) && file_exists(APP_ROOT . '/storage/installed');
}

function find_php(): string {
    $candidates = [PHP_BINARY, 'php8.2', 'php8.1', 'php8', 'php'];
    foreach ($candidates as $bin) {
        $out = shell_exec(escapeshellarg($bin) . ' -r "echo 1;" 2>/dev/null');
        if (trim((string)$out) === '1') return $bin;
    }
    return PHP_BINARY;
}

function run_artisan(string $cmd): array {
    if (!function_exists('exec') && !function_exists('shell_exec')) {
        return ['ok' => false, 'output' => 'exec() and shell_exec() are disabled on this server.'];
    }
    $php  = find_php();
    $full = escapeshellarg($php) . ' ' . escapeshellarg(APP_ROOT . '/artisan') . ' ' . $cmd . ' 2>&1';
    $out  = '';
    $code = 0;
    if (function_exists('exec')) {
        $lines = [];
        exec($full, $lines, $code);
        $out = implode("\n", $lines);
    } else {
        $out  = (string)shell_exec($full);
        $code = 0;
    }
    return ['ok' => $code === 0, 'output' => $out];
}

function generate_app_key(): string {
    return 'base64:' . base64_encode(random_bytes(32));
}

function write_env(array $d): bool {
    $template = <<<ENV
APP_NAME="{APP_NAME}"
APP_ENV=production
APP_KEY={APP_KEY}
APP_DEBUG=false
APP_TIMEZONE={APP_TIMEZONE}
APP_URL={APP_URL}

APP_LOCALE=en
APP_FALLBACK_LOCALE=en

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST={DB_HOST}
DB_PORT={DB_PORT}
DB_DATABASE={DB_DATABASE}
DB_USERNAME={DB_USERNAME}
DB_PASSWORD={DB_PASSWORD}

CACHE_STORE=file
QUEUE_CONNECTION=database
SESSION_DRIVER=file
SESSION_LIFETIME=120

FILESYSTEM_DISK=local

MAIL_MAILER=smtp
MAIL_HOST={MAIL_HOST}
MAIL_PORT={MAIL_PORT}
MAIL_USERNAME={MAIL_USERNAME}
MAIL_PASSWORD={MAIL_PASSWORD}
MAIL_ENCRYPTION={MAIL_ENCRYPTION}
MAIL_FROM_ADDRESS={MAIL_FROM}
MAIL_FROM_NAME="\${APP_NAME}"
ENV;

    $key = generate_app_key();
    $replacements = [
        '{APP_NAME}'     => addslashes($d['app_name'] ?? 'nkhoj'),
        '{APP_KEY}'      => $key,
        '{APP_TIMEZONE}' => $d['timezone'] ?? 'Asia/Kathmandu',
        '{APP_URL}'      => rtrim($d['app_url'] ?? 'http://localhost', '/'),
        '{DB_HOST}'      => $d['db_host'] ?? '127.0.0.1',
        '{DB_PORT}'      => $d['db_port'] ?? '3306',
        '{DB_DATABASE}'  => $d['db_name'] ?? 'nkhoj',
        '{DB_USERNAME}'  => $d['db_user'] ?? 'root',
        '{DB_PASSWORD}'  => $d['db_pass'] ?? '',
        '{MAIL_HOST}'    => $d['mail_host'] ?? '127.0.0.1',
        '{MAIL_PORT}'    => $d['mail_port'] ?? '587',
        '{MAIL_USERNAME}'=> $d['mail_user'] ?? '',
        '{MAIL_PASSWORD}'=> $d['mail_pass'] ?? '',
        '{MAIL_ENCRYPTION}'=> $d['mail_enc'] ?? 'tls',
        '{MAIL_FROM}'    => $d['mail_from'] ?? ('hello@' . parse_url($d['app_url'] ?? 'http://localhost', PHP_URL_HOST)),
    ];

    $_SESSION['generated_key'] = $key;
    $content = str_replace(array_keys($replacements), array_values($replacements), $template);
    return file_put_contents(env_path(), $content) !== false;
}

function test_db(array $d): array {
    try {
        $dsn = "mysql:host={$d['db_host']};port={$d['db_port']};dbname={$d['db_name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $d['db_user'], $d['db_pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);
        $ver = $pdo->query('SELECT VERSION()')->fetchColumn();
        return ['ok' => true, 'version' => $ver];
    } catch (Exception $e) {
        return ['ok' => false, 'error' => $e->getMessage()];
    }
}

function create_admin_user(array $d): array {
    try {
        $dsn = "mysql:host={$d['db_host']};port={$d['db_port']};dbname={$d['db_name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $d['db_user'], $d['db_pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

        // Check if admin already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR role = 'admin' LIMIT 1");
        $stmt->execute([$d['admin_email']]);
        if ($stmt->fetchColumn() > 0) {
            // Update existing admin if email matches
            $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, password=?, role='admin', email_verified_at=NOW() WHERE email=?");
            $stmt->execute([$d['admin_name'], $d['admin_email'], password_hash($d['admin_pass'], PASSWORD_BCRYPT), $d['admin_email']]);
            return ['ok' => true, 'msg' => 'Admin account updated.'];
        }

        $username = strtolower(preg_replace('/[^a-z0-9]/', '', $d['admin_name'])) ?: 'admin';
        $stmt = $pdo->prepare(
            "INSERT INTO users (name, username, email, password, role, email_verified_at, created_at, updated_at)
             VALUES (?, ?, ?, ?, 'admin', NOW(), NOW(), NOW())"
        );
        $stmt->execute([$d['admin_name'], $username, $d['admin_email'], password_hash($d['admin_pass'], PASSWORD_BCRYPT)]);
        return ['ok' => true, 'msg' => 'Admin account created.'];
    } catch (Exception $e) {
        return ['ok' => false, 'error' => $e->getMessage()];
    }
}

// ── Requirements ─────────────────────────────────────────────────────────────

function requirements(): array {
    $checks = [];

    $checks[] = ['name' => 'PHP Version (≥ 8.1)', 'pass' => version_compare(PHP_VERSION, '8.1.0', '>='), 'value' => PHP_VERSION];
    foreach (['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'json', 'bcmath', 'fileinfo', 'xml', 'curl'] as $ext) {
        $checks[] = ['name' => "Extension: $ext", 'pass' => extension_loaded($ext), 'value' => extension_loaded($ext) ? 'Loaded' : 'Missing'];
    }
    foreach ([APP_ROOT . '/storage', APP_ROOT . '/bootstrap/cache', APP_ROOT . '/public'] as $dir) {
        $label = str_replace(APP_ROOT . '/', '', $dir);
        $checks[] = ['name' => "$label (writable)", 'pass' => is_writable($dir), 'value' => is_writable($dir) ? 'Writable' : 'Not writable'];
    }
    $checks[] = ['name' => '.env file (writable parent)', 'pass' => is_writable(APP_ROOT), 'value' => is_writable(APP_ROOT) ? 'OK' : 'Not writable'];
    $checks[] = ['name' => 'exec() available', 'pass' => function_exists('exec'), 'value' => function_exists('exec') ? 'Yes' : 'No (will use fallback)'];

    return $checks;
}

function all_critical_pass(array $checks): bool {
    foreach ($checks as $c) {
        if (!$c['pass'] && strpos($c['name'], 'exec()') === false) return false;
    }
    return true;
}

// ── POST handling ─────────────────────────────────────────────────────────────

$errors = [];
$success_msgs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'reset') {
        session_destroy();
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    if ($action === 'step1') {
        $reqs = requirements();
        if (all_critical_pass($reqs)) {
            set_step(2);
        } else {
            $errors[] = 'Please fix the failing requirements before continuing.';
        }
    }

    if ($action === 'test_db') {
        $d = [
            'db_host' => trim($_POST['db_host'] ?? '127.0.0.1'),
            'db_port' => trim($_POST['db_port'] ?? '3306'),
            'db_name' => trim($_POST['db_name'] ?? ''),
            'db_user' => trim($_POST['db_user'] ?? ''),
            'db_pass' => $_POST['db_pass'] ?? '',
        ];
        $result = test_db($d);
        if ($result['ok']) {
            $_SESSION['db_test_ok'] = true;
            $_SESSION['db_test_msg'] = 'Connected! MySQL ' . $result['version'];
            merge_data($d);
        } else {
            $_SESSION['db_test_ok'] = false;
            $_SESSION['db_test_msg'] = $result['error'];
        }
    }

    if ($action === 'step2') {
        $d = [
            'db_host' => trim($_POST['db_host'] ?? '127.0.0.1'),
            'db_port' => trim($_POST['db_port'] ?? '3306'),
            'db_name' => trim($_POST['db_name'] ?? ''),
            'db_user' => trim($_POST['db_user'] ?? ''),
            'db_pass' => $_POST['db_pass'] ?? '',
        ];
        if (empty($d['db_name']) || empty($d['db_user'])) {
            $errors[] = 'Database name and username are required.';
        } else {
            $test = test_db($d);
            if (!$test['ok']) {
                $errors[] = 'Cannot connect to database: ' . $test['error'];
            } else {
                merge_data($d);
                set_step(3);
            }
        }
    }

    if ($action === 'step3') {
        $d = [
            'app_name'   => trim($_POST['app_name'] ?? 'nkhoj'),
            'app_url'    => rtrim(trim($_POST['app_url'] ?? ''), '/'),
            'timezone'   => trim($_POST['timezone'] ?? 'Asia/Kathmandu'),
            'admin_name' => trim($_POST['admin_name'] ?? ''),
            'admin_email'=> trim($_POST['admin_email'] ?? ''),
            'admin_pass' => $_POST['admin_pass'] ?? '',
            'admin_pass2'=> $_POST['admin_pass2'] ?? '',
            'mail_host'  => trim($_POST['mail_host'] ?? ''),
            'mail_port'  => trim($_POST['mail_port'] ?? '587'),
            'mail_user'  => trim($_POST['mail_user'] ?? ''),
            'mail_pass'  => $_POST['mail_pass'] ?? '',
            'mail_enc'   => trim($_POST['mail_enc'] ?? 'tls'),
            'mail_from'  => trim($_POST['mail_from'] ?? ''),
        ];
        if (empty($d['app_name'])) $errors[] = 'App name is required.';
        if (empty($d['app_url']))  $errors[] = 'App URL is required.';
        if (empty($d['admin_name'])) $errors[] = 'Admin name is required.';
        if (!filter_var($d['admin_email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid admin email is required.';
        if (strlen($d['admin_pass']) < 8) $errors[] = 'Password must be at least 8 characters.';
        if ($d['admin_pass'] !== $d['admin_pass2']) $errors[] = 'Passwords do not match.';

        if (empty($errors)) {
            merge_data($d);
            set_step(4);
        }
    }

    if ($action === 'install') {
        $d = data();
        $log = [];
        $fatal = false;

        // 1. Write .env
        if (!write_env($d)) {
            $log[] = ['ok' => false, 'msg' => 'Failed to write .env file. Check directory permissions.'];
            $fatal = true;
        } else {
            $log[] = ['ok' => true, 'msg' => '.env file created with APP_KEY = ' . ($_SESSION['generated_key'] ?? '?')];
        }

        if (!$fatal) {
            // 2. Generate key via artisan (if exec available)
            if (function_exists('exec')) {
                $r = run_artisan('key:generate --force');
                $log[] = ['ok' => $r['ok'], 'msg' => 'Generate APP_KEY: ' . ($r['ok'] ? 'Done' : $r['output'])];

                // 3. Migrate
                $r = run_artisan('migrate --force');
                $log[] = ['ok' => $r['ok'], 'msg' => 'Database migration: ' . ($r['ok'] ? 'Done' : $r['output'])];
                if (!$r['ok']) $fatal = true;

                // 4. Optimize
                run_artisan('config:clear');
                run_artisan('cache:clear');
                run_artisan('view:clear');
                $log[] = ['ok' => true, 'msg' => 'Caches cleared.'];
            } else {
                $log[] = ['ok' => false, 'msg' => 'exec() disabled — run these manually: php artisan key:generate --force && php artisan migrate --force'];
                $fatal = true;
            }
        }

        // 5. Create admin (even if exec not available, we can do this via PDO)
        if (!$fatal) {
            $r = create_admin_user($d);
            $log[] = ['ok' => $r['ok'], 'msg' => 'Admin account: ' . ($r['ok'] ? $r['msg'] : $r['error'])];
            if (!$r['ok']) $fatal = true;
        }

        // 6. Mark as installed
        if (!$fatal) {
            file_put_contents(APP_ROOT . '/storage/installed', date('Y-m-d H:i:s'));
            $log[] = ['ok' => true, 'msg' => 'Installation marker created.'];
            set_step(5);
        }

        $_SESSION['install_log']  = $log;
        $_SESSION['install_fatal']= $fatal;
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$step = step();
$d    = data();
$reqs = ($step === 1) ? requirements() : [];
$all_pass = ($step === 1) ? all_critical_pass($reqs) : true;

?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>nkhoj — Web Installer</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config = { darkMode:'class', theme:{ extend:{ colors:{ brand:{ 50:'#eef2ff',100:'#e0e7ff',500:'#6366f1',600:'#4f46e5',700:'#4338ca' } } } } }</script>
<style>
body { background: #0f172a; }
.step-badge { transition: all .2s; }
.step-active { background: #6366f1; color: #fff; }
.step-done   { background: #10b981; color: #fff; }
.step-todo   { background: #1e293b; color: #64748b; border: 1px solid #334155; }
input, select, textarea { outline: none; }
input:focus, select:focus { box-shadow: 0 0 0 2px #6366f1; }
.log-ok   { color: #4ade80; }
.log-fail { color: #f87171; }
</style>
</head>
<body class="min-h-screen flex flex-col items-center justify-start py-12 px-4">

<div class="w-full max-w-2xl">

    {{-- Logo / Header --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center gap-2 mb-3">
            <span class="text-3xl font-black text-white tracking-tight">nkhoj</span>
            <span class="text-xs font-bold text-indigo-400 bg-indigo-900/40 border border-indigo-700 px-2 py-0.5 rounded-full uppercase tracking-wide">Installer v<?= INSTALLER_VERSION ?></span>
        </div>
        <p class="text-slate-400 text-sm">Set up your Nepali news platform in minutes</p>
    </div>

    {{-- Step indicator --}}
    <div class="flex items-center justify-center gap-1 mb-8">
        <?php
        $steps = ['Requirements','Database','Configuration','Installing','Complete'];
        foreach ($steps as $i => $label):
            $n = $i + 1;
            $cls = $n < $step ? 'step-done' : ($n === $step ? 'step-active' : 'step-todo');
        ?>
        <div class="flex items-center gap-1">
            <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold step-badge <?= $cls ?>">
                <?= $n < $step ? '✓' : $n ?>
            </div>
            <span class="text-xs font-medium hidden sm:block <?= $n === $step ? 'text-white' : 'text-slate-500' ?>"><?= $label ?></span>
        </div>
        <?php if ($i < 4): ?><div class="w-4 h-px bg-slate-700 mx-0.5"></div><?php endif; ?>
        <?php endforeach; ?>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="mb-5 bg-red-900/30 border border-red-700 rounded-xl p-4">
        <?php foreach ($errors as $e): ?>
        <p class="text-red-300 text-sm flex items-start gap-2"><span class="text-red-500 flex-shrink-0 mt-0.5">✕</span><?= htmlspecialchars($e) ?></p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="bg-slate-800 border border-slate-700 rounded-2xl shadow-2xl overflow-hidden">

    <?php

    // ═══════════════════════════════════════════════════════════════
    // STEP 1 — Requirements
    // ═══════════════════════════════════════════════════════════════
    if ($step === 1): ?>

    <div class="px-6 py-5 border-b border-slate-700">
        <h2 class="text-lg font-bold text-white">Server Requirements</h2>
        <p class="text-slate-400 text-sm mt-0.5">Checking that your server meets the minimum requirements.</p>
    </div>

    <div class="p-6">
        <div class="space-y-2 mb-6">
            <?php foreach ($reqs as $r): ?>
            <div class="flex items-center justify-between py-2 px-3 rounded-lg <?= $r['pass'] ? 'bg-slate-700/50' : 'bg-red-900/20 border border-red-800' ?>">
                <span class="text-sm <?= $r['pass'] ? 'text-slate-300' : 'text-red-300' ?>"><?= htmlspecialchars($r['name']) ?></span>
                <span class="text-xs font-semibold flex items-center gap-1.5 <?= $r['pass'] ? 'text-green-400' : 'text-red-400' ?>">
                    <?= $r['pass'] ? '✓' : '✕' ?>
                    <?= htmlspecialchars($r['value']) ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (!$all_pass): ?>
        <div class="bg-amber-900/30 border border-amber-700 rounded-xl p-4 mb-5">
            <p class="text-amber-300 text-sm font-semibold mb-1">Action required</p>
            <p class="text-amber-400 text-xs">Fix the failing items above (contact your hosting provider if needed), then refresh this page.</p>
        </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="action" value="step1">
            <button <?= !$all_pass ? 'disabled' : '' ?>
                class="w-full py-3 rounded-xl text-sm font-bold transition-colors <?= $all_pass ? 'bg-indigo-600 hover:bg-indigo-500 text-white cursor-pointer' : 'bg-slate-700 text-slate-500 cursor-not-allowed' ?>">
                Continue to Database Setup →
            </button>
        </form>
    </div>

    <?php

    // ═══════════════════════════════════════════════════════════════
    // STEP 2 — Database
    // ═══════════════════════════════════════════════════════════════
    elseif ($step === 2): ?>

    <div class="px-6 py-5 border-b border-slate-700">
        <h2 class="text-lg font-bold text-white">Database Configuration</h2>
        <p class="text-slate-400 text-sm mt-0.5">Enter your MySQL database credentials.</p>
    </div>

    <div class="p-6">

        <?php if (isset($_SESSION['db_test_ok'])): ?>
        <div class="mb-5 px-4 py-3 rounded-xl text-sm font-medium flex items-center gap-2 <?= $_SESSION['db_test_ok'] ? 'bg-green-900/30 border border-green-700 text-green-400' : 'bg-red-900/30 border border-red-700 text-red-400' ?>">
            <?= $_SESSION['db_test_ok'] ? '✓' : '✕' ?>
            <?= htmlspecialchars($_SESSION['db_test_msg'] ?? '') ?>
        </div>
        <?php unset($_SESSION['db_test_ok'], $_SESSION['db_test_msg']); ?>
        <?php endif; ?>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Database Host *</label>
                <input type="text" name="db_host" form="form2" form="formTest" value="<?= htmlspecialchars($d['db_host'] ?? '127.0.0.1') ?>"
                    id="db_host" class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2.5 text-sm text-white focus:border-indigo-500" placeholder="127.0.0.1">
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Port</label>
                <input type="number" name="db_port" form="form2" id="db_port" value="<?= htmlspecialchars($d['db_port'] ?? '3306') ?>"
                    class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2.5 text-sm text-white focus:border-indigo-500" placeholder="3306">
            </div>
            <div class="col-span-2">
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Database Name *</label>
                <input type="text" name="db_name" form="form2" id="db_name" value="<?= htmlspecialchars($d['db_name'] ?? '') ?>"
                    class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2.5 text-sm text-white focus:border-indigo-500" placeholder="nkhoj">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Username *</label>
                <input type="text" name="db_user" form="form2" id="db_user" value="<?= htmlspecialchars($d['db_user'] ?? '') ?>"
                    class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2.5 text-sm text-white focus:border-indigo-500" placeholder="root">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Password</label>
                <input type="password" name="db_pass" form="form2" id="db_pass" value="<?= htmlspecialchars($d['db_pass'] ?? '') ?>"
                    class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2.5 text-sm text-white focus:border-indigo-500" placeholder="(leave blank if none)">
            </div>
        </div>

        {{-- Test connection button --}}
        <form method="POST" class="mb-3" id="formTest">
            <input type="hidden" name="action" value="test_db">
            <button onclick="syncFields()" type="submit" class="w-full py-2.5 border border-indigo-600 text-indigo-400 hover:bg-indigo-900/30 rounded-xl text-sm font-semibold transition-colors">
                Test Connection
            </button>
        </form>

        <form method="POST" id="form2">
            <input type="hidden" name="action" value="step2">
            <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-bold transition-colors">
                Save & Continue →
            </button>
        </form>

        <script>
        function syncFields() {
            ['db_host','db_port','db_name','db_user','db_pass'].forEach(id => {
                const v = document.getElementById(id).value;
                document.querySelectorAll('[name="'+id+'"]').forEach(el => el.value = v);
            });
        }
        document.getElementById('form2').addEventListener('submit', syncFields);
        </script>
    </div>

    <?php

    // ═══════════════════════════════════════════════════════════════
    // STEP 3 — App Configuration
    // ═══════════════════════════════════════════════════════════════
    elseif ($step === 3): ?>

    <div class="px-6 py-5 border-b border-slate-700">
        <h2 class="text-lg font-bold text-white">Application Configuration</h2>
        <p class="text-slate-400 text-sm mt-0.5">Configure your site settings and admin account.</p>
    </div>

    <form method="POST" class="p-6 space-y-5">
        <input type="hidden" name="action" value="step3">

        <div>
            <h3 class="text-xs font-bold text-indigo-400 uppercase tracking-widest mb-3">Site Settings</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Site Name *</label>
                    <input type="text" name="app_name" value="<?= htmlspecialchars($d['app_name'] ?? 'nkhoj') ?>" required
                        class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2.5 text-sm text-white focus:border-indigo-500" placeholder="My News Site">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Site URL *</label>
                    <input type="url" name="app_url" value="<?= htmlspecialchars($d['app_url'] ?? 'https://') ?>" required
                        class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2.5 text-sm text-white focus:border-indigo-500" placeholder="https://yourdomain.com">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Timezone</label>
                    <select name="timezone" class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2.5 text-sm text-white focus:border-indigo-500">
                        <?php
                        $tzs = ['Asia/Kathmandu','Asia/Kolkata','UTC','America/New_York','America/Los_Angeles','Europe/London','Europe/Paris','Asia/Dubai','Asia/Singapore','Asia/Tokyo','Australia/Sydney'];
                        foreach ($tzs as $tz): ?>
                        <option value="<?= $tz ?>" <?= ($d['timezone'] ?? 'Asia/Kathmandu') === $tz ? 'selected' : '' ?>><?= $tz ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-700 pt-5">
            <h3 class="text-xs font-bold text-indigo-400 uppercase tracking-widest mb-3">Admin Account</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Full Name *</label>
                    <input type="text" name="admin_name" value="<?= htmlspecialchars($d['admin_name'] ?? '') ?>" required
                        class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2.5 text-sm text-white focus:border-indigo-500" placeholder="Admin">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Email Address *</label>
                    <input type="email" name="admin_email" value="<?= htmlspecialchars($d['admin_email'] ?? '') ?>" required
                        class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2.5 text-sm text-white focus:border-indigo-500" placeholder="admin@yourdomain.com">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Password * (min 8 chars)</label>
                        <input type="password" name="admin_pass" required minlength="8"
                            class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2.5 text-sm text-white focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Confirm Password *</label>
                        <input type="password" name="admin_pass2" required
                            class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2.5 text-sm text-white focus:border-indigo-500">
                    </div>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-700 pt-5">
            <button type="button" onclick="this.nextElementSibling.classList.toggle('hidden')"
                class="w-full flex items-center justify-between py-2 text-sm font-semibold text-slate-400 hover:text-white transition-colors">
                <span>Email / SMTP Settings <span class="text-slate-600 font-normal">(optional)</span></span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="hidden space-y-3 mt-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">SMTP Host</label>
                        <input type="text" name="mail_host" value="<?= htmlspecialchars($d['mail_host'] ?? 'smtp.gmail.com') ?>"
                            class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2.5 text-sm text-white focus:border-indigo-500" placeholder="smtp.gmail.com">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">SMTP Port</label>
                        <input type="number" name="mail_port" value="<?= htmlspecialchars($d['mail_port'] ?? '587') ?>"
                            class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2.5 text-sm text-white focus:border-indigo-500" placeholder="587">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">SMTP Username</label>
                        <input type="text" name="mail_user" value="<?= htmlspecialchars($d['mail_user'] ?? '') ?>"
                            class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2.5 text-sm text-white focus:border-indigo-500" placeholder="your@gmail.com">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">SMTP Password</label>
                        <input type="password" name="mail_pass" value="<?= htmlspecialchars($d['mail_pass'] ?? '') ?>"
                            class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2.5 text-sm text-white focus:border-indigo-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Encryption</label>
                        <select name="mail_enc" class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2.5 text-sm text-white focus:border-indigo-500">
                            <option value="tls" <?= ($d['mail_enc'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS</option>
                            <option value="ssl" <?= ($d['mail_enc'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">From Address</label>
                        <input type="email" name="mail_from" value="<?= htmlspecialchars($d['mail_from'] ?? '') ?>"
                            class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2.5 text-sm text-white focus:border-indigo-500" placeholder="noreply@domain.com">
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-bold transition-colors mt-2">
            Review & Install →
        </button>
    </form>

    <?php

    // ═══════════════════════════════════════════════════════════════
    // STEP 4 — Install
    // ═══════════════════════════════════════════════════════════════
    elseif ($step === 4): ?>

    <div class="px-6 py-5 border-b border-slate-700">
        <h2 class="text-lg font-bold text-white">Ready to Install</h2>
        <p class="text-slate-400 text-sm mt-0.5">Review your settings below, then click Install.</p>
    </div>

    <div class="p-6">

        <div class="bg-slate-700/50 rounded-xl p-4 mb-5 space-y-2 text-sm">
            <div class="flex justify-between"><span class="text-slate-400">Site Name</span><span class="text-white font-medium"><?= htmlspecialchars($d['app_name'] ?? '') ?></span></div>
            <div class="flex justify-between"><span class="text-slate-400">Site URL</span><span class="text-white font-medium"><?= htmlspecialchars($d['app_url'] ?? '') ?></span></div>
            <div class="flex justify-between"><span class="text-slate-400">Database</span><span class="text-white font-medium"><?= htmlspecialchars($d['db_name'] ?? '') ?> @ <?= htmlspecialchars($d['db_host'] ?? '') ?></span></div>
            <div class="flex justify-between"><span class="text-slate-400">Admin Email</span><span class="text-white font-medium"><?= htmlspecialchars($d['admin_email'] ?? '') ?></span></div>
            <div class="flex justify-between"><span class="text-slate-400">Timezone</span><span class="text-white font-medium"><?= htmlspecialchars($d['timezone'] ?? '') ?></span></div>
        </div>

        <div class="bg-amber-900/30 border border-amber-700 rounded-xl p-4 mb-5">
            <p class="text-amber-300 text-sm font-semibold">⚠ Before you continue</p>
            <ul class="text-amber-400 text-xs mt-1.5 space-y-1 list-disc list-inside">
                <li>Make sure the database <strong class="text-amber-300"><?= htmlspecialchars($d['db_name'] ?? '') ?></strong> already exists in MySQL</li>
                <li>Ensure the database user has CREATE TABLE privileges</li>
                <li>This will overwrite any existing <code class="text-amber-200">.env</code> file</li>
            </ul>
        </div>

        <form method="POST" id="installForm">
            <input type="hidden" name="action" value="install">
            <button id="installBtn" type="submit" onclick="startInstall()"
                class="w-full py-3 bg-green-600 hover:bg-green-500 text-white rounded-xl text-sm font-bold transition-colors flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Install nkhoj
            </button>
        </form>

        <div id="installProgress" class="hidden mt-4 text-center">
            <div class="inline-flex items-center gap-2 text-indigo-400 text-sm">
                <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"/><path d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" fill="currentColor" class="opacity-75"/></svg>
                Installing, please wait…
            </div>
        </div>

        <script>
        function startInstall() {
            document.getElementById('installBtn').disabled = true;
            document.getElementById('installBtn').classList.add('opacity-50','cursor-not-allowed');
            document.getElementById('installProgress').classList.remove('hidden');
        }
        </script>
    </div>

    <?php

    // ═══════════════════════════════════════════════════════════════
    // STEP 5 — Complete
    // ═══════════════════════════════════════════════════════════════
    elseif ($step === 5): ?>

    <?php $log = $_SESSION['install_log'] ?? []; $fatal = $_SESSION['install_fatal'] ?? false; ?>

    <div class="px-6 py-5 border-b border-slate-700">
        <?php if (!$fatal): ?>
        <h2 class="text-lg font-bold text-green-400">✓ Installation Complete!</h2>
        <p class="text-slate-400 text-sm mt-0.5">nkhoj has been successfully installed.</p>
        <?php else: ?>
        <h2 class="text-lg font-bold text-amber-400">⚠ Installation Partially Completed</h2>
        <p class="text-slate-400 text-sm mt-0.5">Some steps require manual action.</p>
        <?php endif; ?>
    </div>

    <div class="p-6">

        {{-- Log --}}
        <div class="bg-slate-900 rounded-xl p-4 mb-5 font-mono text-xs space-y-1.5">
            <?php foreach ($log as $entry): ?>
            <div class="<?= $entry['ok'] ? 'log-ok' : 'log-fail' ?>">
                <?= $entry['ok'] ? '[OK]  ' : '[ERR] ' ?><?= htmlspecialchars($entry['msg']) ?>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($fatal): ?>
        <div class="bg-slate-700/50 border border-slate-600 rounded-xl p-4 mb-5">
            <p class="text-white text-sm font-semibold mb-2">Manual Steps Required</p>
            <p class="text-slate-400 text-xs mb-2">SSH into your server and run:</p>
            <div class="bg-slate-900 rounded-lg p-3 font-mono text-xs text-green-400">
                cd <?= htmlspecialchars(APP_ROOT) ?><br>
                php artisan key:generate --force<br>
                php artisan migrate --force<br>
                php artisan db:seed --class=AdminSeeder  <span class="text-slate-500"># optional</span>
            </div>
        </div>
        <?php else: ?>

        <div class="bg-green-900/20 border border-green-800 rounded-xl p-4 mb-5">
            <p class="text-green-300 text-sm font-semibold mb-2">Next Steps</p>
            <ul class="text-green-400 text-xs space-y-1.5 list-disc list-inside">
                <li>Log in to the admin panel at <a href="<?= htmlspecialchars(rtrim($d['app_url'] ?? '', '/')) ?>/admin" class="text-indigo-400 underline" target="_blank"><?= htmlspecialchars(rtrim($d['app_url'] ?? '', '/')) ?>/admin</a></li>
                <li>Configure site settings at <strong>/admin/settings</strong></li>
                <li>Add categories, tags, and your first post</li>
            </ul>
        </div>

        <?php endif; ?>

        <div class="bg-red-900/30 border border-red-800 rounded-xl p-4 mb-5">
            <p class="text-red-300 text-sm font-bold">🔒 Security: Delete the installer!</p>
            <p class="text-red-400 text-xs mt-1">Remove the <code class="text-red-300">public/install/</code> directory from your server immediately. Leaving it accessible is a serious security risk.</p>
            <div class="bg-slate-900 rounded-lg p-2.5 mt-2 font-mono text-xs text-yellow-400">
                rm -rf <?= htmlspecialchars(realpath(__DIR__)) ?>
            </div>
        </div>

        <?php if (!$fatal): ?>
        <a href="<?= htmlspecialchars(rtrim($d['app_url'] ?? '/', '/')) ?>/admin"
            class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-bold transition-colors flex items-center justify-center gap-2">
            Go to Admin Panel →
        </a>
        <?php endif; ?>

        <form method="POST" class="mt-3">
            <input type="hidden" name="action" value="reset">
            <button class="w-full py-2 text-slate-500 hover:text-slate-300 text-xs transition-colors">Start Over</button>
        </form>
    </div>

    <?php endif; ?>

    </div>{{-- end card --}}

    <p class="text-center text-slate-600 text-xs mt-6">
        nkhoj Installer <?= INSTALLER_VERSION ?> &nbsp;·&nbsp;
        <a href="https://laravel.com" class="hover:text-slate-400">Powered by Laravel</a>
    </p>

</div>
</body>
</html>
