<?php
/**
 * helpers.php — Global helper functions from the user management project
 * Loaded once by index.php before routing
 */

if (!function_exists('stf_language_code_from_value')) {
    function stf_language_code_from_value(?string $value): string {
        return match ($value) {
            'Anglais'  => 'en', 'Espagnol' => 'es',
            'Arabe'    => 'ar', 'Allemand' => 'de',
            'Francais', 'Français' => 'fr',
            default    => 'fr',
        };
    }
}

if (!function_exists('stf_current_lang')) {
    function stf_current_lang(): string {
        $lang = $_SESSION['site_lang'] ?? 'fr';
        return in_array($lang, ['fr','en','es','ar','de'], true) ? $lang : 'fr';
    }
}

if (!function_exists('stf_is_rtl')) {
    function stf_is_rtl(): bool { return stf_current_lang() === 'ar'; }
}

if (!function_exists('stf_t')) {
    function stf_t(string $key): string {
        $translations = [
            'fr' => [
                'home'=>'Accueil','register'=>"S'inscrire",'login'=>'Connexion',
                'logout'=>'Deconnexion','admin'=>'Admin','start'=>'Commencer',
                'users_registered'=>'Utilisateurs inscrits','administrators'=>'Administrateurs',
                'profiles_created'=>'Profils crees','avg_completion'=>'Completion moyenne',
                'full_profiles'=>'Profils complets','top_city'=>'Ville principale',
                'best_profile'=>'Profil le plus complet','choose_path'=>'Choisis ton parcours',
                'path_subtitle'=>'Chaque choix donne un resultat different',
                'community'=>'Communaute','join'=>'Rejoindre','no_profiles'=>'Aucun profil.',
                'login_title'=>'Se connecter','login_subtitle'=>'Acces Simule Ton Futur',
                'login_intro'=>'Entrez vos identifiants','create_account'=>'Creer un compte',
                'no_account'=>'Pas encore de compte ?','register_title'=>'Creer mon compte',
                'register_subtitle'=>'Rejoins la communaute','my_account'=>'Mon compte',
                'email'=>'Email','password'=>'Mot de passe',
                'classic_login'=>'Connexion classique','face_login'=>'Connexion Face ID',
                'face_register'=>'Activer Face ID','webcam_on'=>'Activer webcam',
                'scan_face'=>'Scanner mon visage','save_face'=>'Enregistrer Face ID',
                'disable_face'=>'Desactiver Face ID','first_name'=>'Prenom','last_name'=>'Nom',
                'role'=>'Role','user_role'=>'Utilisateur','bio'=>'Bio','city'=>'Ville',
                'country'=>'Pays','language'=>'Langue','choose'=>'-- Choisir --',
                'save'=>'Enregistrer','cancel'=>'Annuler','back_home'=>"Retour a l'accueil",
                'account_info'=>'Informations du compte','profile_info'=>'Informations du profil',
                'complete_profile'=>'Completer mon profil','edit_profile'=>'Modifier mon profil',
                'profile_photo'=>'Photo de profil',
                'hero_badge'=>'Plateforme Interactive',
                'hero_title_before'=>'Simule','hero_title_highlight'=>'Ton Futur',
                'hero_title_after'=>'Professionnel',
                'hero_text_1'=>'Decouvre le monde du travail moderne en faisant des choix reels.',
                'hero_text_2'=>"Gagne de l'argent, de l'experience et de la reputation.",
            ],
            'en' => [
                'home'=>'Home','register'=>'Register','login'=>'Login','logout'=>'Logout',
                'admin'=>'Admin','start'=>'Get started','users_registered'=>'Registered users',
                'administrators'=>'Administrators','profiles_created'=>'Profiles created',
                'avg_completion'=>'Avg completion','full_profiles'=>'Full profiles',
                'top_city'=>'Top city','best_profile'=>'Best profile',
                'choose_path'=>'Choose your path','path_subtitle'=>'Each choice leads somewhere different',
                'community'=>'Community','join'=>'Join','no_profiles'=>'No profiles yet.',
                'login_title'=>'Sign in','login_subtitle'=>'Access your space',
                'login_intro'=>'Enter your credentials','create_account'=>'Create account',
                'no_account'=>"Don't have an account?",'register_title'=>'Create my account',
                'register_subtitle'=>'Join the community','my_account'=>'My account',
                'email'=>'Email','password'=>'Password','classic_login'=>'Classic login',
                'face_login'=>'Face ID login','face_register'=>'Enable Face ID',
                'webcam_on'=>'Turn on webcam','scan_face'=>'Scan my face',
                'save_face'=>'Save Face ID','disable_face'=>'Disable Face ID',
                'first_name'=>'First name','last_name'=>'Last name','role'=>'Role',
                'user_role'=>'User','bio'=>'Bio','city'=>'City','country'=>'Country',
                'language'=>'Language','choose'=>'-- Choose --','save'=>'Save',
                'cancel'=>'Cancel','back_home'=>'Back to home',
                'account_info'=>'Account info','profile_info'=>'Profile info',
                'complete_profile'=>'Complete profile','edit_profile'=>'Edit profile',
                'profile_photo'=>'Profile photo',
                'hero_badge'=>'Interactive Platform','hero_title_before'=>'Simulate',
                'hero_title_highlight'=>'Your Future','hero_title_after'=>'Career',
                'hero_text_1'=>'Discover the modern world of work through real choices.',
                'hero_text_2'=>'Earn money, experience and reputation.',
            ],
        ];
        $lang = stf_current_lang();
        return $translations[$lang][$key] ?? $translations['fr'][$key] ?? $key;
    }
}
