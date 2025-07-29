<script setup lang="ts">
import getFormItems from './data/getFormItems'
import type { MaFormExpose } from '@mineadmin/form'
import useForm from '@/hooks/useForm.ts'
import { create, save, getGroupDevices, getAvailableDevices, addDevicesToGroup, removeDevicesFromGroup, batchSetGroupDevicesContent, batchSetGroupDevicesPlaylist } from '../../api/smartScreenDeviceGroup'
import { ResultCode } from '@/utils/ResultCode.ts'
import { useMessage } from '@/hooks/useMessage.ts'
import MaRemoteSelect from '@/components/ma-remote-select/index.vue'

const { formType = 'add', data = null } = defineProps<{ formType: 'add' | 'edit' | 'manageDevices' | 'manageContent', data?: any | null }>()

const t = useTrans().globalTrans
const msg = useMessage()
const formRef = ref<MaFormExpose>()
const formData = ref<any>({})

// 设备管理相关状态
const currentDevices = ref<any[]>([])
const availableDevices = ref<any[]>([])
const selectedCurrentDevices = ref<number[]>([])
const selectedAvailableDevices = ref<number[]>([])
const currentDevicesLoading = ref(false)
const availableDevicesLoading = ref(false)
const activeTab = ref('current')

// 内容管理相关状态
const contentManagementData = ref({
  current_content_id: null,
  playlist_ids: []
})
const contentManagementLoading = ref(false)

// 根据模式初始化
if (formType === 'manageDevices') {
  // 设备管理模式：直接设置数据并加载设备列表
  if (data) {
    // 只复制需要的字段
    const allowedFields = ['id', 'name', 'description', 'color', 'sort_order', 'status']
    allowedFields.forEach((key: string) => {
      if (data[key] !== undefined) {
        formData.value[key] = data[key]
      }
    })
  }
  
  if (data?.id) {
    loadCurrentDevices()
    loadAvailableDevices()
  }
} else if (formType === 'manageContent') {
  // 内容管理模式：初始化内容管理数据
  if (data) {
    // 只复制需要的字段
    const allowedFields = ['id', 'name', 'description', 'color', 'sort_order', 'status']
    allowedFields.forEach((key: string) => {
      if (data[key] !== undefined) {
        formData.value[key] = data[key]
      }
    })
  }
  
  if (data?.id) {
    loadCurrentDevices()
  }
} else {
  // 表单模式：初始化表单组件
  useForm('formRef').then((form: MaFormExpose) => {
    if (formType === 'edit' && data) {
      // 只复制表单需要的字段，避免传递数组数据导致后端错误
      const allowedFields = ['id', 'name', 'description', 'color', 'sort_order', 'status']
      allowedFields.forEach((key: string) => {
        if (data[key] !== undefined) {
          formData.value[key] = data[key]
        }
      })
    }
    
    form.setItems(getFormItems(t))
    form.setOptions({ labelWidth: '120px' })
  })
}

// 加载当前分组设备
async function loadCurrentDevices() {
  if (!data?.id) return
  
  currentDevicesLoading.value = true
  try {
    const res = await getGroupDevices(data.id)
    
    if (res.code === ResultCode.SUCCESS) {
      currentDevices.value = res.data || []
    }
  } catch (error: any) {
    console.error('加载当前设备失败:', error)
  } finally {
    currentDevicesLoading.value = false
  }
}

// 加载可添加设备
async function loadAvailableDevices() {
  if (!data?.id) return
  
  availableDevicesLoading.value = true
  try {
    const res = await getAvailableDevices(data.id)
    
    if (res.code === ResultCode.SUCCESS) {
      availableDevices.value = res.data || []
    }
  } catch (error: any) {
    console.error('加载可添加设备失败:', error)
  } finally {
    availableDevicesLoading.value = false
  }
}

// 添加设备到分组
async function handleAddDevices() {
  if (selectedAvailableDevices.value.length === 0) {
    msg.warning(t('smartscreen.group.selectDevicesToAdd'))
    return
  }
  
  try {
    const res = await addDevicesToGroup(data.id, selectedAvailableDevices.value)
    
    if (res.code === ResultCode.SUCCESS) {
      msg.success(t('smartscreen.group.addDevicesSuccess'))
      selectedAvailableDevices.value = []
      // 重新加载数据
      loadCurrentDevices()
      loadAvailableDevices()
    } else {
      msg.error(res.message || '添加设备失败')
    }
  } catch (error: any) {
    msg.error(error.message || '添加设备失败')
  }
}

// 从分组移除设备
async function handleRemoveDevices() {
  if (selectedCurrentDevices.value.length === 0) {
    msg.warning(t('smartscreen.group.selectDevicesToRemove'))
    return
  }
  
  try {
    const res = await removeDevicesFromGroup(data.id, selectedCurrentDevices.value)
    if (res.code === ResultCode.SUCCESS) {
      msg.success(t('smartscreen.group.removeDevicesSuccess'))
      selectedCurrentDevices.value = []
      // 重新加载数据
      loadCurrentDevices()
      loadAvailableDevices()
    }
  } catch (error: any) {
    msg.error(error.message || '移除设备失败')
  }
}

// 批量设置分组内设备的显示内容
async function handleBatchSetContent() {
  if (!data?.id) return
  
  contentManagementLoading.value = true
  try {
    const res = await batchSetGroupDevicesContent(data.id, contentManagementData.value.current_content_id)
    
    if (res.code === ResultCode.SUCCESS) {
      const result = res.data
      let message = `批量设置完成：成功 ${result.success_count} 个，失败 ${result.fail_count} 个`
      
      // 统计WebSocket推送状态
      if (result.results && Array.isArray(result.results)) {
        const wsStats: Record<string, number> = {
          pushed: 0,
          offline: 0,
          service_unavailable: 0,
          failed: 0,
          error: 0
        }
        
        result.results.forEach((item: any) => {
          if (item.success && item.websocket_status) {
            wsStats[item.websocket_status] = (wsStats[item.websocket_status] || 0) + 1
          }
        })
        
        const wsMessages: string[] = []
        if (wsStats.pushed > 0) wsMessages.push(`${wsStats.pushed}个设备已推送`)
        if (wsStats.offline > 0) wsMessages.push(`${wsStats.offline}个设备离线`)
        if (wsStats.service_unavailable > 0) wsMessages.push(`${wsStats.service_unavailable}个设备WebSocket服务不可用`)
        if (wsStats.failed > 0) wsMessages.push(`${wsStats.failed}个设备推送失败`)
        
        if (wsMessages.length > 0) {
          message += `；推送状态：${wsMessages.join('，')}`
        }
      }
      
      msg.success(message)
      // 刷新设备列表
      loadCurrentDevices()
    } else {
      msg.error(res.message || '批量设置内容失败')
    }
  } catch (error: any) {
    msg.error(error.message || '批量设置内容失败')
  } finally {
    contentManagementLoading.value = false
  }
}

// 批量设置分组内设备的播放列表
async function handleBatchSetPlaylist() {
  if (!data?.id) return
  
  contentManagementLoading.value = true
  try {
    const res = await batchSetGroupDevicesPlaylist(data.id, contentManagementData.value.playlist_ids || [])
    
    if (res.code === ResultCode.SUCCESS) {
      const result = res.data
      let message = `批量设置完成：成功 ${result.success_count} 个，失败 ${result.fail_count} 个`
      
      // 统计WebSocket推送状态
      if (result.results && Array.isArray(result.results)) {
        const wsStats: Record<string, number> = {
          pushed: 0,
          offline: 0,
          service_unavailable: 0,
          failed: 0,
          error: 0
        }
        
        result.results.forEach((item: any) => {
          if (item.success && item.websocket_status) {
            wsStats[item.websocket_status] = (wsStats[item.websocket_status] || 0) + 1
          }
        })
        
        const wsMessages: string[] = []
        if (wsStats.pushed > 0) wsMessages.push(`${wsStats.pushed}个设备已推送`)
        if (wsStats.offline > 0) wsMessages.push(`${wsStats.offline}个设备离线`)
        if (wsStats.service_unavailable > 0) wsMessages.push(`${wsStats.service_unavailable}个设备WebSocket服务不可用`)
        if (wsStats.failed > 0) wsMessages.push(`${wsStats.failed}个设备推送失败`)
        
        if (wsMessages.length > 0) {
          message += `；推送状态：${wsMessages.join('，')}`
        }
      }
      
      msg.success(message)
      // 刷新设备列表
      loadCurrentDevices()
    } else {
      msg.error(res.message || '批量设置播放列表失败')
    }
  } catch (error: any) {
    msg.error(error.message || '批量设置播放列表失败')
  } finally {
    contentManagementLoading.value = false
  }
}

// 获取设备状态文本
function getDeviceStatusText(status: number) {
  return status === 1 ? t('smartscreen.device.active') : t('smartscreen.device.inactive')
}

// 获取设备状态标签类型
function getDeviceStatusTagType(status: number) {
  return status === 1 ? 'success' : 'danger'
}

// 获取在线状态文本
function getOnlineStatusText(isOnline: boolean) {
  return isOnline ? t('smartscreen.device.online') : t('smartscreen.device.offline')
}

// 获取在线状态标签类型
function getOnlineStatusTagType(isOnline: boolean) {
  return isOnline ? 'success' : 'info'
}

function add(): Promise<any> {
  return new Promise((resolve, reject) => {
    create(formData.value).then((res: any) => {
      res.code === ResultCode.SUCCESS ? resolve(res) : reject(res)
    }).catch(reject)
  })
}

function edit(): Promise<any> {
  return new Promise((resolve, reject) => {
    save(formData.value.id as number, formData.value).then((res: any) => {
      res.code === ResultCode.SUCCESS ? resolve(res) : reject(res)
    }).catch(reject)
  })
}

function getFormData() {
  return formData.value
}

defineExpose({ add, edit, getFormData, maForm: formRef })
</script>

<template>
  <div>
    <!-- 内容管理模式 -->
    <div v-if="formType === 'manageContent'">
      <!-- 分组信息 -->
      <div class="group-info-section">
        <el-row :gutter="24">
          <el-col :span="6">
            <div class="info-item">
              <div class="info-label">分组名称</div>
              <div class="info-value">{{ data?.name || '--' }}</div>
            </div>
          </el-col>
          <el-col :span="6">
            <div class="info-item">
              <div class="info-label">设备数量</div>
              <div class="info-value">
                <el-tag type="info" size="small">{{ currentDevices.length }} 台</el-tag>
              </div>
            </div>
          </el-col>
          <el-col :span="6">
            <div class="info-item">
              <div class="info-label">分组状态</div>
              <div class="info-value">
                <el-tag :type="data?.status === 1 ? 'success' : 'danger'" size="small">
                  {{ data?.status === 1 ? '已启用' : '已禁用' }}
                </el-tag>
              </div>
            </div>
          </el-col>
          <el-col :span="6">
            <div class="info-item">
              <div class="info-label">分组颜色</div>
              <div class="info-value">
                <div class="color-display">
                  <div 
                    class="color-block" 
                    :style="{ backgroundColor: data?.color || '#1890ff' }"
                  />
                  <span>{{ data?.color || '#1890ff' }}</span>
                </div>
              </div>
            </div>
          </el-col>
        </el-row>
      </div>

      <el-divider>批量内容管理</el-divider>

      <!-- 批量设置内容 -->
      <div class="content-management-section">
        <el-row :gutter="24">
          <el-col :span="12">
            <div class="management-card">
              <div class="card-header">
                <el-icon class="card-icon"><Document /></el-icon>
                <span class="card-title">批量设置显示内容</span>
              </div>
              <div class="card-content">
                <el-form-item label="选择内容">
                  <MaRemoteSelect
                    v-model="contentManagementData.current_content_id"
                    placeholder="请选择要在所有设备上显示的内容..."
                    :multiple="false"
                    clearable
                    filterable
                    url="/admin/plugin/smart-screen/content/list"
                                         :axios-config="{
                       autoRequest: true,
                       method: 'get',
                       params: { pageSize: 9999 },
                     }"
                    :data-handle="(response: any) => {
                      return (response.data?.list || [])
                        .filter((item: any) => item.status === 1)
                        .map((item: any) => ({
                          label: `${item.content_type === 1 ? '🌐' : item.content_type === 2 ? '🖼️' : '🎬'} ${item.title}`,
                          value: item.id,
                        }))
                    }"
                  />
                </el-form-item>
                <el-button 
                  type="primary" 
                  :loading="contentManagementLoading"
                  @click="handleBatchSetContent"
                  style="width: 100%;"
                >
                  批量设置内容
                </el-button>
              </div>
            </div>
          </el-col>
          
          <el-col :span="12">
            <div class="management-card">
              <div class="card-header">
                <el-icon class="card-icon"><List /></el-icon>
                <span class="card-title">批量设置播放列表</span>
              </div>
              <div class="card-content">
                <el-form-item label="选择播放列表">
                  <MaRemoteSelect
                    v-model="contentManagementData.playlist_ids"
                    placeholder="请选择要关联的播放列表（可多选）..."
                    :multiple="true"
                    clearable
                    filterable
                    url="/admin/plugin/smart-screen/playlist/list"
                                         :axios-config="{
                       autoRequest: true,
                       method: 'get',
                       params: { pageSize: 9999 },
                     }"
                    :data-handle="(response: any) => {
                      return (response.data?.list || [])
                        .filter((item: any) => item.status === 1)
                        .map((item: any) => ({
                          label: `📋 ${item.name}`,
                          value: item.id,
                        }))
                    }"
                  />
                </el-form-item>
                <el-button 
                  type="primary" 
                  :loading="contentManagementLoading"
                  @click="handleBatchSetPlaylist"
                  style="width: 100%;"
                >
                  批量设置播放列表
                </el-button>
              </div>
            </div>
          </el-col>
        </el-row>
      </div>

      <el-divider>设备列表</el-divider>

      <!-- 设备列表 -->
      <div class="device-list-section" v-loading="currentDevicesLoading">
        <el-empty v-if="currentDevices.length === 0" description="该分组下没有设备" />
        <el-table
          v-else
          :data="currentDevices"
          :style="{ width: '100%' }"
        >
          <el-table-column label="设备名称" prop="device_name" min-width="150" />
          <el-table-column label="MAC地址" prop="mac_address" width="150" />
          <el-table-column label="设备状态" width="100">
            <template #default="{ row }">
              <el-tag :type="getDeviceStatusTagType(row.status)">
                {{ getDeviceStatusText(row.status) }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column label="在线状态" width="100">
            <template #default="{ row }">
              <el-tag :type="getOnlineStatusTagType(row.is_online)">
                {{ getOnlineStatusText(row.is_online) }}
              </el-tag>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </div>

    <!-- 设备管理模式 -->
    <div v-else-if="formType === 'manageDevices'">
      <el-tabs v-model="activeTab" class="device-management-tabs">
        <!-- 当前设备 -->
        <el-tab-pane :label="t('smartscreen.group.currentDevices')" name="current">
          <div class="device-section">
            <div class="section-header">
              <div class="section-info">
                <span class="device-count">{{ currentDevices.length }} 台设备</span>
              </div>
              <div class="section-actions">
                <el-button
                  type="danger"
                  :disabled="selectedCurrentDevices.length === 0"
                  @click="handleRemoveDevices"
                >
                  {{ t('smartscreen.group.removeDevices') }}
                </el-button>
              </div>
            </div>
            
            <div class="device-list" v-loading="currentDevicesLoading">
              <el-empty v-if="currentDevices.length === 0" :description="t('smartscreen.group.noDevicesInGroup')" />
              <el-table
                v-else
                :data="currentDevices"
                @selection-change="(selection: any[]) => selectedCurrentDevices = selection.map((item: any) => item.id)"
                :style="{ width: '100%' }"
              >
                <el-table-column type="selection" width="55" />
                <el-table-column :label="t('smartscreen.group.deviceName')" prop="device_name" min-width="150" />
                <el-table-column :label="t('smartscreen.group.macAddress')" prop="mac_address" width="150" />
                <el-table-column :label="t('smartscreen.device.status')" width="100">
                  <template #default="{ row }">
                    <el-tag :type="getDeviceStatusTagType(row.status)">
                      {{ getDeviceStatusText(row.status) }}
                    </el-tag>
                  </template>
                </el-table-column>
                <el-table-column :label="t('smartscreen.group.onlineStatus')" width="100">
                  <template #default="{ row }">
                    <el-tag :type="getOnlineStatusTagType(row.is_online)">
                      {{ getOnlineStatusText(row.is_online) }}
                    </el-tag>
                  </template>
                </el-table-column>
                <!-- <el-table-column :label="t('smartscreen.group.addedAt')" prop="created_at" width="180" /> -->
              </el-table>
            </div>
          </div>
        </el-tab-pane>

        <!-- 可添加设备 -->
        <el-tab-pane :label="t('smartscreen.group.availableDevices')" name="available">
          <div class="device-section">
            <div class="section-header">
              <div class="section-info">
                <span class="device-count">{{ availableDevices.length }} 台可添加设备</span>
              </div>
              <div class="section-actions">
                <el-button
                  type="primary"
                  :disabled="selectedAvailableDevices.length === 0"
                  @click="handleAddDevices"
                >
                  {{ t('smartscreen.group.addDevices') }}
                </el-button>
              </div>
            </div>
            
            <div class="device-list" v-loading="availableDevicesLoading">
              <el-empty v-if="availableDevices.length === 0" :description="t('smartscreen.group.noAvailableDevices')" />
              <el-table
                v-else
                :data="availableDevices"
                ref="availableDevicesTable"
                @selection-change="(selection: any[]) => selectedAvailableDevices = selection.map((item: any) => item.id)"
                :style="{ width: '100%' }"
              >
                <el-table-column type="selection" width="55" />
                <el-table-column :label="t('smartscreen.group.deviceName')" prop="device_name" min-width="150" />
                <el-table-column :label="t('smartscreen.group.macAddress')" prop="mac_address" width="150" />
                <el-table-column :label="t('smartscreen.device.status')" width="100">
                  <template #default="{ row }">
                    <el-tag :type="getDeviceStatusTagType(row.status)">
                      {{ getDeviceStatusText(row.status) }}
                    </el-tag>
                  </template>
                </el-table-column>
                <el-table-column :label="t('smartscreen.group.onlineStatus')" width="100">
                  <template #default="{ row }">
                    <el-tag :type="getOnlineStatusTagType(row.is_online)">
                      {{ getOnlineStatusText(row.is_online) }}
                    </el-tag>
                  </template>
                </el-table-column>
                <!-- <el-table-column :label="t('crud.created_at')" prop="created_at" width="180" /> -->
              </el-table>
            </div>
          </div>
        </el-tab-pane>
      </el-tabs>
    </div>
    
    <!-- 表单模式 -->
    <div v-else>
      <ma-form ref="formRef" v-model="formData" />
    </div>
  </div>
</template>

<style scoped>
.group-info-section {
  padding: 16px;
  background-color: #f8f9fb;
  border-radius: 8px;
  margin-bottom: 20px;
}

.info-item {
  text-align: center;
}

.info-label {
  font-size: 12px;
  color: #909399;
  margin-bottom: 4px;
}

.info-value {
  font-size: 14px;
  color: #303133;
  font-weight: 600;
}

.color-display {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}

.color-block {
  width: 16px;
  height: 16px;
  border-radius: 3px;
  border: 1px solid #dcdfe6;
}

.content-management-section {
  margin-bottom: 20px;
}

.management-card {
  border: 1px solid #ebeef5;
  border-radius: 8px;
  overflow: hidden;
}

.card-header {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  background-color: #f5f7fa;
  border-bottom: 1px solid #ebeef5;
}

.card-icon {
  color: #606266;
  margin-right: 8px;
}

.card-title {
  font-size: 14px;
  font-weight: 600;
  color: #303133;
}

.card-content {
  padding: 16px;
}

.device-list-section {
  min-height: 200px;
}

.device-management-tabs {
  height: 100%;
}

.device-section {
  padding: 16px 0;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
  padding: 0 4px;
}

.section-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.device-count {
  font-size: 14px;
  color: #606266;
  font-weight: 500;
}

.section-actions {
  display: flex;
  gap: 8px;
}

.device-list {
  min-height: 300px;
}

:deep(.el-tabs__content) {
  padding: 0;
}

:deep(.el-table) {
  border-radius: 8px;
  overflow: hidden;
}

:deep(.el-table th) {
  background-color: #fafafa;
  font-weight: 600;
}

:deep(.el-empty) {
  padding: 60px 0;
}
</style> 