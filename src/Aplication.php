<?php
declare(strict_types=1);

namespace App;

use Cake\Http\BaseApplication;
use Cake\Http\MiddlewareQueue;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\Middleware\AssetMiddleware;
use Cake\Http\Middleware\BodyParserMiddleware;
use Authentication\Middleware\AuthenticationMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationServiceProviderInterface;
use Authentication\AuthenticationService;
use Authentication\Identifier\AbstractIdentifier;
use Cake\Core\Configure;
use Cake\Routing\Router;

class Application extends BaseApplication implements AuthenticationServiceProviderInterface
{
    public function bootstrap(): void
    {
        parent::bootstrap();

        $this->addPlugin('Authentication');
    }

    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        $middlewareQueue
            ->add(new ErrorHandlerMiddleware(Configure::read('Error')))
            //->add(new AssetMiddleware())
            ->add(new BodyParserMiddleware())
            ->add(new AuthenticationMiddleware($this)) // <- antes do RoutingMiddleware
            ->add(new RoutingMiddleware($this));

        return $middlewareQueue;
    }
    public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
    {
        $service = new AuthenticationService();

        // Configura redirecionamento caso não autenticado
        $service->setConfig([
            'unauthenticatedRedirect' => [
                'controller' => 'Users',
                'action' => 'login',
            ],
            'queryParam' => 'redirect',
        ]);

        // Configura o identificador de senha
        /*
        $identifier = new PasswordIdentifier([
            'fields' => [
                'username' => 'email',   // campo de login
                'password' => 'password' // campo de senha
            ],
        ]);
        */

        // Autenticadores
        $service->loadAuthenticator('Authentication.Session'); // autenticação via sessão
        $service->loadAuthenticator('Authentication.Form', [
            'fields' => [
                'username' => 'email',
                'password' => 'password',
            ],
            'loginUrl' => Router::url([
                'controller' => 'Users',
                'action' => 'login',
            ]),
            //'identifiers' => [$identifier], // <-- aqui passamos o identificador diretamente
        ]);

        return $service;
    }
}
