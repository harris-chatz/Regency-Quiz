<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useQuizStore } from '@/stores/quiz'

const quiz = useQuizStore()
const router = useRouter()

const redemptionCode = computed(() => quiz.leadResponse?.redemption_code ?? null)
const copied = ref(false)

async function copyCode() {
  if (!redemptionCode.value) return
  try {
    await navigator.clipboard.writeText(redemptionCode.value)
    copied.value = true
    setTimeout(() => (copied.value = false), 2000)
  } catch {
    /* clipboard not available */
  }
}

function restart() {
  quiz.reset()
  router.push({ name: 'onboarding' })
}
</script>

<template>
  <section class="w-full max-w-xl text-center space-y-8">
    <div class="space-y-3">
      <div class="text-6xl">🎉</div>
      <p class="text-xs uppercase tracking-[0.3em] text-brand-yellow">Επιτυχία</p>
      <h1 class="text-3xl md:text-4xl font-bold leading-tight">
        Ευχαριστούμε για τη συμμετοχή σου!
      </h1>
      <p class="text-white/70 max-w-md mx-auto">
        Σε λίγο θα λάβεις στο κινητό σου ένα SMS με τον κωδικό εξαργύρωσης του δώρου σου.
      </p>
    </div>

    <div
      v-if="redemptionCode"
      class="bg-brand-surface/60 border border-white/10 rounded-2xl p-6 space-y-3"
    >
      <p class="text-xs uppercase tracking-[0.3em] text-white/50">
        Ο κωδικός εξαργύρωσής σου
      </p>
      <div
        class="text-3xl md:text-4xl font-bold tracking-wider bg-gradient-to-r from-brand-pink via-brand-yellow to-brand-green bg-clip-text text-transparent select-all"
      >
        {{ redemptionCode }}
      </div>
      <button
        type="button"
        @click="copyCode"
        class="text-xs text-white/60 hover:text-white underline underline-offset-4 transition"
      >
        {{ copied ? '✓ Αντιγράφηκε!' : 'Αντιγραφή κωδικού' }}
      </button>
      <p class="text-xs text-white/40">
        Παρουσίασε αυτόν τον κωδικό στην υποδοχή του Regency Casino Mont Parnes
        για να εξαργυρώσεις το δώρο σου.
      </p>
    </div>

    <details
      v-if="quiz.leadResponse"
      class="text-left bg-brand-surface/40 border border-white/10 rounded-xl p-4 text-xs text-white/60"
    >
      <summary class="cursor-pointer font-semibold text-white/80">
        Λεπτομέρειες υποβολής (debug)
      </summary>
      <pre class="mt-3 whitespace-pre-wrap break-all">{{ quiz.leadResponse }}</pre>
    </details>

    <button
      type="button"
      @click="restart"
      class="text-xs text-white/40 hover:text-white/80 underline underline-offset-4"
    >
      Επανεκκίνηση
    </button>
  </section>
</template>
