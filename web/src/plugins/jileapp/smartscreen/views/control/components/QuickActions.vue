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
defineOptions({
  name: 'SmartScreenQuickActions',
})

const props = defineProps<{
  loading: boolean
}>()

const emit = defineEmits<{
  quickAction: [action: string]
}>()

function handleQuickAction(action: string) {
  emit('quickAction', action)
}
</script>

<template>
  <div class="mine-card">
    <div class="flex items-center gap-2 text-base mb-5">
      <ma-svg-icon name="heroicons:bolt" style="color: #33D1C9" />
      <span>快速控制</span>
    </div>

    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
      <el-button
        type="primary"
        :loading="loading"
        @click="handleQuickAction('refresh')"
        class="h-12"
      >
        <ma-svg-icon name="heroicons:arrow-path" class="mr-2" />
        刷新所有设备
      </el-button>
      <el-button
        type="success"
        :loading="loading"
        @click="handleQuickAction('activate')"
        class="h-12"
      >
        <ma-svg-icon name="heroicons:power" class="mr-2" />
        激活所有设备
      </el-button>
      <el-button
        type="warning"
        :loading="loading"
        @click="handleQuickAction('deactivate')"
        class="h-12"
      >
        <ma-svg-icon name="heroicons:pause" class="mr-2" />
        禁用所有设备
      </el-button>
    </div>
  </div>
</template>
