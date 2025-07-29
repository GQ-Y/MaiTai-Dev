<script setup lang="ts">
/**
 * 通用动态表单组件
 * 支持根据配置动态显示/隐藏表单字段
 * 使用MineAdmin标准的MaForm组件
 */
import { h } from 'vue'
import type { MaFormExpose, MaFormItem } from '@mineadmin/form'
import useForm from '@/hooks/useForm.ts'

interface FormField {
  prop: string
  label: string
  type: 'input' | 'textarea' | 'select' | 'radio' | 'inputNumber' | 'custom'
  placeholder?: string
  options?: { label: string, value: any }[]
  min?: number
  max?: number
  rows?: number
  required?: boolean
  rules?: any[]
  show?: (formData: any) => boolean // 动态显示条件
  customRender?: () => any // 自定义渲染内容
  suffix?: string // 后缀文本
  style?: any
}

interface Props {
  modelValue: any
  fields: FormField[]
  labelWidth?: string
  labelPosition?: 'left' | 'right' | 'top'
  style?: any
}

const props = withDefaults(defineProps<Props>(), {
  labelWidth: '120px',
  labelPosition: 'left',
})

const emit = defineEmits<{
  'update:modelValue': [value: any]
}>()

const formData = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value),
})

const formRef = ref<MaFormExpose>()

// 转换字段配置为MaFormItem格式
function getMaFormItems(): MaFormItem[] {
  return props.fields.map((field): MaFormItem => {
    const baseItem: MaFormItem = {
      label: field.label,
      prop: field.prop,
      show: field.show,
    }

    // 根据字段类型设置render属性
    switch (field.type) {
      case 'input':
        baseItem.render = 'input'
        baseItem.renderProps = {
          placeholder: field.placeholder,
          clearable: true,
          style: field.style,
        }
        break

      case 'textarea':
        baseItem.render = 'input'
        baseItem.renderProps = {
          type: 'textarea',
          rows: field.rows || 2,
          placeholder: field.placeholder,
          style: field.style,
        }
        break

      case 'select':
        baseItem.render = 'select'
        baseItem.renderProps = {
          placeholder: field.placeholder,
          style: { width: '100%', ...field.style },
          options: field.options?.map(opt => ({
            label: opt.label,
            value: opt.value,
          })) || [],
        }
        break

      case 'radio':
        baseItem.render = 'radio'
        baseItem.renderProps = {
          options: field.options?.map(opt => ({
            label: opt.label,
            value: opt.value,
          })) || [],
        }
        break

      case 'inputNumber':
        baseItem.render = 'inputNumber'
        baseItem.renderProps = {
          min: field.min,
          max: field.max,
          placeholder: field.placeholder,
          style: { width: '100%', ...field.style },
        }
        if (field.suffix) {
          baseItem.renderSlots = {
            append: () => h('span', { style: { color: '#666', fontSize: '12px' } }, field.suffix),
          }
        }
        break

      case 'custom':
        if (field.customRender) {
          baseItem.render = field.customRender
        }
        break

      default:
        baseItem.render = 'input'
        baseItem.renderProps = {
          placeholder: field.placeholder,
          style: field.style,
        }
        break
    }

    // 设置验证规则
    if (field.required || field.rules) {
      baseItem.itemProps = {
        rules: [
          ...(field.required ? [{ required: true, message: `请输入${field.label}` }] : []),
          ...(field.rules || []),
        ],
      }
    }

    return baseItem
  })
}

// 初始化表单
useForm('formRef').then((form: MaFormExpose) => {
  form.setItems(getMaFormItems())
  form.setOptions({
    labelWidth: props.labelWidth,
    labelPosition: props.labelPosition,
  })
})

// 监听字段变化，重新设置表单项
watch(() => props.fields, () => {
  nextTick(() => {
    if (formRef.value) {
      formRef.value.setItems(getMaFormItems())
    }
  })
}, { deep: true })

// 监听样式变化
watch(() => [props.labelWidth, props.labelPosition], () => {
  nextTick(() => {
    if (formRef.value) {
      formRef.value.setOptions({
        labelWidth: props.labelWidth,
        labelPosition: props.labelPosition,
      })
    }
  })
})

defineExpose({
  formRef,
})
</script>

<template>
  <div :style="style">
    <ma-form ref="formRef" v-model="formData" />
  </div>
</template>

<style scoped lang="scss">
</style>
