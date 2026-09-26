<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import type { AnswerValue, Question } from '../types'

const props = defineProps<{
  question: Question
  initialValue?: AnswerValue
  submitting: boolean
}>()

const emit = defineEmits<{
  answer: [value: AnswerValue]
  skip: []
}>()

const textValue = ref<string>(typeof props.initialValue === 'string' ? props.initialValue : '')

watch(
  () => props.question.id,
  () => {
    textValue.value = typeof props.initialValue === 'string' ? props.initialValue : ''
  },
)

const isChoiceType = computed(() =>
  ['multipla_escolha', 'sim_nao', 'moeda'].includes(props.question.tipo),
)

const isFreeTextType = computed(() =>
  ['texto', 'numero', 'texto_livre', 'nome', 'whatsapp', 'email'].includes(props.question.tipo),
)

const inputType = computed(() => {
  switch (props.question.tipo) {
    case 'numero':
      return 'number'
    case 'email':
      return 'email'
    case 'whatsapp':
      return 'tel'
    default:
      return 'text'
  }
})

const canContinue = computed(() => {
  if (!props.question.obrigatoria) return true
  return textValue.value.trim().length > 0
})

function selectOption(value: string) {
  if (props.submitting) return
  emit('answer', value)
}

function selectScale(value: number) {
  if (props.submitting) return
  emit('answer', value)
}

function submitText() {
  if (props.submitting) return
  if (props.question.obrigatoria && textValue.value.trim().length === 0) return
  emit('answer', textValue.value.trim())
}

function onEnter() {
  if (canContinue.value) submitText()
}

const scaleMin = computed(() => Number(props.question.config?.min ?? 1))
const scaleMax = computed(() => Number(props.question.config?.max ?? 10))
const scaleRange = computed(() => {
  const arr: number[] = []
  for (let i = scaleMin.value; i <= scaleMax.value; i++) arr.push(i)
  return arr
})
</script>

<template>
  <div class="question">
    <p class="question__texto">{{ question.texto }}</p>

    <div v-if="isChoiceType && question.opcoes" class="options">
      <button
        v-for="opcao in question.opcoes"
        :key="opcao.value"
        class="option-btn"
        :disabled="submitting"
        @click="selectOption(opcao.value)"
      >
        {{ opcao.label }}
      </button>
    </div>

    <div v-else-if="question.tipo === 'escala'" class="scale-row">
      <button
        v-for="n in scaleRange"
        :key="n"
        class="scale-btn"
        :disabled="submitting"
        @click="selectScale(n)"
      >
        {{ n }}
      </button>
    </div>

    <div v-else-if="isFreeTextType">
      <textarea
        v-if="question.tipo === 'texto_livre'"
        v-model="textValue"
        class="text-input"
        rows="4"
        placeholder="Digite aqui..."
      />
      <input
        v-else
        v-model="textValue"
        class="text-input"
        :type="inputType"
        placeholder="Digite aqui..."
        @keyup.enter="onEnter"
      />

      <div class="actions">
        <button class="btn-primary" :disabled="!canContinue || submitting" @click="submitText">
          Continuar
        </button>
        <button v-if="!question.obrigatoria" class="btn-secondary" @click="$emit('skip')">
          Pular
        </button>
      </div>
    </div>
  </div>
</template>
