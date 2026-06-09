import { createRouter, createWebHistory } from 'vue-router'
import { useQuizStore } from '@/stores/quiz'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'onboarding',
      component: () => import('@/views/OnboardingView.vue'),
      meta: { step: 1, title: 'Καλώς ήρθες' },
    },
    {
      path: '/sorry',
      name: 'sorry',
      component: () => import('@/views/SorryRegisteredView.vue'),
      meta: { step: 1, title: 'Είσαι ήδη εγγεγραμμένος' },
    },
    {
      path: '/consent',
      name: 'consent',
      component: () => import('@/views/ConsentView.vue'),
      meta: { step: 2, title: 'Πριν ξεκινήσουμε', requires: 'visited-no' },
    },
    {
      path: '/quiz',
      name: 'quiz',
      component: () => import('@/views/QuizView.vue'),
      meta: { step: 3, title: 'Το παιχνίδι', requires: 'consent' },
    },
    {
      path: '/result',
      name: 'result',
      component: () => import('@/views/ResultView.vue'),
      meta: { step: 4, title: 'Αποτέλεσμα', requires: 'completed-quiz' },
    },
    {
      path: '/form',
      name: 'form',
      component: () => import('@/views/FormView.vue'),
      meta: { step: 5, title: 'Συμμετοχή', requires: 'completed-quiz' },
    },
    {
      path: '/thank-you',
      name: 'thank-you',
      component: () => import('@/views/ThankYouView.vue'),
      meta: { step: 6, title: 'Ευχαριστούμε', requires: 'submitted-form' },
    },
  ],
})

router.beforeEach((to) => {
  const store = useQuizStore()

  if (to.meta.requires === 'visited-no' && store.hasVisitedCasino !== false) {
    return { name: 'onboarding' }
  }

  if (to.meta.requires === 'consent' && !store.preGameConsent) {
    return { name: 'consent' }
  }

  if (to.meta.requires === 'completed-quiz' && !store.dominantColor) {
    return { name: 'onboarding' }
  }

  if (to.meta.requires === 'submitted-form' && !store.lead) {
    return { name: 'onboarding' }
  }

  return true
})

export default router
