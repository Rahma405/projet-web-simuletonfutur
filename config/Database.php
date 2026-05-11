<?php

/**
 * Database.php — Singleton PDO connection
 * Single database: simule_ton_futur
 * Used by both quiz MVC classes and user management classes (via Config alias)
 */
class Database {
    private static ?PDO $instance = null;

    private string $host   = 'localhost';
    private string $dbname = 'simule_ton_futur';
    private string $user   = 'root';
    private string $pass   = '';

    private function __construct() {}

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $db = new self();
            try {
                self::$instance = new PDO(
                    "mysql:host={$db->host};dbname={$db->dbname};charset=utf8mb4",
                    $db->user,
                    $db->pass,
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]
                );
            } catch (PDOException $e) {
                die('<div style="background:#c0392b;color:#fff;padding:20px;font-family:Arial;border-radius:8px">
                     Database connection failed: ' . htmlspecialchars($e->getMessage()) . '</div>');
            }
        }
        return self::$instance;
    }

    private function __clone() {}
    public function __wakeup() {}
}

/**
 * Config — alias class so UtilisateurC / ProfilC (which call Config::getConnexion())
 * use the same singleton PDO instance as the quiz MVC models.
 */
class Config {
    private const HCAPTCHA_SITE_KEY    = '10000000-ffff-ffff-ffff-000000000001';
    private const HCAPTCHA_SECRET_KEY  = '0x0000000000000000000000000000000000000000';

    public static function getConnexion(): PDO {
        return Database::getInstance();
    }

    public static function getHCaptchaSiteKey(): string {
        return self::HCAPTCHA_SITE_KEY;
    }

    public static function getPublicBaseUrl(): string {
        $scheme    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host      = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        $basePath  = preg_replace('#/public$#', '', $scriptDir);
        return rtrim($scheme . '://' . $host . $basePath, '/');
    }

    public static function verifyHCaptcha(string $token, string $remoteIp = ''): bool {
        if ($token === '') return false;
        $payload = http_build_query([
            'secret'   => self::HCAPTCHA_SECRET_KEY,
            'response' => $token,
            'remoteip' => $remoteIp,
            'sitekey'  => self::HCAPTCHA_SITE_KEY,
        ]);
        $ctx    = stream_context_create(['http' => ['method' => 'POST',
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => $payload, 'timeout' => 10]]);
        $result = @file_get_contents('https://api.hcaptcha.com/siteverify', false, $ctx);
        if ($result === false) return false;
        $data = json_decode($result, true);
        return !empty($data['success']);
    }

    // Self-healing: create extra tables if missing
    public static function ensureTables(): void {
        $pdo = self::getConnexion();
        $pdo->exec("ALTER TABLE utilisateur ADD COLUMN IF NOT EXISTS statut
                    ENUM('actif','bloque','en_attente') NOT NULL DEFAULT 'actif'");
        $pdo->exec("CREATE TABLE IF NOT EXISTS password_reset (
            idReset INT AUTO_INCREMENT PRIMARY KEY,
            idUtilisateur INT NOT NULL,
            tokenHash VARCHAR(255) NOT NULL,
            expireAt DATETIME NOT NULL,
            createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (idUtilisateur) REFERENCES utilisateur(idUtilisateur) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("CREATE TABLE IF NOT EXISTS qr_login (
            idQrLogin INT AUTO_INCREMENT PRIMARY KEY,
            idUtilisateur INT NOT NULL,
            tokenHash VARCHAR(255) NOT NULL,
            status ENUM('pending','approved','used','expired') NOT NULL DEFAULT 'pending',
            expireAt DATETIME NOT NULL,
            createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (idUtilisateur) REFERENCES utilisateur(idUtilisateur) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("CREATE TABLE IF NOT EXISTS quiz_result (
            id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            quiz_id   INT UNSIGNED NOT NULL,
            user_id   INT NOT NULL,
            score     INT NOT NULL DEFAULT 0,
            total     INT NOT NULL DEFAULT 0,
            passed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (quiz_id) REFERENCES quiz(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES utilisateur(idUtilisateur) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("CREATE TABLE IF NOT EXISTS face_descriptors (
            idFaceDescriptor INT AUTO_INCREMENT PRIMARY KEY,
            idUtilisateur INT NOT NULL,
            descriptorJson LONGTEXT NOT NULL,
            createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_face_user (idUtilisateur),
            FOREIGN KEY (idUtilisateur) REFERENCES utilisateur(idUtilisateur) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
}
