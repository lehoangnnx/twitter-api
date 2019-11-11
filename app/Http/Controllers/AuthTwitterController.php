<?php

namespace App\Http\Controllers;

use App\User;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthTwitterController extends Controller
{
    const SIGNATURE_METHOD_HMAC = 'HMAC-SHA1';

    private $_twitter_consumer_api_key;

    private $_twitter_consumer_api_api_secret_key;

    private $_twitter_access_token;

    private $_twitter_access_token_secret;

    public function __construct()
    {
        $this->middleware('auth');
        $this->_twitter_consumer_api_key = config('twitter.twitter_consumer_api_key');
        $this->_twitter_consumer_api_api_secret_key = config('twitter.twitter_consumer_api_api_secret_key');
        $this->_twitter_access_token = config('twitter.twitter_access_token');
        $this->_twitter_access_token_secret = config('twitter.twitter_access_token_secret');
    }

    public function requestToken()
    {
        $user = User::find(Auth::user()->id);
        if (!is_null($user['oauth_token'])) {
//            $home_time_line = $this->homeTimeLine();
            $user_time_line = $this->userTimeLine();

            return view('home')->with('check_oauth_token', true)
                ->with('user_time_line', $user_time_line['data']);
        }

        $stack = HandlerStack::create();
        $middleware = new Oauth1([
            'consumer_key' => $this->_twitter_consumer_api_key,
            'consumer_secret' => $this->_twitter_consumer_api_api_secret_key,
            'token' => $this->_twitter_access_token,
            'token_secret' => $this->_twitter_access_token_secret,
            'callback' => 'https://hoangnl.ngrok.io/auth/twitter',
        ]);

        $stack->push($middleware);

        $client = new Client([
            'base_uri' => 'https://api.twitter.com/',
            'handler' => $stack,
            'auth' => 'oauth',
        ]);

        try {
            $response = $client->post('oauth/request_token');

            $result = explode('&', (string)$response->getBody());
            $array = array();
            foreach ($result as $item) {
                $tmp = explode('=', $item);
                $array[$tmp[0]] = $tmp[1];
            }

            return view('home')->with('oauth_token', $array['oauth_token'])
                ->with('check_oauth_token', false);
        } catch (\Exception $exception) {
            return ['status' => false, 'message' => $exception->getMessage()];
        }
    }

    public function auth(Request $request)
    {
        $oauth_token = $request->input('oauth_token');
        $oauth_verifier = $request->input('oauth_verifier');
        $accessToken = $this->accessToken($oauth_token, $oauth_verifier);
        if ($accessToken['status']) {
            $data = array(
                'oauth_token' => $accessToken['data']['oauth_token'],
                'oauth_token_secret' => $accessToken['data']['oauth_token_secret'],
                'twitter_id_str' => $accessToken['data']['user_id'],
            );
            $user = User::find(Auth::user()->id)->update($data);

            return redirect('home')->with('check_oauth_token', true);
        }

        return redirect('home')->with('check_oauth_token', false);
    }

    public function accessToken($oauth_token, $oauth_verifier)
    {
        $stack = HandlerStack::create();
        $middleware = new Oauth1([
            'consumer_key' => $this->_twitter_consumer_api_key,
            'consumer_secret' => $this->_twitter_consumer_api_api_secret_key,
            'token' => $oauth_token,
            'token_secret' => '',
            'verifier' => $oauth_verifier,
        ]);

        $stack->push($middleware);

        $client = new Client([
            'base_uri' => 'https://api.twitter.com/',
            'handler' => $stack,
            'auth' => 'oauth',
        ]);

        try {
            $response = $client->post('oauth/access_token');

            $result = explode('&', (string)$response->getBody());
            $array = array();
            foreach ($result as $item) {
                $tmp = explode('=', $item);
                $array[$tmp[0]] = $tmp[1];
            }

            return ['status' => true, 'data' => $array];
        } catch (\Exception $exception) {
            return ['status' => false, 'message' => $exception->getMessage()];
        }
    }

    public function homeTimeLine()
    {
        $user = User::find(Auth::user()->id);
        if (is_null($user['oauth_token']) || is_null($user['oauth_token_secret'])) {
            return ['status' => false];
        }
        $stack = HandlerStack::create();
        $middleware = new Oauth1([
            'consumer_key' => $this->_twitter_consumer_api_key,
            'consumer_secret' => $this->_twitter_consumer_api_api_secret_key,
            'token' => $user['oauth_token'],
            'token_secret' => $user['oauth_token_secret'],
        ]);

        $stack->push($middleware);

        $client = new Client([
            'base_uri' => 'https://api.twitter.com/1.1/',
            'handler' => $stack,
            'auth' => 'oauth',
        ]);

        try {
            $response = $client->get('statuses/home_timeline.json');

            return ['status' => true, 'data' => json_decode($response->getBody()->getContents())];
        } catch (\Exception $exception) {
            return ['status' => false, 'message' => $exception->getMessage()];
        }
    }

    public function userTimeLine()
    {
        $user = User::find(Auth::user()->id);
        if (is_null($user['oauth_token']) || is_null($user['oauth_token_secret'])) {
            return ['status' => false];
        }
        $stack = HandlerStack::create();
        $middleware = new Oauth1([
            'consumer_key' => $this->_twitter_consumer_api_key,
            'consumer_secret' => $this->_twitter_consumer_api_api_secret_key,
            'token' => $user['oauth_token'],
            'token_secret' => $user['oauth_token_secret'],
        ]);

        $stack->push($middleware);

        $client = new Client([
            'base_uri' => 'https://api.twitter.com/1.1/',
            'handler' => $stack,
            'auth' => 'oauth',
        ]);

        try {
            $response = $client->get('statuses/user_timeline.json');

            return ['status' => true, 'data' => json_decode($response->getBody()->getContents())];
        } catch (\Exception $exception) {
            return ['status' => false, 'message' => $exception->getMessage()];
        }
    }

    public function createTweet(Request $request)
    {
//        $mediaUpload = '';
//        $base64 = base64_encode($request->file('file'));
//        dd($request->hasFile('file'));
//        var_dump($request->hasFile('file'));
        if ($request->hasFile('file')) {
            $mediaUpload = $this->mediaUpload($request->file('file'));
//            var_dump($mediaUpload);
        }
//        dd(123);
//        dd($mediaUpload);
        $content = $request->input('content');
        $user = User::find(Auth::user()->id);
        if (is_null($user['oauth_token']) || is_null($user['oauth_token_secret'])) {
            return redirect('home');
        }
        $stack = HandlerStack::create();
        $middleware = new Oauth1([
            'consumer_key' => $this->_twitter_consumer_api_key,
            'consumer_secret' => $this->_twitter_consumer_api_api_secret_key,
            'token' => $user['oauth_token'],
            'token_secret' => $user['oauth_token_secret'],
        ]);

        $stack->push($middleware);

        $client = new Client([
            'base_uri' => 'https://api.twitter.com/1.1/',
            'handler' => $stack,
            'auth' => 'oauth',
        ]);

        try {
            $processing_info = 'pendding';
            do {
                $mediaUploadStatus = $this->mediaUploadStatus($mediaUpload['data']['media_id_string']);
//                dd($mediaUploadStatus);
                $processing_info = $mediaUploadStatus['data']['processing_info']->state;
                if ($processing_info != 'succeeded') {
                    sleep(2);
                }
            } while ($processing_info != 'succeeded');

            $query = array();
            if (!empty($content)) {
                $query['status'] = $content;
            }
            if (isset($mediaUpload) && $mediaUpload['status']) {
                $query['media_ids'] = $mediaUpload['data']['media_id_string'];
            }

            $response = $client->post('statuses/update.json',
                [
                    'query' => $query,
                ]
            );
        } catch (\Exception $exception) {
            return ['status' => false, 'message CREATE TWEET' => $exception->getMessage()];
        }

        return redirect('home');
    }

    public function deleteTweet(Request $request)
    {
        $id = $request->input('id');
        $user = User::find(Auth::user()->id);
        if (is_null($user['oauth_token']) || is_null($user['oauth_token_secret'])) {
            return redirect('home');
        }
        $stack = HandlerStack::create();
        $middleware = new Oauth1([
            'consumer_key' => $this->_twitter_consumer_api_key,
            'consumer_secret' => $this->_twitter_consumer_api_api_secret_key,
            'token' => $user['oauth_token'],
            'token_secret' => $user['oauth_token_secret'],
        ]);

        $stack->push($middleware);

        $client = new Client([
            'base_uri' => 'https://api.twitter.com/1.1/',
            'handler' => $stack,
            'auth' => 'oauth',
        ]);

        try {
            $response = $client->post("statuses/destroy/{$id}.json");
        } catch (\Exception $exception) {
            return ['status' => false, 'message' => $exception->getMessage()];
        }

        return redirect('home');
    }

    public function showTweet($id)
    {
        $user = User::find(Auth::user()->id);
        if (is_null($user['oauth_token']) || is_null($user['oauth_token_secret'])) {
            return redirect('home');
        }
        $stack = HandlerStack::create();
        $middleware = new Oauth1([
            'consumer_key' => $this->_twitter_consumer_api_key,
            'consumer_secret' => $this->_twitter_consumer_api_api_secret_key,
            'token' => $user['oauth_token'],
            'token_secret' => $user['oauth_token_secret'],
        ]);

        $stack->push($middleware);

        $client = new Client([
            'base_uri' => 'https://api.twitter.com/1.1/',
            'handler' => $stack,
            'auth' => 'oauth',
        ]);

        try {
            $response = $client->get("statuses/show/{$id}.json");

            $resultTweet = json_decode($response->getBody()->getContents());

            $resultReply = $this->getReplyOfTweet($resultTweet);

            dd($resultReply);
//            return ['status' => true, 'data' => json_decode($response->getBody()->getContents())];
        } catch (\Exception $exception) {
            return ['status' => false, 'message' => $exception->getMessage()];
        }

        return view('show-tweet')->with('tweet', $resultTweet);
    }

    public function retweetTweet(Request $request)
    {
        $id = $request->input('id');
        $type = $request->input('type');
        $user = User::find(Auth::user()->id);
        if (is_null($user['oauth_token']) || is_null($user['oauth_token_secret'])) {
            return redirect('home');
        }
        $stack = HandlerStack::create();
        $middleware = new Oauth1([
            'consumer_key' => $this->_twitter_consumer_api_key,
            'consumer_secret' => $this->_twitter_consumer_api_api_secret_key,
            'token' => $user['oauth_token'],
            'token_secret' => $user['oauth_token_secret'],
        ]);

        $stack->push($middleware);

        $client = new Client([
            'base_uri' => 'https://api.twitter.com/1.1/',
            'handler' => $stack,
            'auth' => 'oauth',
        ]);

        try {
            if ($type === 'retweet') {
                $uri = "statuses/retweet/{$id}.json";
            } else {
                $uri = "statuses/unretweet/{$id}.json";
            }
            $response = $client->post($uri);
        } catch (\Exception $exception) {
            return ['status' => false, 'message' => $exception->getMessage()];
        }

        return redirect('home');
    }

    public function favoritesTweet(Request $request)
    {
        $id = $request->input('id');
        $type = $request->input('type');
        $user = User::find(Auth::user()->id);
        if (is_null($user['oauth_token']) || is_null($user['oauth_token_secret'])) {
            return redirect('home');
        }
        $stack = HandlerStack::create();
        $middleware = new Oauth1([
            'consumer_key' => $this->_twitter_consumer_api_key,
            'consumer_secret' => $this->_twitter_consumer_api_api_secret_key,
            'token' => $user['oauth_token'],
            'token_secret' => $user['oauth_token_secret'],
        ]);

        $stack->push($middleware);

        $client = new Client([
            'base_uri' => 'https://api.twitter.com/1.1/',
            'handler' => $stack,
            'auth' => 'oauth',
        ]);

        try {
            if ($type === 'create') {
                $uri = "favorites/create.json?id={$id}";
            } else {
                $uri = "favorites/destroy.json?id={$id}";
            }
            $response = $client->post($uri);
        } catch (\Exception $exception) {
            return ['status' => false, 'message' => $exception->getMessage()];
        }

        return redirect('home');
    }

    public function mediaUpload($file)
    {
        try {
            $mediaUploadInit = $this->mediaUploadInit($file);

            $mediaUploadAppend = $this->mediaUploadAppend($mediaUploadInit['data']['media_id_string'], $file);

//            $mediaUploadStatus = $this->mediaUploadStatus($mediaUploadInit['data']['media_id_string']);
//            dd($mediaUploadStatus);

            if ($mediaUploadAppend['status']) {
                $mediaUploadFinalize = $this->mediaUploadFinalize($mediaUploadInit['data']['media_id_string']);
            }

            return ['status' => true, 'data' => $mediaUploadFinalize['data']];
        } catch (\Exception $exception) {
            return ['status' => false, 'message MEDIA UPLOAD' => $exception->getMessage()];
        }
    }

    public function mediaUploadInit($file)
    {
//        dd($file);
        $typeImage = array('png', 'jpeg', 'jpg');
        $typeGif = array('gif');
        $typeVideo = array('mp4');
//        $mime = mime_content_type($file->getRealPath());
        $typeFile = $file->getClientOriginalExtension();
//        dd($file->getClientOriginalExtension());
        $user = User::find(Auth::user()->id);
        if (is_null($user['oauth_token']) || is_null($user['oauth_token_secret'])) {
            return redirect('home');
        }
        $stack = HandlerStack::create();
        $middleware = new Oauth1([
            'consumer_key' => $this->_twitter_consumer_api_key,
            'consumer_secret' => $this->_twitter_consumer_api_api_secret_key,
            'token' => $user['oauth_token'],
            'token_secret' => $user['oauth_token_secret'],
        ]);

        $stack->push($middleware);

        $client = new Client([
            'base_uri' => 'https://upload.twitter.com/1.1/',
            'handler' => $stack,
            'auth' => 'oauth',
        ]);
        try {
            $query = array(
                'command' => 'INIT',
                'total_bytes' => $file->getSize(),
                'media_type' => $file->getMimeType(),
            );
            if (in_array($typeFile, $typeVideo)) {
                $query['media_category'] = 'tweet_video';
            } elseif (in_array($typeFile, $typeImage)) {
                $query['media_category'] = 'tweet_image';
            } elseif (in_array($typeFile, $typeGif)) {
                $query['media_category'] = 'tweet_gif';
            }
//            dd($query);
            $response = $client->post('media/upload.json',
                [
                    'query' => $query,
                ]
            );

            return ['status' => true, 'data' => (array)json_decode($response->getBody()->getContents())];
        } catch (\Exception $exception) {
            return ['status' => false, 'message INIT' => $exception->getMessage()];
        }
    }

    public function mediaUploadAppend($media_id, $file)
    {
        $media_path = $file->getRealPath();
        $user = User::find(Auth::user()->id);
        if (is_null($user['oauth_token']) || is_null($user['oauth_token_secret'])) {
            return redirect('home');
        }
        $stack = HandlerStack::create();
        $middleware = new Oauth1([
            'consumer_key' => $this->_twitter_consumer_api_key,
            'consumer_secret' => $this->_twitter_consumer_api_api_secret_key,
            'token' => $user['oauth_token'],
            'token_secret' => $user['oauth_token_secret'],
        ]);

        $stack->push($middleware);

        $client = new Client([
            'base_uri' => 'https://upload.twitter.com/1.1/',
            'handler' => $stack,
            'auth' => 'oauth',
        ]);
        try {
            $fp = fopen($media_path, 'r');
            $segment_id = 0;
            while (!feof($fp)) {
                $chunk = fread($fp, 1048576); // 1MB per chunk for this sample

                $response = $client->post('media/upload.json',
                    [
                        'multipart' => [
                            [
                                'name' => 'command',
                                'contents' => 'APPEND',
                            ],
                            [
                                'name' => 'media_id',
                                'contents' => $media_id,
                            ],
                            [
                                'name' => 'segment_index',
                                'contents' => $segment_id,
                            ],
                            [
                                'name' => 'media_data',
                                'contents' => base64_encode($chunk),
                            ],
                        ],
                    ]
                );
                $segment_id++;
                sleep(10);
            }

            return ['status' => true, 'data' => json_decode($response->getStatusCode())];
        } catch (\Exception $exception) {
            return ['status' => false, 'message APPEND' => $exception->getMessage()];
        }
    }

    public function mediaUploadFinalize($media_id)
    {
        $user = User::find(Auth::user()->id);
        if (is_null($user['oauth_token']) || is_null($user['oauth_token_secret'])) {
            return redirect('home');
        }
        $stack = HandlerStack::create();
        $middleware = new Oauth1([
            'consumer_key' => $this->_twitter_consumer_api_key,
            'consumer_secret' => $this->_twitter_consumer_api_api_secret_key,
            'token' => $user['oauth_token'],
            'token_secret' => $user['oauth_token_secret'],
        ]);

        $stack->push($middleware);

        $client = new Client([
            'base_uri' => 'https://upload.twitter.com/1.1/',
            'handler' => $stack,
            'auth' => 'oauth',
        ]);
        try {
            $response = $client->post('media/upload.json',
                [
                    'query' => [
                        'command' => 'FINALIZE',
                        'media_id' => $media_id,
                    ],
                ]
            );

            return ['status' => true, 'data' => (array)json_decode($response->getBody()->getContents())];
        } catch (\Exception $exception) {
            return ['status' => false, 'message FINALIZE' => $exception->getMessage()];
        }
    }

    public function mediaUploadStatus($media_id)
    {
        $user = User::find(Auth::user()->id);
        if (is_null($user['oauth_token']) || is_null($user['oauth_token_secret'])) {
            return redirect('home');
        }
        $stack = HandlerStack::create();
        $middleware = new Oauth1([
            'consumer_key' => $this->_twitter_consumer_api_key,
            'consumer_secret' => $this->_twitter_consumer_api_api_secret_key,
            'token' => $user['oauth_token'],
            'token_secret' => $user['oauth_token_secret'],
        ]);

        $stack->push($middleware);

        $client = new Client([
            'base_uri' => 'https://upload.twitter.com/1.1/',
            'handler' => $stack,
            'auth' => 'oauth',
        ]);
        try {
            $response = $client->get('media/upload.json',
                [
                    'query' => [
                        'command' => 'STATUS',
                        'media_id' => $media_id,
                    ],
                ]
            );

            return ['status' => true, 'data' => (array)json_decode($response->getBody()->getContents())];
        } catch (\Exception $exception) {
            return ['status' => false, 'message' => $exception->getMessage()];
        }
    }

    public function getReplyOfTweet($tweet)
    {
        $screen_name = $tweet->user->screen_name;
        $tweet_id = $tweet->id_str;
        $max_id = '';
        $resultSearch = array();
        do {
            $query = array(
                'q' => 'to:' . $screen_name,
                'since_id' => $tweet_id,
                'max_id' => $max_id,
                'count' => 100,
            );
            $result = $this->search($query);
//            dd((array)$result['data']->statuses);
//            array_merge($resultSearch, (array)$result['data']->statuses);

//            $resultSearch = $this->search($query);
//            dd($resultSearch);
            foreach ((array) $result['data']->statuses as $item) {
                array_push($resultSearch, $item);
//                dd($item);
               $resultReplyOfReply = $this->getReplyOfTweet($item);
               foreach ($resultReplyOfReply as $resultReplyOfReplyItem) {
                   array_push($resultSearch, $resultReplyOfReplyItem);
               }
//                array_merge($resultSearch, $this->getReplyOfTweet($item));
            }
//            dd($resultSearch);
            return $resultSearch;
        } while (count($resultSearch) !== 100);
    }

    public function search($query)
    {
        $user = User::find(Auth::user()->id);
        if (is_null($user['oauth_token']) || is_null($user['oauth_token_secret'])) {
            return redirect('home');
        }
        $stack = HandlerStack::create();
        $middleware = new Oauth1([
            'consumer_key' => $this->_twitter_consumer_api_key,
            'consumer_secret' => $this->_twitter_consumer_api_api_secret_key,
            'token' => $user['oauth_token'],
            'token_secret' => $user['oauth_token_secret'],
        ]);

        $stack->push($middleware);

        $client = new Client([
            'base_uri' => 'https://api.twitter.com/1.1/',
            'handler' => $stack,
            'auth' => 'oauth',
        ]);

        try {
            $response = $client->get("search/tweets.json", [
                'query' => $query,
            ]);

            return ['status' => true, 'data' => json_decode($response->getBody()->getContents())];
        } catch (\Exception $exception) {
            return ['status' => false, 'message' => $exception->getMessage()];
        }
    }
}
