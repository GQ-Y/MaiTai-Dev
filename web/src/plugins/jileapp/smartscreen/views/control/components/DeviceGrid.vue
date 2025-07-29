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
import type { Device } from '../types'
import { computed } from 'vue'
import { Check, Close } from '@element-plus/icons-vue'
import DeviceCard from './DeviceCard.vue'

defineOptions({
  name: 'DeviceGrid',
})

const props = defineProps<{
  devices: Device[]
  selectedIds: number[]
  loading?: boolean
}>()

const emit = defineEmits<{
  'device-toggle': [device: Device]
  'select-all': []
  'clear-selection': []
}>()

// 可用设备（未禁用的设备）
const availableDevices = computed(() => {
  return props.devices.filter(device => !device.disabled)
})

// 是否设备被选中
function isDeviceSelected(device: Device): boolean {
  return props.selectedIds.includes(device.id)
}

// 处理设备点击
function handleDeviceToggle(device: Device) {
  emit('device-toggle', device)
}

// 全选设备
function handleSelectAll() {
  emit('select-all')
}

// 清空选择
function handleClearSelection() {
  emit('clear-selection')
}
</script>

<template>
  <div class="device-grid">
    <!-- 设备列表头部 -->
    <div class="grid-header">
      <div class="header-info">
        <div class="flex items-center gap-2">
          <div class="header-icon">
            <ma-svg-icon name="heroicons:queue-list" />
          </div>
          <h3 class="header-title">设备选择</h3>
          <el-tag v-if="devices.length" size="small" type="primary" round>
            {{ devices.length }} 台设备
          </el-tag>
        </div>
        <p class="header-description">点击设备卡片进行选择，支持多选</p>
      </div>
      
      <div class="header-actions">
        <el-button
          size="small"
          type="primary"
          :icon="Check"
          @click="handleSelectAll"
          :disabled="!availableDevices.length"
          round
        >
          全选可用
        </el-button>
        <el-button
          size="small"
          :icon="Close"
          @click="handleClearSelection"
          :disabled="!selectedIds.length"
          round
        >
          清空选择
        </el-button>
      </div>
    </div>

    <!-- 设备网格 -->
    <div class="grid-content">
      <!-- 空状态 -->
      <div v-if="!devices.length && !loading" class="empty-state">
        <div class="empty-icon">
          <ma-svg-icon name="heroicons:tv-slash" />
        </div>
        <h4 class="empty-title">暂无可用设备</h4>
        <p class="empty-description">请检查设备连接状态或联系管理员</p>
      </div>

      <!-- 加载状态 -->
      <div v-else-if="loading" class="loading-state">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4">
          <div
            v-for="i in 6"
            :key="i"
            class="skeleton-card"
          >
            <el-skeleton animated>
              <template #template>
                <div class="skeleton-content">
                  <el-skeleton-item variant="circle" class="skeleton-avatar" />
                  <div class="skeleton-info">
                    <el-skeleton-item variant="text" class="skeleton-title" />
                    <el-skeleton-item variant="text" class="skeleton-subtitle" />
                  </div>
                </div>
              </template>
            </el-skeleton>
          </div>
        </div>
      </div>

      <!-- 设备列表 -->
      <div v-else class="device-list">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4">
          <DeviceCard
            v-for="device in devices"
            :key="device.id"
            :device="device"
            :selected="isDeviceSelected(device)"
            @toggle="handleDeviceToggle"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.device-grid {
  @apply space-y-6;
}

.grid-header {
  @apply flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700;
}

.header-info {
  @apply flex-1;
}

.header-icon {
  @apply w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center;
}

.header-icon .ma-svg-icon {
  @apply text-white text-lg;
}

.header-title {
  @apply text-lg font-semibold text-gray-800 dark:text-gray-200 m-0;
}

.header-description {
  @apply text-sm text-gray-600 dark:text-gray-400 mt-1 m-0;
}

.header-actions {
  @apply flex gap-2;
}

.grid-content {
  @apply min-h-96;
}

/* 空状态样式 */
.empty-state {
  @apply flex flex-col items-center justify-center py-16 text-center;
}

.empty-icon {
  @apply w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4;
}

.empty-icon .ma-svg-icon {
  @apply text-gray-400 text-2xl;
}

.empty-title {
  @apply text-lg font-medium text-gray-700 dark:text-gray-300 mb-2 m-0;
}

.empty-description {
  @apply text-sm text-gray-500 dark:text-gray-400 m-0;
}

/* 加载状态样式 */
.loading-state {
  @apply py-4;
}

.skeleton-card {
  @apply bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4;
  min-height: 140px;
}

.skeleton-content {
  @apply flex items-start gap-3;
}

.skeleton-avatar {
  @apply w-10 h-10 flex-shrink-0;
}

.skeleton-info {
  @apply flex-1 space-y-2;
}

.skeleton-title {
  @apply h-4;
}

.skeleton-subtitle {
  @apply h-3 w-3/4;
}

/* 设备列表样式 */
.device-list {
  @apply py-2;
}

/* 响应式网格调整 */
@media (max-width: 640px) {
  .grid-header {
    @apply flex-col items-start;
  }
  
  .header-actions {
    @apply w-full justify-end;
  }
}

/* 动画效果 */
.device-list .device-card {
  animation: fadeInUp 0.3s ease-out;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* 延迟动画 */
.device-list .device-card:nth-child(1) { animation-delay: 0.1s; }
.device-list .device-card:nth-child(2) { animation-delay: 0.2s; }
.device-list .device-card:nth-child(3) { animation-delay: 0.3s; }
.device-list .device-card:nth-child(4) { animation-delay: 0.4s; }
.device-list .device-card:nth-child(5) { animation-delay: 0.5s; }
.device-list .device-card:nth-child(6) { animation-delay: 0.6s; }
</style> 