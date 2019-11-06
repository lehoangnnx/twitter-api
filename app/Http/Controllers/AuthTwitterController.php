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
        $this->_twitter_consumer_api_key = config('twitter.twitter_consumer_api_key');
        $this->_twitter_consumer_api_api_secret_key = config('twitter.twitter_consumer_api_api_secret_key');
        $this->_twitter_access_token = config('twitter.twitter_access_token');
        $this->_twitter_access_token_secret = config('twitter.twitter_access_token_secret');
    }

    public function requestToken()
    {
        $user = User::find(Auth::user()->id);
        if (!is_null($user['oauth_token'])) {
            $home_time_line = $this->homeTimeLine();

            return view('home')->with('check_oauth_token', true)
                ->with('home_time_line', $home_time_line['data']);
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
            'auth' => 'oauth'
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
            'auth' => 'oauth'
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
            'auth' => 'oauth'
        ]);

        try {
            $response = $client->get('statuses/home_timeline.json');
            return ['status' => true, 'data' => json_decode($response->getBody()->getContents())];
        } catch (\Exception $exception) {
            return ['status' => false, 'message' => $exception->getMessage()];
        }
    }

    public function createTweet(Request $request)
    {
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
            $response = $client->post('statuses/update.json',
                [
                    'query' => ['status' => $content],
                ]
            );

        } catch (\Exception $exception) {
            return ['status' => false, 'message' => $exception->getMessage()];
        }

        return redirect('home');
    }

    public function deleteTweet(Request $request){
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
}
