<?php

namespace App\Http\Controllers;

use Auth;
use App\Loop;
use App\LoopDownload;
use App\Soundkit;
use App\User;
use App\Order;
use App\Http\Controllers\Controller;
use Common\Files\FileEntry;
use Common\Files\Response\FileResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Common\Settings\Settings;

class DownloadController extends Controller
{
    public function downloadLoop($id) {
        
        $loop = Loop::with('artists')
            ->findOrFail($id);

        if ( 
            Order::where('product_id', $loop->id)
                ->where('product_type', $loop->model_type)
                ->where('user_id', Auth::user()->id)
                ->where('status', 1)
                ->exists()
            || $loop->free
        ) {
            return redirect()->to('/download');
        }

        return view('download', [
            'media' => $loop
        ]);

    }

    public function downloadSoundkit($id) {

        $soundkit = Soundkit::with('artist')
            ->findOrFail($id);

        if ( 
            Order::where('product_id', $soundkit->id)
                ->where('product_type', $soundkit->model_type)
                ->where('user_id', Auth::user()->id)
                ->where('status', 1)
                ->exists()
            || $soundkit->free
        ) {
            return redirect()->to('/download');
        }
        
        return view('download', [
            'media' => $soundkit
        ]);

    }


    public function download($id) {

        $track = Loop::findOrFail($id);
        
        $log = new LoopDownload;
        $log->user_id = Auth::user()->id;
        $log->loop_id = $track->id;
        $log->save();

        //$this->authorize('download', $track);

        if ( ! $track->url) {
            abort(404);
        }

        preg_match('/.+?\/storage\/track_media\/(.+?\.[a-z0-9]+)/', $track->url, $matches);

        // track is local
        if (isset($matches[1])) {
            $entry = $this->fileEntry->where('file_name', $matches[1])->firstOrFail();

            $ext = pathinfo($track->url, PATHINFO_EXTENSION);
            $trackName = str_replace('%', '', Str::ascii($track->name)).".$ext";
            $entry->name = $trackName;

            return app(FileResponseFactory::class)->create($entry, 'attachment');

        // track is remote
        } else {
            $response = response()->stream(function() use($track) {
                echo file_get_contents($track->url);
            });
            $disposition = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                "$track->name.mp3",
                str_replace('%', '', Str::ascii("$track->name.mp3"))
            );
            $response->headers->set('Content-Disposition', $disposition);
            return $response;
        }

    }
}
