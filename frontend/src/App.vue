<script setup lang="ts">
import { computed, onMounted } from 'vue'
import WizardQuestion from './components/WizardQuestion.vue'
import { useWizardStore } from './stores/wizard'
import type { AnswerValue } from './types'

const wizard = useWizardStore()

function extractSlug(): string | null {
  // Matches /v/{slug}
  const match = window.location.pathname.match(/\/v\/([^/]+)/)
  return match ? decodeURIComponent(match[1]) : null
}

onMounted(async () => {
  const slug = extractSlug()
  if (!slug) {
    wizard.notFound = true
    wizard.loading = false
    return
  }
  await wizard.load(slug)
})

const currentValue = computed<AnswerValue | undefined>(() => {
  const q = wizard.currentQuestion
  if (!q) return undefined
  return wizard.answers[q.id]
})

function handleAnswer(value: AnswerValue) {
  if (wizard.currentQuestion) {
    wizard.answer(wizard.currentQuestion, value)
  }
}

function handleSkip() {
  wizard.skip()
}
</script>

<template>
  <div v-if="wizard.loading" class="screen screen--center">
    <p class="hero-text">Carregando...</p>
  </div>

  <div v-else-if="wizard.notFound" class="screen screen--center">
    <p class="hero-title">Campanha não encontrada</p>
    <p class="hero-text">Verifique se o link está correto.</p>
  </div>

  <div v-else-if="wizard.finished" class="screen screen--center">
    <p class="hero-title">Obrigado! 🎉</p>
    <p class="hero-text">
      Suas respostas foram registradas. Elas nos ajudam a validar essa ideia com evidências reais
      — sem achismo.
    </p>
  </div>

  <div v-else-if="wizard.currentQuestion" class="screen">
    <div class="top-bar">
      <button v-if="wizard.currentIndex > 0" class="btn-back" @click="wizard.goBack()">←</button>
    </div>

    <div class="progress-bar">
      <div class="progress-bar__fill" :style="{ width: wizard.progressPercent + '%' }" />
    </div>
    <p class="progress-label">Pergunta {{ wizard.currentIndex + 1 }} de {{ wizard.total }}</p>

    <WizardQuestion
      :key="wizard.currentQuestion.id"
      :question="wizard.currentQuestion"
      :initial-value="currentValue"
      :submitting="wizard.submitting"
      @answer="handleAnswer"
      @skip="handleSkip"
    />
  </div>

  <div v-else class="screen screen--center">
    <p class="hero-text">Esta campanha ainda não tem perguntas configuradas.</p>
  </div>
</template>
