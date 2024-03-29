<?php

namespace App\Services\Loops;

use App\Loop;
use App\LoopPlay;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

class LogLoopPlay
{
    /**
     * @var Loop
     */
    private $track;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Agent
     */
    private $agent;

    /**
     * @param Loop $track
     * @param Request $request
     * @param Agent $agent
     */
    public function __construct(Loop $track, Request $request, Agent $agent)
    {
        $this->track = $track;
        $this->request = $request;
        $this->agent = $agent;
    }

    /**
     * @param Loop $track
     * @return LoopPlay|void
     */
    public function execute(Loop $track)
    {
        // only log play every minute for same video
        $existing = $track->plays()
            ->whereBetween('created_at', [Carbon::now()->subMinute(), Carbon::now()])
            ->first();
        if ( ! $existing) {
            return $this->log($track);
        }
    }

    /**
     * @param Loop $track
     * @return LoopPlay
     */
    private function log(Loop $track)
    {
        $attributes = [
            'location' => $this->getLocation(),
            'platform' => strtolower($this->agent->platform()),
            'device' => $this->getDevice(),
            'browser' => strtolower($this->agent->browser()),
            'user_id' => Auth::id(),
        ];

        return $track->plays()->create($attributes);
    }

    private function getDevice() {
        if ($this->agent->isMobile()) {
            return 'mobile';
        } else if ($this->agent->isTablet()) {
            return 'tablet';
        } else {
            return 'desktop';
        }
    }

    private function getLocation()
    {
        return strtolower(geoip($this->getIp())['iso_code']);
    }

    private function getIp(){
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
            if (array_key_exists($key, $_SERVER) === true){
                foreach (explode(',', $_SERVER[$key]) as $ip){
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                        return $ip;
                    }
                }
            }
        }
        return $this->request->ip();
    }
}
