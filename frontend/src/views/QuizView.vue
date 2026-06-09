<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useQuizStore } from '@/stores/quiz'
import { fetchQuestions, type ApiQuestion, type ApiQuestionOption } from '@/services/quiz'

const router = useRouter()
const quiz = useQuizStore()

const questions = ref<ApiQuestion[]>([])
const currentIndex = ref(0)
const loading = ref(true)
const error = ref<string | null>(null)
const submitting = ref(false)

const currentQuestion = computed<ApiQuestion | null>(
  () => questions.value[currentIndex.value] ?? null,
)
const totalSteps = computed(() => questions.value.length)
const stepNumber = computed(() => currentIndex.value + 1)

onMounted(async () => {
  try {
    loading.value = true
    error.value = null
    questions.value = await fetchQuestions()
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Σφάλμα φόρτωσης ερωτήσεων'
  } finally {
    loading.value = false
  }
})

function selectOption(option: ApiQuestionOption) {
  if (!currentQuestion.value || submitting.value) return

  submitting.value = true

  quiz.recordAnswer({
    questionId: currentQuestion.value.id,
    questionOrder: currentQuestion.value.order,
    optionId: option.id,
    optionLabel: option.label,
    color: option.color,
  })

  setTimeout(() => {
    submitting.value = false

    if (currentIndex.value < questions.value.length - 1) {
      currentIndex.value++
    } else {
      router.push({ name: 'result' })
    }
  }, 280)
}

function goBack() {
  if (currentIndex.value > 0) currentIndex.value--
}

function colorClass(color: ApiQuestionOption['color']) {
  switch (color) {
    case 'green':
      return 'hover:border-brand-green hover:bg-brand-green/10 focus-visible:ring-brand-green/60'
    case 'yellow':
      return 'hover:border-brand-yellow hover:bg-brand-yellow/10 focus-visible:ring-brand-yellow/60'
    case 'pink':
      return 'hover:border-brand-pink hover:bg-brand-pink/10 focus-visible:ring-brand-pink/60'
  }
}

function selectedClass(color: ApiQuestionOption['color'], isSelected: boolean) {
  if (!isSelected) return ''
  switch (color) {
    case 'green':
      return 'border-brand-green bg-brand-green/15 ring-2 ring-brand-green/40'
    case 'yellow':
      return 'border-brand-yellow bg-brand-yellow/15 ring-2 ring-brand-yellow/40'
    case 'pink':
      return 'border-brand-pink bg-brand-pink/15 ring-2 ring-brand-pink/40'
  }
}

const selectedOptionId = computed(() => {
  if (!currentQuestion.value) return null
  return quiz.findAnswerFor(currentQuestion.value.id)?.optionId ?? null
})
</script>

<template>
  <section class="w-full max-w-xl space-y-8">
    <div class="space-y-3">
      <div class="flex items-center justify-between text-xs uppercase tracking-[0.3em]">
        <span class="text-brand-yellow">Ερώτηση {{ stepNumber }} από {{ totalSteps || 3 }}</span>
        <button
          v-if="currentIndex > 0"
          type="button"
          @click="goBack"
          class="text-white/40 hover:text-white/80 transition normal-case tracking-normal"
        >
          ← Πίσω
        </button>
      </div>

      <div class="h-1 bg-white/10 rounded-full overflow-hidden">
        <div
          class="h-full bg-gradient-to-r from-brand-pink via-brand-yellow to-brand-green transition-all duration-300"
          :style="{ width: `${totalSteps ? (stepNumber / totalSteps) * 100 : 0}%` }"
        />
      </div>
    </div>

    <div v-if="loading" class="text-center py-12 text-white/60">
      <div class="inline-block w-8 h-8 border-2 border-white/20 border-t-brand-yellow rounded-full animate-spin" />
      <p class="mt-3 text-sm">Φόρτωση ερωτήσεων…</p>
    </div>

    <div
      v-else-if="error"
      class="text-center py-8 px-4 bg-red-500/10 border border-red-500/30 rounded-xl"
    >
      <p class="text-red-300 text-sm">{{ error }}</p>
    </div>

    <template v-else-if="currentQuestion">
      <h1 class="text-2xl md:text-3xl font-bold leading-snug text-center">
        {{ currentQuestion.text }}
      </h1>

      <div class="space-y-3">
        <button
          v-for="option in currentQuestion.options"
          :key="option.id"
          type="button"
          :disabled="submitting"
          @click="selectOption(option)"
          class="w-full py-4 px-5 rounded-xl border border-white/15 bg-white/5 text-left font-semibold transition active:scale-[0.99] focus:outline-none focus-visible:ring-2 disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-between gap-3"
          :class="[
            colorClass(option.color),
            selectedClass(option.color, selectedOptionId === option.id),
          ]"
        >
          <span>{{ option.label }}</span>
          <span
            class="w-3 h-3 rounded-full shrink-0"
            :class="{
              'bg-brand-green': option.color === 'green',
              'bg-brand-yellow': option.color === 'yellow',
              'bg-brand-pink': option.color === 'pink',
            }"
          />
        </button>
      </div>
    </template>

    <!-- Hidden form: real <form> with hidden inputs so the answer state is
         inspectable in DevTools and can be submitted as multipart/form-data
         later if we ever ditch JSON. -->
    <form aria-hidden="true" class="hidden" :data-persona="quiz.dominantColor ?? ''">
      <input type="hidden" name="has_visited_casino" :value="String(quiz.hasVisitedCasino)" />
      <input type="hidden" name="pre_game_consent" :value="String(quiz.preGameConsent)" />
      <input type="hidden" name="started_at" :value="quiz.startedAt ?? ''" />
      <input type="hidden" name="persona_color" :value="quiz.dominantColor ?? ''" />
      <template v-for="a in quiz.answers" :key="a.questionId">
        <input type="hidden" :name="`answers[${a.questionId}][option_id]`" :value="a.optionId" />
        <input type="hidden" :name="`answers[${a.questionId}][color]`" :value="a.color" />
      </template>
    </form>
  </section>
</template>
