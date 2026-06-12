@extends('layouts.app')

@section('content')
  <div class="container" data-barba="container" data-barba-namespace="question-1">
    <div class="question-content middle-align container-animations">
      <div class="main-image">
        <img src="/assets/images/img-q1.png" alt="" class="main" />
      </div>
      <div class="logo-image">
        <img src="/assets/images/game_adrenline.svg" alt="" />
      </div>
      <div class="question">
        <h3 class="title">Ποιο είναι το αγαπημένο σου χρώμα;</h3>
        <ul class="answers flex col">
          <li class="answer verde">Πράσινο</li>
          <li class="answer giallo">Κίτρινο</li>
          <li class="answer rosa">Ροζ</li>
        </ul>
      </div>
    </div>
  </div>
@endsection
