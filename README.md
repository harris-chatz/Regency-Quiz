# 🎰 Regency Quiz — RCMP Quiz Game

> **Mobile-first promotional landing page** για το Regency Casino Mont Parnes.
> Συνδυάζει gamification (3-step persona quiz) με lead generation,
> SMS αποστολή κωδικού εξαργύρωσης μέσω Apifon, και admin CSV export.

---

## 📑 Πίνακας Περιεχομένων

- [Τι κάνει](#-τι-κάνει)
- [Tech Stack](#-tech-stack)
- [Προαπαιτούμενα](#-προαπαιτούμενα)
- [Εγκατάσταση από το μηδέν](#-εγκατάσταση-από-το-μηδέν)
- [Καθημερινή λειτουργία](#-καθημερινή-λειτουργία)
- [Project URLs](#-project-urls)
- [Configuration (.env)](#-configuration-env)
- [Apifon SMS Integration](#-apifon-sms-integration)
- [Admin Panel & CSV Export](#-admin-panel--csv-export)
- [Database Operations](#-database-operations)
- [Δομή του project](#-δομή-του-project)
- [Troubleshooting](#-troubleshooting)
- [Backup & Restore](#-backup--restore)
- [Production Deployment](#-production-deployment)

---

## 🎯 Τι κάνει

Single-page funnel σε mobile-first design:

```
Landing → Intro (ΝΑΙ/ΟΧΙ) → Terms → 3 Questions → Result → Form → SMS με κωδικό
                  │
                  ΟΧΙ → Exit (ήδη επισκέφθηκε το casino)
```

**Persona logic:** Κάθε απάντηση σε ερώτηση είναι χρωματισμένη (`green` / `yellow` / `pink`).
Το persona ορίζεται από την **τελευταία απάντηση** (Q3) — αυτό καθορίζει σε ποιο `result-X.html` πάει ο χρήστης.

**Δώρο:** Όλοι κερδίζουν τον ίδιο `redemption_code` (`RCMP2026` by default) → λαμβάνεται με SMS από αριθμό **MONT PARNES** μετά την υποβολή της φόρμας.

---

## ⚙️ Tech Stack

| Layer | Tech |
|---|---|
| **Backend** | Laravel 11.53 · PHP 8.4 (FPM, Alpine) |
| **Frontend** | Static HTML (multi-page) · Barba.js v2 (SPA transitions) · Custom CSS |
| **Database** | SQLite (WAL mode) — single file στο `backend/database/database.sqlite` |
| **Web server** | nginx 1.27 (Alpine) |
| **Containerization** | Docker · Docker Compose |
| **SMS Provider** | Apifon IM Gateway (OAuth2 client_credentials) |
| **Auth (admin)** | HTTP Basic Auth — credentials από `.env` |

---

## ✅ Προαπαιτούμενα

| Tool | Version | Σύνδεσμος |
|---|---|---|
| Docker Desktop | 25+ | [docker.com/products/docker-desktop](https://www.docker.com/products/docker-desktop) |
| Git | 2.40+ | [git-scm.com](https://git-scm.com) |
| 4GB RAM ελάχιστο | — | — |

> 💡 **Δεν χρειάζεται** PHP, Composer, Node.js, ή SQLite εγκατεστημένα τοπικά —
> όλα τρέχουν μέσα σε containers.

---

## 🚀 Εγκατάσταση από το μηδέν

### 1. Clone το repository

```powershell
cd C:\Users\<εσένα>\Desktop
git clone https://github.com/harris-chatz/Regency-Quiz.git
cd "Regency Quiz"
```

### 2. Δημιούργησε το `.env` αρχείο

```powershell
cd backend
copy .env.example .env
```

Άνοιξε το `.env` σε editor και συμπλήρωσε τα **απαραίτητα**:

```env
APP_KEY=                                # θα παραχθεί στο επόμενο βήμα

# Apifon (production credentials — ζητούνται από τον πελάτη)
APIFON_ENABLED=true
APIFON_CLIENT_ID=EmHav07ZiI...DxaQYi
APIFON_CLIENT_SECRET=fdExIGy...nkka

# Admin Panel — ΑΛΛΑΞΕ τους κωδικούς
ADMIN_USERNAME=admin
ADMIN_PASSWORD=<κάτι strong & μοναδικό>

# Το URL που μπαίνει στο SMS (η promotional landing page)
QUIZ_REDEMPTION_URL=https://shorturl.at/porCe
```

### 3. Σήκωσε τα containers

```powershell
cd ..    # επιστροφή στο root του project
docker compose up -d
```

Πρώτη φορά παίρνει ~5 λεπτά (build images). Επόμενες φορές <30 δευτερόλεπτα.

### 4. Generate APP_KEY + Run migrations

```powershell
docker compose exec app php artisan key:generate --force
docker compose exec app php artisan migrate --seed --force
```

### 5. Επιβεβαίωση

Άνοιξε στον browser:
- **http://localhost:8080** → πρέπει να δεις τη landing page του quiz
- **http://localhost:8080/admin** → login με τα ADMIN_* credentials
- **http://localhost:8081** → SQLite browser

---

## 🔄 Καθημερινή λειτουργία

| Εντολή | Τι κάνει |
|---|---|
| `docker compose up -d` | Ξεκινάει όλα τα services (background) |
| `docker compose down` | Σταματάει τα services (data preserved) |
| `docker compose ps` | Status όλων των containers |
| `docker compose logs -f app` | Live tail των Laravel logs |
| `docker compose logs -f nginx` | Live tail των HTTP requests |
| `docker compose restart app` | Restart μόνο του Laravel container |

---

## 🌐 Project URLs

| URL | Service | Auth | Σκοπός |
|---|---|---|---|
| http://localhost:8080 | **Quiz funnel** | None | Public landing → quiz → form |
| http://localhost:8080/api/health | API health | None | `{"status":"ok"}` |
| http://localhost:8080/api/quiz/questions | Quiz API | None | JSON με ερωτήσεις (από `Q&A` table) |
| http://localhost:8080/api/leads | Lead API | None | POST submit της φόρμας |
| http://localhost:8080/admin | **Admin** | Basic Auth | Stats + CSV export |
| http://localhost:8080/admin/leads/export.csv | CSV export | Basic Auth | Download όλων των leads |
| http://localhost:8081 | **SQLite browser** | None | Dev-only DB inspection |

---

## 🔐 Configuration (`.env`)

Όλη η ρύθμιση γίνεται μέσω **environment variables**. Πλήρης λίστα στο `backend/.env.example`.

### Apifon

```env
APIFON_ENABLED=false              # true = πραγματικά SMS, false = dry-run (μόνο logs)
APIFON_BASE_URL=https://ars.apifon.com
APIFON_ENDPOINT=/services/api/v1/im/send
APIFON_IDENTITY_URL=https://ids.apifon.com/oauth2/token
APIFON_CLIENT_ID=                 # από τον πελάτη (64-char string)
APIFON_CLIENT_SECRET=             # από τον πελάτη (28-char string)
APIFON_SCOPE=imGateway
APIFON_SENDER_ID="MONT PARNES"    # ο "From" του SMS
APIFON_SMS_TEMPLATE="New Regs Game! ... {url}"
APIFON_COUNTRY_CODE=30            # ελληνικό prefix
APIFON_TIMEOUT=10                 # seconds
```

### Redemption Code

```env
QUIZ_REDEMPTION_CODE_MODE=generic     # 'generic' = όλοι παίρνουν το ίδιο, 'unique' = random
QUIZ_GENERIC_REDEMPTION_CODE=RCMP2026
QUIZ_REDEMPTION_URL=https://shorturl.at/porCe  # το URL μέσα στο SMS
```

### Admin Panel

```env
ADMIN_USERNAME=admin
ADMIN_PASSWORD=<strong-password>
```

> ⚠️ **Μετά από κάθε αλλαγή στο `.env`** τρέξε:
> ```powershell
> docker compose exec app php artisan config:clear
> ```

---

## 📱 Apifon SMS Integration

### Πώς δουλεύει

1. Χρήστης υποβάλει τη φόρμα → POST `/api/leads`
2. `LeadController::store` δημιουργεί το `Lead` row
3. **Αμέσως μετά**, καλείται `QuizSmsSender::sendForLead($lead)`
4. Ο `ApifonClient`:
   - Ελέγχει αν έχει cached Bearer token (Laravel cache, TTL ~24h)
   - Αν όχι, κάνει OAuth2 exchange στο `ids.apifon.com/oauth2/token`
   - Στέλνει POST στο `ars.apifon.com/services/api/v1/im/send`
5. Όλο το request/response logged στο `sms_logs` table
6. Σε 401 (token revoked) → auto-refresh + retry **μία φορά**
7. Σε άλλο σφάλμα → log + ο user βλέπει επιτυχία ούτως ή άλλως (το lead σώθηκε)

### Dry-run mode (για test)

Στο `.env`:
```env
APIFON_ENABLED=false
```

Δεν γίνεται πραγματικό HTTP call. Στο `sms_logs` γράφεται row με `status='dry_run'`
και το full payload που **θα στελνόταν**. Καλό για να δεις τι θα φτάσει χωρίς να καίγονται credits.

### Switch σε production

```env
APIFON_ENABLED=true
APIFON_CLIENT_ID=<από-Apifon-dashboard>
APIFON_CLIENT_SECRET=<από-Apifon-dashboard>
```

```powershell
docker compose exec app php artisan config:clear
```

### Force-refresh του Bearer

Αν για κάποιο λόγο θες να αναγκάσεις νέο OAuth exchange:
```powershell
docker compose exec app php artisan cache:clear
```

### Δοκίμασε αποστολή χειροκίνητα

```powershell
docker compose exec app php artisan tinker --execute="`$lead = \App\Models\Lead::find(1); app(\App\Services\Apifon\QuizSmsSender::class)->sendForLead(`$lead);"
```

---

## 🛠 Admin Panel & CSV Export

### Πρόσβαση

1. Άνοιξε **http://localhost:8080/admin**
2. Browser θα ζητήσει credentials → δώσε `ADMIN_USERNAME` / `ADMIN_PASSWORD` από `.env`
3. Βλέπεις stats (total leads, ανά persona, ανά SMS status) + πίνακα τελευταίων 50

### CSV Export

Click στο κουμπί **"⬇ Κατέβασμα CSV (όλα τα leads)"**. Θα κατεβάσει αρχείο:

```
regency-quiz-leads_2026-06-11_174500.csv
```

22 στήλες με UTF-8 BOM (ανοίγει σωστά Ελληνικά στο Excel). Στρεαμάρεται με
`chunk(500)` ώστε να δουλεύει και με 100K+ leads.

---

## 💾 Database Operations

Όλα τα data ζουν σε **ένα αρχείο**: `backend/database/database.sqlite`.
Όταν κάνεις `docker compose down` τα data παραμένουν εκεί.

### Tables (μετά από fresh migrate + seed)

| Table | Σκοπός |
|---|---|
| `leads` | Οι συμμετοχές χρηστών (PII, persona, redemption code, IP, UA) |
| `questions` | Οι 3 ερωτήσεις του quiz |
| `question_options` | Τα 9 options (3 ανά ερώτηση) |
| `sms_logs` | Logs αποστολών SMS (request, response, http_status, sent_at) |
| `users`, `sessions`, `cache*`, `jobs*`, `personal_access_tokens` | Default Laravel |

### Common operations

```powershell
# Δες ποια migrations έχουν τρέξει
docker compose exec app php artisan migrate:status

# Τρέξε pending migrations
docker compose exec app php artisan migrate --force

# RESET της βάσης (drops everything + remigrates + reseeds)
docker compose exec app php artisan migrate:fresh --seed --force

# Δες ποια queries τρέχουν live
docker compose exec app php artisan tinker
```

### Direct inspection

- **Web UI (sqlite-web):** http://localhost:8081 → επιλογή table → "Content"
- **Desktop app:** [DB Browser for SQLite](https://sqlitebrowser.org/) → άνοιξε το `backend/database/database.sqlite`

---

## 📁 Δομή του project

```
Regency Quiz/
├── backend/                  ← Laravel 11
│   ├── app/
│   │   ├── Http/Controllers/Api/     QuestionController, LeadController
│   │   ├── Http/Controllers/Admin/   LeadsAdminController
│   │   ├── Http/Middleware/          AdminBasicAuth
│   │   ├── Http/Requests/            StoreLeadRequest (validation)
│   │   ├── Http/Resources/           Question, QuestionOption, LeadResource
│   │   ├── Models/                   Lead, Question, QuestionOption, SmsLog
│   │   └── Services/Apifon/
│   │       ├── ApifonClient.php           ← OAuth2 + caching
│   │       ├── ApifonResult.php           ← DTO
│   │       └── QuizSmsSender.php          ← template + log
│   ├── config/
│   │   ├── cors.php                       ← CORS settings
│   │   ├── database.php                   ← SQLite με WAL mode
│   │   └── quiz.php                       ← Apifon + redemption + admin keys
│   ├── database/
│   │   ├── database.sqlite                ← ΟΛΑ ΤΑ DATA
│   │   ├── migrations/                    ← 8 migration files
│   │   └── seeders/QuestionSeeder.php
│   ├── public/                            ← STATIC ASSETS (στις 11+ HTML)
│   │   ├── *.html                         ← landing, intro, terms, questions, results, submit, exit
│   │   ├── scripts/scripts.js             ← Barba transitions + API submit
│   │   ├── styles/                        ← 7 CSS files
│   │   ├── assets/                        ← 59 SVG/PNG images, fonts
│   │   └── index.php                      ← Laravel entry point
│   ├── resources/views/admin/             ← Blade template για admin
│   ├── routes/
│   │   ├── web.php                        ← / → redirect, /admin
│   │   └── api.php                        ← /api/quiz/questions, /api/leads
│   └── scripts/                           ← Diagnostic PHP scripts (dev only)
├── docker/
│   ├── php/Dockerfile                     ← PHP 8.4-fpm-alpine + extensions
│   ├── nginx/default.conf                 ← serves /public + proxy PHP
│   └── node/Dockerfile                    ← παλιό Vue server (stopped)
├── docker-compose.yml                     ← orchestration
├── frontend/                              ← (αρχειοθετημένο) παλιά Vue SPA
└── README.md                              ← αυτό το αρχείο
```

---

## 🐛 Troubleshooting

### "Docker error during connect / pipe not found"

**Αιτία:** Docker Desktop δεν τρέχει.
**Λύση:** Άνοιξε το Docker Desktop, περίμενε ~30s να γίνει "Engine running",
ξαναπροσπάθησε `docker compose up -d`.

### Browser δείχνει 404 για /landing.html

**Αιτία:** Τα static files δεν είναι στο `backend/public/`, ή ο `nginx` δεν τα serv-άρει.
**Λύση:**
```powershell
# Έλεγξε ότι υπάρχουν
ls backend\public\landing.html
# Restart nginx
docker compose restart nginx
```

### Form submission επιστρέφει 422

**Αιτία:** Server-side validation απορρίπτει το input.
**Λύση:** Άνοιξε DevTools → Network tab → στο failed request βλέπεις JSON body με `errors` object που λέει ακριβώς ποιο πεδίο έσπασε.

### Δεν φτάνει SMS μετά από submit

**Έλεγξε αυτά με σειρά:**

1. `APIFON_ENABLED=true`; (στο `.env`)
2. `APIFON_CLIENT_ID` και `APIFON_CLIENT_SECRET` set?
3. Έγινε `config:clear` μετά την αλλαγή;
4. Δες το `sms_logs` table στο localhost:8081 — τι λέει το `status` και το `error_message`;
5. Αν δείχνει `failed` με `http_status: 401` → Bearer token issue, τρέξε `php artisan cache:clear`
6. Αν δείχνει `failed` με `http_status: 403` με "IM Channel can not be null or empty" → ο payload σου δεν έχει `im_channels`
7. Αν δείχνει `dry_run` → `APIFON_ENABLED` είναι ακόμα `false`

### Greek characters εμφανίζονται ως ???

**Αιτία:** Browser cache ή PowerShell terminal encoding.
**Λύση:** Στον browser → hard refresh (Ctrl+Shift+R). Στο PowerShell → δεν επηρεάζει τα data, μόνο την οθόνη.

### "Permission denied" όταν προσπαθώ να γράψω σε αρχείο

**Αιτία:** Container δημιούργησε αρχείο με root ownership.
**Λύση:**
```powershell
docker compose exec --user root app chown -R app:app /var/www/html/storage /var/www/html/database
```

### Logs

```powershell
docker compose logs -f app                        # Laravel errors
docker compose exec app cat storage/logs/laravel.log
docker compose logs -f nginx                       # HTTP access/error logs
```

---

## 💾 Backup & Restore

### Backup του database

```powershell
# Live snapshot
copy "backend\database\database.sqlite" "backups\database-$(Get-Date -Format yyyyMMdd-HHmmss).sqlite"
```

### Restore

```powershell
# Σταμάτησε τα containers πρώτα
docker compose down

# Αντικατάστησε το αρχείο
copy "backups\database-XXX.sqlite" "backend\database\database.sqlite"

# Σήκωσε ξανά
docker compose up -d
```

### Επαναφορά σε προηγούμενη κατάσταση κώδικα

```powershell
# Δες όλα τα tags
git tag

# Επαναφορά σε συγκεκριμένη έκδοση
git checkout v1-vue-spa
```

---

## 🚢 Production Deployment

Δες το αναλυτικό deployment guide στο [`docs/deployment.md`](docs/deployment.md)
(όταν αυτό δημιουργηθεί). Σύντομη περίληψη:

| Στάδιο | Action |
|---|---|
| 1 | VPS με Ubuntu 24.04 (Hetzner CX22 €4/μήνα ή ισοδύναμο) |
| 2 | DNS A record από το domain στο IP του server |
| 3 | Install Docker + git + ufw |
| 4 | Clone repo, copy `.env.example` → `.env`, fill in production values |
| 5 | `docker compose up -d` |
| 6 | Let's Encrypt SSL cert via certbot |
| 7 | Update nginx config για HTTPS + redirect 80→443 |
| 8 | Setup daily backup cron |
| 9 | (Optional) UptimeRobot για monitoring |

**Critical checklist πριν live:**
- ✅ `APP_DEBUG=false`
- ✅ `APP_ENV=production`
- ✅ Strong `ADMIN_PASSWORD`
- ✅ HTTPS (όχι HTTP)
- ✅ Firewall μόνο 22/80/443
- ✅ Daily backup ενεργό

---

## 📞 Επικοινωνία / Help

| Issue | Where |
|---|---|
| Bug στο κώδικα | [GitHub Issues](https://github.com/harris-chatz/Regency-Quiz/issues) |
| Apifon questions | [docs.apifon.com](https://docs.apifon.com) ή support@apifon.com |
| Laravel docs | [laravel.com/docs/11.x](https://laravel.com/docs/11.x) |
| Barba.js docs | [barba.js.org](https://barba.js.org) |

---

## 📄 License

Private — All rights reserved. Confidential project for Regency Casino Mont Parnes.

---

**Built with care** για την καμπάνια **RCMP Quiz Game** του Regency Casino Athens.
