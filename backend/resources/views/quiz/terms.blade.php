@extends('layouts.app')

@section('content')
  <div class="container" data-barba="container" data-barba-namespace="terms">
    <div class="terms-container middle-align">
      <div class="image-wrapper">
        <div class="top-images">
          <div class="spade-img">
            <img src="/assets/images/spade.svg" alt="" />
          </div>
          <div class="queen-img">
            <img src="/assets/images/QUEEN.png" alt="" />
          </div>
          <div class="glover-img">
            <img src="/assets/images/glover.svg" alt="" />
          </div>
        </div>
        <div class="logo-image">
          <img src="/assets/images/game_red.svg" alt="" />
        </div>
        <h2 class="title">ΤΙ ΤΥΠΟΣ ΕΙΣΑΙ;</h2>
        <p>Απάντησε και ένα special δώρο σε περιμένει!</p>
        <div class="form">
          <p class="note">Η ενέργεια αφορά άτομα άνω των 21 ετών</p>
          <div class="check-group">
            <div class="custom-checkbox">
              <input type="checkbox" id="age" name="age" required />
              <label for="age">
                <span class="box" aria-hidden="true"></span>
                Είμαι άνω των 21, έχω διαβάσει και αποδέχομαι τους όρους και
                προϋποθέσεις του παιχνιδιού
              </label>
            </div>
          </div>
          <p class="alert">
            Πρέπει να επιλέξεις/συμπληρώσεις όλα τα παραπάνω για να
            μπορέσεις να προχωρήσεις
          </p>
          <button type="submit">
            <img src="/assets/images/btn-play.svg" alt="" width="152" />
          </button>
        </div>

        <div class="footer-images">
          <div class="img-left">
            <img src="/assets/images/luck.png" alt="" />
          </div>
          <div class="img-right">
            <img src="/assets/images/777.svg" alt="" />
          </div>
        </div>

        <div class="error-msg">
          <div class="error-img">
            <img src="/assets/images/error.svg" alt="" />
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
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
