<?php
/**
 * config.php — Connexion PDO (Singleton)
 * Simule Ton Futur | ESPRIT UP Web 2025/2026
 */
class Config
{
    private static $pdo = null;
    private const HCAPTCHA_SITE_KEY = '10000000-ffff-ffff-ffff-000000000001';
    private const HCAPTCHA_SECRET_KEY = '0x0000000000000000000000000000000000000000';
    private const APP_PUBLIC_URL = 'http://192.168.100.8/simule_ton_futur/simule_ton_futur';

    public static function getConnexion(): PDO
    {
        if (!isset(self::$pdo)) {
            $host   = 'localhost';
            $dbname = 'simule_ton_futur';
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
                self::ensureUtilisateurStatutColumn();
                self::ensurePasswordResetTable();
                self::ensureQrLoginTable();
            } catch (Exception $e) {
                die('<div style="background:#c0392b;color:#fff;padding:20px;font-family:Arial;border-radius:8px">
                     ❌ Erreur de connexion PDO : ' . htmlspecialchars($e->getMessage()) . '</div>');
            }
        }
        return self::$pdo;
    }

    private static function ensureUtilisateurStatutColumn(): void
    {
        $check = self::$pdo->query("SHOW COLUMNS FROM utilisateur LIKE 'statut'");
        if ($check && !$check->fetch()) {
            self::$pdo->exec("ALTER TABLE utilisateur ADD COLUMN statut ENUM('actif','bloque','en_attente') NOT NULL DEFAULT 'actif' AFTER role");
            self::$pdo->exec("UPDATE utilisateur SET statut = 'actif' WHERE statut IS NULL");
        }
    }

    private static function ensurePasswordResetTable(): void
    {
        self::$pdo->exec(
            "CREATE TABLE IF NOT EXISTS password_reset (
                idReset INT AUTO_INCREMENT PRIMARY KEY,
                idUtilisateur INT NOT NULL,
                tokenHash VARCHAR(255) NOT NULL,
                expireAt DATETIME NOT NULL,
                createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (idUtilisateur) REFERENCES utilisateur(idUtilisateur) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    private static function ensureQrLoginTable(): void
    {
        self::$pdo->exec(
            "CREATE TABLE IF NOT EXISTS qr_login (
                idQrLogin INT AUTO_INCREMENT PRIMARY KEY,
                idUtilisateur INT NOT NULL,
                tokenHash VARCHAR(255) NOT NULL,
                status ENUM('pending','approved','used','expired') NOT NULL DEFAULT 'pending',
                expireAt DATETIME NOT NULL,
                createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (idUtilisateur) REFERENCES utilisateur(idUtilisateur) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    public static function getHCaptchaSiteKey(): string
    {
        return self::HCAPTCHA_SITE_KEY;
    }

    public static function getPublicBaseUrl(): string
    {
        if (self::APP_PUBLIC_URL !== '') {
            return rtrim(self::APP_PUBLIC_URL, '/');
        }

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        $basePath = preg_replace('#/view(?:/.*)?$#', '', $scriptDir);
        $basePath = rtrim((string) $basePath, '/');

        $hostOnly = preg_replace('/:\d+$/', '', $host);
        if (in_array($hostOnly, ['localhost', '127.0.0.1', '::1'], true)) {
            $lanIp = @gethostbyname((string) gethostname());
            if (
                $lanIp &&
                filter_var($lanIp, FILTER_VALIDATE_IP) &&
                !in_array($lanIp, ['127.0.0.1', '0.0.0.0'], true)
            ) {
                $port = '';
                if (preg_match('/:(\d+)$/', $host, $matches) === 1 && $matches[1] !== '80') {
                    $port = ':' . $matches[1];
                }
                $host = $lanIp . $port;
            }
        }

        return $scheme . '://' . $host . $basePath;
    }

    public static function verifyHCaptcha(string $token, string $remoteIp = ''): bool
    {
        if ($token === '') {
            return false;
        }

        $payload = http_build_query([
            'secret' => self::HCAPTCHA_SECRET_KEY,
            'response' => $token,
            'remoteip' => $remoteIp,
            'sitekey' => self::HCAPTCHA_SITE_KEY,
        ]);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => $payload,
                'timeout' => 10,
            ],
        ]);

        $result = @file_get_contents('https://api.hcaptcha.com/siteverify', false, $context);
        if ($result === false) {
            return false;
        }

        $data = json_decode($result, true);
        return !empty($data['success']);
    }
}
Config::getConnexion();

if (!function_exists('stf_pdf_escape')) {
    function stf_pdf_escape(string $text): string
    {
        $encoded = iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $text);
        if ($encoded === false) {
            $encoded = $text;
        }

        return str_replace(
            ['\\', '(', ')', "\r", "\n"],
            ['\\\\', '\\(', '\\)', '', ' '],
            $encoded
        );
    }
}

if (!function_exists('stf_pdf_text')) {
    function stf_pdf_text(int $x, int $y, int $size, string $text, string $font = 'F1'): string
    {
        return "BT\n/{$font} {$size} Tf\n{$x} {$y} Td\n(" . stf_pdf_escape($text) . ") Tj\nET\n";
    }
}

if (!function_exists('stf_pdf_fill_color')) {
    function stf_pdf_fill_color(float $r, float $g, float $b): string
    {
        return sprintf("%.3F %.3F %.3F rg\n", $r, $g, $b);
    }
}

if (!function_exists('stf_pdf_rect')) {
    function stf_pdf_rect(int $x, int $y, int $width, int $height): string
    {
        return "{$x} {$y} {$width} {$height} re f\n";
    }
}

if (!function_exists('stf_stream_simple_pdf')) {
    function stf_stream_simple_pdf(string $filename, string $title, array $lines): void
    {
        $pages = [];
        $lineIndex = 0;
        $totalLines = count($lines);

        while ($lineIndex < $totalLines || empty($pages)) {
            $content = '';
            $y = 735;

            $content .= stf_pdf_fill_color(0.11, 0.17, 0.31);
            $content .= stf_pdf_rect(32, 742, 531, 66);
            $content .= stf_pdf_fill_color(0.90, 0.22, 0.27);
            $content .= stf_pdf_rect(32, 742, 8, 66);
            $content .= stf_pdf_fill_color(1, 1, 1);
            $content .= stf_pdf_text(56, 783, 18, $title, 'F2');
            $content .= stf_pdf_text(56, 762, 10, 'Simule Ton Futur', 'F1');

            $content .= stf_pdf_fill_color(0.96, 0.97, 0.99);
            $content .= stf_pdf_rect(32, 706, 531, 24);
            $content .= stf_pdf_fill_color(0.11, 0.17, 0.31);
            $content .= stf_pdf_text(48, 714, 10, 'Resultats exportes', 'F2');

            $y = 676;
            $linesPerPage = 24;
            for ($count = 0; $count < $linesPerPage && $lineIndex < $totalLines; $count++, $lineIndex++) {
                $line = mb_substr((string) $lines[$lineIndex], 0, 105);
                $rowY = $y - ($count * 24);
                $bg = $count % 2 === 0 ? [0.98, 0.98, 1.00] : [0.94, 0.96, 0.99];
                $content .= stf_pdf_fill_color($bg[0], $bg[1], $bg[2]);
                $content .= stf_pdf_rect(32, $rowY - 6, 531, 18);
                $content .= stf_pdf_fill_color(0.17, 0.20, 0.29);
                $content .= stf_pdf_text(44, $rowY, 10, $line, 'F1');
            }

            $content .= stf_pdf_fill_color(0.52, 0.58, 0.70);
            $content .= stf_pdf_text(32, 32, 9, 'Document genere automatiquement', 'F1');
            $pages[] = $content;
        }

        $objects = [];
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
        $objects[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>';

        $kids = [];
        $nextObject = 5;

        foreach ($pages as $content) {
            $pageObject = $nextObject++;
            $contentObject = $nextObject++;
            $kids[] = $pageObject . ' 0 R';

            $objects[$pageObject] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents ' . $contentObject . ' 0 R >>';
            $objects[$contentObject] = "<< /Length " . strlen($content) . " >>\nstream\n" . $content . "endstream";
        }

        $objects[2] = '<< /Type /Pages /Count ' . count($pages) . ' /Kids [ ' . implode(' ', $kids) . ' ] >>';

        ksort($objects);
        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $number => $object) {
            $offsets[$number] = strlen($pdf);
            $pdf .= $number . " 0 obj\n" . $object . "\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $maxObject = max(array_keys($objects));

        $pdf .= "xref\n0 " . ($maxObject + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= $maxObject; $i++) {
            $offset = $offsets[$i] ?? 0;
            $pdf .= sprintf('%010d 00000 n ', $offset) . "\n";
        }

        $pdf .= "trailer\n<< /Size " . ($maxObject + 1) . " /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF";

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
        exit;
    }
}
