@extends('layouts.app')

@section('content')
  <div class="container" data-barba="container" data-barba-namespace="submit-page">
    <div class="submit-content middle-align container-animations">
      <div class="images-wrapper">
        <div class="abs-img left-img">
          <img src="/assets/images/img-sm-1.png" alt="" width="151" />
        </div>
        <div class="abs-img right-img">
          <img src="/assets/images/img_01.png" alt="" width="147" />
        </div>
        <div class="logo-image">
          <img src="/assets/images/game_red.svg" alt="" />
        </div>
      </div>
      <p>
        Συμπλήρωσε τα στοιχεία σου & κέρδισε ΔΩΡΟ μπίρα & γύρο (Παρασκευή,
        Σάββατο και Κυριακή) ή 1 σάντουιτς με 2 μπίρες (Δευτέρα έως Πέμπτη)
      </p>
      <div class="form-wrapper">
        <form action="" class="submit-form">
          <div class="field-group flex col">
            <div class="form-field">
              <input type="email" id="email" placeholder="Διεύθυνση e-mail*" required />
            </div>
            <div class="form-field">
              <input type="text" id="tel" placeholder="Τηλέφωνο*" required />
            </div>
          </div>

          <div class="check-group flex col">
            <div class="custom-checkbox">
              <input type="checkbox" id="age" name="age" required />
              <label for="age">
                <span class="box" aria-hidden="true"></span>
                Είμαι άνω των 21, έχω διαβάσει και αποδέχομαι τους όρους
                και προϋποθέσεις του παιχνιδιού*
              </label>
            </div>

            <div class="custom-checkbox">
              <input type="checkbox" id="news" name="news" />
              <label for="news">
                <span class="box" aria-hidden="true"></span>
                Δέχομαι να λαμβάνω ενημερώσεις μέσω Newsletter, Viber, SMS
                από το Regency Casino Mont Parnes
              </label>
            </div>
          </div>

          <p class="alert">
            Πρέπει να επιλέξεις/συμπληρώσεις όλα τα παραπάνω
            για να μπορέσεις να προχωρήσεις
          </p>

          <button type="submit" class="disabled">Υποβολή</button>
        </form>

        <p class="note">
          ΣΗΜΕΙΩΣΗ: Το δώρο παραλαμβάνεται από την υποδοχή του καζίνο και για την απόκτησή του δεν απαιτείται παικτική δραστηριότητα
        </p>
      </div>

      <div class="congrats-msg">
        <img src="/assets/images/error.svg" alt="" />
        <p>
          <span>Συγχαρητήρια, κέρδισες!</span>Θα λάβεις Viber/SMS με οδηγίες για την παραλαβή του δώρου σου.
        </p>
        <p>Κοινοποίησε το παιχνίδι σε μη εγγεγραμμένους φίλους σου!</p>
        <p>Πάτησε <span class="share-btn">εδώ <img src="/assets/images/share-btn.svg" alt="" class="share-icon"></span></p>
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
