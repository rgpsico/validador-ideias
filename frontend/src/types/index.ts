export interface QuestionOption {
  label: string
  value: string
}

export interface Question {
  id: number
  tipo:
    | 'texto'
    | 'multipla_escolha'
    | 'sim_nao'
    | 'numero'
    | 'escala'
    | 'moeda'
    | 'texto_livre'
    | 'nome'
    | 'whatsapp'
    | 'email'
  texto: string
  ordem: number
  obrigatoria: boolean
  opcoes: QuestionOption[] | null
  config: Record<string, unknown> | null
}

export interface QuestionRule {
  question_id: number
  depends_on_question_id: number
  operator: 'equals' | 'not_equals' | 'in'
  value: string | string[]
}

export interface CampaignData {
  titulo: string
  descricao: string | null
  formato: string
  slug: string
  questions: Question[]
  rules: QuestionRule[]
}

export type AnswerValue = string | number
