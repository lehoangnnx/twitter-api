@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            @if(!$check_oauth_token)
                <a href="https://api.twitter.com/oauth/authorize?oauth_token={{ $oauth_token  }}"
                   class="btn btn-success mb-2">Connect Twitter</a>
            @endif
        </div>
        <div class="row justify-content-center">
            <div class="col-6">
                <div class="card mt-3 tab-card">
                    <div class="card-header tab-card-header">
                        <h3>Create Tweet</h3>
                    </div>
                    <div class="tab-content p-3" id="myTabContent">
                        <form action="{{ route('createTweet') }}" method="POST">
                            @csrf
                            @method("POST")
                            <div class="form-group">
                                <label for="exampleInputEmail1">Content</label>
                                <textarea name="content" class="form-control" id="exampleFormControlTextarea1" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Create</button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
        <div class="row justify-content-center">
            @foreach($home_time_line as $item)
            <div class="col-6">
                <div class="card mt-3 tab-card">
                    <div class="card-header tab-card-header">
                        <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link" id="one-tab" data-toggle="tab" href="#one" role="tab" aria-controls="One" aria-selected="true">One</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="two-tab" data-toggle="tab" href="#two" role="tab" aria-controls="Two" aria-selected="false">Two</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="three-tab" data-toggle="tab" href="#three" role="tab" aria-controls="Three" aria-selected="false">Three</a>
                            </li>
                        </ul>
                    </div>

                    <div class="tab-content p-3" id="myTabContent">
                        <div class="tab-pane fade show active" id="one" role="tabpanel" aria-labelledby="one-tab">
                            <h5 class="card-title">Tab Card One</h5>
                            <p class="card-text">{{ $item->text  }}</p>
                            <a href="#" class="btn btn-primary">Go somewhere</a>
                            <a href="{{ route('deleteTweet', [ 'id' => $item->id_str])  }}" class="btn btn-danger right">Delete</a>
                        </div>
                        <div class="tab-pane fade" id="two" role="tabpanel" aria-labelledby="two-tab">
                            <h5 class="card-title">Tab Card Two</h5>
                            <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                            <a href="#" class="btn btn-primary">Go somewhere</a>
                        </div>
                        <div class="tab-pane fade" id="three" role="tabpanel" aria-labelledby="three-tab">
                            <h5 class="card-title">Tab Card Three</h5>
                            <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                            <a href="#" class="btn btn-primary">Go somewhere</a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    </div>
@endsection
