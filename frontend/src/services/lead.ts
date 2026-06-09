import { apiFetch, ApiError } from './api'
import type { LeadFormData, QuizColor, QuizAnswer } from '@/stores/quiz'

export interface CreateLeadPayload {
  name: string
  email: string
  phone: string
  age_consent: boolean
  terms_consent: boolean
  marketing_consent: boolean
  persona_color: QuizColor
  has_visited_casino: boolean | null
  started_at: string | null
  answers: Array<{
    question_id: number
    option_id: number
    color: QuizColor
    answered_at: string
  }>
}

export interface LeadResponse {
  id: number
  name: string
  email: string
  phone: string
  persona_color: QuizColor
  redemption_code: string
  submitted_at: string
}

export interface ValidationErrors {
  [field: string]: string[]
}

export class ValidationFailedError extends Error {
  constructor(public errors: ValidationErrors) {
    super('Validation failed')
    this.name = 'ValidationFailedError'
  }
}

export function buildLeadPayload(
  form: LeadFormData,
  quiz: {
    dominantColor: QuizColor | null
    hasVisitedCasino: boolean | null
    startedAt: string | null
    answers: QuizAnswer[]
  },
): CreateLeadPayload {
  if (!quiz.dominantColor) {
    throw new Error('Cannot submit lead before completing the quiz.')
  }

  return {
    name: form.name,
    email: form.email,
    phone: form.phone,
    age_consent: form.ageConsent,
    terms_consent: form.termsConsent,
    marketing_consent: form.marketingConsent,
    persona_color: quiz.dominantColor,
    has_visited_casino: quiz.hasVisitedCasino,
    started_at: quiz.startedAt,
    answers: quiz.answers.map((a) => ({
      question_id: a.questionId,
      option_id: a.optionId,
      color: a.color,
      answered_at: a.answeredAt,
    })),
  }
}

export async function submitLead(payload: CreateLeadPayload): Promise<LeadResponse> {
  try {
    const res = await apiFetch<{ data: LeadResponse }>('/leads', {
      method: 'POST',
      body: JSON.stringify(payload),
    })
    return res.data
  } catch (e) {
    if (e instanceof ApiError && e.status === 422) {
      const body = e.payload as { errors?: ValidationErrors } | null
      throw new ValidationFailedError(body?.errors ?? {})
    }
    throw e
  }
}
