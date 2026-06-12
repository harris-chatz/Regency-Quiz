@extends('layouts.app')

@section('content')
  <div class="container" data-barba="container" data-barba-namespace="question-2">
    <div class="question-content middle-align container-animations">
      <div class="main-image">
        <img src="/assets/images/img-q2.png" alt="" class="main" />
        <img src="/assets/images/icon-q1-l1.svg" alt="" class="icon left-1" width="55" />
        <img src="/assets/images/icon-q1-l2.svg" alt="" class="icon left-2" width="58" />
      </div>
      <div class="logo-image">
        <img src="/assets/images/game_entertainment.svg" alt="" />
      </div>
      <div class="question">
        <h3 class="title">Τι προτιμάς;</h3>
        <ul class="answers flex col">
          <li class="answer verde">DJ Party</li>
          <li class="answer giallo">Live Dance Show</li>
          <li class="answer rosa">Live Band</li>
        </ul>
      </div>
    </div>
  </div>
@endsection
