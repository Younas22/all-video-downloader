<?php

namespace App\Http\Controllers;

use App\Http\Classes\FacebookDownloader;
use Illuminate\Http\Request;

class MainController extends Controller
{
    public function index(){
        return view('home');
    }

    public function download(Request $request){
        $request->validate([
            "videourl" => 'required'
        ]);
        $url = $request->input('videourl');
        $data = [
            'url' => $url
        ];
        switch ($this->detectWebsite($url)){
            case 'youtube':
                if($this->detectPlaylist($url) == false){
                    $yt = new \YouTubeDownloader();
                    $links = $yt->getDownloadLinks($url);
                    $id = $yt->extractId($url);
$data_string = array();
$curl = curl_init();
$test = http_build_query($data_string);
curl_setopt_array($curl, array(
CURLOPT_URL => "https://youtube.com/get_video_info?video_id=".$id,
CURLOPT_POSTFIELDS => $test,
CURLOPT_RETURNTRANSFER => true,
CURLOPT_ENCODING => '',
CURLOPT_MAXREDIRS => 10,
CURLOPT_TIMEOUT => 0,
CURLOPT_FOLLOWLOCATION => true,
CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
CURLOPT_CUSTOMREQUEST => 'POST',
));

$content = curl_exec($curl);
curl_close($curl);

dd($links);
                    parse_str($content, $yt);
                    $img = "https://img.youtube.com/vi/".$id."/0.jpg";
                    $data['links'] = $links;
                    $data['thumbnail'] = $img;
                    $data['title'] = @$yt['title'];
                    return view('download.youtube-video',$data);
                }else{
                    $playlist_id = $this->detectPlaylist($url);
                    $url = "https://www.youtube.com/list_ajax?style=json&action_get_list=1&list=PLvah45Gv0-CfNA0zfS4Sr6ov0pAL96Fr1";
                    $playlistData = json_decode(file_get_contents($url),true);
                    $data['playlist'] = $playlistData;
                    return view('download.youtube-playlist',$data);
                }
                break;
            case 'facebook':
                $downloader = new FacebookDownloader();
                $videoData = $downloader->getVideoInfo($url);

                if($videoData == false){
                    return redirect('/')->withErrors(["Can't download private videos"]);
                }
                $data['videoData'] = $videoData;
                return view('download.facebook',$data);
                break;
            case 'vimeo':
                break;
            case 'unknown':
                return redirect()->back()->withErrors(["Invalid Url"]);
                break;
        }
    }

    public function detectWebsite($url){
        if (strpos($url, 'youtube') > 0) {
            return 'youtube';
        } elseif (strpos($url, 'vimeo') > 0) {
            return 'vimeo';
        }elseif (strpos($url, 'facebook') > 0) {
            return 'facebook';
        } else {
            return 'unknown';
        }
    }

    public function detectPlaylist($url){
        if (strpos($url, 'playlist') > 0) {
            $playlist_pattern = '~(?:http|https|)(?::\/\/|)(?:www.|)(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/ytscreeningroom\?v=|\/feeds\/api\/videos\/|\/user\S*[^\w\-\s]|\S*[^\w\-\s]))([\w\-]{12,})[a-z0-9;:@#?&%=+\/\$_.-]*~i';
            $playlist_id = (preg_replace($playlist_pattern, '$1', $url));
            return $playlist_id;
        } else {
            return false;
        }
    }





}
