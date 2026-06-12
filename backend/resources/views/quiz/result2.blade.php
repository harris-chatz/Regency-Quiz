@extends('layouts.app')

@section('content')
  <div class="container" data-barba="container" data-barba-namespace="result-2">
    <div class="result-content middle-align giallo container-animations">
      <div class="main-image">
        <img src="/assets/images/img-q3.png" alt="" class="double-left" />
        <img src="/assets/images/img-q2.png" alt="" class="double-right" />
      </div>
      <div class="logo-image">
        <img src="/assets/images/game_red.svg" alt="" width="191" />
      </div>
      <div class="result-box">
        <h2 class="title">Είσαι λάτρης της περιπέτειας!</h2>
        <p class="info">
          Όλα στα κόκκινα. You live for the thrill και όλα τα σχετικά! Είσαι
          αυτός που προτείνει πάντα τις πιο δυνατές εξόδους και τις πιο
          αυθόρμητες αποδράσεις!
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
