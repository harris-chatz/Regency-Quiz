<script setup lang="ts">
import { ref, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useQuizStore } from '@/stores/quiz'

const router = useRouter()
const quiz = useQuizStore()

const showCountdown = ref(false)
const seconds = ref(4)
let timer: ReturnType<typeof setInterval> | null = null

function clearTimer() {
  if (timer) {
    clearInterval(timer)
    timer = null
  }
}

function onAnswerYes() {
  quiz.setHasVisitedCasino(true)
  router.push({ name: 'sorry' })
}

function onAnswerNo() {
  quiz.setHasVisitedCasino(false)
  showCountdown.value = true
  seconds.value = 4

  timer = setInterval(() => {
    seconds.value--
    if (seconds.value <= 0) {
      clearTimer()
      router.push({ name: 'consent' })
    }
  }, 1000)
}

onUnmounted(clearTimer)
</script>

<template>
  <section class="w-full max-w-xl text-center space-y-8">
    <div class="space-y-3">
      <p class="text-xs uppercase tracking-[0.3em] text-brand-yellow">Βήμα 1 από 3</p>
      <h1 class="text-3xl md:text-4xl font-bold leading-tight">
        Έχεις επισκεφθεί το
        <span class="bg-gradient-to-r from-brand-pink via-brand-yellow to-brand-green bg-clip-text text-transparent">
          Regency Casino Mont Parnes
        </span>
        ;
      </h1>
      <p class="text-white/60 text-sm md:text-base">
        Πες μας για να σου ετοιμάσουμε τη σωστή εμπειρία.
      </p>
    </div>

    <div class="grid grid-cols-2 gap-4 max-w-sm mx-auto">
      <button
        type="button"
        @click="onAnswerYes"
        class="py-4 rounded-xl border border-white/15 bg-white/5 hover:bg-white/10 active:scale-[0.98] transition font-semibold tracking-wide"
      >
        ΝΑΙ
      </button>
      <button
        type="button"
        @click="onAnswerNo"
        class="py-4 rounded-xl bg-gradient-to-r from-brand-pink to-brand-yellow text-black hover:opacity-90 active:scale-[0.98] transition font-semibold tracking-wide"
      >
        ΟΧΙ
      </button>
    </div>

    <Teleport to="body">
      <Transition name="modal">
        <div
          v-if="showCountdown"
          class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        >
          <div class="bg-brand-surface border border-white/10 rounded-2xl p-8 max-w-md w-full text-center space-y-5 shadow-2xl">
            <div class="text-7xl font-bold bg-gradient-to-r from-brand-pink via-brand-yellow to-brand-green bg-clip-text text-transparent">
              {{ seconds }}
            </div>
            <h2 class="text-xl font-semibold">Ετοιμάσου!</h2>
            <p class="text-white/60 text-sm">
              Σε λίγο ξεκινάει το παιχνίδι. Κέρδισε ένα μοναδικό δώρο για την επόμενη επίσκεψή σου.
            </p>
          </div>
        </div>
      </Transition>
    </Teleport>
  </section>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>
