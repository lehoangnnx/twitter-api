@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-6">
                <div class="card mt-3 tab-card">
                    <div class="card-header tab-card-header">
                        <div>
                            <img src="{{ $tweet->user->profile_image_url_https  }}" alt="">
                            <span>{{ $tweet->user->screen_name  }}</span> <br>
                            <span>{{ $tweet->user->created_at  }}</span>
                        </div>
                    </div>
                    <div class="tab-content p-3" id="myTabContent">
                        <p>{{ $tweet->text  }}</p>
                        <form action="{{ route('replyTweet') }}" method="POST">
                            @csrf
                            @method("POST")
                            <input type="text" class="form-control" name="content" placeholder="Content Reply">
                            <button type="submit" class="btn btn-primary m-2">Reply</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                @isset($resultReply)
                    @foreach($resultReply as $item)
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
                                        <h5 class="card-title">Tab Card One</h5>
                                        <p class="card-text">{{ $item->text  }}</p>
                                        <a href="#" class="btn btn-primary">Go somewhere</a>
                                        <a href="{{ route('deleteTweet', [ 'id' => $item->id_str])  }}"
                                           class="btn btn-danger right">Delete</a>
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
