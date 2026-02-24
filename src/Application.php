<?php
declare(strict_types=1);

namespace App;

use Cake\Http\BaseApplication;
use Cake\Http\MiddlewareQueue;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\Middleware\BodyParserMiddleware;
use Authentication\Middleware\AuthenticationMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationServiceProviderInterface;
use Authentication\AuthenticationService;
use Cake\Core\Configure;
use Cake\Routing\Router;
use Authentication\Identifier\PasswordIdentifier;
use Cake\Core\ContainerInterface;
use Cake\Datasource\FactoryLocator;
use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Cake\ORM\Locator\TableLocator;
use Cake\Http\Client\Request;

class Application extends BaseApplication implements AuthenticationServiceProviderInterface
{
    public function bootstrap(): void
    {
        parent::bootstrap();
        FactoryLocator::add(
            'Table',
            (new TableLocator())->allowFallbackClass(false)
        );

        if (Configure::read('debug') && Configure::read('env') === 'develop') {
            $this->addPlugin('DebugKit');
        }
    }

    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        $middlewareQueue
            ->add(new ErrorHandlerMiddleware(Configure::read('Error')))
            ->add(new RoutingMiddleware($this))
            ->add(new BodyParserMiddleware())
            ->add(new AuthenticationMiddleware($this))
            ->add(new CsrfProtectionMiddleware([
                'httponly' => true,
            ]));

        return $middlewareQueue;
    }
    
    public function services(ContainerInterface $container): void
    {
    }

    public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
    {
        //$service = new AuthenticationService();

        $service = new AuthenticationService([
            'unauthenticatedRedirect' => Router::url('/'),
            'queryParam' => 'redirect',
        ]);

        // Redirecionamento padrão caso não autenticado
        $service->setConfig([
            'unauthenticatedRedirect' => [
                'controller' => 'Users',
                'action' => 'login',
            ],
            'queryParam' => 'redirect',
        ]);

        
        $service->loadIdentifier('Authentication.Password', [
            'fields' => [
                'username' => 'cpf',
                'password' => 'password',
            ],
            'resolver' => [
                'className' => 'Authentication.Orm',
                'userModel' => 'Usuarios'
            ]
        ]);
        

        $identifier = new PasswordIdentifier([
            'fields' => [
                'username' => 'cpf',
                'password' => 'password',
            ],
        ]);

        // Autenticadores
        $service->loadAuthenticator('Authentication.Session'); // via sessão
        $service->loadAuthenticator('Authentication.Form', [
            'fields' => [
                'username' => 'cpf',
                'password' => 'password',
            ],
            'loginUrl' => Router::url('/login'),
            'resolver' => [
                'className' => 'Authentication.Orm',
                'userModel' => 'Usuarios'
            ],
            'identifiers' => [$identifier],
        ]);

        return $service;
    }

}
