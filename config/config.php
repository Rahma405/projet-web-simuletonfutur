<?php
class Config
{
    private static $pdo = null;

    public static function getConnexion(): PDO
    {
        if (!isset(self::$pdo)) {
            $host   = 'localhost';
            $dbname = 'gestion portfilio';
            $user   = 'root';
            $pass   = '';
            try {
                self::$pdo = new PDO(
                    "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                    $user,
                    $pass
                );
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                die('<div style="background:#c0392b;color:#fff;padding:20px;font-family:Poppins,Arial;border-radius:8px">
                     Erreur de connexion PDO : ' . htmlspecialchars($e->getMessage()) . '</div>');
            }
        }
        return self::$pdo;
    }
}
Config::getConnexion();
