<?php

/**
 * UserController — bridges the quiz MVC router to the
 * UtilisateurC / ProfilC controllers from the user management project.
 * All actual logic stays in UtilisateurC and ProfilC.
 */
class UserController extends Controller {

    // Helper: compute base URL for views that need it
    private function baseUrl(): string {
        $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        return rtrim(preg_replace('#/public$#', '', $dir), '/');
    }

    // ── Auth pages ────────────────────────────────────────────────────────────
    public function login(): void    { $this->renderUser('user/login'); }
    public function register(): void { $this->renderUser('user/register'); }
    public function logout(): void   { $this->renderUser('user/logout'); }
    public function faceLogin(): void   { $this->renderUser('user/face_login'); }
    public function faceRegister(): void { $this->renderUser('user/face_register'); }
    public function resetPassword(): void { $this->renderUser('user/reset_password'); }

    // ── Profile ───────────────────────────────────────────────────────────────
    public function editAccount(): void { $this->renderUser('user/edit_utilisateur'); }
    public function editProfile(): void { $this->renderUser('user/edit_profil'); }

    // ── Admin: users ──────────────────────────────────────────────────────────
    public function adminUsers(): void    { $this->renderUser('admin/list_utilisateurs'); }
    public function adminAddUser(): void  { $this->renderUser('admin/add_utilisateur'); }
    public function adminEditUser(): void { $this->renderUser('admin/edit_utilisateur'); }
    public function adminDelUser(): void  { $this->renderUser('admin/delete_utilisateur'); }

    // ── Admin: profiles ───────────────────────────────────────────────────────
    public function adminProfiles(): void   { $this->renderUser('admin/list_profils'); }
    public function adminAddProfile(): void  { $this->renderUser('admin/add_profil'); }
    public function adminEditProfile(): void { $this->renderUser('admin/edit_profil'); }
    public function adminDelProfile(): void  { $this->renderUser('admin/delete_profil'); }

    // ── Private: load a user-management view inside our views/ folder ─────────
    private function renderUser(string $viewPath): void {
        // Make $baseUrl and $sessionUser available inside the included view
        $baseUrl     = $this->baseUrl();
        $sessionUser = $_SESSION['user'] ?? null;

        $fullPath = __DIR__ . '/../views/' . $viewPath . '.php';
        if (!file_exists($fullPath)) {
            http_response_code(404);
            echo "View not found: $viewPath";
            return;
        }

        // Inject the shared layout (header + footer) around the view.
        // $base is what header.php expects for building asset URLs.
        $base      = $baseUrl;
        $pageTitle = null; // views set their own $pageTitle before HTML output

        // Buffer the view so it can set $pageTitle before we output the header
        ob_start();
        require $fullPath;
        $content = ob_get_clean();

        // Now output header (which uses $pageTitle if set by the view),
        // the buffered content, then the footer.
        require __DIR__ . '/../views/front/header.php';
        echo $content;
        require __DIR__ . '/../views/front/footer.php';
    }
}
