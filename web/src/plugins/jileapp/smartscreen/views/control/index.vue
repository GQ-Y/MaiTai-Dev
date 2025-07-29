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
import { computed, onMounted, reactive, ref } from 'vue'
import * as controlApi from './api/control'
import { useMessage } from '@/hooks/useMessage.ts'
import type { BroadcastForm, Content, Device, LoadingState, ModeForm, PushForm } from './types'

// 组件导入
import DeviceStats from './components/DeviceStats.vue'
import QuickActions from './components/QuickActions.vue'
import DeviceSelector from './components/DeviceSelector.vue'
import ContentPush from './components/ContentPush.vue'
import ModeSwitch from './components/ModeSwitch.vue'
// import BroadcastControl from './components/BroadcastControl.vue'

defineOptions({
  name: 'SmartScreenControl',
})

const msg = useMessage()

// 响应式数据
const loading = reactive<LoadingState>({
  deviceStatus: false,
  pushContent: false,
  switchMode: false,
  broadcast: false,
  quickAction: false,
})

const deviceList = ref<Device[]>([])
const contentList = ref<Content[]>([])
const selectedDevices = ref<number[]>([])

// 数据加载
async function refreshDeviceStatus() {
  loading.deviceStatus = true
  try {
    const result = await controlApi.getDeviceStatus({ page: 1, size: 100 })
    deviceList.value = (result.data.list as Device[]) || []
  }
  catch (error) {
    console.error('获取设备状态失败:', error)
    msg.error('获取设备状态失败')
  }
  finally {
    loading.deviceStatus = false
  }
}

async function loadContentList() {
  try {
    const result = await useHttp().get(
      '/admin/plugin/smart-screen/content/list',
      {
        params: { page: 1, size: 100 },
      },
    )
    contentList.value = (result.data.list as Content[]) || []
  }
  catch (error) {
    console.error('获取内容列表失败:', error)
    msg.error('获取内容列表失败')
  }
}

// 设备选择处理
function handleUpdateSelectedDevices(devices: number[]) {
  selectedDevices.value = devices
}

// 控制操作
async function handleQuickAction(action: string) {
  loading.quickAction = true
  try {
    await controlApi.broadcast({
      action,
      device_ids: [],
      message: '',
    })

    const actionText = action === 'refresh'
      ? '刷新'
      : action === 'activate'
        ? '激活'
        : action === 'deactivate'
          ? '禁用'
          : '刷新'

    msg.success(`${actionText}操作已执行`)
    await refreshDeviceStatus()
  }
  catch {
    msg.error('操作失败')
  }
  finally {
    loading.quickAction = false
  }
}

async function handlePushContent(form: PushForm) {
  if (!form.content_id || selectedDevices.value.length === 0) {
    return
  }

  loading.pushContent = true
  try {
    await controlApi.pushContent({
      device_ids: selectedDevices.value,
      content_id: form.content_id,
      is_temp: form.is_temp,
      duration: form.duration,
    })

    msg.success(`内容推送成功，影响 ${selectedDevices.value.length} 个设备`)
    await refreshDeviceStatus()
  }
  catch {
    msg.error('内容推送失败')
  }
  finally {
    loading.pushContent = false
  }
}

async function handleSwitchMode(form: ModeForm) {
  if (selectedDevices.value.length === 0) {
    return
  }

  loading.switchMode = true
  try {
    await controlApi.switchMode({
      device_ids: selectedDevices.value,
      display_mode: form.display_mode,
    })

    msg.success(`模式切换成功，影响 ${selectedDevices.value.length} 个设备`)
    await refreshDeviceStatus()
  }
  catch {
    msg.error('模式切换失败')
  }
  finally {
    loading.switchMode = false
  }
}

async function handleBroadcast(form: BroadcastForm) {
  if (selectedDevices.value.length === 0) {
    return
  }

  loading.broadcast = true
  try {
    await controlApi.broadcast({
      device_ids: selectedDevices.value,
      action: form.action,
      message: form.message,
    })

    msg.success(`广播操作成功，影响 ${selectedDevices.value.length} 个设备`)
    await refreshDeviceStatus()
  }
  catch {
    msg.error('广播操作失败')
  }
  finally {
    loading.broadcast = false
  }
}

// 初始化
onMounted(async () => {
  await Promise.all([refreshDeviceStatus(), loadContentList()])
})
</script>

<template>
  <div class="mine-layout">
    <!-- 页面标题 -->
    <div class="mine-card mineadmin-pro-table-header ma-pro-table-header">
      <div class="mineadmin-pro-table-header-title">
        <div class="main-title">
          智慧屏控制中心
        </div>
        <div class="secondary-title">
          管理和控制所有智慧屏设备
        </div>
      </div>
      <div class="mineadmin-pro-table-header-actions">
        <el-button
          type="primary"
          :loading="loading.deviceStatus"
          @click="refreshDeviceStatus"
        >
          <ma-svg-icon name="ic:outline-refresh" class="mr-1" />
          刷新数据
        </el-button>
      </div>
    </div>

    <!-- 设备统计 -->
    <DeviceStats :device-list="deviceList" />

    <!-- 快速控制 -->
    <QuickActions
      :loading="loading.quickAction"
      @quick-action="handleQuickAction"
    />

    <!-- 设备选择 -->
    <DeviceSelector
      v-model="selectedDevices"
      :devices="deviceList"
      @selection-change="handleUpdateSelectedDevices"
    />

    <!-- 三个控制模块 -->
    <div class="grid grid-cols-1 gap-0 lg:grid-cols-3">
      <!-- 内容推送 -->
      <ContentPush
        :content-list="contentList"
        :selected-devices-count="selectedDevices.length"
        :loading="loading.pushContent"
        @push-content="handlePushContent"
      />

      <!-- 模式切换 -->
      <ModeSwitch
        :selected-devices-count="selectedDevices.length"
        :loading="loading.switchMode"
        @switch-mode="handleSwitchMode"
      />

      <!-- 广播控制 -->
      <BroadcastControl
        :selected-devices-count="selectedDevices.length"
        :loading="loading.broadcast"
        @broadcast="handleBroadcast"
      />
    </div>
  </div>
</template>

<style scoped>
/* 使用与 @mineadmin/pro-table 完全一致的头部标题栏样式 */
.mineadmin-pro-table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 13px;
  border-color: rgb(255, 255, 255, var(--tw-border-opacity));
  /* background: rgba(255, 255, 255, 0.4); */
}

.mineadmin-pro-table-header-title {
  display: flex;
  column-gap: 1rem;
  align-items: center;
}

.mineadmin-pro-table-header-title .main-title {
  font-size: 16px;
}

.mineadmin-pro-table-header-title .secondary-title {
  color: #6b7280;
  font-size: 14px;
}

.mineadmin-pro-table-header-actions {
  display: flex;
  column-gap: 10px;
  align-items: center;
}

/* 暗色模式适配 */
.dark .mineadmin-pro-table-header-title .secondary-title {
  color: #9ca3af;
}

/* 移除响应式特殊处理，保持与 pro-table 一致 */
</style>
