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
import type { BroadcastForm } from '../types'

defineOptions({
  name: 'SmartScreenBroadcastControl',
})

defineProps<{
  selectedDevicesCount: number
  loading: boolean
}>()

const emit = defineEmits<{
  broadcast: [form: BroadcastForm]
}>()

const form = reactive<BroadcastForm>({
  action: 'refresh',
  message: '',
})

const actionOptions = [
  {
    value: 'refresh',
    title: '刷新设备',
    description: '重新加载设备内容，不影响设备运行状态',
    icon: 'heroicons:arrow-path',
    color: 'blue',
    bgColor: 'bg-blue-50 dark:bg-blue-950/20',
    borderColor: 'border-blue-200 dark:border-blue-800',
    iconBg: 'bg-blue-500',
    buttonType: 'primary' as const,
  },
  {
    value: 'restart',
    title: '重启设备',
    description: '完全重启设备系统，可能需要较长时间',
    icon: 'heroicons:arrow-path-rounded-square',
    color: 'orange',
    bgColor: 'bg-orange-50 dark:bg-orange-950/20',
    borderColor: 'border-orange-200 dark:border-orange-800',
    iconBg: 'bg-orange-500',
    buttonType: 'warning' as const,
  },
  {
    value: 'shutdown',
    title: '关闭设备',
    description: '安全关闭设备，设备将无法响应直到手动开启',
    icon: 'heroicons:power',
    color: 'red',
    bgColor: 'bg-red-50 dark:bg-red-950/20',
    borderColor: 'border-red-200 dark:border-red-800',
    iconBg: 'bg-red-500',
    buttonType: 'danger' as const,
  },
]

function handleBroadcast() {
  emit('broadcast', { ...form })
}

const selectedAction = computed(() => {
  return actionOptions.find(action => action.value === form.action)
})

const isDestructiveAction = computed(() => {
  return ['restart', 'shutdown'].includes(form.action)
})
</script>

<template>
  <div class="mine-card overflow-hidden compact-card">
    <!-- 头部区域 -->
    <div class="relative">
      <div class="absolute inset-0 bg-gradient-to-r from-green-50 to-teal-50 dark:from-green-950/20 dark:to-teal-950/20 rounded-lg -m-6 mb-0" />
      <div class="relative p-6 -m-6 mb-6">
        <div class="flex items-center gap-3">
          <div class="h-12 w-12 bg-green-500 rounded-xl flex items-center justify-center shadow-lg">
            <ma-svg-icon name="heroicons:megaphone" class="text-white" :size="20" />
          </div>
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">广播控制</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">远程控制设备运行状态</p>
          </div>
        </div>
      </div>
    </div>

    <!-- 控制内容 -->
    <div class="space-y-6">
      <!-- 操作类型选择 -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">
          <ma-svg-icon name="heroicons:command-line" class="inline mr-2" :size="16" />
          选择操作类型
        </label>

        <div class="grid gap-3">
          <div
            v-for="action in actionOptions"
            :key="action.value"
            class="relative cursor-pointer transition-all duration-200"
            @click="form.action = action.value"
          >
            <div
              class="p-4 rounded-lg border-2 transition-all duration-200"
              :class="[
                form.action === action.value
                  ? `${action.bgColor} ${action.borderColor} shadow-sm`
                  : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
              ]"
            >
              <div class="flex items-start gap-3">
                <div
                  class="h-10 w-10 rounded-lg flex items-center justify-center transition-all duration-200"
                  :class="form.action === action.value ? action.iconBg : 'bg-gray-100 dark:bg-gray-700'"
                >
                  <ma-svg-icon
                    :name="action.icon"
                    :size="18"
                    :class="form.action === action.value ? 'text-white' : 'text-gray-600 dark:text-gray-400'"
                  />
                </div>
                <div class="flex-1">
                  <div class="flex items-center gap-2 mb-1">
                    <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ action.title }}</h4>
                    <div
                      v-if="form.action === action.value"
                      class="h-2 w-2 rounded-full bg-current animate-pulse"
                      :class="`text-${action.color}-500`"
                    />
                  </div>
                  <p class="text-sm text-gray-600 dark:text-gray-400">{{ action.description }}</p>
                </div>
                <div
                  class="h-5 w-5 rounded-full border-2 flex items-center justify-center transition-all duration-200"
                  :class="form.action === action.value
                    ? `border-${action.color}-500 bg-${action.color}-500`
                    : 'border-gray-300 dark:border-gray-600'"
                >
                  <div
                    v-if="form.action === action.value"
                    class="h-2 w-2 rounded-full bg-white"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 危险操作警告 -->
      <div v-if="isDestructiveAction" class="p-4 bg-red-50 dark:bg-red-950/20 rounded-lg border border-red-200 dark:border-red-800">
        <div class="flex items-start gap-3">
          <ma-svg-icon name="heroicons:exclamation-triangle" :size="20" class="text-red-500 mt-0.5" />
          <div>
            <h4 class="font-medium text-red-800 dark:text-red-200 mb-1">危险操作警告</h4>
            <p class="text-sm text-red-700 dark:text-red-300">
              {{ form.action === 'restart' ? '重启操作将中断设备当前运行的所有任务' : '关闭操作将使设备完全停止工作' }}，
              请确认设备当前没有重要任务正在执行。
            </p>
          </div>
        </div>
      </div>

      <!-- 广播消息 -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
          <ma-svg-icon name="heroicons:chat-bubble-bottom-center-text" class="inline mr-2" :size="16" />
          广播消息（可选）
        </label>
        <el-input
          v-model="form.message"
          type="textarea"
          :rows="4"
          placeholder="输入要发送给设备的消息，可用于通知或说明本次操作的目的..."
          maxlength="500"
          show-word-limit
          class="broadcast-textarea"
        />
        <p class="text-xs text-gray-500 mt-2">
          <ma-svg-icon name="heroicons:information-circle" class="inline mr-1" :size="14" />
          消息将同步发送到所有选中的设备
        </p>
      </div>

      <!-- 当前选择预览 -->
      <div v-if="selectedAction" class="p-4 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
        <div class="flex items-center gap-3 mb-3">
          <ma-svg-icon name="heroicons:check-circle" :size="18" class="text-green-500" />
          <span class="font-medium text-gray-900 dark:text-gray-100">执行预览</span>
        </div>
        <div class="flex items-start gap-3">
          <div :class="`h-8 w-8 ${selectedAction.iconBg} rounded-lg flex items-center justify-center`">
            <ma-svg-icon :name="selectedAction.icon" :size="16" class="text-white" />
          </div>
          <div class="flex-1">
            <div class="font-medium text-gray-900 dark:text-gray-100">{{ selectedAction.title }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ selectedAction.description }}</div>
            <div v-if="form.message" class="text-sm">
              <span class="text-gray-600 dark:text-gray-400">附带消息：</span>
              <span class="text-gray-900 dark:text-gray-100 font-medium">"{{ form.message }}"</span>
            </div>
          </div>
        </div>
      </div>

      <!-- 执行按钮 -->
      <div class="flex flex-col gap-3">
        <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400">
          <span>目标设备</span>
          <span class="font-semibold">{{ selectedDevicesCount }} 台</span>
        </div>

        <el-button
          :type="selectedAction?.buttonType || 'primary'"
          size="large"
          :loading="loading"
          :disabled="selectedDevicesCount === 0"
          @click="handleBroadcast"
          class="w-full h-12 shadow-lg"
        >
          <template v-if="!loading">
            <ma-svg-icon :name="selectedAction?.icon || 'heroicons:megaphone'" class="mr-2" />
            {{ selectedAction?.title }} ({{ selectedDevicesCount }}台设备)
          </template>
          <template v-else>
            <ma-svg-icon name="heroicons:arrow-path" class="mr-2 animate-spin" />
            执行中...
          </template>
        </el-button>

        <p v-if="selectedDevicesCount === 0" class="text-xs text-red-500 text-center">
          请先选择要执行操作的设备
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
:deep(.broadcast-textarea .el-textarea__inner) {
  font-size: 14px;
  line-height: 1.5;
}



/* 紧凑卡片样式 */
.compact-card {
  padding: 12px !important;
}
</style>
