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
import type { Content, PushForm } from '../types'

defineOptions({
  name: 'SmartScreenContentPush',
})

const props = defineProps<{
  contentList: Content[]
  selectedDevicesCount: number
  loading: boolean
}>()

const emit = defineEmits<{
  pushContent: [form: PushForm]
}>()

const form = reactive<PushForm>({
  content_id: undefined,
  is_temp: false,
  duration: 0,
})

// 搜索相关
const searchQuery = ref('')
const filteredContentList = computed(() => {
  if (!searchQuery.value) return props.contentList
  
  const query = searchQuery.value.toLowerCase()
  return props.contentList.filter(content => {
    // 标题匹配
    if (content.title.toLowerCase().includes(query)) return true
    // 内容类型匹配
    if (getContentTypeText(content.content_type).toLowerCase().includes(query)) return true
    // ID匹配
    if (content.id.toString().includes(query)) return true
    return false
  })
})

function handleSearch(query: string) {
  searchQuery.value = query
}

function getContentTypeText(type: number) {
  const types: Record<number, string> = { 1: '网页', 2: '图片', 3: '视频' , 4: '直播', 5: '音频' }
  return types[type] || '未知'
}

function getContentTypeTagType(
  type: number,
): 'primary' | 'success' | 'warning' | 'info' | 'danger' {
  const types: Record<
    number,
    'primary' | 'success' | 'warning' | 'info' | 'danger'
  > = { 1: 'primary', 2: 'success', 3: 'warning' }
  return types[type] || 'info'
}

function getContentTypeIcon(type: number) {
  const icons: Record<number, string> = {
    1: 'heroicons:globe-alt',
    2: 'heroicons:photo',
    3: 'heroicons:video-camera',
  }
  return icons[type] || 'heroicons:document'
}

function handlePushContent() {
  emit('pushContent', { ...form })
}

const selectedContent = computed(() => {
  return props.contentList.find(c => c.id === form.content_id)
})
</script>

<template>
  <div class="mine-card overflow-hidden compact-card">
    <!-- 头部区域 -->
    <div class="relative">
      <div class="absolute inset-0 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/20 dark:to-indigo-950/20 rounded-lg -m-6 mb-0" />
      <div class="relative p-6 -m-6 mb-6">
        <div class="flex items-center gap-3">
          <div class="h-12 w-12 bg-blue-500 rounded-xl flex items-center justify-center shadow-lg">
            <ma-svg-icon name="heroicons:paper-airplane" class="text-white" :size="20" />
          </div>
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">内容推送</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">向选定设备推送内容</p>
          </div>
        </div>
      </div>
    </div>

    <!-- 内容选择区域 -->
    <div class="space-y-6">
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
          <ma-svg-icon name="heroicons:document-text" class="inline mr-2" :size="16" />
          选择推送内容
        </label>
        <el-select
          v-model="form.content_id"
          placeholder="请选择要推送的内容"
          class="w-full"
          clearable
          size="large"
          filterable
          :filter-method="handleSearch"
          popper-class="content-select-dropdown"
        >
          <template #prefix>
            <ma-svg-icon name="heroicons:magnifying-glass" class="text-gray-400" :size="16" />
          </template>
          <template #empty>
            <div class="p-4 text-center">
              <ma-svg-icon name="heroicons:document-magnifying-glass" class="text-gray-400 mb-2" :size="32" />
              <div class="text-sm text-gray-600 dark:text-gray-400">
                {{ searchQuery ? '未找到匹配的内容' : '暂无可推送的内容' }}
              </div>
              <div v-if="searchQuery" class="text-xs text-gray-500 mt-1">
                尝试使用其他关键词搜索
              </div>
            </div>
          </template>
          <el-option
            v-for="content in filteredContentList"
            :key="content.id"
            :value="content.id"
            :label="content.title"
          >
            <div class="flex items-center justify-between py-3">
              <div class="flex items-center gap-3">
                <div class="h-8 w-8 bg-gray-100 dark:bg-gray-800 rounded-lg flex items-center justify-center">
                  <ma-svg-icon
                    :name="getContentTypeIcon(content.content_type)"
                    :size="16"
                    class="text-gray-600 dark:text-gray-400"
                  />
                </div>
                <div>
                  <div class="font-medium">{{ content.title }}</div>
                  <div class="text-xs text-gray-500">{{ getContentTypeText(content.content_type) }}内容</div>
                </div>
              </div>
              <el-tag
                :type="getContentTypeTagType(content.content_type)"
                size="small"
                round
              >
                {{ getContentTypeText(content.content_type) }}
              </el-tag>
            </div>
          </el-option>
        </el-select>
      </div>

      <!-- 选中内容预览 -->
      <div v-if="selectedContent" class="p-4 bg-blue-50 dark:bg-blue-950/20 rounded-lg border border-blue-200 dark:border-blue-800">
        <div class="flex items-start gap-3">
          <div class="h-10 w-10 bg-blue-500 rounded-lg flex items-center justify-center">
            <ma-svg-icon
              :name="getContentTypeIcon(selectedContent.content_type)"
              :size="18"
              class="text-white"
            />
          </div>
          <div class="flex-1">
            <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ selectedContent.title }}</h4>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ getContentTypeText(selectedContent.content_type) }}类型内容</p>
            <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
              <span>类型: {{ getContentTypeText(selectedContent.content_type) }}</span>
              <span>ID: {{ selectedContent.id }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- 推送设置 -->
      <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
          <ma-svg-icon name="heroicons:cog-6-tooth" :size="16" />
          推送设置
        </h4>

        <div class="space-y-4">
          <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3">
              <ma-svg-icon name="heroicons:clock" :size="18" class="text-orange-500" />
              <div>
                <div class="font-medium text-gray-900 dark:text-gray-100">临时推送</div>
                <div class="text-xs text-gray-500">推送后自动恢复原内容</div>
              </div>
            </div>
            <el-switch v-model="form.is_temp" />
          </div>

          <div v-if="form.is_temp" class="p-3 bg-orange-50 dark:bg-orange-950/20 rounded-lg border border-orange-200 dark:border-orange-800">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              持续时间（秒）
            </label>
            <el-input-number
              v-model="form.duration"
              :min="1"
              :max="3600"
              :step="30"
              class="w-full"
              controls-position="right"
            />
            <p class="text-xs text-gray-500 mt-1">设置临时推送的持续时间，到期后自动恢复</p>
          </div>
        </div>
      </div>

      <!-- 推送按钮 -->
      <div class="flex flex-col gap-3">
        <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400">
          <span>已选择设备</span>
          <span class="font-semibold">{{ selectedDevicesCount }} 台</span>
        </div>

        <el-button
          type="primary"
          size="large"
          :loading="loading"
          :disabled="!form.content_id || selectedDevicesCount === 0"
          @click="handlePushContent"
          class="w-full h-12 shadow-lg"
        >
          <template v-if="!loading">
            <ma-svg-icon name="heroicons:paper-airplane" class="mr-2" />
            立即推送到 {{ selectedDevicesCount }} 台设备
          </template>
          <template v-else>
            <ma-svg-icon name="heroicons:arrow-path" class="mr-2 animate-spin" />
            推送中...
          </template>
        </el-button>

        <p v-if="selectedDevicesCount === 0" class="text-xs text-red-500 text-center">
          请先选择要推送的设备
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
:deep(.el-select) {
  --el-select-input-font-size: 14px;
}

:deep(.el-input-number) {
  width: 100%;
}

:deep(.el-input-number .el-input__inner) {
  text-align: left;
}

/* 下拉选择器样式优化 */
:global(.content-select-dropdown) {
  --el-select-dropdown-max-height: 300px;
}

:global(.content-select-dropdown .el-select-dropdown__item) {
  height: auto !important;
  min-height: 60px;
  padding: 8px 12px !important;
  line-height: 1.4;
}

:global(.content-select-dropdown .el-select-dropdown__item:hover) {
  background-color: var(--el-fill-color-light) !important;
}

:global(.content-select-dropdown .el-select-dropdown__item.selected) {
  background-color: var(--el-color-primary-light-9) !important;
  color: var(--el-color-primary) !important;
}

/* 紧凑卡片样式 */
.compact-card {
  padding: 12px !important;
}

/* 搜索框样式优化 */
:deep(.el-select .el-input__prefix) {
  left: 12px;
}

:deep(.el-select .el-input__inner) {
  padding-left: 36px;
}
</style>
