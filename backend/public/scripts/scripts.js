// transitions

// Μια απλή βοηθητική συνάρτηση για να καθυστερεί το Barba όσο διαρκεί το CSS transition
const delay = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
const overlay = document.querySelector(".wipe-overlay");

// -----------------------------------------------------------------------------
// Quiz state (localStorage) — minimal tracking για το backend submit
// -----------------------------------------------------------------------------
const QUIZ_STATE_KEY = "regency_quiz_state";

// Maps τα ιταλικά color classes που χρησιμοποιεί ο FE dev στις σωστές enum
// τιμές που περιμένει το Laravel backend.
const COLOR_MAP = { verde: "green", giallo: "yellow", rosa: "pink" };

function getQuizState() {
  try {
    return JSON.parse(localStorage.getItem(QUIZ_STATE_KEY)) || {};
  } catch (_e) {
    return {};
  }
}

function setQuizState(patch) {
  const current = getQuizState();
  const next = { ...current, ...patch };
  localStorage.setItem(QUIZ_STATE_KEY, JSON.stringify(next));
  return next;
}

function clearQuizState() {
  localStorage.removeItem(QUIZ_STATE_KEY);
}

function recordAnswer(namespace, answerEl) {
  // Βρίσκουμε ποιο χρώμα κλικαρίστηκε από το CSS class του <li>
  const colorClass = ["verde", "giallo", "rosa"].find((c) =>
    answerEl.classList.contains(c),
  );
  if (!colorClass) return;
  const persona = COLOR_MAP[colorClass];

  const state = getQuizState();
  const answers = Array.isArray(state.answers) ? state.answers : [];

  // namespace = "question-1" | "question-2" | "question-3"
  const qNumber = parseInt(namespace.split("-")[1], 10);

  // αντικαθιστά υπάρχουσα απάντηση για την ίδια ερώτηση (αν π.χ. ο user γύρισε πίσω)
  const existingIdx = answers.findIndex((a) => a.question === qNumber);
  const entry = { question: qNumber, color: persona, at: new Date().toISOString() };
  if (existingIdx >= 0) answers.splice(existingIdx, 1, entry);
  else answers.push(entry);

  setQuizState({ answers, persona_color: persona });
}

// -----------------------------------------------------------------------------
// API submission
// -----------------------------------------------------------------------------
async function submitLeadToApi() {
  const state = getQuizState();
  const email = document.querySelector(".submit-content #email").value.trim();
  const phone = document.querySelector(".submit-content #tel").value.trim();
  const ageConsent = document.querySelector(".submit-content #age").checked;
  const newsConsent = document.querySelector(".submit-content #news").checked;

  const payload = {
    email,
    phone,
    age_consent: ageConsent,
    marketing_consent: newsConsent,
    persona_color: state.persona_color || null,
    has_visited_casino: state.has_visited_casino ?? false,
    started_at: state.started_at || null,
  };

  const res = await fetch("/api/leads", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
    },
    body: JSON.stringify(payload),
  });

  if (!res.ok) {
    const body = await res.json().catch(() => ({}));
    const err = new Error(body.message || `HTTP ${res.status}`);
    err.status = res.status;
    err.payload = body;
    throw err;
  }

  return await res.json();
}

// -----------------------------------------------------------------------------
// Barba transitions (ως είχαν)
// -----------------------------------------------------------------------------
barba.init({
  transitions: [
    {
      name: "wipe-transition",

      // 1. Κατεβαίνει το μαύρο ορθογώνιο
      async leave(data) {
        overlay.classList.remove("is-loaded");
        overlay.classList.add("is-loading");
        await delay(500); // Περιμένουμε να καλυφθεί η οθόνη
      },

      // 2. ΜΟΛΙΣ καλυφθεί η οθόνη και ΠΡΙΝ ξεκινήσει η επόμενη σελίδα,
      // εξαφανίζουμε ακαριαία την παλιά σελίδα που βρίσκεται από πίσω
      beforeEnter(data) {
        data.current.container.style.display = "none";
      },

      // 3. Το μαύρο ορθογώνιο ανεβαίνει και αποκαλύπτει τη νέα σελίδα
      async enter(data) {
        overlay.classList.remove("is-loading");
        overlay.classList.add("is-loaded");
        setUp();
        await delay(500);
      },

      afterEnter(data) {
        const delayedElement = data.next.container.querySelector(
          ".container-animations",
        );

        if (delayedElement) {
          delayedElement.classList.add("animate-in");
          const hasAnswers = document.querySelector(".answers");
          if (hasAnswers) {
            setTimeout(function () {
              delayedElement.classList.add("reset-delay");
            }, 3000);
          }
        }
      },

      beforeLeave() {
        if (autoRedirectTimer != null) {
          clearTimeout(autoRedirectTimer);
        }
      },
    },
  ],
});

function setUp() {
  let autoRedirectTimer = null;

  // landing

  const landingPage = document.querySelector(".landing-content");

  if (landingPage) {
    // Νέο funnel ξεκινά — καθαρίζω το παλιό quiz state
    clearQuizState();
    setQuizState({ started_at: new Date().toISOString() });

    const landingLogo = document.querySelector(".game-logo");
    const landingImg = document.querySelector(".landing-img");
    // const landingMsg = document.querySelector(".landing-msg");
    const landingBtn = document.querySelector(".landing-panel-btn");

    setTimeout(function () {
      landingPage.classList.add("active");
      setTimeout(function () {
        landingBtn.classList.add("on");
      }, 1500);
    }, 1000);
  }

  // intro buttons

  const introBtns = document.querySelectorAll(".intro-btn");

  if (introBtns.length > 0) {
    const startBtn = document.querySelector(".start-btn");
    const errorBtn = document.querySelector(".error-msg");
    introBtns.forEach((btn) =>
      btn.addEventListener("click", function () {
        if (btn.classList.contains("ci-btn")) {
          // ΝΑΙ ("πρώτη φορά") → has_visited_casino = false
          setQuizState({ has_visited_casino: false });

          const box = btn.closest(".msg");
          if (box && startBtn) {
            startBtn.classList.add("active");
            setTimeout(function () {
              box.classList.add("msg-on");
              console.log("timer set");
              autoRedirectTimer = setTimeout(function () {
                barba.go("./terms.html");
              }, 3000);
            }, 400);
          }
        }
        if (btn.classList.contains("non-btn")) {
          // ΟΧΙ → ΕΧΕΙ επισκεφθεί ξανά → exit
          setQuizState({ has_visited_casino: true });

          btn.classList.add("active");
          setTimeout(function () {
            barba.go("./exit.html");
          }, 1500);
        }
      }),
    );

    startBtn.addEventListener("click", function () {
      clearTimeout(autoRedirectTimer);
      barba.go("./terms.html");
    });
  }

  // answer buttons

  const answers = document.querySelectorAll(".answer");

  if (answers.length > 0) {
    answers.forEach((ans) => {
      ans.addEventListener("click", function () {
        const namespace = getCurrentNamespace();
        // Καταγραφή της απάντησης στο localStorage ΠΡΙΝ το barba.go
        recordAnswer(namespace, ans);
        const link = nameSpaceToLink(namespace, ans);
        barba.go(link);
      });
    });
  }

  // check boxes

  const checkTerms2 = document.querySelector(".terms-container #age");

  if (checkTerms2) {
    // terms button

    const termsBtn = document.querySelector(".terms-container button");
    if (termsBtn) {
      termsBtn.addEventListener("click", function () {
        console.log("click");
        if (checkTerms2.checked) {
          barba.go("./question-1.html");
        } else {
          console.log("warning");
          const warningMsg = document.querySelector(".terms-container .alert");
          console.log(warningMsg);
          if (warningMsg) warningMsg.classList.add("on");
        }
      });
    }
  }

  const submitBtn = document.querySelector(".submit-content button");
  if (submitBtn) {
    submitBtn.addEventListener("click", async function (event) {
      event.preventDefault();

      if (submitBtn.classList.contains("disabled")) {
        console.log("error");
        const errorMsg = document.querySelector(".submit-content .alert");
        if (errorMsg) errorMsg.classList.add("on");
        return;
      }

      // Αποτρέπω double-submit
      if (submitBtn.dataset.sending === "1") return;
      submitBtn.dataset.sending = "1";

      const errorMsgEl = document.querySelector(".submit-content .alert");
      if (errorMsgEl) errorMsgEl.classList.remove("on");

      try {
        await submitLeadToApi();

        const container = document.querySelector(".container .submit-content");
        if (container) {
          container.classList.add("msg-pre");
          setTimeout(function () {
            container.classList.add("msg-on");
          }, 400);
        }
      } catch (e) {
        console.error("Submit failed", e);
        submitBtn.dataset.sending = "";

        if (errorMsgEl) {
          // Δείχνω το πρώτο message validation error αν υπάρχει
          let msg = "Παρουσιάστηκε σφάλμα. Δοκίμασε ξανά σε λίγο.";
          if (e.status === 422 && e.payload && e.payload.errors) {
            const firstField = Object.values(e.payload.errors)[0];
            if (Array.isArray(firstField) && firstField.length > 0) {
              msg = firstField[0];
            }
          }
          errorMsgEl.textContent = msg;
          errorMsgEl.classList.add("on");
        }
      }
    });
  }

  const submitTerms1 = document.querySelector(".submit-content #age");
  // const submitTerms2 = document.querySelector(".submit-content #terms");
  const submitTerms3 = document.querySelector(".submit-content #news");
  const emailInput = document.querySelector(".submit-content #email");
  const telInput = document.querySelector(".submit-content #tel");

  if (submitTerms1 && submitTerms3) {
    submitTerms1.addEventListener("change", checkFormValidity);
    emailInput.addEventListener("input", checkFormValidity);
    telInput.addEventListener("input", checkFormValidity);
  }

  function validateEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
  }

  function validateDigits(value) {
    const regex = /^\d{10}$/;
    return regex.test(value);
  }

  function checkFormValidity() {
    const isNameValid = validateDigits(telInput.value.trim());

    // 2. Έλεγχος αν το email είναι έγκυρο
    const isEmailValid = emailInput.checkValidity();

    // 3. Έλεγχος αν το checkbox είναι checked
    const isCheckboxValid = submitTerms1.checked;

    // Αν ΚΑΙ τα 3 είναι true, τότε το κουμπί ενεργοποιείται (disabled = false)
    if (isNameValid && isEmailValid && isCheckboxValid) {
      // submitBtn.disabled = false;
      submitBtn.classList.remove("disabled");
    } else {
      // submitBtn.disabled = true;
      submitBtn.classList.add("disabled");
    }
  }

  const share = document.querySelector(".share-btn");

  if (share) {
    share.addEventListener("click", function () {
      const box = document.querySelector(".middle-align");
      if (box) {
        box.classList.add("share-on");
      }
    });
  }

  setUpSocialShare();
}

// -----------------------------------------------------------------------------
// Social share buttons — Facebook / Instagram / Email
// Δίνει λειτουργικά links στα social κουμπιά ώστε να ανοίγει το αντίστοιχο
// share dialog για τον σύνδεσμο του παιχνιδιού.
// -----------------------------------------------------------------------------
function setUpSocialShare() {
  const shareUrl = window.location.origin + "/";
  const shareText = "Παίξε το New Regs Game και διεκδίκησε μοναδικά προνόμια!";
  const encode = encodeURIComponent;

  document.querySelectorAll(".share-wrapper").forEach(function (wrapper) {
    const fbLink = wrapper.querySelector("li.fb > a");
    const igLink = wrapper.querySelector("li.ig > a");
    const mailLink = wrapper.querySelector("li.ml > a");

    if (fbLink) {
      fbLink.href =
        "https://www.facebook.com/sharer/sharer.php?u=" + encode(shareUrl);
      fbLink.target = "_blank";
      fbLink.rel = "noopener noreferrer";
    }

    if (mailLink) {
      mailLink.href =
        "mailto:?subject=" +
        encode("New Regs Game") +
        "&body=" +
        encode(shareText + " " + shareUrl);
    }

    if (igLink) {
      // Το Instagram δεν διαθέτει direct web share link. Σε κινητά ανοίγουμε
      // το native share sheet· διαφορετικά αντιγράφουμε τον σύνδεσμο.
      igLink.href = "https://www.instagram.com/";
      igLink.target = "_blank";
      igLink.rel = "noopener noreferrer";
      igLink.addEventListener("click", function (e) {
        if (navigator.share) {
          e.preventDefault();
          navigator
            .share({ title: "New Regs Game", text: shareText, url: shareUrl })
            .catch(function () {});
        } else if (navigator.clipboard && navigator.clipboard.writeText) {
          e.preventDefault();
          navigator.clipboard
            .writeText(shareUrl)
            .then(function () {
              alert(
                "Ο σύνδεσμος αντιγράφηκε! Επικόλλησέ τον στο Instagram story ή μήνυμα.",
              );
              window.open("https://www.instagram.com/", "_blank", "noopener");
            })
            .catch(function () {
              window.open("https://www.instagram.com/", "_blank", "noopener");
            });
        }
      });
    }
  });
}

//  Επιστρέφει το τρέχον namespace του barba
function getCurrentNamespace() {
  const currentContainer = document.querySelector('[data-barba="container"]');

  return currentContainer
    ? currentContainer.getAttribute("data-barba-namespace")
    : null;
}

// μετατροπή namespace σε link
function nameSpaceToLink(namespace, answer) {
  if (namespace === "question-1") return "./question-2.html";
  if (namespace === "question-2") return "./question-3.html";
  if (answer.classList.contains("verde")) return "./result-1.html";
  if (answer.classList.contains("giallo")) return "./result-2.html";
  if (answer.classList.contains("rosa")) return "./result-3.html";
}

let autoRedirectTimer = null;
setUp();

function getRandomInt(max) {
  return Math.ceil(Math.random() * max);
}
