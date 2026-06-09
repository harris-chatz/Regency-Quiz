import { defineStore } from 'pinia'
import { computed, ref } from 'vue'

export type QuizColor = 'green' | 'yellow' | 'pink'

export interface QuizAnswer {
  questionId: number
  questionOrder: number
  optionId: number
  optionLabel: string
  color: QuizColor
  answeredAt: string
}

export interface LeadFormData {
  name: string
  email: string
  phone: string
  ageConsent: boolean
  termsConsent: boolean
  marketingConsent: boolean
  submittedAt: string
}

export interface LeadServerResponse {
  id: number
  name: string
  email: string
  phone: string
  persona_color: QuizColor
  redemption_code: string
  submitted_at: string
}

/**
 * Holds all in-memory state for the quiz funnel.
 *
 * The funnel is single-page (refresh = restart). When the user submits
 * the lead form at the end, everything gets POSTed to the Laravel API.
 */
export const useQuizStore = defineStore('quiz', () => {
  const hasVisitedCasino = ref<boolean | null>(null)
  const preGameConsent = ref(false)
  const answers = ref<QuizAnswer[]>([])
  const startedAt = ref<string | null>(null)
  const lead = ref<LeadFormData | null>(null)
  const leadResponse = ref<LeadServerResponse | null>(null)

  const colorCounts = computed(() => {
    const counts: Record<QuizColor, number> = { green: 0, yellow: 0, pink: 0 }
    for (const a of answers.value) counts[a.color]++
    return counts
  })

  /**
   * Returns the persona color based on the answers.
   *
   * Tie-breaker (per WBS Task #4): if all three colors appear once (1-1-1),
   * use the color of the LAST answer.
   */
  const dominantColor = computed<QuizColor | null>(() => {
    if (answers.value.length === 0) return null
    const { green, yellow, pink } = colorCounts.value
    const max = Math.max(green, yellow, pink)
    const winners = (['green', 'yellow', 'pink'] as QuizColor[]).filter(
      (c) => colorCounts.value[c] === max,
    )
    if (winners.length === 1) return winners[0]
    return answers.value[answers.value.length - 1].color
  })

  function setHasVisitedCasino(value: boolean) {
    hasVisitedCasino.value = value
    if (!startedAt.value) startedAt.value = new Date().toISOString()
  }

  function setPreGameConsent(value: boolean) {
    preGameConsent.value = value
  }

  function recordAnswer(answer: Omit<QuizAnswer, 'answeredAt'>) {
    const existingIndex = answers.value.findIndex(
      (a) => a.questionId === answer.questionId,
    )
    const next: QuizAnswer = { ...answer, answeredAt: new Date().toISOString() }

    if (existingIndex >= 0) {
      answers.value.splice(existingIndex, 1, next)
    } else {
      answers.value.push(next)
    }
  }

  function findAnswerFor(questionId: number) {
    return answers.value.find((a) => a.questionId === questionId) ?? null
  }

  /**
   * The "hidden form" payload — what gets POSTed to the API along
   * with the lead form at submission time.
   */
  const hiddenFormPayload = computed(() => ({
    has_visited_casino: hasVisitedCasino.value,
    pre_game_consent: preGameConsent.value,
    started_at: startedAt.value,
    persona_color: dominantColor.value,
    answers: answers.value.map((a) => ({
      question_id: a.questionId,
      option_id: a.optionId,
      color: a.color,
      answered_at: a.answeredAt,
    })),
  }))

  function setLead(data: LeadFormData) {
    lead.value = data
  }

  function setLeadResponse(data: LeadServerResponse) {
    leadResponse.value = data
  }

  function reset() {
    hasVisitedCasino.value = null
    preGameConsent.value = false
    answers.value = []
    startedAt.value = null
    lead.value = null
    leadResponse.value = null
  }

  return {
    hasVisitedCasino,
    preGameConsent,
    answers,
    startedAt,
    lead,
    leadResponse,
    colorCounts,
    dominantColor,
    hiddenFormPayload,
    setHasVisitedCasino,
    setPreGameConsent,
    recordAnswer,
    findAnswerFor,
    setLead,
    setLeadResponse,
    reset,
  }
})
