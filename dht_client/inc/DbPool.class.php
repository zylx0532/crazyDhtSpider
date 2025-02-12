<?php

use Swoole\Database\PDOConfig;
use Swoole\Database\PDOPool;

class DbPool
{
    private static $pool;
    private const POOL_SIZE = 16;  // 根据服务器配置调整连接池大小

    // 初始化连接池（服务启动时调用）
    public static function initPool()
    {
        global $database_config;

        self::$pool = new PDOPool(
            (new PDOConfig())
                ->withHost($database_config['db']['host'])
                ->withPort(3306)
                ->withDbName($database_config['db']['name'])
                ->withCharset('utf8mb4')
                ->withUsername($database_config['db']['user'])
                ->withPassword($database_config['db']['pass'])
                ->withOptions([
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_STRINGIFY_FETCHES => false,
                    PDO::ATTR_TIMEOUT => 5,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]),
            self::POOL_SIZE
        );
    }

    // 获取数据库连接
    private static function getConnection()
    {
        return self::$pool->get();
    }

    // 归还数据库连接
    private static function releaseConnection($connection)
    {
        self::$pool->put($connection);
    }

    // 检查 infohash 是否存在（协程安全版本）
    public static function checkInfoHash(string $infohash): bool
    {
        $pdo = self::getConnection();
        try {
            // 使用事务保证操作原子性
            $pdo->beginTransaction();

            // 检查 history 表
            $stmt = $pdo->prepare("SELECT infohash FROM history WHERE infohash = ? LIMIT 1");
            $stmt->execute([$infohash]);
            $exists = (bool)$stmt->fetch(PDO::FETCH_ASSOC);

            if ($exists) {
                // 更新 bt 表热度
                $update = $pdo->prepare("UPDATE bt SET hot = hot + 1 WHERE infohash = ?");
                $update->execute([$infohash]);

                // 如果更新行数为0说明记录不存在（可选处理）
                if ($update->rowCount() === 0) {
                    // 这里可以添加日志记录异常情况
                }
            }

            $pdo->commit();
            return $exists;
        } catch (Throwable $e) {
            $pdo->rollBack();
            // 记录日志或处理异常
            throw new RuntimeException("Database operation failed: " . $e->getMessage());
        } finally {
            self::releaseConnection($pdo);
        }
    }

    // 健康检查（定时任务调用）
    public static function healthCheck()
    {
        $pdo = self::getConnection();
        try {
            return (bool)$pdo->query('SELECT 1');
        } catch (Throwable $e) {
            // 自动重连机制（连接池会自动创建新连接替换失效连接）
            return false;
        } finally {
            self::releaseConnection($pdo);
        }
    }
}
