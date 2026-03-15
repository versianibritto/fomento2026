<?php
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

use Cake\Http\ServerRequest;
use Cake\Routing\Router;


return function (RouteBuilder $routes): void {
    
    $routes->setRouteClass(DashedRoute::class);

    $routes->scope('/', function (RouteBuilder $builder): void {
       
      

        $builder->connect('/', ['controller' => 'Index', 'action' => 'home']);
        $builder->connect('/users/gestao/{id}', ['controller' => 'Users', 'action' => 'ver'])->setPass(['id']);
        $builder->connect('/{controller}', ['action' => 'index']);
        $builder->connect('/{controller}/{action}/*', []);
        $builder->connect('/dashboard', ['controller' => 'Index', 'action' => 'dashboard']);
        $builder->connect('/dashyoda', ['controller' => 'Index', 'action' => 'dashyoda']);
        $builder->connect('/index', ['controller' => 'Index', 'action' => 'index']);

        $builder->connect('/manuais', ['controller' => 'Index', 'action' => 'manuais']);
        $builder->connect('/unidades', ['controller' => 'Index', 'action' => 'unidades']);
        $builder->connect('/editais', ['controller' => 'Index', 'action' => 'editais']);

        $builder->connect('/manutencao', ['controller' => 'Index', 'action' => 'manutencao']);
        $builder->connect('/talentos', ['controller' => 'Index', 'action' => 'talentos']);
        if((strstr(Router::getRequest()->host(), 'fomento2026.local')) || (strstr(Router::getRequest()->host(), 'homolog.pibic.fiocruz.br'))) {
            $builder->connect('/login', ['controller' => 'Users', 'action' => 'login']);
        } else {
            $builder->connect('/login', ['controller' => 'Users', 'action' => 'login-unico']);
        }
        /*
           $builder->connect('/login', ['controller' => 'Users', 'action' => 'login']);
            $builder->connect('/logindev', ['controller' => 'Users', 'action' => 'logindev']);
            $builder->connect('/login-unico', ['controller' => 'Users', 'action' => 'loginUnico']);

        */
        $builder->connect('/logout', ['controller' => 'Users', 'action' => 'logout']);
        $builder->connect('/programas/{tipo}',['controller' => 'Index', 'action' => 'programas'])->setPass(['tipo']);
        $builder->connect('/dashdetalhes/{tipo}',['controller' => 'Index', 'action' => 'dashdetalhes'])->setPass(['tipo']);

       

        $builder->connect('/detalhe-unidade/*', ['controller' => 'Unidades', 'action' => 'view']);

        $builder->fallbacks();
    });

};
