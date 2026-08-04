<?php

declare(strict_types=1);

use App\Middleware\AuthMiddleware;
use App\Middleware\SubscriptionMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\RateLimitMiddleware;

/** @var Router $router */

$router->get('/', 'HomeController@landing');

$router->get('/dictionary', 'DictionaryController@search', [RateLimitMiddleware::class]);
$router->get('/dictionary/{word}', 'DictionaryController@show', [RateLimitMiddleware::class]);

$router->get('/subscribe', 'AuthController@mobileEntry');
$router->post('/subscribe/otp/send', 'AuthController@sendOtp', [CsrfMiddleware::class, RateLimitMiddleware::class]);
$router->post('/subscribe/otp/verify', 'AuthController@verifyOtp', [CsrfMiddleware::class, RateLimitMiddleware::class]);
$router->get('/subscribe/callback', 'AuthController@gatewayCallback');

$router->get('/dashboard', 'DashboardController@index', [AuthMiddleware::class, SubscriptionMiddleware::class]);

$router->get('/learn', 'LearnController@session', [AuthMiddleware::class, SubscriptionMiddleware::class]);
$router->post('/learn/mark', 'LearnController@markWord', [AuthMiddleware::class, SubscriptionMiddleware::class, CsrfMiddleware::class]);

$router->get('/review', 'ReviewController@queue', [AuthMiddleware::class, SubscriptionMiddleware::class]);
$router->post('/review/answer', 'ReviewController@submitAnswer', [AuthMiddleware::class, SubscriptionMiddleware::class, CsrfMiddleware::class]);

$router->get('/quiz', 'QuizController@index', [AuthMiddleware::class, SubscriptionMiddleware::class]);
$router->post('/quiz/submit', 'QuizController@submit', [AuthMiddleware::class, SubscriptionMiddleware::class, CsrfMiddleware::class]);

$router->get('/collection', 'CollectionController@index', [AuthMiddleware::class, SubscriptionMiddleware::class]);

$router->get('/logout', 'AuthController@logout', [AuthMiddleware::class]);

// Admin (staff only)
$router->get('/admin', 'AdminController@dashboard', [AuthMiddleware::class]);
$router->get('/admin/words', 'AdminController@words', [AuthMiddleware::class]);
$router->post('/admin/words', 'AdminController@wordsStore', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/words/{id}/delete', 'AdminController@wordsDelete', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/users', 'AdminController@users', [AuthMiddleware::class]);
$router->get('/admin/exclusive-pool', 'AdminController@exclusivePool', [AuthMiddleware::class]);
