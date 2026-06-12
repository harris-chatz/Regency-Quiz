@extends('layouts.app')

@section('content')
  <div class="container" data-barba="container" data-barba-namespace="result-3">
    <div class="result-content middle-align rosa container-animations">
      <div class="main-image">
        <img src="/assets/images/result-1.png" alt="" class="double-left" />
        <img src="/assets/images/img-q2.png" alt="" class="double-right" />
      </div>
      <div class="logo-image">
        <img src="/assets/images/game_red.svg" alt="" width="191" />
      </div>
      <div class="result-box">
        <h2 class="title">Είσαι ρομαντική ψυχή!</h2>
        <p class="info">
          Το Notebook και το Crazy Stupid Love γυρίστηκαν για εσένα! Ξέρεις
          να δημιουργείς στιγμές που μένουν ανεξίτηλες και ζεις τα πάντα με
          την ψυχή σου.
        </p>
        <div class="result-action">
          <a href="/submit"></a>
          <p>
            Κέρδισες στο New Regs Game! Δες τις οδηγίες για το δώρο σου και
            διεκδίκησε μοναδικά προνόμια <span>εδώ</span>:
          </p>
        </div>
      </div>
    </div>
  </div>
@endsection
