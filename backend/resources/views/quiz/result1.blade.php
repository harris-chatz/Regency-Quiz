@extends('layouts.app')

@section('content')
  <div class="container" data-barba="container" data-barba-namespace="result-1">
    <div class="result-content middle-align verde container-animations">
      <div class="main-image">
        <img src="/assets/images/result-1.png" alt="" class="double-left" />
        <img src="/assets/images/img_01.png" alt="" class="double-right" />
      </div>
      <div class="logo-image">
        <img src="/assets/images/game_red.svg" alt="" width="191" />
      </div>
      <div class="result-box">
        <h2 class="title">Είσαι cool τύπος!</h2>
        <p class="info">
          Κατέχεις την τέχνη της καλοπέρασης, με όλη τη σημασία της λέξης!
          Σε όλες τις συνθήκες, με όλες τις παρέες, εσύ έχεις τον τρόπο σου
          να απολαμβάνεις τη στιγμή.
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
