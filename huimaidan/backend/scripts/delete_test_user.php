<?php

declare(strict_types=1);

/**
 * Delete one test user and its WeChat binding for login/register retesting.
 *
 * Preview:
 *   php scripts/delete_test_user.php
 *
 * Execute:
 *   php scripts/delete_test_user.php --force
 *
 * Custom target:
 *   php scripts/delete_test_user.php --uid=2 --wechat-user-id=1 --account=wx11781083584 --force
 */

const DEFAULT_UID = 5;
const DEFAULT_WECHAT_USER_ID = 4;
const DEFAULT_ACCOUNT = 'wx41781142107';

main($argv);

function main(array $argv): void
{
    $options = parseOptions($argv);
    $uid = (int)($options['uid'] ?? DEFAULT_UID);
    $wechatUserId = (int)($options['wechat-user-id'] ?? DEFAULT_WECHAT_USER_ID);
    $account = (string)($options['account'] ?? DEFAULT_ACCOUNT);
    $force = isset($options['force']);
    $allowBusinessData = isset($options['allow-business-data']);

    if ($uid <= 0 || $wechatUserId <= 0 || $account === '') {
        fail('参数不完整：uid、wechat-user-id、account 都必须有效。');
    }

    $root = dirname(__DIR__);
    $env = parseEnvFile($root . DIRECTORY_SEPARATOR . '.env');
    $db = readDatabaseConfig($env);
    $prefix = $db['prefix'];

    $pdo = connect($db);
    $pdo->beginTransaction();

    try {
        $userTable = table($prefix, 'user');
        $wechatUserTable = table($prefix, 'wechat_user');

        $user = fetchOne(
            $pdo,
            "SELECT * FROM {$userTable} WHERE `uid` = :uid FOR UPDATE",
            ['uid' => $uid]
        );
        if (!$user) {
            fail("未找到 eb_user.uid={$uid} 的用户。");
        }
        if ((string)$user['account'] !== $account) {
            fail("账号不匹配：期望 {$account}，实际 {$user['account']}。");
        }
        if ((int)$user['wechat_user_id'] !== $wechatUserId) {
            fail("wechat_user_id 不匹配：期望 {$wechatUserId}，实际 {$user['wechat_user_id']}。");
        }

        $wechatUser = fetchOne(
            $pdo,
            "SELECT * FROM {$wechatUserTable} WHERE `wechat_user_id` = :wechat_user_id FOR UPDATE",
            ['wechat_user_id' => $wechatUserId]
        );
        if (!$wechatUser) {
            fail("未找到 eb_wechat_user.wechat_user_id={$wechatUserId} 的微信用户。");
        }

        printLine('目标用户：');
        printLine(sprintf(
            '  uid=%d account=%s phone=%s nickname=%s user_type=%s wechat_user_id=%d',
            $uid,
            (string)$user['account'],
            (string)($user['phone'] ?? ''),
            (string)$user['nickname'],
            (string)$user['user_type'],
            $wechatUserId
        ));
        printLine(sprintf(
            '  routine_openid=%s unionid=%s openid=%s',
            mask((string)($wechatUser['routine_openid'] ?? '')),
            mask((string)($wechatUser['unionid'] ?? '')),
            mask((string)($wechatUser['openid'] ?? ''))
        ));

        $businessCounts = countExistingRows($pdo, $prefix, [
            'store_order' => ['uid' => $uid],
            'store_refund_order' => ['uid' => $uid],
            'user_order' => ['uid' => $uid],
            'user_recharge' => ['uid' => $uid],
            'user_extract' => ['uid' => $uid],
            'circle_agent' => ['uid' => $uid],
        ]);

        $businessRows = array_sum($businessCounts);
        if ($businessRows > 0 && !$allowBusinessData) {
            printLine('检测到业务数据，默认拒绝删除：');
            foreach ($businessCounts as $name => $count) {
                if ($count > 0) {
                    printLine("  {$prefix}{$name}: {$count}");
                }
            }
            fail('如确认这是测试数据，可追加 --allow-business-data --force。');
        }

        $cleanupTables = [
            'store_cart' => ['uid' => $uid],
            'store_coupon_user' => ['uid' => $uid],
            'user_address' => ['uid' => $uid],
            'user_bill' => ['uid' => $uid],
            'user_fields' => ['uid' => $uid],
            'user_history' => ['uid' => $uid],
            'user_merchant' => ['uid' => $uid],
            'user_receipt' => ['uid' => $uid],
            'user_relation' => ['uid' => $uid],
            'user_sign' => ['uid' => $uid],
            'user_spread_log' => ['uid' => $uid],
            'user_visit' => ['uid' => $uid],
        ];

        if ($allowBusinessData) {
            $cleanupTables += [
                'circle_agent' => ['uid' => $uid],
                'store_order' => ['uid' => $uid],
                'store_refund_order' => ['uid' => $uid],
                'user_extract' => ['uid' => $uid],
                'user_order' => ['uid' => $uid],
                'user_recharge' => ['uid' => $uid],
            ];
        }

        $deleteCounts = [];
        foreach ($cleanupTables as $name => $where) {
            $deleteCounts[$name] = deleteIfTableExists($pdo, $prefix, $name, $where);
        }

        $deleteCounts['user'] = deleteExact(
            $pdo,
            $userTable,
            ['uid' => $uid, 'wechat_user_id' => $wechatUserId, 'account' => $account]
        );
        if ($deleteCounts['user'] !== 1) {
            fail("删除 eb_user 失败：期望删除 1 行，实际 {$deleteCounts['user']} 行。");
        }

        $linkedUserCount = (int)fetchValue(
            $pdo,
            "SELECT COUNT(*) FROM {$userTable} WHERE `wechat_user_id` = :wechat_user_id",
            ['wechat_user_id' => $wechatUserId]
        );
        if ($linkedUserCount > 0) {
            fail("仍有 {$linkedUserCount} 个 eb_user 绑定 wechat_user_id={$wechatUserId}，拒绝删除 eb_wechat_user。");
        }

        $deleteCounts['wechat_user'] = deleteExact(
            $pdo,
            $wechatUserTable,
            ['wechat_user_id' => $wechatUserId]
        );
        if ($deleteCounts['wechat_user'] !== 1) {
            fail("删除 eb_wechat_user 失败：期望删除 1 行，实际 {$deleteCounts['wechat_user']} 行。");
        }

        printLine($force ? '删除结果：' : '预览结果（未提交）：');
        foreach ($deleteCounts as $name => $count) {
            if ($count > 0) {
                printLine("  {$prefix}{$name}: {$count}");
            }
        }

        if ($force) {
            $pdo->commit();
            printLine('已提交删除。请退出小程序当前登录态后重新注册登录测试。');
        } else {
            $pdo->rollBack();
            printLine('未执行删除。确认无误后运行：php scripts/delete_test_user.php --force');
        }
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        fail($e->getMessage());
    }
}

function parseOptions(array $argv): array
{
    $options = [];
    foreach (array_slice($argv, 1) as $arg) {
        if (!str_starts_with($arg, '--')) {
            fail("无法识别参数：{$arg}");
        }
        $arg = substr($arg, 2);
        if (str_contains($arg, '=')) {
            [$key, $value] = explode('=', $arg, 2);
            $options[$key] = $value;
        } else {
            $options[$arg] = true;
        }
    }
    return $options;
}

function parseEnvFile(string $path): array
{
    if (!is_file($path)) {
        fail("未找到 .env 文件：{$path}");
    }

    $env = [];
    $section = '';
    foreach (file($path, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (preg_match('/^\[(.+)]$/', $line, $match)) {
            $section = strtolower(trim($match[1]));
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = strtolower(trim($key));
        $value = trim($value);
        $value = trim($value, "\"'");
        $env[$section ? "{$section}.{$key}" : $key] = $value;
    }

    return $env;
}

function readDatabaseConfig(array $env): array
{
    $required = ['database.hostname', 'database.hostport', 'database.username', 'database.database'];
    foreach ($required as $key) {
        if (($env[$key] ?? '') === '') {
            fail(".env 缺少数据库配置：{$key}");
        }
    }

    return [
        'host' => $env['database.hostname'],
        'port' => $env['database.hostport'],
        'username' => $env['database.username'],
        'password' => $env['database.password'] ?? '',
        'database' => $env['database.database'],
        'charset' => $env['database.charset'] ?? 'utf8mb4',
        'prefix' => $env['database.prefix'] ?? '',
    ];
}

function connect(array $db): PDO
{
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $db['host'],
        $db['port'],
        $db['database'],
        $db['charset']
    );

    return new PDO($dsn, $db['username'], $db['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

function table(string $prefix, string $name): string
{
    return quoteIdentifier($prefix . $name);
}

function quoteIdentifier(string $identifier): string
{
    return '`' . str_replace('`', '``', $identifier) . '`';
}

function tableExists(PDO $pdo, string $prefix, string $name): bool
{
    $fullName = $prefix . $name;
    $stmt = $pdo->prepare('SHOW TABLES LIKE :name');
    $stmt->execute(['name' => $fullName]);
    return (bool)$stmt->fetchColumn();
}

function fetchOne(PDO $pdo, string $sql, array $params = []): ?array
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row ?: null;
}

function fetchValue(PDO $pdo, string $sql, array $params = []): mixed
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

function countExistingRows(PDO $pdo, string $prefix, array $tables): array
{
    $counts = [];
    foreach ($tables as $name => $where) {
        if (!tableExists($pdo, $prefix, $name)) {
            $counts[$name] = 0;
            continue;
        }
        $counts[$name] = (int)fetchValue(
            $pdo,
            'SELECT COUNT(*) FROM ' . table($prefix, $name) . whereClause($where),
            $where
        );
    }
    return $counts;
}

function deleteIfTableExists(PDO $pdo, string $prefix, string $name, array $where): int
{
    if (!tableExists($pdo, $prefix, $name)) {
        return 0;
    }
    return deleteExact($pdo, table($prefix, $name), $where);
}

function deleteExact(PDO $pdo, string $table, array $where): int
{
    $stmt = $pdo->prepare('DELETE FROM ' . $table . whereClause($where));
    $stmt->execute($where);
    return $stmt->rowCount();
}

function whereClause(array $where): string
{
    if (!$where) {
        fail('拒绝无条件 SQL。');
    }
    $parts = [];
    foreach (array_keys($where) as $column) {
        $parts[] = quoteIdentifier((string)$column) . ' = :' . $column;
    }
    return ' WHERE ' . implode(' AND ', $parts);
}

function mask(string $value): string
{
    if ($value === '') {
        return '';
    }
    if (strlen($value) <= 10) {
        return substr($value, 0, 2) . '***';
    }
    return substr($value, 0, 6) . '***' . substr($value, -4);
}

function printLine(string $message): void
{
    fwrite(STDOUT, $message . PHP_EOL);
}

function fail(string $message): never
{
    fwrite(STDERR, '错误：' . $message . PHP_EOL);
    exit(1);
}
