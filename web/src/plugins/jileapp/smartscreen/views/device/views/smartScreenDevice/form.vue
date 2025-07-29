<script setup lang="ts">
import getFormItems from './data/getFormItems'
import type { MaFormExpose } from '@mineadmin/form'
import useForm from '@/hooks/useForm.ts'
import { create, save, setContent, setPlaylist } from '../../api/smartScreenDevice'
import { ResultCode } from '@/utils/ResultCode.ts'
import { CircleCheck, CircleClose, Connection, Document, Monitor } from '@element-plus/icons-vue'

const { formType = 'add', data = null } = defineProps<{ formType: 'add' | 'edit' | 'setContent' | 'setPlaylist', data?: any | null }>()

const t = useTrans().globalTrans
const formRef = ref<MaFormExpose>()
const formData = ref<any>({})

useForm('formRef').then((form: MaFormExpose) => {
  if ((formType === 'edit' || formType === 'setContent' || formType === 'setPlaylist') && data) {
    Object.keys(data).map((key: string) => {
      formData.value[key] = data[key]
    })
    
    // 如果是设置播放列表模式，获取当前设备的播放列表ID
    if (formType === 'setPlaylist' && data.playlists) {
      formData.value.playlist_ids = data.playlists.map((playlist: any) => playlist.id)
    }
  }
  form.setItems(getFormItems(formType, t, data))
  form.setOptions({ labelWidth: '120px' })
})

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

function setDeviceContent(): Promise<any> {
  return new Promise((resolve, reject) => {
    const deviceId = formData.value.id as number
    const contentId = formData.value.current_content_id || null

    // 参数验证
    if (!deviceId) {
      reject(new Error('设备ID不能为空，请确保设备数据正确传递'))
      return
    }

    console.log('设置设备内容参数:', { deviceId, contentId, formData: formData.value })

    setContent(deviceId, contentId).then((res: any) => {
      res.code === ResultCode.SUCCESS ? resolve(res) : reject(res)
    }).catch(reject)
  })
}

function setDevicePlaylist(): Promise<any> {
  return new Promise((resolve, reject) => {
    const deviceId = formData.value.id as number
    const playlistIds = formData.value.playlist_ids || []

    // 参数验证
    if (!deviceId) {
      reject(new Error('设备ID不能为空，请确保设备数据正确传递'))
      return
    }

    console.log('设置设备播放列表参数:', { deviceId, playlistIds, formData: formData.value })

    setPlaylist(deviceId, playlistIds).then((res: any) => {
      res.code === ResultCode.SUCCESS ? resolve(res) : reject(res)
    }).catch(reject)
  })
}

function getFormData() {
  return formData.value
}

defineExpose({ add, edit, setDeviceContent, setDevicePlaylist, getFormData, maForm: formRef })
</script>

<template>
  <div>
        <!-- 设置内容/播放列表模式下显示设备信息 -->
    <div v-if="formType === 'setContent' || formType === 'setPlaylist'" style="margin-bottom: 24px;">
      <!-- 设备基本信息 -->
      <div style="margin-bottom: 20px;">
        <el-row :gutter="24">
          <el-col :span="6">
            <div style="text-align: center;">
              <div style="font-size: 12px; color: #909399; margin-bottom: 4px;">MAC地址</div>
              <div style="font-size: 14px; color: #303133; font-family: 'Courier New', monospace; font-weight: 600;">
                {{ data?.mac_address || '--' }}
              </div>
            </div>
          </el-col>
          <el-col :span="6">
            <div style="text-align: center;">
              <div style="font-size: 12px; color: #909399; margin-bottom: 4px;">设备名称</div>
              <div style="font-size: 14px; color: #303133; font-weight: 600;">
                {{ data?.device_name || '--' }}
              </div>
            </div>
          </el-col>
          <el-col :span="6">
            <div style="text-align: center;">
              <div style="font-size: 12px; color: #909399; margin-bottom: 4px;">激活状态</div>
              <el-tag
                :type="data?.status === 1 ? 'success' : 'danger'"
                size="small"
                effect="plain"
                round
              >
                {{ data?.status === 1 ? '已启用' : '未启用' }}
              </el-tag>
            </div>
          </el-col>
          <el-col :span="6">
            <div style="text-align: center;">
              <div style="font-size: 12px; color: #909399; margin-bottom: 4px;">在线状态</div>
              <el-tag
                :type="data?.is_online === 1 ? 'success' : 'info'"
                size="small"
                effect="plain"
                round
              >
                <div style="display: flex; align-items: center; justify-content: center;">
                  <div
                    :style="`
                      width: 6px;
                      height: 6px;
                      border-radius: 50%;
                      background-color: ${data?.is_online === 1 ? '#67c23a' : '#909399'};
                      margin-right: 4px;
                      animation: ${data?.is_online === 1 ? 'pulse 1.5s ease-in-out infinite' : 'none'};
                    `"
                  />
                  {{ data?.is_online === 1 ? '在线' : '离线' }}
                </div>
              </el-tag>
            </div>
          </el-col>
        </el-row>
      </div>

      <!-- 当前显示内容 -->
      <div v-if="data?.current_content_title" style="margin-bottom: 20px;">
        <div style="text-align: center; padding: 16px; background-color: #f8f9fb; border-radius: 8px; border-left: 4px solid #667eea;">
          <div style="font-size: 12px; color: #909399; margin-bottom: 6px;">当前显示内容</div>
          <div style="font-size: 15px; color: #303133; font-weight: 600; margin-bottom: 6px;">
            {{ data?.current_content_title }}
          </div>
          <el-tag
            size="small"
            type="info"
            effect="plain"
            round
          >
            {{
              data?.current_content_type === 1 ? '🌐 网页'
                : data?.current_content_type === 2 ? '🖼️ 图片' : '🎬 视频'
            }}
          </el-tag>
        </div>
      </div>

      <!-- 分隔线 -->
      <el-divider style="margin: 20px 0;">
        <span style="font-size: 12px; color: #909399;">
          {{ formType === 'setContent' ? '选择新内容' : '选择播放列表' }}
        </span>
      </el-divider>
    </div>

    <!-- 表单区域 -->
    <ma-form ref="formRef" v-model="formData" />
  </div>
</template>

<style scoped>
@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.5; }
  100% { opacity: 1; }
}
</style>
