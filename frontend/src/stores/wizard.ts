import { defineStore } from 'pinia'
import api from '../services/api'
import type { AnswerValue, CampaignData, Question, QuestionRule } from '../types'

function evaluateRule(rule: QuestionRule, actual: AnswerValue | undefined): boolean {
  if (actual === undefined || actual === null) return false

  const norm = (v: unknown) => String(v).trim().toLowerCase()
  const actualNorm = norm(actual)

  switch (rule.operator) {
    case 'equals':
      return actualNorm === norm(Array.isArray(rule.value) ? rule.value[0] : rule.value)
    case 'not_equals':
      return actualNorm !== norm(Array.isArray(rule.value) ? rule.value[0] : rule.value)
    case 'in': {
      const list = Array.isArray(rule.value) ? rule.value : [rule.value]
      return list.map(norm).includes(actualNorm)
    }
    default:
      return true
  }
}

interface State {
  slug: string
  campaign: CampaignData | null
  leadUuid: string | null
  answers: Record<number, AnswerValue>
  currentIndex: number
  loading: boolean
  notFound: boolean
  finished: boolean
  submitting: boolean
}

export const useWizardStore = defineStore('wizard', {
  state: (): State => ({
    slug: '',
    campaign: null,
    leadUuid: null,
    answers: {},
    currentIndex: 0,
    loading: true,
    notFound: false,
    finished: false,
    submitting: false,
  }),

  getters: {
    allQuestionsSorted(state): Question[] {
      return state.campaign ? [...state.campaign.questions].sort((a, b) => a.ordem - b.ordem) : []
    },

    visibleQuestions(): Question[] {
      const rules = this.campaign?.rules ?? []
      return this.allQuestionsSorted.filter((q) => {
        const qRules = rules.filter((r) => r.question_id === q.id)
        if (qRules.length === 0) return true
        return qRules.every((r) => evaluateRule(r, this.answers[r.depends_on_question_id]))
      })
    },

    total(): number {
      return this.visibleQuestions.length
    },

    currentQuestion(state): Question | null {
      return this.visibleQuestions[state.currentIndex] ?? null
    },

    progressPercent(state): number {
      if (this.total === 0) return 0
      return Math.round(((state.currentIndex + 1) / this.total) * 100)
    },
  },

  actions: {
    async load(slug: string) {
      this.slug = slug
      this.loading = true
      this.notFound = false

      try {
        const { data } = await api.get<CampaignData>(`/campanhas/${slug}`)
        this.campaign = data
      } catch {
        this.notFound = true
        this.loading = false
        return
      }

      const storageKey = `validador_lead_${slug}`
      const savedUuid = localStorage.getItem(storageKey)

      if (savedUuid) {
        this.leadUuid = savedUuid
      } else {
        try {
          const { data } = await api.post<{ lead_uuid: string }>(`/campanhas/${slug}/iniciar`)
          this.leadUuid = data.lead_uuid
          localStorage.setItem(storageKey, data.lead_uuid)
        } catch {
          this.notFound = true
        }
      }

      this.loading = false
    },

    async answer(question: Question, value: AnswerValue) {
      this.answers[question.id] = value

      if (this.leadUuid) {
        this.submitting = true
        try {
          await api.post(`/leads/${this.leadUuid}/responder`, {
            question_id: question.id,
            value,
          })
        } finally {
          this.submitting = false
        }
      }

      await this.advance()
    },

    async skip() {
      await this.advance()
    },

    async advance() {
      if (this.currentIndex + 1 < this.total) {
        this.currentIndex += 1
      } else {
        await this.finish()
      }
    },

    goBack() {
      if (this.currentIndex > 0) {
        this.currentIndex -= 1
      }
    },

    async finish() {
      if (!this.leadUuid) return

      this.submitting = true
      try {
        await api.post(`/leads/${this.leadUuid}/finalizar`, {})
        this.finished = true
        localStorage.removeItem(`validador_lead_${this.slug}`)
      } finally {
        this.submitting = false
      }
    },
  },
})
