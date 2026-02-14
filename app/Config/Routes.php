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
    $routes->get('media/(:num)',                'MediaController::show/$1');
    $routes->get('contact',                     'PagesController::contact');
    $routes->post('contact',                    'PagesController::contactPost');

$routes->group('auth', static function ($routes) {
    $routes->get('login',                       'Auth::loginForm');
    $routes->post('login',                      'Auth::loginSubmit');
    $routes->match(['get', 'post'], 'logout',   'Auth::logout');
});

$routes->get('panel/social-accounts/meta/cron',   'Panel\MetaOAuthController::cron');
$routes->get('panel/social-accounts/meta/health', 'Panel\MetaOAuthController::health');

$routes->group('panel', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/',                                               'Panel::index');
    $routes->get('settings',                                        'Panel\SettingsController::index');
    $routes->post('settings/password',                              'Panel\SettingsController::updatePassword');
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
    $routes->get('auth/tiktok',                                     'Panel\TikTokController::start');
    $routes->get('auth/tiktok/callback',                            'Panel\TikTokController::callback');
    $routes->get('templates',                                       'Panel\TemplatesController::index');
    $routes->get('templates/(:num)',                                'Panel\TemplatesController::show/$1');
    $routes->get('templates/(:num)/edit',                           'Panel\TemplatesController::edit/$1');
    $routes->post('templates/(:num)/save',                          'Panel\TemplatesController::save/$1');
    $routes->post('templates/(:num)/export',                        'Panel\TemplatesController::export/$1');
    $routes->get('templates/(:num)/use-video',                      'Panel\TemplatesController::useVideo/$1');

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
    // (admin -> user geçişi)
    $routes->post('users/(:num)/impersonate',       'Users::impersonate/$1');

    $routes->get('logs',                            'LogsController::index');
    
    $routes->get('jobs',                            'JobsController::index');
    $routes->get('jobs/(:num)',                     'JobsController::show/$1');
    $routes->post('jobs/(:num)/retry',              'JobsController::retry/$1');
    $routes->post('jobs/(:num)/reset',              'JobsController::reset/$1');
    $routes->post('jobs/(:num)/cancel',             'JobsController::cancel/$1');
    
    $routes->get('publishes/create',                'PublishesController::create');
    $routes->post('publishes',                      'PublishesController::store');
    $routes->get('publishes',                       'PublishesController::index');
    $routes->get('publishes/(:num)',                'PublishesController::show/$1');
    $routes->post('publishes/(:num)/cancel',        'PublishesController::cancel/$1');
    $routes->post('publishes/(:num)/retry',         'PublishesController::retry/$1');
    $routes->post('publishes/(:num)/check',         'PublishesController::check/$1');
    $routes->post('publishes/(:num)/reset-job',     'PublishesController::resetJob/$1');

    $routes->get('templates',                               'TemplatesController::index');
    $routes->get('templates/new',                           'TemplatesController::create');
    $routes->post('templates',                              'TemplatesController::store');
    $routes->post('templates/(:num)/toggle',                'TemplatesController::toggle/$1');

    $routes->get('template-collections',                    'TemplateCollectionsController::index');
    $routes->get('template-collections/new',                'TemplateCollectionsController::create');
    $routes->post('template-collections',                   'TemplateCollectionsController::store');
    $routes->get('template-collections/(:num)/edit',        'TemplateCollectionsController::edit/$1');
    $routes->put('template-collections/(:num)',             'TemplateCollectionsController::update/$1');
    $routes->post('template-collections/(:num)/toggle',     'TemplateCollectionsController::toggle/$1');
    
});
    $routes->post('admin/users/stop-impersonate', 'Admin\Users::stopImpersonate', ['filter' => 'auth']);

    $routes->group('dealer', ['namespace' => 'App\Controllers\Dealer','filter'    => 'dealer',], static function ($routes) {

    $routes->get('/', 'Dashboard::index');

    $routes->get('users',                       'Users::index');
    $routes->post('users',                      'Users::store');
    $routes->post('users/(:num)/toggle-status', 'Users::toggleStatus/$1');

});