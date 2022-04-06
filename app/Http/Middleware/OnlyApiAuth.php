<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class OnlyApiAuth
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
    	//TODO: сделать обработку ключа передаваемого в заголовке Authorization
        $apikey = $request->get('apikey');
        if ($apikey === null) {
            return Response()->json(
                'Ошибка доступа. Нужно передать apikey.',
                Response::HTTP_FORBIDDEN,
                [],
                JSON_UNESCAPED_UNICODE
            );
        }

        //TODO запрос перенести в model user_access
        //TODO добавить кеширование в REDIS

        $isUserApikeyInDatabase = DB::table('user_access')->where('apikey', '=', $apikey)->count();

        if($isUserApikeyInDatabase === 0){
            return Response()->json(
                'Ошибка доступа. По данному ключу нет доступа.',
                Response::HTTP_FORBIDDEN,
                [],
                JSON_UNESCAPED_UNICODE
            );
        }

        return $next($request);
    }
}
