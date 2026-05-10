<?php

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
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// ── Error display (dev only) ──────────────────────────────────────────────────
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ── Routes ────────────────────────────────────────────────────────────────────
$router = new Router();

// ── Landing ───────────────────────────────────────────────────────────────────
$router->get('/',                          'HomeController',  'landing');

// ── Back Office ───────────────────────────────────────────────────────────────
$router->get('/back',                      'QuizController',  'index');
$router->get('/quizzes',                   'QuizController',  'list');
$router->get('/quizzes/show',              'QuizController',  'show');
$router->get('/quizzes/create',            'QuizController',  'create');
$router->post('/quizzes/build-form',       'QuizController',  'buildForm');
$router->post('/quizzes/store',            'QuizController',  'store');
$router->get('/quizzes/update-list',       'QuizController',  'updateList');
$router->get('/quizzes/edit',              'QuizController',  'edit');
$router->post('/quizzes/update',           'QuizController',  'update');
$router->get('/quizzes/delete-list',       'QuizController',  'deleteList');
$router->get('/quizzes/delete',            'QuizController',  'delete');
$router->get('/quizzes/feedback',          'QuizController',  'feedbackList');

// ── Front Office ──────────────────────────────────────────────────────────────
$router->get('/front',                     'FrontController', 'home');
$router->get('/front/play',                'FrontController', 'play');
$router->post('/front/submit',             'FrontController', 'submit');
$router->post('/front/feedback',           'FrontController', 'saveFeedback');

// ── Dispatch ──────────────────────────────────────────────────────────────────
$router->dispatch(
    $_SERVER['REQUEST_URI'],
    $_SERVER['REQUEST_METHOD']
);
