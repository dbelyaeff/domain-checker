<?php

namespace App\Http\Controllers\Api;

use App\TLD;
use Phois\Whois\Whois;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
// use TrueBV\Punycode;

class WhoisController extends Controller
{
    private static $Punycode;
    private function request($domain){
        return Cache::remember($domain, 0, function () use ($domain) {
            $result = false;
            // if(preg_match("/^xn/",$domain)){
                // self::$Punycode = self::$Punycode ?? new Punycode();
                // $domain = self::$Punycode->decode($domain);
            // }
            $whois = new Whois($domain);
            $result = $whois->isAvailable();
            return $result;
        });
    }
    public function get(Request $request){
        if($request->get('query')){
            $domains = preg_split("/[,\s]+/",$request->get('query'),-1,PREG_SPLIT_NO_EMPTY);
            $result = [];
            if($root_domains = $request->get('domains')){
                $root_domains = preg_split("/[,\s]+/",$root_domains,-1,PREG_SPLIT_NO_EMPTY);
            }
            else {
                $root_domains = TLD::get();
            }
            foreach ($domains as $domain) {
                if (false === strpos($domain, '.')) {
                    foreach ($root_domains as $root_domain) {
                        $domain_with_root = join([$domain,$root_domain], '.');
                        $result[$domain_with_root] = $this->request($domain_with_root);
                    }
                } else {
                        $result[$domain] = $this->request($domain);
                }
        
            }
            return $result;
        }
        else {
            abort(400);
        }
    }
}
