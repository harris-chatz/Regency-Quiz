<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useQuizStore, type QuizColor } from '@/stores/quiz'

const router = useRouter()
const quiz = useQuizStore()

interface Persona {
  emoji: string
  title: string
  paragraphs: string[]
}

const PERSONAS: Record<QuizColor, Persona> = {
  green: {
    emoji: '🌿',
    title: 'Είσαι cool τύπος!',
    paragraphs: [
      'Κατέχεις την τέχνη της καλοπέρασης, με όλη τη σημασία της λέξης! Σε όλες τις συνθήκες, με όλες τις παρέες, εσύ έχεις τον τρόπο σου να απολαμβάνεις τη στιγμή.',
    ],
  },
  yellow: {
    emoji: '⚡',
    title: 'Είσαι λάτρης της περιπέτειας!',
    paragraphs: [
      'Όλα στα κόκκινα.',
      'You live for the thrill και όλα τα σχετικά!',
      'Είσαι αυτός που προτείνει πάντα τις πιο δυνατές εξόδους και τις πιο αυθόρμητες αποδράσεις!',
    ],
  },
  pink: {
    emoji: '💗',
    title: 'Είσαι ρομαντική ψυχή!',
    paragraphs: [
      'Το Notebook και το Crazy Stupid Love γυρίστηκαν για εσένα!',
      'Ξέρεις να δημιουργείς στιγμές που μένουν ανεξίτηλες και ζεις τα πάντα με την ψυχή σου.',
    ],
  },
}

const persona = computed<Persona | null>(() =>
  quiz.dominantColor ? PERSONAS[quiz.dominantColor] : null,
)

const colorTheme = computed(() => {
  switch (quiz.dominantColor) {
    case 'green':
      return {
        badge: 'bg-brand-green/15 border-brand-green/40 text-brand-green',
        glow: 'from-brand-green/40 via-brand-green/10 to-transparent',
        button: 'from-brand-green to-emerald-400',
        accent: 'text-brand-green',
      }
    case 'yellow':
      return {
        badge: 'bg-brand-yellow/15 border-brand-yellow/40 text-brand-yellow',
        glow: 'from-brand-yellow/40 via-brand-yellow/10 to-transparent',
        button: 'from-brand-yellow to-amber-300',
        accent: 'text-brand-yellow',
      }
    case 'pink':
      return {
        badge: 'bg-brand-pink/15 border-brand-pink/40 text-brand-pink',
        glow: 'from-brand-pink/40 via-brand-pink/10 to-transparent',
        button: 'from-brand-pink to-rose-400',
        accent: 'text-brand-pink',
      }
    default:
      return {
        badge: 'bg-white/10 border-white/20 text-white/60',
        glow: 'from-white/10 via-transparent to-transparent',
        button: 'from-white/20 to-white/30',
        accent: 'text-white/60',
      }
  }
})

onMounted(() => {
  if (!quiz.dominantColor) {
    router.replace({ name: 'onboarding' })
  }
})

function goToForm() {
  router.push({ name: 'form' })
}
</script>

<template>
  <section v-if="persona" class="w-full max-w-xl space-y-8 relative">
    <div
      class="absolute -top-32 left-1/2 -translate-x-1/2 w-[28rem] h-[28rem] rounded-full blur-3xl opacity-60 pointer-events-none -z-10 bg-gradient-radial"
      :class="`bg-gradient-to-b ${colorTheme.glow}`"
    />

    <div class="text-center space-y-4">
      <span
        class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs uppercase tracking-[0.3em] border"
        :class="colorTheme.badge"
      >
        Το αποτέλεσμά σου
      </span>

      <div class="text-6xl select-none" aria-hidden="true">{{ persona.emoji }}</div>

      <h1
        class="text-3xl md:text-4xl font-bold leading-tight"
        :class="colorTheme.accent"
      >
        {{ persona.title }}
      </h1>
    </div>

    <div class="bg-brand-surface/60 border border-white/10 rounded-2xl p-6 md:p-8 space-y-4">
      <p
        v-for="(p, idx) in persona.paragraphs"
        :key="idx"
        class="text-white/85 leading-relaxed text-base md:text-lg"
      >
        {{ p }}
      </p>
    </div>

    <div class="space-y-3 pt-2">
      <button
        type="button"
        @click="goToForm"
        class="w-full py-4 px-6 rounded-xl text-black font-semibold tracking-wide bg-gradient-to-r hover:opacity-90 active:scale-[0.99] transition shadow-lg"
        :class="colorTheme.button"
      >
        Διεκδίκησε το δώρο σου →
      </button>
      <p class="text-center text-xs text-white/40">
        Σε λίγα βήματα ολοκληρώνεις τη συμμετοχή σου
      </p>
    </div>
  </section>
</template>
