<?php

namespace App\Http\Middleware;

use Closure;

class HasCustomerSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$required)
    {
        foreach (config('billpay.session') as $key => $session_key) {
            if ($this->missingRequiredKey($request,$key,$required)) {
                $error = $session_key['error'];
                switch($session_key['redirect']){
                    case "fallback":
                    $redirect = $this->fallback();
                    break;
                    default:
                    $redirect = $this->default();
                }
                return choose_redirect($request,$redirect,true,$error);
            }
        }
        return $next($request);
    }

    private function missingRequiredKey($request,$key,$required){
        if(empty($required) || in_array($key, $required)){
            return !$request->session()->exists($key);
        }
        return false;
    }

    private function default(){
        return billpay_url();
    }

    private function fallback(){
        return billpay_url() . session()->get('company_path');
    }
}
