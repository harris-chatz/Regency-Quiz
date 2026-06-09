<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useQuizStore } from '@/stores/quiz'

const router = useRouter()
const quiz = useQuizStore()

const accepted = ref(quiz.preGameConsent)
const error = ref<string | null>(null)
const submitting = ref(false)

function onSubmit() {
  error.value = null

  if (!accepted.value) {
    error.value = 'Πρέπει να αποδεχτείς τους όρους για να συνεχίσεις.'
    return
  }

  submitting.value = true
  quiz.setPreGameConsent(true)

  setTimeout(() => {
    router.push({ name: 'quiz' })
  }, 250)
}
</script>

<template>
  <section class="w-full max-w-xl space-y-8">
    <div class="space-y-3 text-center">
      <p class="text-xs uppercase tracking-[0.3em] text-brand-yellow">Βήμα 2 από 3</p>
      <h1 class="text-3xl md:text-4xl font-bold leading-tight">Πριν ξεκινήσουμε</h1>
      <p class="text-white/60 text-sm md:text-base">
        Χρειαζόμαστε την επιβεβαίωσή σου για να συνεχίσεις στο παιχνίδι.
      </p>
    </div>

    <form @submit.prevent="onSubmit" class="space-y-6">
      <label
        class="block bg-brand-surface/60 border rounded-xl p-5 cursor-pointer transition"
        :class="[
          error
            ? 'border-red-500/60 ring-2 ring-red-500/30'
            : accepted
              ? 'border-brand-green/60 ring-2 ring-brand-green/20'
              : 'border-white/10 hover:border-white/25',
        ]"
      >
        <div class="flex items-start gap-3">
          <input
            type="checkbox"
            v-model="accepted"
            class="mt-1 w-5 h-5 accent-brand-green cursor-pointer"
          />
          <div class="space-y-1">
            <p class="font-semibold">Είμαι 21+ ετών και αποδέχομαι τους όρους χρήσης.</p>
            <p class="text-xs text-white/50 leading-relaxed">
              Επιβεβαιώνω ότι έχω συμπληρώσει το 21ο έτος της ηλικίας μου και έχω διαβάσει
              και αποδέχομαι τους
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
              >Πολιτική Απορρήτου</a>
              της καμπάνιας.
            </p>
          </div>
        </div>
      </label>

      <p v-if="error" role="alert" class="text-sm text-red-400 text-center -mt-2">
        {{ error }}
      </p>

      <button
        type="submit"
        :disabled="submitting"
        class="w-full py-4 rounded-xl bg-gradient-to-r from-brand-pink to-brand-yellow text-black font-semibold tracking-wide hover:opacity-90 active:scale-[0.98] transition disabled:opacity-50 disabled:cursor-not-allowed"
      >
        {{ submitting ? 'Φόρτωση…' : 'Ξεκίνα το παιχνίδι' }}
      </button>
    </form>
  </section>
</template>
