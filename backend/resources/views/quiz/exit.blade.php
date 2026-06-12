@extends('layouts.app')

@section('content')
  <div class="container" data-barba="container" data-barba-namespace="exit">
    <div class="error-content middle-align msg">
      <div class="main-image">
        <img src="/assets/images/img_01.png" alt="" />
      </div>
      <div class="logo-image">
        <img src="/assets/images/game_blue.svg" alt="" />
      </div>

      <div class="error-msg">
        <div class="error-img">
          <img src="/assets/images/error-large.svg" alt="" />
        </div>
        <div class="error-text">
          <p>
            Το παιχνίδι απευθύνεται μόνο σε μη εγγεγραμμένους επισκέπτες.
          </p>

          <p>
            Συνέχισε να απολαμβάνεις μοναδικά προνόμια και να ενημερώνεσαι
            για τις νέες προσφορές.
          </p>

          <p>Κοινοποίησε το παιχνίδι σε μη εγγεγραμμένους φίλους σου!</p>

          <p>
            Πάτησε
            <span class="share-btn">εδώ
              <img src="/assets/images/share-btn.svg" alt="" class="share-icon" />
            </span>
          </p>
        </div>
      </div>

      <div class="share-wrapper social white">
        <ul class="flex">
          <li class="fb">
            <a href="https://www.facebook.com/sharer/sharer.php?u=https://athens.regencycasinos.gr/en/"></a>
          </li>
          <li class="ig"><a href=""></a></li>
          <li class="ml">
            <a href="mailto:?subject=Παίξε και κέρδισε!&body=Σου προτείνω να επισκεφτείς αυτό το site: https://athens.regencycasinos.gr/en/"></a>
          </li>
        </ul>
      </div>
    </div>
  </div>
@endsection
