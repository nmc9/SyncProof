<?php

namespace App\Http\Middleware;

use App\Exceptions\NonexistantDatabaseVersionException;
use App\Exceptions\NullDatabaseVersionException;
use App\Exceptions\UnsupportedDatabaseVersionException;
use App\Version\VersionHandler;
use Closure;
use Illuminate\Http\Response;

class CheckDatabaseVersion
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $version = $request[config('database.version_key')];

        if($version != config('database.version')){
            $callback = $this->getCallback($version);
            $versionHandler = new VersionHandler($request,$version,$next);
            return $this->runCallback($callback,$versionHandler,$next);
        }
        return $next($request);
    }

    private function runCallback($callback,$versionHandler){
        if(method_exists($versionHandler, $callback)){
            $response = $versionHandler->$callback();
            return $response;
        }
        throw new UnsupportedDatabaseVersionException();
    }

    private function getCallback($version){
        $supported_versions = config('database.previous_versions');
        if(array_key_exists($version,$supported_versions)){
            return $supported_versions[$version];
        }
        throw new NullDatabaseVersionException();

    }
}
