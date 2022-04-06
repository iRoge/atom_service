<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

/** @var Router $router */

use App\Http\Middleware\OnlyApiAuth;
use Laravel\Lumen\Routing\Router;

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(
    [
        'prefix' => 'api',
        'middleware' => OnlyApiAuth::class
    ],
    function () use ($router) {
        //ADD
        $router->post('/atom', 'AtomController@add');
        $router->post('/atom/addMany', 'AtomController@addMany');
        //UPDATE
        $router->patch('/atom/addState/{atom_id}', 'AtomController@changeState');
        $router->patch('/atom/addStates', 'AtomController@changeStates');
        $router->patch('/atom/changeFirstStates', 'AtomController@changeFirstStates');
        $router->patch('/atom/returnMany', 'AtomController@returnMany');
        //GET
        $router->get('/atom/getFirstStates', 'AtomController@getFirstAtomStates');
        $router->get('/atom/{atom_id}', 'AtomController@getLastAtomState');
        // DELETE
        $router->delete('/atom/deleteOne/{atom_id}', 'AtomController@deleteByAtomId');
        // POST
        $router->post('/atom/findByPlacesAndAssortmentId/', 'AtomController@findByPlacesAndAssortmentId');
        $router->post('/atom/getByMatrixCodes/', 'AtomController@getAtomsByMatrixCodes');
        $router->post('/atom/deleteMany/', 'AtomController@deleteByAtomIds');
        $router->post('/atom/getAtomStatesByIds', 'AtomController@getAtomStatesByIds');
    }
);



