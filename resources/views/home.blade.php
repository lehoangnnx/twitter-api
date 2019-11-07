@extends('layouts.app')

@section('content')
    <div class="container">
        @if(!$check_oauth_token)
            <div class="row justify-content-center">
                <a href="https://api.twitter.com/oauth/authorize?oauth_token={{ $oauth_token  }}"
                   class="btn btn-success mb-2">Connect Twitter</a>
            </div>
        @else
            <div class="row justify-content-center">
                <div class="col-6">
                    <div class="card mt-3 tab-card">
                        <div class="card-header tab-card-header">
                            <h3>Create Tweet</h3>
                        </div>
                        <div class="tab-content p-3" id="myTabContent">
                            <form action="{{ route('createTweet') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method("POST")
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Content</label>
                                    <textarea name="content" class="form-control" id="exampleFormControlTextarea1"
                                              rows="3"></textarea>
                                    <input type="file" name="file" class="mt-2">
                                </div>
                                <button type="submit" class="btn btn-primary">Create</button>
                            </form>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            <div class="row justify-content-center">
                @isset($user_time_line)
                    @foreach($user_time_line as $item)
                        <div class="col-6">
                            <div class="card mt-3 tab-card">
                                <div class="card-header tab-card-header">
                                    <ul class="nav nav-tabs card-header-tabs" id="myTab{{$loop->index}}" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="one-tab" data-toggle="tab"
                                               href="#one{{$loop->index}}" role="tab" aria-controls="One"
                                               aria-selected="true">One</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="two-tab" data-toggle="tab"
                                               href="#two{{$loop->index}}" role="tab" aria-controls="Two"
                                               aria-selected="false">Two</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="three-tab" data-toggle="tab"
                                               href="#three{{$loop->index}}" role="tab" aria-controls="Three"
                                               aria-selected="false">Three</a>
                                        </li>
                                    </ul>
                                </div>

                                <div class="tab-content p-3" id="myTabContent{{$loop->index}}">
                                    <div class="tab-pane fade show active" id="one{{$loop->index}}" role="tabpanel"
                                         aria-labelledby="one-tab">
                                        <h5 class="card-title">{{ $item->text  }}</h5>
                                        @if($item->retweeted)
                                            <a href="{{ route('retweetTweet', ['id' => $item->id_str, 'type' => 'unretweet']) }}"
                                               class="card-text">{{ $item->retweet_count  }} UnRetweet</a> <br>
                                        @else
                                            <a href="{{ route('retweetTweet', ['id' => $item->id_str, 'type' => 'retweet']) }}"
                                               class="card-text">{{ $item->retweet_count  }} Retweet</a> <br>
                                        @endif

                                        @if($item->favorited)
                                            <a href="{{ route('favoritesTweet', ['id' => $item->id_str, 'type' => 'destroy']) }}"
                                               class="card-text">{{ $item->favorite_count  }} UnFavorited</a> <br>
                                        @else
                                            <a href="{{ route('favoritesTweet', ['id' => $item->id_str, 'type' => 'create']) }}"
                                               class="card-text">{{ $item->favorite_count  }} Favorited</a> <br>
                                        @endif
                                        <form action="{{ route('replyTweet') }}" method="POST">
                                            @csrf
                                            @method("POST")
                                            <input type="text" class="form-control" name="content" placeholder="Content Reply">
                                            <button type="submit" class="btn btn-primary m-2">Reply</button>
                                        </form>
                                        <a href="{{ route('deleteTweet', [ 'id' => $item->id_str ])  }}"
                                           class="btn btn-danger m-2">Delete</a>
                                    </div>
                                    <div class="tab-pane fade" id="two{{$loop->index}}" role="tabpanel"
                                         aria-labelledby="two-tab">
                                        <h5 class="card-title">Tab Card Two</h5>
                                        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                                        <a href="#" class="btn btn-primary">Go somewhere</a>
                                    </div>
                                    <div class="tab-pane fade" id="three{{$loop->index}}" role="tabpanel"
                                         aria-labelledby="three-tab">
                                        <h5 class="card-title">Tab Card Three</h5>
                                        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                                        <a href="#" class="btn btn-primary">Go somewhere</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endisset
            </div>
    </div>
    </div>
@endsection
