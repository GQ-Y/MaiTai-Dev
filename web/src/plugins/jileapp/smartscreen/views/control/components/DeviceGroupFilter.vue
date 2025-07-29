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
import type { DeviceGroup } from '../types'
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import * as deviceGroupApi from '../../group/api/smartScreenDeviceGroup'

const { t } = useI18n()

defineOptions({
  name: 'DeviceGroupFilter',
})

const props = defineProps<{
  modelValue: number[]
}>()

const emit = defineEmits<{
  'update:modelValue': [value: number[]]
  'change': [groupIds: number[]]
}>()

// 设备分组相关
const deviceGroups = ref<DeviceGroup[]>([])
const loading = ref(false)

// 加载设备分组
async function loadDeviceGroups() {
  try {
    loading.value = true
    const res = await deviceGroupApi.getAllEnabled()
    if (res.code === 200) {
      deviceGroups.value = res.data || []
    }
  } catch (error) {
    console.error('Failed to load device groups:', error)
    deviceGroups.value = []
  } finally {
    loading.value = false
  }
}

// 处理分组选择变化
function handleChange(groupIds: number[]) {
  emit('update:modelValue', groupIds || [])
  emit('change', groupIds || [])
}

// 初始化
onMounted(() => {
  loadDeviceGroups()
})
</script>

<template>
  <div class="device-group-filter">
    <div class="filter-content">
      <el-select
        :model-value="props.modelValue"
        multiple
        collapse-tags
        collapse-tags-tooltip
        clearable
        filterable
        placeholder="选择设备分组进行筛选"
        class="group-selector"
        @update:model-value="handleChange"
        :loading="loading"
        size="default"
      >
        <el-option
          v-for="group in deviceGroups"
          :key="group.id"
          :label="group.name"
          :value="group.id"
        >
          <span class="group-name">{{ group.name }}</span>
        </el-option>
      </el-select>
    </div>
  </div>
</template>

<style scoped>
.device-group-filter {
  @apply mb-4;
}

.filter-content {
  @apply w-full;
}

.group-selector {
  @apply w-full;
}

.group-name {
  @apply font-medium text-gray-900 dark:text-gray-100;
}

:deep(.el-select) {
  @apply w-full;
}

:deep(.el-select .el-input__wrapper) {
  @apply bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 rounded-lg;
}

:deep(.el-select .el-input__wrapper:hover) {
  @apply border-blue-400 dark:border-blue-500;
}

:deep(.el-select .el-input__wrapper.is-focus) {
  @apply border-blue-500 dark:border-blue-400 shadow-sm;
}

:deep(.el-select .el-input__inner) {
  @apply text-gray-900 dark:text-gray-100;
}

:deep(.el-select .el-input__inner::placeholder) {
  @apply text-gray-500 dark:text-gray-400;
}
</style> 