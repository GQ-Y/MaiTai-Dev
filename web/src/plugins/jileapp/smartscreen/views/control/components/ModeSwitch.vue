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
import type { ModeForm } from '../types'

defineOptions({
  name: 'SmartScreenModeSwitch',
})

defineProps<{
  selectedDevicesCount: number
  loading: boolean
}>()

const emit = defineEmits<{
  switchMode: [form: ModeForm]
}>()

const form = reactive<ModeForm>({
  display_mode: 1,
})

const modeOptions = [
  {
    value: 1,
    title: '播放列表优先',
    description: '优先播放设备播放列表，其次播放直接推送内容',
    icon: 'heroicons:list-bullet',
    color: 'blue',
    bgColor: 'bg-blue-50 dark:bg-blue-950/20',
    borderColor: 'border-blue-200 dark:border-blue-800',
    iconBg: 'bg-blue-500',
  },
  {
    value: 2,
    title: '直接内容优先',
    description: '优先播放直接推送内容，其次播放播放列表',
    icon: 'heroicons:paper-airplane',
    color: 'green',
    bgColor: 'bg-green-50 dark:bg-green-950/20',
    borderColor: 'border-green-200 dark:border-green-800',
    iconBg: 'bg-green-500',
  },
  {
    value: 3,
    title: '仅播放列表',
    description: '只播放设备播放列表，忽略直接推送内容',
    icon: 'heroicons:queue-list',
    color: 'orange',
    bgColor: 'bg-orange-50 dark:bg-orange-950/20',
    borderColor: 'border-orange-200 dark:border-orange-800',
    iconBg: 'bg-orange-500',
  },
  {
    value: 4,
    title: '仅直接内容',
    description: '只播放直接推送内容，忽略播放列表',
    icon: 'heroicons:bolt',
    color: 'purple',
    bgColor: 'bg-purple-50 dark:bg-purple-950/20',
    borderColor: 'border-purple-200 dark:border-purple-800',
    iconBg: 'bg-purple-500',
  },
]

function handleSwitchMode() {
  emit('switchMode', { ...form })
}

const selectedMode = computed(() => {
  return modeOptions.find(mode => mode.value === form.display_mode)
})
</script>

<template>
  <div class="mine-card overflow-hidden compact-card">
    <!-- 头部区域 -->
    <div class="relative">
      <div class="absolute inset-0 bg-gradient-to-r from-orange-50 to-red-50 dark:from-orange-950/20 dark:to-red-950/20 rounded-lg -m-6 mb-0" />
      <div class="relative p-6 -m-6 mb-6">
        <div class="flex items-center gap-3">
          <div class="h-12 w-12 bg-orange-500 rounded-xl flex items-center justify-center shadow-lg">
            <ma-svg-icon name="heroicons:arrow-path-rounded-square" class="text-white" :size="20" />
          </div>
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">显示模式切换</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">设置设备的内容播放模式</p>
          </div>
        </div>
      </div>
    </div>

    <!-- 模式选择区域 -->
    <div class="space-y-6">
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">
          <ma-svg-icon name="heroicons:cog-6-tooth" class="inline mr-2" :size="16" />
          选择显示模式
        </label>

        <div class="grid gap-3">
          <div
            v-for="mode in modeOptions"
            :key="mode.value"
            class="relative cursor-pointer transition-all duration-200"
            @click="form.display_mode = mode.value"
          >
            <div
              class="p-4 rounded-lg border-2 transition-all duration-200"
              :class="[
                form.display_mode === mode.value
                  ? `${mode.bgColor} ${mode.borderColor} shadow-sm`
                  : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
              ]"
            >
              <div class="flex items-start gap-3">
                <div
                  class="h-10 w-10 rounded-lg flex items-center justify-center transition-all duration-200"
                  :class="form.display_mode === mode.value ? mode.iconBg : 'bg-gray-100 dark:bg-gray-700'"
                >
                  <ma-svg-icon
                    :name="mode.icon"
                    :size="18"
                    :class="form.display_mode === mode.value ? 'text-white' : 'text-gray-600 dark:text-gray-400'"
                  />
                </div>
                <div class="flex-1">
                  <div class="flex items-center gap-2 mb-1">
                    <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ mode.title }}</h4>
                    <div
                      v-if="form.display_mode === mode.value"
                      class="h-2 w-2 rounded-full bg-current animate-pulse"
                      :class="`text-${mode.color}-500`"
                    />
                  </div>
                  <p class="text-sm text-gray-600 dark:text-gray-400">{{ mode.description }}</p>
                </div>
                <div
                  class="h-5 w-5 rounded-full border-2 flex items-center justify-center transition-all duration-200"
                  :class="form.display_mode === mode.value
                    ? `border-${mode.color}-500 bg-${mode.color}-500`
                    : 'border-gray-300 dark:border-gray-600'"
                >
                  <div
                    v-if="form.display_mode === mode.value"
                    class="h-2 w-2 rounded-full bg-white"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 当前选择预览 -->
      <div v-if="selectedMode" class="p-4 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
        <div class="flex items-center gap-3 mb-2">
          <ma-svg-icon name="heroicons:check-circle" :size="18" class="text-green-500" />
          <span class="font-medium text-gray-900 dark:text-gray-100">当前选择</span>
        </div>
        <div class="flex items-start gap-3">
          <div :class="`h-8 w-8 ${selectedMode.iconBg} rounded-lg flex items-center justify-center`">
            <ma-svg-icon :name="selectedMode.icon" :size="16" class="text-white" />
          </div>
          <div>
            <div class="font-medium text-gray-900 dark:text-gray-100">{{ selectedMode.title }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">{{ selectedMode.description }}</div>
          </div>
        </div>
      </div>

      <!-- 切换按钮 -->
      <div class="flex flex-col gap-3">
        <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400">
          <span>影响设备</span>
          <span class="font-semibold">{{ selectedDevicesCount }} 台</span>
        </div>

        <el-button
          type="warning"
          size="large"
          :loading="loading"
          :disabled="selectedDevicesCount === 0"
          @click="handleSwitchMode"
          class="w-full h-12 shadow-lg"
        >
          <template v-if="!loading">
            <ma-svg-icon name="heroicons:arrow-path" class="mr-2" />
            切换到{{ selectedMode?.title }} ({{ selectedDevicesCount }}台)
          </template>
          <template v-else>
            <ma-svg-icon name="heroicons:arrow-path" class="mr-2 animate-spin" />
            切换中...
          </template>
        </el-button>

        <p v-if="selectedDevicesCount === 0" class="text-xs text-red-500 text-center">
          请先选择要切换模式的设备
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>


/* 紧凑卡片样式 */
.compact-card {
  padding: 12px !important;
}
</style>
