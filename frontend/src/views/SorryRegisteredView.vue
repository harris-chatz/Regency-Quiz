<script setup lang="ts">
import { useQuizStore } from '@/stores/quiz'
import { useRouter } from 'vue-router'

const quiz = useQuizStore()
const router = useRouter()

function startOver() {
  quiz.reset()
  router.push({ name: 'onboarding' })
}
</script>

<template>
  <section class="w-full max-w-xl text-center space-y-8">
    <div class="space-y-3">
      <div class="text-6xl">🎲</div>
      <h1 class="text-3xl md:text-4xl font-bold leading-tight">
        Σ' ευχαριστούμε που μας ξανα-επισκέπτεσαι!
      </h1>
      <p class="text-white/60 text-sm md:text-base max-w-md mx-auto">
        Φαίνεται ότι είσαι ήδη γνωστός μας. Η συγκεκριμένη καμπάνια απευθύνεται σε νέους
        επισκέπτες — αλλά σε περιμένουμε ξανά σύντομα στο Mont Parnes.
      </p>
    </div>

    <div class="space-y-3">
      <a
        href="https://www.regencycasinos.gr/mont-parnes/"
        target="_blank"
        rel="noopener"
        class="inline-block w-full max-w-xs mx-auto py-4 px-6 rounded-xl bg-gradient-to-r from-brand-pink to-brand-yellow text-black font-semibold tracking-wide hover:opacity-90 active:scale-[0.98] transition"
      >
        Επίσκεψη στο casino
      </a>
      <button
        type="button"
        @click="startOver"
        class="block mx-auto text-xs text-white/40 hover:text-white/70 underline underline-offset-4"
      >
        Επιστροφή στην αρχή
      </button>
    </div>
  </section>
</template>
