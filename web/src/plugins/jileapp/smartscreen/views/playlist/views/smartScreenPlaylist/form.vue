<script setup lang="ts">
import getFormItems from './data/getFormItems'
import type { MaFormExpose } from '@mineadmin/form'
import useForm from '@/hooks/useForm.ts'
import { create, save } from '../../api/smartScreenPlaylist'
import { ResultCode } from '@/utils/ResultCode.ts'

const { formType = 'add', data = null } = defineProps<{ formType: 'add' | 'edit', data?: any | null }>()

const t = useTrans().globalTrans
const formRef = ref<MaFormExpose>()
const formData = ref<any>({
  name: '',
  play_mode: 1, // 默认顺序播放
  status: 1, // 默认启用
})

// 初始化表单
useForm('formRef').then((form: MaFormExpose) => {
  if (formType === 'edit' && data) {
    Object.keys(data).map((key: string) => {
      formData.value[key] = data[key]
    })
  }

  // 设置表单项
  const formItems = getFormItems(formType, t, formData.value)
  form.setItems(formItems)
  form.setOptions({ labelWidth: '120px' })
})

function add(): Promise<any> {
  return new Promise((resolve, reject) => {
    create(formData.value).then((res: any) => {
      res.code === ResultCode.SUCCESS ? resolve(res) : reject(res)
    }).catch(reject)
  })
}

function edit(): Promise<any> {
  return new Promise((resolve, reject) => {
    save(formData.value.id as number, formData.value).then((res: any) => {
      res.code === ResultCode.SUCCESS ? resolve(res) : reject(res)
    }).catch(reject)
  })
}

defineExpose({ add, edit, maForm: formRef })
</script>

<template>
  <div>
    <ma-form ref="formRef" v-model="formData" />
  </div>
</template>

<style scoped lang="scss">
</style>
