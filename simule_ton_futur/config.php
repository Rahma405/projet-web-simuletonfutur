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
    private const APP_PUBLIC_URL = 'http://172.20.10.3/simule_ton_futur/simule_ton_futur';

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
                self::ensureFaceDescriptorsTable();
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

    private static function ensureFaceDescriptorsTable(): void
    {
        self::$pdo->exec(
            "CREATE TABLE IF NOT EXISTS face_descriptors (
                idFaceDescriptor INT AUTO_INCREMENT PRIMARY KEY,
                idUtilisateur INT NOT NULL,
                descriptorJson LONGTEXT NOT NULL,
                createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_face_user (idUtilisateur),
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

if (!function_exists('stf_language_code_from_value')) {
    function stf_language_code_from_value(?string $value): string
    {
        return match ($value) {
            'Anglais' => 'en',
            'Espagnol' => 'es',
            'Arabe' => 'ar',
            'Allemand' => 'de',
            'Francais', 'Français', 'FranÃ§ais' => 'fr',
            default => 'fr',
        };
    }
}

if (!function_exists('stf_current_lang')) {
    function stf_current_lang(): string
    {
        $lang = $_SESSION['site_lang'] ?? 'fr';
        return in_array($lang, ['fr', 'en', 'es', 'ar', 'de'], true) ? $lang : 'fr';
    }
}

if (!function_exists('stf_is_rtl')) {
    function stf_is_rtl(): bool
    {
        return stf_current_lang() === 'ar';
    }
}

if (!function_exists('stf_t')) {
    function stf_t(string $key): string
    {
        $translations = [
            'fr' => [
                'home' => 'Accueil',
                'register' => "S'inscrire",
                'login' => 'Connexion',
                'logout' => 'Deconnexion',
                'admin' => 'Admin',
                'hero_badge' => 'Plateforme Interactive',
                'hero_title_before' => 'Simule',
                'hero_title_highlight' => 'Ton Futur',
                'hero_title_after' => 'Professionnel',
                'hero_text_1' => 'Decouvre le monde du travail moderne en faisant des choix reels.',
                'hero_text_2' => "Gagne de l'argent, de l'experience et de la reputation.",
                'start' => 'Commencer',
                'users_registered' => 'Utilisateurs inscrits',
                'administrators' => 'Administrateurs',
                'profiles_created' => 'Profils crees',
                'avg_completion' => 'Completion moyenne des profils',
                'full_profiles' => 'Profils complets',
                'top_city' => 'Ville la plus representee',
                'best_profile' => 'Profil le plus complet',
                'choose_path' => 'Choisis ton parcours',
                'path_subtitle' => 'Chaque choix donne un resultat different',
                'community' => 'Communaute',
                'join' => 'Rejoindre',
                'no_profiles' => 'Aucun profil pour le moment.',
                'login_title' => 'Se connecter',
                'login_subtitle' => 'Accedez a votre espace Simule Ton Futur',
                'login_intro' => 'Entrez vos identifiants ci-dessous',
                'create_account' => 'Creer un compte',
                'no_account' => 'Pas encore de compte ?',
                'register_title' => 'Creer mon compte',
                'register_subtitle' => 'Rejoins la communaute Simule Ton Futur',
                'my_account' => 'Mon compte',
                'email' => 'Email',
                'password' => 'Mot de passe',
                'classic_login' => 'Retour a la connexion classique',
                'face_login' => 'Connexion Face ID',
                'face_register' => 'Activer Face ID',
                'webcam_on' => 'Activer la webcam',
                'scan_face' => 'Scanner mon visage',
                'save_face' => 'Enregistrer Face ID',
                'disable_face' => 'Desactiver Face ID',
                'first_name' => 'Prenom',
                'last_name' => 'Nom',
                'role' => 'Role',
                'user_role' => 'Utilisateur',
                'bio' => 'Bio',
                'city' => 'Ville',
                'country' => 'Pays',
                'language' => 'Langue',
                'choose' => '-- Choisir --',
                'save' => 'Enregistrer',
                'cancel' => 'Annuler',
                'back_home' => "Retour a l'accueil",
                'account_info' => 'Informations du compte',
                'profile_info' => 'Informations du profil (optionnel)',
                'complete_profile' => 'Completer mon profil',
                'edit_profile' => 'Modifier mon profil',
                'profile_photo' => 'Photo de profil',
            ],
            'en' => [
                'home' => 'Home',
                'register' => 'Register',
                'login' => 'Login',
                'logout' => 'Logout',
                'admin' => 'Admin',
                'hero_badge' => 'Interactive Platform',
                'hero_title_before' => 'Simulate',
                'hero_title_highlight' => 'Your Future',
                'hero_title_after' => 'Career',
                'hero_text_1' => 'Discover the modern world of work through real choices.',
                'hero_text_2' => 'Earn money, experience and reputation.',
                'start' => 'Get started',
                'users_registered' => 'Registered users',
                'administrators' => 'Administrators',
                'profiles_created' => 'Profiles created',
                'avg_completion' => 'Average profile completion',
                'full_profiles' => 'Complete profiles',
                'top_city' => 'Top city',
                'best_profile' => 'Most complete profile',
                'choose_path' => 'Choose your path',
                'path_subtitle' => 'Each choice leads to a different result',
                'community' => 'Community',
                'join' => 'Join',
                'no_profiles' => 'No profiles yet.',
                'login_title' => 'Sign in',
                'login_subtitle' => 'Access your Simule Ton Futur space',
                'login_intro' => 'Enter your credentials below',
                'create_account' => 'Create an account',
                'no_account' => "Don't have an account yet?",
                'register_title' => 'Create my account',
                'register_subtitle' => 'Join the Simule Ton Futur community',
                'my_account' => 'My account',
                'email' => 'Email',
                'password' => 'Password',
                'classic_login' => 'Back to classic login',
                'face_login' => 'Face ID login',
                'face_register' => 'Enable Face ID',
                'webcam_on' => 'Turn on webcam',
                'scan_face' => 'Scan my face',
                'save_face' => 'Save Face ID',
                'disable_face' => 'Disable Face ID',
                'first_name' => 'First name',
                'last_name' => 'Last name',
                'role' => 'Role',
                'user_role' => 'User',
                'bio' => 'Bio',
                'city' => 'City',
                'country' => 'Country',
                'language' => 'Language',
                'choose' => '-- Choose --',
                'save' => 'Save',
                'cancel' => 'Cancel',
                'back_home' => 'Back to home',
                'account_info' => 'Account information',
                'profile_info' => 'Profile information (optional)',
                'complete_profile' => 'Complete my profile',
                'edit_profile' => 'Edit my profile',
                'profile_photo' => 'Profile photo',
            ],
            'es' => [
                'home' => 'Inicio',
                'register' => 'Registrarse',
                'login' => 'Conexion',
                'logout' => 'Cerrar sesion',
                'admin' => 'Admin',
                'hero_badge' => 'Plataforma interactiva',
                'hero_title_before' => 'Simula',
                'hero_title_highlight' => 'Tu Futuro',
                'hero_title_after' => 'Profesional',
                'hero_text_1' => 'Descubre el mundo laboral moderno tomando decisiones reales.',
                'hero_text_2' => 'Gana dinero, experiencia y reputacion.',
                'start' => 'Comenzar',
                'users_registered' => 'Usuarios registrados',
                'administrators' => 'Administradores',
                'profiles_created' => 'Perfiles creados',
                'avg_completion' => 'Promedio de perfil completado',
                'full_profiles' => 'Perfiles completos',
                'top_city' => 'Ciudad principal',
                'best_profile' => 'Perfil mas completo',
                'choose_path' => 'Elige tu camino',
                'path_subtitle' => 'Cada eleccion da un resultado diferente',
                'community' => 'Comunidad',
                'join' => 'Unirse',
                'no_profiles' => 'Todavia no hay perfiles.',
                'login_title' => 'Iniciar sesion',
                'login_subtitle' => 'Accede a tu espacio Simule Ton Futur',
                'login_intro' => 'Introduce tus credenciales abajo',
                'create_account' => 'Crear una cuenta',
                'no_account' => 'Aun no tienes cuenta?',
                'register_title' => 'Crear mi cuenta',
                'register_subtitle' => 'Unete a la comunidad Simule Ton Futur',
                'my_account' => 'Mi cuenta',
                'email' => 'Correo electronico',
                'password' => 'Contrasena',
                'classic_login' => 'Volver al acceso clasico',
                'face_login' => 'Acceso Face ID',
                'face_register' => 'Activar Face ID',
                'webcam_on' => 'Activar la camara',
                'scan_face' => 'Escanear mi rostro',
                'save_face' => 'Guardar Face ID',
                'disable_face' => 'Desactivar Face ID',
                'first_name' => 'Nombre',
                'last_name' => 'Apellido',
                'role' => 'Rol',
                'user_role' => 'Usuario',
                'bio' => 'Biografia',
                'city' => 'Ciudad',
                'country' => 'Pais',
                'language' => 'Idioma',
                'choose' => '-- Elegir --',
                'save' => 'Guardar',
                'cancel' => 'Cancelar',
                'back_home' => 'Volver al inicio',
                'account_info' => 'Informacion de la cuenta',
                'profile_info' => 'Informacion del perfil (opcional)',
                'complete_profile' => 'Completar mi perfil',
                'edit_profile' => 'Editar mi perfil',
                'profile_photo' => 'Foto de perfil',
            ],
            'ar' => [
                'home' => 'الرئيسية',
                'register' => 'إنشاء حساب',
                'login' => 'تسجيل الدخول',
                'logout' => 'تسجيل الخروج',
                'admin' => 'الإدارة',
                'hero_badge' => 'منصة تفاعلية',
                'hero_title_before' => 'حاكي',
                'hero_title_highlight' => 'مستقبلك',
                'hero_title_after' => 'المهني',
                'hero_text_1' => 'اكتشف عالم العمل الحديث من خلال اختيارات حقيقية.',
                'hero_text_2' => 'اكسب المال والخبرة والسمعة.',
                'start' => 'ابدأ',
                'users_registered' => 'المستخدمون المسجلون',
                'administrators' => 'المسؤولون',
                'profiles_created' => 'الملفات الشخصية',
                'avg_completion' => 'متوسط اكتمال الملفات',
                'full_profiles' => 'ملفات كاملة',
                'top_city' => 'المدينة الاكثر تمثيلا',
                'best_profile' => 'الملف الاكثر اكتمالا',
                'choose_path' => 'اختر مسارك',
                'path_subtitle' => 'كل اختيار يعطي نتيجة مختلفة',
                'community' => 'المجتمع',
                'join' => 'انضم',
                'no_profiles' => 'لا توجد ملفات حاليا.',
                'login_title' => 'تسجيل الدخول',
                'login_subtitle' => 'ادخل إلى مساحة Simule Ton Futur',
                'login_intro' => 'ادخل معلوماتك في الاسفل',
                'create_account' => 'إنشاء حساب',
                'no_account' => 'ليس لديك حساب؟',
                'register_title' => 'إنشاء حسابي',
                'register_subtitle' => 'انضم إلى مجتمع Simule Ton Futur',
                'my_account' => 'حسابي',
                'email' => 'البريد الإلكتروني',
                'password' => 'كلمة المرور',
                'classic_login' => 'العودة إلى الدخول العادي',
                'face_login' => 'تسجيل الدخول Face ID',
                'face_register' => 'تفعيل Face ID',
                'webcam_on' => 'تشغيل الكاميرا',
                'scan_face' => 'مسح وجهي',
                'save_face' => 'حفظ Face ID',
                'disable_face' => 'تعطيل Face ID',
                'first_name' => 'الاسم',
                'last_name' => 'اللقب',
                'role' => 'الدور',
                'user_role' => 'مستخدم',
                'bio' => 'نبذة',
                'city' => 'المدينة',
                'country' => 'البلد',
                'language' => 'اللغة',
                'choose' => '-- اختر --',
                'save' => 'حفظ',
                'cancel' => 'إلغاء',
                'back_home' => 'العودة إلى الرئيسية',
                'account_info' => 'معلومات الحساب',
                'profile_info' => 'معلومات الملف الشخصي (اختياري)',
                'complete_profile' => 'أكمل ملفي',
                'edit_profile' => 'تعديل ملفي',
                'profile_photo' => 'صورة الملف الشخصي',
            ],
            'de' => [
                'home' => 'Startseite',
                'register' => 'Registrieren',
                'login' => 'Anmelden',
                'logout' => 'Abmelden',
                'admin' => 'Admin',
                'hero_badge' => 'Interaktive Plattform',
                'hero_title_before' => 'Simuliere',
                'hero_title_highlight' => 'Deine Zukunft',
                'hero_title_after' => 'Beruflich',
                'hero_text_1' => 'Entdecke die moderne Arbeitswelt durch echte Entscheidungen.',
                'hero_text_2' => 'Sammle Geld, Erfahrung und Ansehen.',
                'start' => 'Starten',
                'users_registered' => 'Registrierte Benutzer',
                'administrators' => 'Administratoren',
                'profiles_created' => 'Erstellte Profile',
                'avg_completion' => 'Durchschnittliche Profilvollstandigkeit',
                'full_profiles' => 'Vollstandige Profile',
                'top_city' => 'Top-Stadt',
                'best_profile' => 'Bestes Profil',
                'choose_path' => 'Wahle deinen Weg',
                'path_subtitle' => 'Jede Wahl fuhrt zu einem anderen Ergebnis',
                'community' => 'Community',
                'join' => 'Beitreten',
                'no_profiles' => 'Noch keine Profile vorhanden.',
                'login_title' => 'Anmeldung',
                'login_subtitle' => 'Greife auf deinen Simule Ton Futur Bereich zu',
                'login_intro' => 'Gib unten deine Zugangsdaten ein',
                'create_account' => 'Konto erstellen',
                'no_account' => 'Noch kein Konto?',
                'register_title' => 'Mein Konto erstellen',
                'register_subtitle' => 'Tritt der Simule Ton Futur Community bei',
                'my_account' => 'Mein Konto',
                'email' => 'E-Mail',
                'password' => 'Passwort',
                'classic_login' => 'Zuruck zur klassischen Anmeldung',
                'face_login' => 'Face ID Anmeldung',
                'face_register' => 'Face ID aktivieren',
                'webcam_on' => 'Webcam aktivieren',
                'scan_face' => 'Mein Gesicht scannen',
                'save_face' => 'Face ID speichern',
                'disable_face' => 'Face ID deaktivieren',
                'first_name' => 'Vorname',
                'last_name' => 'Name',
                'role' => 'Rolle',
                'user_role' => 'Benutzer',
                'bio' => 'Bio',
                'city' => 'Stadt',
                'country' => 'Land',
                'language' => 'Sprache',
                'choose' => '-- Wahlen --',
                'save' => 'Speichern',
                'cancel' => 'Abbrechen',
                'back_home' => 'Zur Startseite',
                'account_info' => 'Kontoinformationen',
                'profile_info' => 'Profilinformationen (optional)',
                'complete_profile' => 'Mein Profil vervollstandigen',
                'edit_profile' => 'Mein Profil bearbeiten',
                'profile_photo' => 'Profilbild',
            ],
        ];

        $lang = stf_current_lang();
        return $translations[$lang][$key] ?? $translations['fr'][$key] ?? $key;
    }
}

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
