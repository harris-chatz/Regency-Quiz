<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- Metadata -->
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <meta name="author" content="" />

    <!-- OpenGraph -->
    <meta property="og:title" content="" />
    <meta property="og:type" content="" />
    <meta property="og:url" content="" />
    <meta property="og:image" content="" />

    <title>Regency Game</title>
    <link rel="stylesheet" href="https://use.typekit.net/ffh5dwp.css" />
    <link rel="stylesheet" href="/styles/fonts2.css?v=3" />
    <link rel="stylesheet" href="/styles/utilities.css?v=3" />
    <link rel="stylesheet" href="/styles/styles.css?v=3" />
    <link rel="stylesheet" href="/styles/footer.css?v=3" />
  </head>
  <body>
    <div class="wipe-overlay">
      <div class="logo-image">
        <img src="/assets/images/game_blue.svg" alt="" />
      </div>
    </div>

    <div class="wrapper" data-barba="wrapper">
      @yield('content')
    </div>

    <footer class="main-footer">
      <div class="container-footer flex">
        <div class="icon">
          <img src="/assets/images/regency-logo.svg" alt="regency logo" width="100" />
        </div>
        <div class="icon">
          <img src="/assets/images/eope-logo.svg" alt="eopae logo" />
        </div>
      </div>

      <div class="footer-wrapper flex">
        <div class="footer-logo">
          <img src="/assets/images/eeep.svg" alt="eeep logo" class="desk" width="135" />
          <img src="/assets/images/eeep-logo-mob.png" alt="eeep logo" class="mob" />
        </div>
        <div class="footer-text">
          21+ | <span>ΑΡΜΟΔΙΟΣ ΡΥΘΜΙΣΤΗΣ ΕΕΕΠ |</span> ΚΙΝΔΥΝΟΣ ΕΘΙΣΜΟΥ &
          ΑΠΩΛΕΙΑΣ ΠΕΡΙΟΥΣΙΑΣ
          <span> | ΕΟΠΑΕ – ΓΡΑΜΜΗ ΣΥΜΒΟΥΛΕΥΤΙΚΗΣ: 1114</span> | ΠΑΙΞΕ ΥΠΕΥΘΥΝΑ
        </div>
      </div>
    </footer>

    <script src="https://unpkg.com/@barba/core"></script>
    <script src="/scripts/scripts.js?v=3"></script>
  </body>
</html>
