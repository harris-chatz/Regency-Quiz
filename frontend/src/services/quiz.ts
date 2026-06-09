import { apiFetch } from './api'
import type { QuizColor } from '@/stores/quiz'

export interface ApiQuestionOption {
  id: number
  label: string
  color: QuizColor
  order: number
}

export interface ApiQuestion {
  id: number
  order: number
  text: string
  options: ApiQuestionOption[]
}

interface QuestionsResponse {
  data: ApiQuestion[]
}

export async function fetchQuestions(): Promise<ApiQuestion[]> {
  const res = await apiFetch<QuestionsResponse>('/quiz/questions')
  return res.data
}
