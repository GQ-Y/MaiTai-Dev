<!--
 - MineAdmin is committed to providing solutions for quickly building web applications
 - Please view the LICENSE file that was distributed with this source code,
 - For the full copyright and license information.
 - Thank you very much for using MineAdmin.
 -
 - @Author X.Mo<root@imoi.cn>
 - @Link   https://github.com/mineadmin
-->
<script setup lang="ts">
import getFormItems from './data/getFormItems'
import type { MaFormExpose } from '@mineadmin/form'
import useForm from '@/hooks/useForm.ts'
import { create, save } from '../../api/smartScreenContent'
import { ResultCode } from '@/utils/ResultCode.ts'

const emit = defineEmits(['uploading', 'upload-progress'])

const { formType = 'add', data = null } = defineProps<{ formType: 'add' | 'edit', data?: any | null }>()

const t = useTrans().globalTrans
const formRef = ref<MaFormExpose>()
const formData = ref<any>({
  title: '',
  content_type: 1, // 默认网页类型
  content_url: '',
  thumbnail: '',
  duration: 0, // 默认永久播放
  sort_order: 0, // 默认排序
  status: 1, // 默认启用
})

// 监听 MaUploadFile 上传事件
function handleUploadStart() {
  emit('uploading', true)
}
function handleUploadProgress(percent: number) {
  emit('upload-progress', percent)
}
function handleUploadSuccess() {
  emit('uploading', false)
  emit('upload-progress', 100)
}
function handleUploadError() {
  emit('uploading', false)
}

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
    <ma-form ref="formRef" v-model="formData">
      <template #content_url="{ model, item }">
        <component
          :is="item.render"
          v-bind="item.renderProps"
          v-model="model.content_url"
          @start="handleUploadStart"
          @progress="handleUploadProgress"
          @success="handleUploadSuccess"
          @error="handleUploadError"
        />
      </template>
    </ma-form>
  </div>
</template>

<style scoped lang="scss">
</style>
