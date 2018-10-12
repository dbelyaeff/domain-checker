<?php

namespace App\Http\Controllers\Api;

use App\TLD;
use Phois\Whois\Whois;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class WhoisController extends Controller
{
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
            foreach($domains as $domain){
                if(FALSE === strpos($domain,'.')){
                    foreach($root_domains as $root_domain){
                        $domain_with_root = join([$domain,$root_domain],'.');
                        $result[$domain_with_root] = Cache::remember($domain_with_root,1440,function() use($domain_with_root){
                            $whois = new Whois($domain_with_root);
                            return $whois->isAvailable();
                        });
                    }
                }
                else {
                    $result[$domain] = Cache::remember($domain,1440,function() use($domain){
                        $whois = new Whois($domain);
                        return $whois->isAvailable();
                    });
                }
            }
            return $result;
        }
    }
}
