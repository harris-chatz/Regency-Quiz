@extends('layouts.app')

@section('content')
  <div class="container" data-barba="container" data-barba-namespace="intro">
    <div class="intro-content middle-align msg">
      <div class="main-image">
        <img src="/assets/images/img_01.png" alt="" />
      </div>
      <div class="logo-image">
        <img src="/assets/images/game_blue.svg" alt="" />
      </div>
      <div class="intro-text">
        <p>
          Είναι η πρώτη φορά που θα επισκεφτείς το Regency Casino Mont Parnes;
        </p>
      </div>

      <div class="intro-buttons flex">
        <button class="intro-btn ci-btn">NAI</button>
        <button class="intro-btn non-btn">OXI</button>
      </div>

      <p class="intro-terms-msg">
        Για τους Όρους Συμμετοχής, παρακαλώ πατήστε
        <a href="https://athens.regencycasinos.gr/oroi-digital-game-regency-casino-mont-parnes/">εδώ</a>
      </p>

      <div class="start-btn">
        <img src="/assets/images/start_btn.svg" alt="" />
        <div class="start-btn-text">
          <p>SUPER!</p>
          <p>Το παιχνίδι ξεκινά!</p>
        </div>
      </div>
    </div>
  </div>
@endsection
