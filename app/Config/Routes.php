<?php

use CodeIgniter\Router\RouteCollection;

$routes->get('/', function () {
    return redirect()->to('/auth/login');
});

    $routes->get('privacy',                     'Legal::privacy');
    $routes->get('terms',                       'Legal::terms');
    $routes->get('data-deletion',               'Legal::dataDeletion');
    $routes->get('media/(:num)',                'MediaController::show/$1');
    $routes->post('deploy/webhook',             'DeployWebhookController::github', ['filter' => 'deploywebhook']);

$routes->group('auth', static function ($routes) {
    $routes->get('login',                       'Auth::loginForm');
    $routes->post('login',                      'Auth::loginSubmit');
    $routes->match(['get', 'post'], 'logout',   'Auth::logout');
});

$routes->get('panel/social-accounts/meta/cron',   'Panel\MetaOAuthController::cron');
$routes->get('panel/social-accounts/meta/health', 'Panel\MetaOAuthController::health');

$routes->group('panel', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/',                                               'Panel::index');
    $routes->get('calendar',                                        'Panel\CalendarController::index');
    $routes->post('calendar',                                       'Panel\CalendarController::store');
    $routes->get('publishes',                                       'Panel\PublishesController::index');
    $routes->get('publishes/(:num)',                                'Panel\PublishesController::show/$1');
    $routes->post('publishes/(:num)/cancel',                        'Panel\PublishesController::cancel/$1');
    $routes->get('planner',                                         'Panel\PlannerController::index');
    $routes->post('planner',                                        'Panel\PlannerController::store');
    $routes->get('social-accounts',                                 'Panel\SocialAccountsController::index');
    $routes->post('social-accounts',                                'Panel\SocialAccountsController::store');
    $routes->post('social-accounts/(:num)/delete',                  'Panel\SocialAccountsController::delete/$1');
    $routes->get('social-accounts/meta/wizard',                     'Panel\MetaOAuthController::wizard');
    $routes->post('social-accounts/meta/consent',                   'Panel\MetaOAuthController::consent');
    $routes->get('social-accounts/meta/connect',                    'Panel\MetaOAuthController::connect');
    $routes->get('social-accounts/meta/callback',                   'Panel\MetaOAuthController::callback');
    $routes->post('social-accounts/meta/attach',                    'Panel\MetaOAuthController::attach');
    $routes->post('social-accounts/meta/disconnect',                'Panel\MetaOAuthController::disconnect');
    $routes->get('social-accounts/meta/health',                     'Panel\MetaOAuthController::health');
    $routes->get('help/account-linking',                            'Panel\HelpController::accountLinking');
    $routes->get('social-accounts/youtube/wizard',                  'Panel\YouTubeOAuthController::wizard');
    $routes->get('social-accounts/youtube/connect',                 'Panel\YouTubeOAuthController::connect');
    $routes->get('social-accounts/youtube/callback',                'Panel\YouTubeOAuthController::callback');
    $routes->post('social-accounts/youtube/disconnect',             'Panel\YouTubeOAuthController::disconnect');
    
    $routes->get('social-accounts/meta/publish-test',               'Panel\MetaOAuthController::publishTestForm');
    $routes->post('social-accounts/meta/test-publish',              'Panel\MetaOAuthController::testPublish');

});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin','filter'    => 'admin',], static function ($routes) {

    $routes->get('/', 'Dashboard::index');
    // Users
    $routes->get('users',                           'Users::index');
    $routes->get('users/new',                       'Users::create');
    $routes->get('users/(:num)/edit',               'Users::edit/$1');
    $routes->post('users',                          'Users::store');
    $routes->post('users/(:num)',                   'Users::update/$1');
    $routes->post('users/(:num)/delete',            'Users::delete/$1');
    $routes->post('users/(:num)/toggle-status',     'Users::toggleStatus/$1');

    $routes->get('logs', 'LogsController::index');
    
    $routes->get('jobs',            'JobsController::index');
    $routes->get('jobs/(:num)',     'JobsController::show/$1');
    $routes->post('jobs/(:num)/retry',      'JobsController::retry/$1');
    $routes->post('jobs/(:num)/reset',      'JobsController::reset/$1');
    $routes->post('jobs/(:num)/cancel',     'JobsController::cancel/$1');
    
    $routes->get('publishes/create', 'PublishesController::create');
    $routes->post('publishes',       'PublishesController::store');
    $routes->get('publishes',        'PublishesController::index');
    $routes->get('publishes/(:num)', 'PublishesController::show/$1');

    $routes->post('publishes/(:num)/cancel',    'PublishesController::cancel/$1');
    $routes->post('publishes/(:num)/retry',     'PublishesController::retry/$1');
    $routes->post('publishes/(:num)/check',     'PublishesController::check/$1');
    $routes->post('publishes/(:num)/reset-job', 'PublishesController::resetJob/$1');
});
