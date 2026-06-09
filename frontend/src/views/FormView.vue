<script setup lang="ts">
import { computed, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useQuizStore } from '@/stores/quiz'
import { ApiError } from '@/services/api'
import { buildLeadPayload, submitLead, ValidationFailedError } from '@/services/lead'

const router = useRouter()
const quiz = useQuizStore()

type FieldKey =
  | 'name'
  | 'email'
  | 'phone'
  | 'ageConsent'
  | 'termsConsent'
  | 'marketingConsent'

const form = reactive({
  name: '',
  email: '',
  phone: '',
  ageConsent: false,
  termsConsent: false,
  marketingConsent: false,
})

const errors = reactive<Record<FieldKey, string | null>>({
  name: null,
  email: null,
  phone: null,
  ageConsent: null,
  termsConsent: null,
  marketingConsent: null,
})

const touched = reactive<Record<FieldKey, boolean>>({
  name: false,
  email: false,
  phone: false,
  ageConsent: false,
  termsConsent: false,
  marketingConsent: false,
})

const submitting = ref(false)
const showSummary = ref(false)
const serverError = ref<string | null>(null)

const SERVER_FIELD_MAP: Record<string, FieldKey | null> = {
  name: 'name',
  email: 'email',
  phone: 'phone',
  age_consent: 'ageConsent',
  terms_consent: 'termsConsent',
  marketing_consent: 'marketingConsent',
}

const EMAIL_RE = /^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$/
const PHONE_RE = /^69\d{8}$/

function validateField(field: FieldKey): string | null {
  switch (field) {
    case 'name': {
      const v = form.name.trim()
      if (!v) return 'Το ονοματεπώνυμο είναι υποχρεωτικό.'
      if (v.length < 3) return 'Συμπλήρωσε το πλήρες ονοματεπώνυμό σου.'
      return null
    }
    case 'email': {
      const v = form.email.trim()
      if (!v) return 'Το email είναι υποχρεωτικό.'
      if (!EMAIL_RE.test(v)) return 'Δεν είναι έγκυρη διεύθυνση email.'
      return null
    }
    case 'phone': {
      const v = form.phone.trim()
      if (!v) return 'Το τηλέφωνο είναι υποχρεωτικό.'
      if (!PHONE_RE.test(v)) return 'Πρέπει να ξεκινάει με 69 και να έχει 10 ψηφία.'
      return null
    }
    case 'ageConsent':
      return form.ageConsent ? null : 'Πρέπει να επιβεβαιώσεις την ηλικία σου.'
    case 'termsConsent':
      return form.termsConsent ? null : 'Πρέπει να αποδεχτείς τους Όρους Χρήσης.'
    case 'marketingConsent':
      return form.marketingConsent
        ? null
        : 'Πρέπει να αποδεχτείς να λαμβάνεις marketing επικοινωνία.'
  }
}

function onBlur(field: FieldKey) {
  touched[field] = true
  errors[field] = validateField(field)
}

function clearError(field: FieldKey) {
  if (errors[field]) errors[field] = null
}

function onConsentChange(field: FieldKey) {
  touched[field] = true
  errors[field] = validateField(field)
}

function onPhoneInput(e: Event) {
  const target = e.target as HTMLInputElement
  form.phone = target.value.replace(/\D/g, '').slice(0, 10)
  clearError('phone')
}

const errorList = computed(() =>
  (Object.keys(errors) as FieldKey[])
    .map((k) => errors[k])
    .filter((e): e is string => e !== null),
)

async function onSubmit() {
  serverError.value = null

  let valid = true
  for (const k of Object.keys(form) as FieldKey[]) {
    touched[k] = true
    const err = validateField(k)
    errors[k] = err
    if (err) valid = false
  }

  showSummary.value = !valid

  if (!valid) {
    document
      .querySelector('[data-error-summary]')
      ?.scrollIntoView({ behavior: 'smooth', block: 'center' })
    return
  }

  const leadFormData = {
    name: form.name.trim(),
    email: form.email.trim().toLowerCase(),
    phone: form.phone.trim(),
    ageConsent: form.ageConsent,
    termsConsent: form.termsConsent,
    marketingConsent: form.marketingConsent,
    submittedAt: new Date().toISOString(),
  }

  submitting.value = true

  try {
    const payload = buildLeadPayload(leadFormData, {
      dominantColor: quiz.dominantColor,
      hasVisitedCasino: quiz.hasVisitedCasino,
      startedAt: quiz.startedAt,
      answers: quiz.answers,
    })

    const response = await submitLead(payload)

    quiz.setLead(leadFormData)
    quiz.setLeadResponse(response)

    router.push({ name: 'thank-you' })
  } catch (e) {
    submitting.value = false

    if (e instanceof ValidationFailedError) {
      for (const [serverField, messages] of Object.entries(e.errors)) {
        const root = serverField.split('.')[0]
        const localField = SERVER_FIELD_MAP[root]
        if (localField && messages.length > 0) {
          touched[localField] = true
          errors[localField] = messages[0]
        }
      }
      showSummary.value = true
      document
        .querySelector('[data-error-summary]')
        ?.scrollIntoView({ behavior: 'smooth', block: 'center' })
    } else if (e instanceof ApiError) {
      serverError.value = `Σφάλμα διακομιστή (${e.status}). Δοκίμασε ξανά σε λίγο.`
    } else if (e instanceof Error) {
      serverError.value = e.message || 'Κάτι πήγε στραβά. Δοκίμασε ξανά σε λίγο.'
    } else {
      serverError.value = 'Κάτι πήγε στραβά. Δοκίμασε ξανά σε λίγο.'
    }
  }
}

function inputClass(field: FieldKey) {
  const base =
    'w-full px-4 py-3 rounded-xl bg-white/5 border outline-none transition placeholder:text-white/30 focus:bg-white/10'
  if (errors[field]) {
    return `${base} border-red-500/60 ring-2 ring-red-500/20`
  }
  if (touched[field] && !errors[field] && (form[field] as string)) {
    return `${base} border-brand-green/50 focus:border-brand-green`
  }
  return `${base} border-white/15 focus:border-white/40`
}

function checkboxBoxClass(field: FieldKey) {
  if (errors[field]) {
    return 'border-red-500/60 ring-2 ring-red-500/20'
  }
  if (form[field]) {
    return 'border-brand-green/60 ring-2 ring-brand-green/20'
  }
  return 'border-white/10 hover:border-white/25'
}
</script>

<template>
  <section class="w-full max-w-xl space-y-6">
    <div class="space-y-2 text-center">
      <p class="text-xs uppercase tracking-[0.3em] text-brand-yellow">Συμμετοχή</p>
      <h1 class="text-3xl md:text-4xl font-bold leading-tight">
        Συμπλήρωσε τα στοιχεία σου
      </h1>
      <p class="text-white/60 text-sm md:text-base">
        Σου στέλνουμε άμεσα το δώρο σου με SMS.
      </p>
    </div>

    <div
      v-if="serverError"
      role="alert"
      class="bg-red-500/15 border border-red-500/40 rounded-xl p-4 text-sm text-red-300"
    >
      {{ serverError }}
    </div>

    <div
      v-if="showSummary && errorList.length"
      data-error-summary
      role="alert"
      class="bg-red-500/10 border border-red-500/40 rounded-xl p-4 space-y-1"
    >
      <p class="text-red-300 text-sm font-semibold">
        Παρακαλούμε διόρθωσε τα παρακάτω για να συνεχίσεις:
      </p>
      <ul class="list-disc list-inside text-red-300/90 text-xs space-y-0.5">
        <li v-for="(msg, i) in errorList" :key="i">{{ msg }}</li>
      </ul>
    </div>

    <form @submit.prevent="onSubmit" novalidate class="space-y-5">
      <div class="space-y-1.5">
        <label for="name" class="block text-sm font-medium text-white/80">
          Ονοματεπώνυμο <span class="text-brand-pink">*</span>
        </label>
        <input
          id="name"
          name="name"
          type="text"
          autocomplete="name"
          v-model="form.name"
          @input="clearError('name')"
          @blur="onBlur('name')"
          :class="inputClass('name')"
          placeholder="π.χ. Γιώργος Παπαδόπουλος"
        />
        <p v-if="errors.name" class="text-xs text-red-400">{{ errors.name }}</p>
      </div>

      <div class="space-y-1.5">
        <label for="email" class="block text-sm font-medium text-white/80">
          Email <span class="text-brand-pink">*</span>
        </label>
        <input
          id="email"
          name="email"
          type="email"
          inputmode="email"
          autocomplete="email"
          v-model="form.email"
          @input="clearError('email')"
          @blur="onBlur('email')"
          :class="inputClass('email')"
          placeholder="onoma@example.gr"
        />
        <p v-if="errors.email" class="text-xs text-red-400">{{ errors.email }}</p>
      </div>

      <div class="space-y-1.5">
        <label for="phone" class="block text-sm font-medium text-white/80">
          Κινητό τηλέφωνο <span class="text-brand-pink">*</span>
        </label>
        <input
          id="phone"
          name="phone"
          type="tel"
          inputmode="numeric"
          autocomplete="tel"
          maxlength="10"
          :value="form.phone"
          @input="onPhoneInput"
          @blur="onBlur('phone')"
          :class="inputClass('phone')"
          placeholder="69XXXXXXXX"
        />
        <p v-if="errors.phone" class="text-xs text-red-400">{{ errors.phone }}</p>
        <p v-else class="text-xs text-white/40">10 ψηφία, ξεκινάει με 69.</p>
      </div>

      <div class="space-y-3 pt-2">
        <label
          class="flex items-start gap-3 p-4 rounded-xl border bg-brand-surface/40 cursor-pointer transition"
          :class="checkboxBoxClass('ageConsent')"
        >
          <input
            type="checkbox"
            v-model="form.ageConsent"
            @change="onConsentChange('ageConsent')"
            class="mt-1 w-5 h-5 accent-brand-green cursor-pointer shrink-0"
          />
          <div class="space-y-0.5">
            <p class="text-sm font-medium text-white/90">
              Επιβεβαιώνω ότι είμαι 21 ετών και άνω.
            </p>
            <p v-if="errors.ageConsent" class="text-xs text-red-400">
              {{ errors.ageConsent }}
            </p>
          </div>
        </label>

        <label
          class="flex items-start gap-3 p-4 rounded-xl border bg-brand-surface/40 cursor-pointer transition"
          :class="checkboxBoxClass('termsConsent')"
        >
          <input
            type="checkbox"
            v-model="form.termsConsent"
            @change="onConsentChange('termsConsent')"
            class="mt-1 w-5 h-5 accent-brand-green cursor-pointer shrink-0"
          />
          <div class="space-y-0.5">
            <p class="text-sm font-medium text-white/90">
              Έχω διαβάσει και αποδέχομαι τους
              <a
                href="https://www.regencycasino.gr/terms-conditions/"
                target="_blank"
                rel="noopener noreferrer"
                class="underline hover:text-white"
              >Όρους Χρήσης</a> και την
              <a
                href="https://www.regencycasino.gr/privacy-policy/"
                target="_blank"
                rel="noopener noreferrer"
                class="underline hover:text-white"
              >Πολιτική Απορρήτου</a>.
            </p>
            <p v-if="errors.termsConsent" class="text-xs text-red-400">
              {{ errors.termsConsent }}
            </p>
          </div>
        </label>

        <label
          class="flex items-start gap-3 p-4 rounded-xl border bg-brand-surface/40 cursor-pointer transition"
          :class="checkboxBoxClass('marketingConsent')"
        >
          <input
            type="checkbox"
            v-model="form.marketingConsent"
            @change="onConsentChange('marketingConsent')"
            class="mt-1 w-5 h-5 accent-brand-green cursor-pointer shrink-0"
          />
          <div class="space-y-0.5">
            <p class="text-sm font-medium text-white/90">
              Επιθυμώ να λαμβάνω marketing επικοινωνία από το Regency Casino Mont Parnes
              (προσφορές, εκδηλώσεις, κλπ.).
            </p>
            <p v-if="errors.marketingConsent" class="text-xs text-red-400">
              {{ errors.marketingConsent }}
            </p>
          </div>
        </label>
      </div>

      <button
        type="submit"
        :disabled="submitting"
        class="w-full py-4 px-6 rounded-xl bg-gradient-to-r from-brand-pink to-brand-yellow text-black font-semibold tracking-wide hover:opacity-90 active:scale-[0.99] transition disabled:opacity-50 disabled:cursor-not-allowed shadow-lg"
      >
        {{ submitting ? 'Αποστολή…' : 'Υποβολή συμμετοχής' }}
      </button>

      <p class="text-center text-xs text-white/40">
        Τα στοιχεία σου είναι ασφαλή και προστατεύονται σύμφωνα με τον GDPR.
      </p>
    </form>
  </section>
</template>
