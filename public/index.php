<?php
session_start();

// ── Autoload ──────────────────────────────────────────────────────────────────
spl_autoload_register(function (string $class): void {
    $dirs = [
        __DIR__ . '/../config/',
        __DIR__ . '/../core/',
        __DIR__ . '/../models/',
        __DIR__ . '/../controllers/',
    ];
    foreach ($dirs as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) { require_once $file; return; }
    }
});

// ── Bootstrap ─────────────────────────────────────────────────────────────────
// Must be required explicitly — Config class lives inside Database.php,
// so the autoloader cannot find it by class name alone.
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/helpers.php';
Config::ensureTables();

ini_set('display_errors', 1);
error_reporting(E_ALL);

// ── Routes ────────────────────────────────────────────────────────────────────
$router = new Router();

// Landing
$router->get('/',                           'HomeController',  'landing');

// ── Auth (front office) ───────────────────────────────────────────────────────
$router->get('/login',                      'UserController',  'login');
$router->post('/login',                     'UserController',  'login');
$router->get('/register',                   'UserController',  'register');
$router->post('/register',                  'UserController',  'register');
$router->get('/logout',                     'UserController',  'logout');
$router->get('/face-login',                 'UserController',  'faceLogin');
$router->post('/face-login',                'UserController',  'faceLogin');
$router->get('/face-register',              'UserController',  'faceRegister');
$router->post('/face-register',             'UserController',  'faceRegister');
$router->get('/reset-password',             'UserController',  'resetPassword');
$router->post('/reset-password',            'UserController',  'resetPassword');

// ── User account ──────────────────────────────────────────────────────────────
$router->get('/account/edit',               'UserController',  'editAccount');
$router->post('/account/edit',              'UserController',  'editAccount');
$router->get('/account/profile',            'UserController',  'editProfile');
$router->post('/account/profile',           'UserController',  'editProfile');

// ── Admin — users ─────────────────────────────────────────────────────────────
$router->get('/admin/users',                'UserController',  'adminUsers');
$router->get('/admin/users/add',            'UserController',  'adminAddUser');
$router->post('/admin/users/add',           'UserController',  'adminAddUser');
$router->get('/admin/users/edit',           'UserController',  'adminEditUser');
$router->post('/admin/users/edit',          'UserController',  'adminEditUser');
$router->get('/admin/users/delete',         'UserController',  'adminDelUser');
$router->post('/admin/users/delete',        'UserController',  'adminDelUser');

// ── Admin — profiles ──────────────────────────────────────────────────────────
$router->get('/admin/profiles',             'UserController',  'adminProfiles');
$router->get('/admin/profiles/add',         'UserController',  'adminAddProfile');
$router->post('/admin/profiles/add',        'UserController',  'adminAddProfile');
$router->get('/admin/profiles/edit',        'UserController',  'adminEditProfile');
$router->post('/admin/profiles/edit',       'UserController',  'adminEditProfile');
$router->get('/admin/profiles/delete',      'UserController',  'adminDelProfile');
$router->post('/admin/profiles/delete',     'UserController',  'adminDelProfile');

// ── Back Office — quizzes ─────────────────────────────────────────────────────
$router->get('/back',                       'QuizController',  'index');
$router->get('/quizzes',                    'QuizController',  'list');
$router->get('/quizzes/show',               'QuizController',  'show');
$router->get('/quizzes/create',             'QuizController',  'create');
$router->post('/quizzes/build-form',        'QuizController',  'buildForm');
$router->post('/quizzes/store',             'QuizController',  'store');
$router->get('/quizzes/update-list',        'QuizController',  'updateList');
$router->get('/quizzes/edit',               'QuizController',  'edit');
$router->post('/quizzes/update',            'QuizController',  'update');
$router->get('/quizzes/delete-list',        'QuizController',  'deleteList');
$router->get('/quizzes/delete',             'QuizController',  'delete');
$router->get('/quizzes/feedback',           'QuizController',  'feedbackList');

// ── Front Office — quizzes ────────────────────────────────────────────────────
$router->get('/front',                      'FrontController', 'home');
$router->get('/front/play',                 'FrontController', 'play');
$router->post('/front/submit',              'FrontController', 'submit');
$router->post('/front/feedback',            'FrontController', 'saveFeedback');

// ── Results & My Results ──────────────────────────────────────────────────
$router->get('/front/my-results',           'FrontController', 'myResults');
$router->get('/quizzes/results',            'QuizController',  'resultsList');

// ── Dispatch ──────────────────────────────────────────────────────────────────
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
