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

defineOptions({
  name: 'DeviceCard',
})

const props = defineProps<{
  device: Device
  selected: boolean
  disabled?: boolean
}>()

const emit = defineEmits<{
  'click': [device: Device]
  'toggle': [device: Device]
}>()

// 处理点击事件
function handleClick() {
  if (props.disabled || props.device.disabled) return
  emit('click', props.device)
  emit('toggle', props.device)
}

// 获取设备状态
function getDeviceStatus() {
  if (props.device.disabled) return { text: '已禁用', type: 'danger' as const }
  if (props.device.online) return { text: '在线', type: 'success' as const }
  return { text: '离线', type: 'info' as const }
}

// 获取连接状态指示器类名
function getStatusDotClass() {
  if (props.device.disabled) return 'status-disabled'
  if (props.device.online) return 'status-online'
  return 'status-offline'
}
</script>

<template>
  <div
    class="device-card"
    :class="{
      'device-selected': selected,
      'device-disabled': disabled || device.disabled,
      'device-offline': !device.online && !device.disabled,
      'device-clickable': !disabled && !device.disabled
    }"
    @click="handleClick"
  >
    <!-- 选择状态指示器 -->
    <div class="selection-indicator" v-if="selected">
      <ma-svg-icon name="heroicons:check" />
    </div>

    <!-- 设备状态指示器 -->
    <div class="status-indicator">
      <div class="status-dot" :class="getStatusDotClass()" />
      <div class="status-pulse" v-if="device.online && !device.disabled" />
    </div>

    <!-- 设备主要信息 -->
    <div class="device-header">
      <div class="device-icon">
        <ma-svg-icon 
          :name="device.online && !device.disabled ? 'heroicons:tv' : 'heroicons:tv-slash'" 
          :class="device.online && !device.disabled ? 'text-blue-500' : 'text-gray-400'"
        />
      </div>
      <div class="device-basic-info">
        <h4 class="device-name" :title="device.device_name">
          {{ device.device_name }}
        </h4>
        <p class="device-mac" :title="device.mac_address">
          {{ device.mac_address }}
        </p>
      </div>
    </div>

    <!-- 设备详细信息 -->
    <div class="device-details">
      <div class="detail-item">
        <span class="detail-label">显示模式</span>
        <span class="detail-value">{{ device.display_mode }}</span>
      </div>
      
      <div class="detail-item" v-if="device.current_content">
        <span class="detail-label">当前内容</span>
        <span class="detail-value" :title="device.current_content">
          {{ device.current_content }}
        </span>
      </div>
    </div>

    <!-- 设备状态标签 -->
    <div class="device-footer">
      <el-tag
        :type="getDeviceStatus().type"
        size="small"
        round
        effect="light"
      >
        {{ getDeviceStatus().text }}
      </el-tag>
      
      <div class="websocket-status" v-if="device.websocket_status">
        <el-tooltip content="WebSocket连接状态" placement="top">
          <ma-svg-icon 
            name="heroicons:signal" 
            :class="device.websocket_status === 'connected' ? 'text-green-500' : 'text-red-500'"
            class="text-sm"
          />
        </el-tooltip>
      </div>
    </div>

    <!-- 悬浮操作按钮 -->
    <div class="device-actions" v-if="!disabled && !device.disabled">
      <el-tooltip content="点击选择设备" placement="top">
        <div class="action-btn">
          <ma-svg-icon 
            :name="selected ? 'heroicons:check-circle' : 'heroicons:plus-circle'" 
            :class="selected ? 'text-green-500' : 'text-blue-500'"
          />
        </div>
      </el-tooltip>
    </div>
  </div>
</template>

<style scoped>
.device-card {
  @apply relative bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 transition-all duration-300 overflow-hidden;
  min-height: 140px;
}

.device-clickable {
  @apply cursor-pointer hover:shadow-lg hover:border-blue-300 dark:hover:border-blue-600 hover:-translate-y-1;
}

.device-selected {
  @apply border-blue-500 dark:border-blue-400 bg-blue-50 dark:bg-blue-900/20 shadow-md;
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(147, 51, 234, 0.05) 100%);
}

.device-disabled {
  @apply opacity-60 cursor-not-allowed;
}

.device-offline {
  @apply bg-gray-50 dark:bg-gray-900/50;
}

.selection-indicator {
  @apply absolute top-2 left-2 w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center z-10;
}

.selection-indicator .ma-svg-icon {
  @apply text-white text-sm;
}

.status-indicator {
  @apply absolute top-2 right-2 flex items-center gap-1;
}

.status-dot {
  @apply w-3 h-3 rounded-full relative z-10;
}

.status-online {
  @apply bg-green-500;
}

.status-offline {
  @apply bg-gray-400;
}

.status-disabled {
  @apply bg-red-500;
}

.status-pulse {
  @apply absolute w-3 h-3 bg-green-400 rounded-full animate-ping;
}

.device-header {
  @apply flex items-start gap-3 p-4 pb-2;
}

.device-icon {
  @apply w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center flex-shrink-0;
}

.device-icon .ma-svg-icon {
  @apply text-xl;
}

.device-basic-info {
  @apply flex-1 min-w-0;
}

.device-name {
  @apply font-semibold text-gray-900 dark:text-gray-100 text-sm leading-tight truncate m-0;
}

.device-mac {
  @apply text-xs text-gray-500 dark:text-gray-400 truncate mt-1 m-0;
}

.device-details {
  @apply px-4 space-y-1;
}

.detail-item {
  @apply flex justify-between items-center;
}

.detail-label {
  @apply text-xs text-gray-500 dark:text-gray-400;
}

.detail-value {
  @apply text-xs text-gray-700 dark:text-gray-300 truncate max-w-20;
}

.device-footer {
  @apply flex items-center justify-between px-4 pb-4 pt-2;
}

.websocket-status {
  @apply flex items-center;
}

.device-actions {
  @apply absolute bottom-2 right-2 opacity-0 transition-opacity duration-200;
}

.device-card:hover .device-actions {
  @apply opacity-100;
}

.action-btn {
  @apply w-8 h-8 bg-white dark:bg-gray-800 rounded-full shadow-lg flex items-center justify-center border border-gray-200 dark:border-gray-600;
}

.action-btn .ma-svg-icon {
  @apply text-lg;
}

/* 响应式设计 */
@media (max-width: 768px) {
  .device-card {
    min-height: 120px;
  }
  
  .device-header {
    @apply p-3 pb-1;
  }
  
  .device-details {
    @apply px-3;
  }
  
  .device-footer {
    @apply px-3 pb-3;
  }
}
</style> 