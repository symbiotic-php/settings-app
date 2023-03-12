<?php


namespace Symbiotic\Apps\Settings;


use Symbiotic\Routing\AppRouting;
use Symbiotic\Routing\RouterInterface;


class Routing extends AppRouting
{
    public function backendRoutes(RouterInterface $router):void
    {

        $router->group(['namespace' => 'Backend'], function (RouterInterface $router) {
            /**
             * Filesystems
             */
            $router->addRoute(['get','post'],'/filesystems/add/{driver}', [
                'uses' => 'Filesystems@add',
                'as' => 'filesystems.add'
            ]);
            $router->addRoute(['get','post'],'/filesystems/add', [
                'uses' => 'Filesystems@selectType',
                'as' => 'filesystems.selectType'
            ]);
            $router->get('/filesystems/{id}/edit', [
                'uses' => 'Filesystems@edit',
                'as' => 'filesystems.edit'
            ]);
            $router->post('/filesystems/save', [
                'uses' => 'Filesystems@save',
                'as' => 'filesystems.save'
            ]);

            $router->get('/filesystems/', [
                'uses' => 'Filesystems@index',
                'as' => 'filesystems.index'
            ]);

            /**
             * Databases
             */
            $router->addRoute(['get','post'],'/databases/add/{driver}', [
                'uses' => 'Databases@add',
                'as' => 'databases.add'
            ]);
            $router->addRoute(['get','post'],'/databases/add', [
                'uses' => 'Databases@selectType',
                'as' => 'databases.selectType'
            ]);
            $router->get('/databases/{id}/edit', [
                'uses' => 'Databases@edit',
                'as' => 'databases.edit'
            ]);
            $router->post('/databases{id}/edit', [
                'uses' => 'Databases@edit',
                'as' => 'databases.save'
            ]);

            $router->get('/databases/', [
                'uses' => 'Databases@index',
                'as' => 'databases.index'
            ]);


            /**
             * Packqges
             */
            $router->get('/package/{package_id}/', [
                'uses' => 'Packages@edit',
                'as' => 'package.edit'
            ]);
            $router->post('/package/{package_id}/', [
                'uses' => 'Packages@save',
                'as' => 'package.save'
            ]);
            $router->get('/packages/', [
                'uses' => 'Packages@index',
                'as' => 'packages.index'
            ]);


            $router->get('/system/', [
                'uses' => 'System@coreEdit',
                'as' => 'system.index'
            ]);
            $router->get('/', [
                'uses' => 'System@coreEdit',
                'as' => 'index'
            ]);
            $router->post('/system/', [
                'uses' => 'System@coreSave',
                'as' => 'system.save'
            ]);
        });
    }
}