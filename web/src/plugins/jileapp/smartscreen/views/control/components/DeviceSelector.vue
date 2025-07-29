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
import type { Device, DeviceGroup } from '../types'
import { ref, computed, watch } from 'vue'
import DeviceGroupFilter from './DeviceGroupFilter.vue'
import DeviceGrid from './DeviceGrid.vue'
import * as deviceGroupApi from '../../group/api/smartScreenDeviceGroup'

defineOptions({
  name: 'SmartScreenDeviceSelector',
})

const props = defineProps<{
  devices: Device[]  // 接收所有设备列表
  modelValue: number[]
  loading?: boolean
}>()

const emit = defineEmits<{
  'update:modelValue': [value: number[]]
  'selection-change': [value: number[]]
}>()

// 设备分组相关
const selectedGroupIds = ref<number[]>([])
const groupDevicesMap = ref<Map<number, Device[]>>(new Map())

// 设备选择相关
const selectedDeviceIds = ref<number[]>(props.modelValue || [])

// 加载分组设备
async function loadGroupDevices(groupIds: number[]) {
  try {
    for (const groupId of groupIds) {
      if (!groupDevicesMap.value.has(groupId)) {
        const res = await deviceGroupApi.getGroupDevices(groupId)
        if (res.code === 200 && res.data) {
          groupDevicesMap.value.set(groupId, res.data)
        }
      }
    }
  } catch (error) {
    console.error('Failed to load group devices:', error)
  }
}

// 处理分组选择变化
async function handleGroupChange(groupIds: number[]) {
  selectedGroupIds.value = groupIds || []
  if (groupIds && groupIds.length > 0) {
    await loadGroupDevices(groupIds)
  }
}

// 转换设备数据格式
function transformDevice(device: any): Device {
  return {
    id: device.id,
    device_name: device.device_name,
    mac_address: device.mac_address,
    display_mode: device.display_mode?.toString() || '1',
    online: Boolean(device.is_online),
    disabled: !Boolean(device.status),
    status: device.status,
    is_online: Boolean(device.is_online),
    websocket_status: device.websocket_status,
    current_content: device.current_content
  }
}

// 获取筛选后的设备列表
const filteredDevices = computed(() => {
  // 如果没有传入设备列表，返回空数组
  if (!props.devices || !props.devices.length) return []
  
  // 转换设备数据格式
  const transformedDevices = props.devices.map(transformDevice)
  
  // 如果没有选择分组，显示所有设备
  if (!selectedGroupIds.value || !selectedGroupIds.value.length) {
    return transformedDevices.sort((a, b) => {
      // 优先显示在线设备
      if (a.online !== b.online) return a.online ? -1 : 1
      // 然后按名称排序
      return a.device_name.localeCompare(b.device_name)
    })
  }
  
  // 如果选择了分组，获取分组内的设备ID
  const groupDeviceIds = new Set<number>()
  selectedGroupIds.value.forEach(groupId => {
    const devices = groupDevicesMap.value.get(groupId) || []
    devices.forEach(device => {
      groupDeviceIds.add(device.id)
    })
  })
  
  // 筛选出分组内的设备
  return transformedDevices
    .filter(device => groupDeviceIds.has(device.id))
    .sort((a, b) => {
      // 优先显示在线设备
      if (a.online !== b.online) return a.online ? -1 : 1
      // 然后按名称排序
      return a.device_name.localeCompare(b.device_name)
    })
})

// 切换设备选择
function toggleDevice(device: Device) {
  if (device.disabled) return
  
  if (!selectedDeviceIds.value) {
    selectedDeviceIds.value = []
  }
  
  const index = selectedDeviceIds.value.indexOf(device.id)
  if (index > -1) {
    selectedDeviceIds.value.splice(index, 1)
  } else {
    selectedDeviceIds.value.push(device.id)
  }
}

// 全选设备
function selectAllDevices() {
  if (!filteredDevices.value) return
  
  const availableIds = filteredDevices.value
    .filter(device => !device.disabled)
    .map(device => device.id)
  selectedDeviceIds.value = availableIds
}

// 清空选择
function clearSelection() {
  selectedDeviceIds.value = []
}

// 监听选择变化
watch(selectedDeviceIds, (newVal) => {
  emit('update:modelValue', newVal || [])
  emit('selection-change', newVal || [])
}, { deep: true })

// 监听 v-model 值变化
watch(() => props.modelValue, (newVal) => {
  selectedDeviceIds.value = newVal || []
}, { deep: true })

// 监听设备列表变化
watch(() => props.devices, () => {
  // 当设备列表更新时，清理无效的选择
  if (selectedDeviceIds.value && selectedDeviceIds.value.length > 0 && props.devices) {
    const validDeviceIds = props.devices.map(device => device.id)
    selectedDeviceIds.value = selectedDeviceIds.value.filter(id => validDeviceIds.includes(id))
  }
}, { deep: true })
</script>

<template>
  <div class="device-selector">
    <!-- 设备分组筛选 -->
    <DeviceGroupFilter
      v-model="selectedGroupIds"
      @change="handleGroupChange"
    />

    <!-- 设备网格列表 -->
    <DeviceGrid
      :devices="filteredDevices"
      :selected-ids="selectedDeviceIds"
      :loading="loading"
      @device-toggle="toggleDevice"
      @select-all="selectAllDevices"
      @clear-selection="clearSelection"
    />
  </div>
</template>

<style scoped>
.device-selector {
  @apply space-y-4 mt-6 mx-4;
}

/* 响应式边距调整 */
@media (min-width: 768px) {
  .device-selector {
    @apply mx-6;
  }
}

@media (min-width: 1024px) {
  .device-selector {
    @apply mx-8;
  }
}
</style> 