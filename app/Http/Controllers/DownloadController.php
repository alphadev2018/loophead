<?php

namespace App\Http\Controllers;

use Auth;
use Storage;
use ZipArchive;
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
    /**
     * @var FileEntry
     */
    private $fileEntry;

    /**
     * @param FileEntry $fileEntry
     */
    public function __construct(FileEntry $fileEntry)
    {
        $this->fileEntry = $fileEntry;
    }
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
            return redirect()->to('/download/loop/'.$loop->id);
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
            return redirect()->to('/download/soundkit/'.$soundkit->id);
        }
        
        return view('download', [
            'media' => $soundkit
        ]);

    }


    public function download($type, $id) {

        $loops = [];
        $product = null;

        if ($type == 'soundkit') {
            $loops = Loop::where('soundkit_id', $id)->get();
            $product = Soundkit::findOrFail($id);
        } else {
            $loops = [ Loop::findOrFail($id) ];
            $product = Loop::findOrFail($id);
        }

        $zip = new ZipArchive;
        $zipFileName = $product->name.'_'.Str::uuid().'.zip';
        $zipPath = Storage::disk('local')->path('tmp') . '/' . $zipFileName;

        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            foreach ($loops as $loop) {
                $matches = explode('/', $loop->url);
                if (!$matches[2]) continue;

                $entry = $this->fileEntry->where('file_name', $matches[2])->firstOrFail();
                $path = Storage::disk('local')->path('track_media');
                $zip->addFile($path.'/'.$matches[2], $entry->name);

                if ($loop->stem) {
                    $stem_name = explode('/', $loop->stem);
                    if (!$stem_name[2]) continue;

                    $stem_path = Storage::disk('public')->path('track_stems');
                    $stem_entry = $this->fileEntry->where('file_name', $stem_name[2])->firstOrFail();
                    $zip->addFile($stem_path.'/'.$stem_name[2], $stem_entry->name);
                }
            }
        }
        $zip->close();
        
        return response()->download($zipPath, "Loophead_".$product->name.".zip", ['location' => '/ttt'])->deleteFileAfterSend(true);
    }
}
