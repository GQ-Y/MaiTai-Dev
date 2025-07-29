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
  name: 'SmartScreenDeviceStats',
})

const props = defineProps<{
  deviceList: Device[]
}>()

// 设备统计
const deviceStats = computed(() => {
  const online = props.deviceList.filter(
    d => d.is_online && d.websocket_status === 'connected',
  ).length
  const offline = props.deviceList.filter(
    d => !d.is_online || d.websocket_status !== 'connected',
  ).length
  const disabled = props.deviceList.filter(d => !d.status).length
  const total = props.deviceList.length

  return { total, online, offline, disabled }
})
</script>

<template>
  <div class="mine-card">
    <div class="text-base mb-6">
      设备状态统计
    </div>
    <div class="grid grid-cols-2 gap-y-3 md:grid-cols-4">
      <div class="content flex gap-3">
        <div
          class="h-[50px] w-[50px] flex-center rounded-md p-1"
          style="background: #E8F3FF"
        >
          <ma-svg-icon
            name="heroicons:computer-desktop"
            style="color: #165DFF"
            :size="24"
          />
        </div>
        <el-statistic :value="deviceStats.total">
          <template #title>
            <div class="text-base">
              设备总数
            </div>
          </template>
        </el-statistic>
      </div>
      <div class="content flex gap-3">
        <div
          class="h-[50px] w-[50px] flex-center rounded-md p-1"
          style="background: #E8FFFB"
        >
          <ma-svg-icon
            name="heroicons:signal"
            style="color: #33D1C9"
            :size="24"
          />
        </div>
        <el-statistic :value="deviceStats.online">
          <template #title>
            <div class="text-base">
              在线设备
            </div>
          </template>
        </el-statistic>
      </div>
      <div class="content flex gap-3">
        <div
          class="h-[50px] w-[50px] flex-center rounded-md p-1"
          style="background: #FFE4BA"
        >
          <ma-svg-icon
            name="heroicons:signal-slash"
            style="color: #F77234"
            :size="24"
          />
        </div>
        <el-statistic :value="deviceStats.offline">
          <template #title>
            <div class="text-base">
              离线设备
            </div>
          </template>
        </el-statistic>
      </div>
      <div class="content flex gap-3">
        <div
          class="h-[50px] w-[50px] flex-center rounded-md p-1"
          style="background: #F5E8FF"
        >
          <ma-svg-icon
            name="heroicons:no-symbol"
            style="color: #722ED1"
            :size="24"
          />
        </div>
        <el-statistic :value="deviceStats.disabled">
          <template #title>
            <div class="text-base">
              禁用设备
            </div>
          </template>
        </el-statistic>
      </div>
    </div>
  </div>
</template>
