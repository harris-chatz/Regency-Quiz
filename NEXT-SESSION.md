# Regency Quiz — Next Session

Project: **RCMP Quiz Game** για το Regency Casino Mont Parnes.
Stack: **Laravel 11 (PHP 8.4) + Vue 3 + Vite + Pinia + Tailwind v4 + SQLite (WAL) + Docker**.

---

## ✅ Τι έχει ολοκληρωθεί

### Infrastructure
- Docker stack (4 services): `app` (PHP-FPM), `nginx`, `node` (Vite), `sqlite-web`
- SQLite με WAL mode + 5s busy timeout
- CORS configured για `http://localhost:5173`
- Laravel API layer εγκατεστημένο (Sanctum)
- Greek locale (`el`) + `Europe/Athens` timezone

### Backend (Laravel)
- Migrations: `users`, `cache`, `jobs`, `personal_access_tokens`, **`questions`**, **`question_options`**
- Seeded τα 3 questions + 9 options από το WBS (Task #3)
- Models: `Question`, `QuestionOption`
- API Resources: `QuestionResource`, `QuestionOptionResource`
- Controller: `App\Http\Controllers\Api\QuestionController`
- Routes:
  - `GET /api/health`
  - `GET /api/quiz/questions`

### Frontend (Vue 3)
- Pinia store `quiz.ts` με: `hasVisitedCasino`, `preGameConsent`, `answers[]`, `dominantColor` (με 1-1-1 tie-breaker per WBS), `hiddenFormPayload`
- API services: `api.ts` (fetch wrapper), `quiz.ts` (questions)
- Router με 5 routes + navigation guards
- Views ολοκληρωμένες:
  - **Task #1** `OnboardingView` (ΝΑΙ/ΟΧΙ + 4s countdown modal)
  - **Task #1** `SorryRegisteredView`
  - **Task #2** `ConsentView` (checkbox + validation)
  - **Task #3** `QuizView` (3-step quiz, buttons, hidden form, progress bar, back button)
  - `ResultView` (placeholder — Task #4 αύριο)

---

## ⏭️ Pending για αύριο (priority order)

| # | Task | Notes |
|---|------|-------|
| 1 | **Backend leads table** | Migration + `Lead` model. Fields: `name`, `email`, `phone`, `age_consent`, `terms_consent`, `marketing_consent`, `persona_color`, `has_visited_casino`, `answers` (JSON), `redemption_code`, `ip_address`, `user_agent`. *Migration file ήδη stub-αρισμένο: `backend/database/migrations/2026_05_22_181740_create_leads_table.php`* |
| 2 | **Backend LeadController** | `POST /api/leads` με FormRequest validation (Greek mobile regex `^69\d{8}$`, accepted consents, email format). Επιστρέφει `lead` + `redemption_code`. |
| 3 | **Task #4 ResultView** | Persona copy από WBS — 3 versions: <br>• **green** "Είσαι cool τύπος!"<br>• **yellow** "Είσαι λάτρης της περιπέτειας!"<br>• **pink** "Είσαι ρομαντική ψυχή!"<br>CTA → `/form` |
| 4 | **Task #5 FormView** | 6 fields + 3 consents + client-side validation + POST to `/api/leads` |
| 5 | **Task #8 + #11 ThankYouView** | Success message, εμφάνιση redemption code, share buttons (WhatsApp/Viber/Email) |
| 6 | **Router** | Routes `/form` και `/thank-you` |
| 7 | **End-to-end test** | Full funnel verification |

### Επόμενα (μετέπειτα days)
- **Task #7** Apifon SMS integration (OAuth token: `EmHav07ZilqrjgFgzWa32S1uFDpaIvKCtyrlStoDUgxRrtjngloaGpQHzYDxaQYi`)
- **Task #9** CSV export page (authenticated)

---

## 🔧 Πώς να ξαναξεκινήσεις αύριο

Από PowerShell στο root του project:

```powershell
# 1. Σήκωσε τα containers
docker compose up -d

# 2. Έλεγξε ότι όλα τρέχουν
docker compose ps

# 3. Άνοιξε στον browser
#    Laravel:    http://localhost:8080
#    Vue:        http://localhost:5173
#    SQLite UI:  http://localhost:8081
```

Αν χρειαστεί restart κάποιο container ή recreate μετά από Dockerfile change:
```powershell
docker compose up -d --force-recreate <service>
```

Artisan commands μέσα στο container:
```powershell
docker compose exec app php artisan <command>
```

### Πριν κλείσεις σήμερα (προαιρετικό)
Αν θες να ελευθερώσεις πόρους:
```powershell
docker compose down
```
Τα data της SQLite μένουν στο `backend/database/database.sqlite` (δεν χάνονται).

---

## 📁 Project structure

```
Regency Quiz/
├── backend/                  # Laravel 11
│   ├── app/
│   │   ├── Http/Controllers/Api/QuestionController.php
│   │   ├── Http/Resources/Question{,Option}Resource.php
│   │   └── Models/Question.php, QuestionOption.php
│   ├── database/
│   │   ├── database.sqlite
│   │   ├── migrations/*_create_{questions,question_options,leads}_table.php
│   │   └── seeders/QuestionSeeder.php
│   ├── config/cors.php       # custom (FRONTEND_URL allowed)
│   ├── routes/api.php
│   └── .env                  # APIFON_*, QUIZ_*, FRONTEND_URL
├── frontend/                 # Vue 3 + Vite
│   └── src/
│       ├── services/{api,quiz}.ts
│       ├── stores/quiz.ts
│       ├── router/index.ts
│       ├── views/{Onboarding,SorryRegistered,Consent,Quiz,Result}View.vue
│       ├── App.vue
│       ├── main.ts
│       └── assets/main.css   # Tailwind v4 + theme
├── docker/
│   ├── php/Dockerfile        # PHP 8.4-fpm-alpine
│   ├── nginx/default.conf
│   └── node/Dockerfile       # Node 22-alpine
├── docker-compose.yml
├── .gitignore
└── NEXT-SESSION.md           # this file
```

---

## 🎨 Σημαντικές αποφάσεις/conventions

- **Persona tie-breaker (1-1-1):** χρησιμοποιούμε το χρώμα της **τελευταίας** απάντησης (per WBS Task #4).
- **Redemption code:** generic προς το παρόν (`RCMP2026`), επιλέγεται από `.env` (`QUIZ_REDEMPTION_CODE_MODE=generic`).
- **Hidden form:** εκτός από το Pinia store, οι απαντήσεις γράφονται και σε πραγματικό `<form>` με hidden inputs (per WBS Task #3 — "hidden form field").
- **API base URL:** `http://localhost:8080/api` (env: `VITE_API_BASE_URL`).

---

Καλό βράδυ! Αύριο συνεχίζουμε από το #1 (backend leads table). 🎲
