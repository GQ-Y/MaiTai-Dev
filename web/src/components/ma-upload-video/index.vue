<!--
 - MineAdmin 视频上传组件，参考图片上传组件实现
-->
<i18n lang="yaml">
en:
  openResource: Open resource
  uploadVideo: Upload video
zh_CN:
  openResource: 打开资源选择器
  uploadVideo: 上传视频
zh_TW:
  openResource: 打開資源選擇器
  uploadVideo: 上載視頻
</i18n>
<script setup lang="tsx">
import { useLocalTrans } from '@/hooks/useLocalTrans.ts'
import type { UploadUserFile } from 'element-plus'
import { isArray, uid } from 'radash'
import { useMessage } from '@/hooks/useMessage.ts'
import { uploadLocal } from '@/utils/uploadLocal.ts'
import { ref } from 'vue'
import type { Ref } from 'vue'

defineOptions({ name: 'MaUploadVideo' })

const {
  modelValue = null,
  title = null,
  size = 120,
  fileSize = 3 * 1024 * 1024 * 1024, // 3GB
  fileType = ['video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/flv', 'video/mkv', 'video/webm', 'video/m4v'],
  limit = 1,
  multiple = false,
} = defineProps<{
  modelValue: string | null
  title?: string
  size?: number
  fileSize?: number
  fileType?: string[]
  limit?: number
  multiple?: boolean
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
  (e: 'upload-progress', value: number): void
}>()

const id = uid(5)
const msg = useMessage()
const t = useLocalTrans()

const uploadBtnRef = ref<HTMLElement>()
const isOpenResource = ref<boolean>(false)
const fileList = ref<UploadUserFile[]>([])
const uploadPercent = ref(0)

function btnRender() {
  return (
    <a class="ma-upload-container" style={{ width: `${size}px`, height: `${size}px` }}>
      <el-tooltip content={t('openResource') || '打开资源选择器'}>
        <a
          class="ma-resource-btn"
          onClick={(e: MouseEvent) => {
            e.stopPropagation()
            isOpenResource.value = true
          }}
        >
          <ma-svg-icon name="material-symbols:folder-open-outline-rounded" size={18} />
        </a>
      </el-tooltip>
      <div class="mt-18% flex flex-col items-center">
        <ma-svg-icon name="ep:plus" size={20} />
        <span class="mt-1 text-[14px]">{ title ?? '上传视频' }</span>
      </div>
    </a>
  )
}

function updateModelValue() {
  emit('update:modelValue', fileList.value[0]?.url || '')
}

function handleSuccess(res: any, uploadFile: any) {
  const index = fileList.value.findIndex((item: any) => item.uid === uploadFile.uid)
  if (index > -1) {
    fileList.value[index].name = res.data.origin_name
    fileList.value[index].url = res.data.url
    updateModelValue()
  }
  uploadPercent.value = 0
}

function beforeUpload(rawFile: File) {
  if (!fileType.includes(rawFile.type)) {
    msg.error(`只允许上传：${fileType.join(', ')}`)
    return false
  }
  if (fileSize < rawFile.size) {
    msg.error(`只允许上传${fileSize}字节大小的文件`)
    return false
  }
  return true
}

function handleExceed() {
  msg.error(`当前只允许上传 1 个视频文件，请重新选择！`)
}

function handleError() {
  msg.error(`视频上传失败，请您重新上传！`)
  uploadPercent.value = 0
}

watch(
  () => fileList.value.length,
  (length: number) => {
    const uploadTextDom: HTMLElement | null = document.querySelector(`.ma-upload-${id} .el-upload--text`)
    if (uploadTextDom) {
      uploadTextDom.style.display = length > 0 ? 'none' : 'block'
    }
  },
  { immediate: true },
)

watch(
  () => modelValue,
  (val: string | null) => {
    if (!val) {
      fileList.value = []
      return
    }

    if (fileList.value.length === 0 || fileList.value[0].url !== val) {
      fileList.value = [{
        name: val.split('/').pop() as string,
        url: val,
        uid: uid(10),
        status: 'success',
      }]
    }
  },
  { immediate: true, deep: true },
)
</script>

<template>
  <el-upload
    v-model:file-list="fileList"
    :class="`ma-upload-${id}`"
    :before-upload="beforeUpload"
    :http-request="(options) => uploadLocal(options)"
    :on-success="handleSuccess"
    :on-exceed="handleExceed"
    :on-error="handleError"
    :on-progress="(evt) => {
      console.log('Upload progress:', evt.percent)
      uploadPercent = evt.percent
      emit('upload-progress', evt.percent)
    }"
    :multiple="false"
    :limit="1"
    v-bind="$attrs"
  >
    <slot name="default">
      <component :is="btnRender()" v-show="fileList.length === 0" ref="uploadBtnRef" />
    </slot>
    <template #file="{ file, index }">
      <div class="ma-preview-list ma-upload-container relative" :style="{ width: `${size}px`, height: `${size}px` }">
        <div v-if="uploadPercent > 0 && uploadPercent < 100" class="absolute z-10 w-full h-full flex items-center justify-center bg-dark-5/80 text-white">
          {{ uploadPercent }}%
        </div>
        <div class="ma-preview-mask">
          <ma-svg-icon
            name="material-symbols:delete"
            class="icon"
            :size="20"
            @click="() => { fileList.splice(index, 1); updateModelValue() }"
          />
        </div>
        <div class="absolute left-0 top-0 w-full h-full flex items-center justify-center">
          <ma-svg-icon name="mdi:file-video" size="32" />
        </div>
        <div class="absolute bottom-0 left-0 w-full text-center text-xs truncate bg-dark-1/60 text-white">
          {{ file.name || (file.url ? file.url.split('/').pop() : '') }}
        </div>
      </div>
    </template>
    <template #tip>
      <div v-if="fileList.length < 1" class="pt-1 text-sm text-dark-50 dark-text-gray-3">
        <slot name="tip">
          {{ $attrs?.tip }}
        </slot>
      </div>
    </template>
    <MaResourcePicker
      v-model:visible="isOpenResource"
      :multiple="false"
      :limit="1"
      :file-type="fileType"
      @confirm="(selected) => {
        if (selected && selected.length > 0) {
          fileList = selected.map((item: any) => {
            return { name: item.origin_name, url: item.url, uid: item.id ?? uid(10), status: 'success' }
          })
          updateModelValue()
        }
      }"
    />
  </el-upload>
</template>

<style scoped lang="scss">
:deep(.el-upload-list) {
  @apply flex gap-1.5 flex-wrap;
  .el-upload-list__item {
    @apply w-auto outline-none b-0;
  }
  .el-upload-list__item:hover {
    background: none;
  }
  & :last-child {
    @apply flex gap-x-1.5;
  }
}
.ma-upload-container {
  @apply flex items-center justify-center bg-gray-50 b-1 b-dashed rounded-md b-gray-3 dark-b-dark-50
    transition-all duration-300 text-gray-5 dark-bg-dark-5 relative;

  .ma-resource-btn {
    @apply absolute top-0 b-1 b-dashed b-gray-3 dark-b-dark-50 transition-all duration-300 rounded-t-md
      w-[calc(100%)] mx-auto b-t-0 b-l-0 b-r-0 text-gray-5 dark-bg-dark-8 bg-gray-1 h-[calc(100%-80%)]
      flex items-center justify-center;
  }

  .ma-preview-mask {
    @apply absolute z-8 w-full h-full rounded-md transition-all duration-300 flex items-center justify-center gap-x-3;
    .icon {
      @apply hidden text-white cursor-pointer;
    }
  }
  .ma-preview-mask:hover {
    @apply bg-dark-5/50%;
    .icon {
      @apply inline;
    }
  }

  &:hover, .ma-resource-btn:hover {
    @apply text-[rgb(var(--ui-primary))] b-[rgb(var(--ui-primary))];
  }
}
</style> 