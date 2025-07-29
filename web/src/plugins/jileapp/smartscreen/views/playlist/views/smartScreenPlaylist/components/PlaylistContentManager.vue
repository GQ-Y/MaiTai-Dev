<!--
 - 播放列表内容管理组件（抽屉版本）
 - 支持拖拽排序和内容管理
-->
<script setup lang="tsx">
import { ElMessage, ElMessageBox, ElTag, ElImage, ElButton, ElEmpty } from 'element-plus'
import { Plus, Delete, ArrowUp, ArrowDown } from '@element-plus/icons-vue'
import { page, create, deleteByIds, updateSortOrder } from '../../../api/smartScreenPlaylistContent'
import { page as getContentList } from '../../../../content/api/smartScreenContent'
import { useMessage } from '@/hooks/useMessage.ts'
import { ResultCode } from '@/utils/ResultCode.ts'
import Sortable from 'sortablejs'

interface Props {
  playlistId: number
  playlistName?: string
}

const props = defineProps<Props>()
const emit = defineEmits<{
  contentUpdated: []
}>()

const msg = useMessage()

// 数据状态
const loading = ref(false)
const playlistContentList = ref<any[]>([])
const availableContentList = ref<any[]>([])

// 弹窗状态
const addContentDialog = ref(false)
const selectedContent = ref<number[]>([])

// 内容类型映射
const contentTypeMap = {
  1: { label: '网页', icon: '🌐', color: 'primary' },
  2: { label: '图片', icon: '🖼️', color: 'warning' },
  3: { label: '视频', icon: '🎬', color: 'success' },
  4: { label: '直播', icon: '🎥', color: 'info' },
  5: { label: '音频', icon: '🎤', color: 'danger' }
}

// 加载播放列表内容
const loadPlaylistContent = async () => {
  if (!props.playlistId) return
  
  loading.value = true
  try {
    const res = await page({ playlist_id: props.playlistId, pageSize: 999 })
    if (res.code === ResultCode.SUCCESS) {
      playlistContentList.value = res.data?.list || []
      emit('contentUpdated')
    }
  } catch (error) {
    msg.error('加载播放列表内容失败')
  } finally {
    loading.value = false
  }
}

// 加载可用内容列表
const loadAvailableContent = async () => {
  try {
    const res = await getContentList({ status: 1, pageSize: 999 })
    if (res.code === ResultCode.SUCCESS) {
      availableContentList.value = res.data?.list || []
    }
  } catch (error) {
    msg.error('加载内容列表失败')
  }
}

// 添加内容到播放列表
const handleAddContent = async () => {
  if (selectedContent.value.length === 0) {
    msg.warning('请选择要添加的内容')
    return
  }

  try {
    const promises = selectedContent.value.map((contentId, index) => {
      const maxSortOrder = Math.max(...playlistContentList.value.map(item => item.sort_order), 0)
      return create({
        playlist_id: props.playlistId,
        content_id: contentId,
        sort_order: maxSortOrder + index + 1
      })
    })

    await Promise.all(promises)
    msg.success('内容添加成功')
    addContentDialog.value = false
    selectedContent.value = []
    loadPlaylistContent()
  } catch (error) {
    msg.error('添加内容失败')
  }
}

// 删除内容
const handleDelete = async (item: any) => {
  try {
    await ElMessageBox.confirm('确认删除该内容吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning',
    })

    await deleteByIds([item.id])
    msg.success('删除成功')
    loadPlaylistContent()
  } catch (error) {
    if (error !== 'cancel') {
      msg.error('删除失败')
    }
  }
}

// 移动内容位置
const moveItem = async (index: number, direction: 'up' | 'down') => {
  const list = [...playlistContentList.value]
  const targetIndex = direction === 'up' ? index - 1 : index + 1
  
  if (targetIndex < 0 || targetIndex >= list.length) return

  // 交换位置
  [list[index], list[targetIndex]] = [list[targetIndex], list[index]]
  
  // 更新排序值
  const updateData = list.map((item, idx) => ({
    id: item.id,
    sort_order: idx + 1
  }))

  try {
    await updateSortOrder(updateData)
    playlistContentList.value = list.map((item, idx) => ({
      ...item,
      sort_order: idx + 1
    }))
    msg.success('调整成功')
  } catch (error) {
    msg.error('调整失败')
  }
}

// 初始化拖拽排序
const initSortable = () => {
  nextTick(() => {
    const container = document.querySelector('.playlist-content-list')
    if (container) {
      Sortable.create(container, {
        animation: 150,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        dragClass: 'sortable-drag',
        onEnd: async (evt) => {
          const { oldIndex, newIndex } = evt
          if (oldIndex === newIndex) return

          const list = [...playlistContentList.value]
          const [movedItem] = list.splice(oldIndex, 1)
          list.splice(newIndex, 0, movedItem)

          // 更新排序值
          const updateData = list.map((item, idx) => ({
            id: item.id,
            sort_order: idx + 1
          }))

          try {
            await updateSortOrder(updateData)
            playlistContentList.value = list.map((item, idx) => ({
              ...item,
              sort_order: idx + 1
            }))
            msg.success('排序更新成功')
          } catch (error) {
            msg.error('排序更新失败')
            loadPlaylistContent() // 重新加载以恢复原始顺序
          }
        }
      })
    }
  })
}

// 过滤已添加的内容
const getFilteredAvailableContent = computed(() => {
  const addedContentIds = new Set(playlistContentList.value.map(item => item.content_id))
  return availableContentList.value.filter(content => !addedContentIds.has(content.id))
})

// 组件初始化
onMounted(() => {
  loadPlaylistContent()
  loadAvailableContent()
  initSortable()
})

// 监听播放列表内容变化，重新初始化拖拽
watch(() => playlistContentList.value.length, () => {
  initSortable()
})

// 监听playlistId变化，重新加载数据
watch(() => props.playlistId, () => {
  if (props.playlistId) {
    loadPlaylistContent()
  }
})
</script>

<template>
  <div class="playlist-content-manager">
    <!-- 操作栏 -->
    <div class="action-bar">
      <div class="info">
        <span class="playlist-name">{{ playlistName || '播放列表' }}</span>
        <span class="content-count">共 {{ playlistContentList.length }} 个内容</span>
      </div>
      <el-button 
        type="primary" 
        :icon="Plus" 
        @click="addContentDialog = true"
      >
        添加内容
      </el-button>
    </div>

    <!-- 内容列表 -->
    <div class="content-container" v-loading="loading">
      <div v-if="playlistContentList.length === 0" class="empty-state">
        <el-empty description="暂无内容">
          <el-button type="primary" @click="addContentDialog = true">
            添加第一个内容
          </el-button>
        </el-empty>
      </div>

      <div v-else class="playlist-content-list">
        <div 
          v-for="(item, index) in playlistContentList" 
          :key="item.id"
          class="content-item"
        >
          <!-- 拖拽手柄 -->
          <div class="drag-handle">
            <ma-svg-icon name="material-symbols:drag-indicator" />
          </div>

          <!-- 内容信息 -->
          <div class="content-info">
            <div class="content-preview">
              <el-image
                v-if="(item.content?.content_type || item.content_type) === 2 || (item.content?.content_type || item.content_type) === 3"
                :src="item.content?.thumbnail || item.thumbnail || item.content?.content_url || item.content_url"
                fit="cover"
                class="content-thumbnail"
                :preview-src-list="[item.content?.thumbnail || item.thumbnail || item.content?.content_url || item.content_url]"
              />
              <div v-else class="content-icon">
                {{ contentTypeMap[item.content?.content_type || item.content_type]?.icon }}
              </div>
            </div>

            <div class="content-details">
              <div class="content-title">{{ item.content?.title || item.title || '未知内容' }}</div>
              <div class="content-meta">
                <el-tag 
                  :type="contentTypeMap[item.content?.content_type || item.content_type]?.color" 
                  size="small"
                >
                  {{ contentTypeMap[item.content?.content_type || item.content_type]?.label || '未知类型' }}
                </el-tag>
                <span class="duration">
                  {{ (item.content?.duration !== undefined ? item.content.duration : item.duration) === 0 ? '永久播放' : `${item.content?.duration || item.duration || 0}秒` }}
                </span>
              </div>
            </div>
          </div>

          <!-- 排序信息 -->
          <div class="sort-info">
            <span class="sort-number">{{ index + 1 }}</span>
          </div>

          <!-- 操作按钮 -->
          <div class="content-actions">
            <el-button
              v-if="index > 0"
              size="small"
              :icon="ArrowUp"
              @click="moveItem(index, 'up')"
              title="上移"
            />
            <el-button
              v-if="index < playlistContentList.length - 1"
              size="small"
              :icon="ArrowDown"
              @click="moveItem(index, 'down')"
              title="下移"
            />
            <el-button
              size="small"
              type="danger"
              :icon="Delete"
              @click="handleDelete(item)"
              title="删除"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- 添加内容对话框 -->
    <el-dialog
      v-model="addContentDialog"
      title="添加内容到播放列表"
      width="700px"
      :close-on-click-modal="false"
    >
      <div class="available-content-list">
        <div v-if="getFilteredAvailableContent.length === 0" class="empty-state">
          <el-empty description="没有可添加的内容" />
        </div>
        
        <el-checkbox-group v-else v-model="selectedContent">
          <div 
            v-for="content in getFilteredAvailableContent" 
            :key="content.id"
            class="available-content-item"
          >
            <el-checkbox :value="content.id" class="content-checkbox">
              <div class="content-card">
                <div class="content-preview">
                  <el-image
                    v-if="content.content_type === 2 || content.content_type === 3"
                    :src="content.thumbnail || content.content_url"
                    fit="cover"
                    class="content-thumbnail"
                  />
                  <div v-else class="content-icon">
                    {{ contentTypeMap[content.content_type]?.icon }}
                  </div>
                </div>
                
                <div class="content-details">
                  <div class="content-title">{{ content.title }}</div>
                  <div class="content-meta">
                    <el-tag 
                      :type="contentTypeMap[content.content_type]?.color" 
                      size="small"
                    >
                      {{ contentTypeMap[content.content_type]?.label }}
                    </el-tag>
                    <span class="duration">
                      {{ content.duration === 0 ? '永久播放' : `${content.duration}秒` }}
                    </span>
                  </div>
                </div>
              </div>
            </el-checkbox>
          </div>
        </el-checkbox-group>
      </div>

      <template #footer>
        <div class="dialog-footer">
          <el-button @click="addContentDialog = false">取消</el-button>
          <el-button 
            type="primary" 
            @click="handleAddContent"
            :disabled="selectedContent.length === 0"
          >
            添加 ({{ selectedContent.length }})
          </el-button>
        </div>
      </template>
    </el-dialog>
  </div>
</template>

<style scoped lang="scss">
.playlist-content-manager {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.action-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 0;
  border-bottom: 1px solid #e4e7ed;
  margin-bottom: 16px;

  .info {
    display: flex;
    align-items: center;
    gap: 16px;

    .playlist-name {
      font-size: 16px;
      font-weight: 600;
      color: #303133;
    }

    .content-count {
      font-size: 14px;
      color: #909399;
    }
  }
}

.content-container {
  flex: 1;
  overflow-y: auto;
}

.playlist-content-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.content-item {
  display: flex;
  align-items: center;
  padding: 16px;
  background: #fff;
  border: 1px solid #e4e7ed;
  border-radius: 8px;
  transition: all 0.3s ease;
  cursor: move;

  &:hover {
    border-color: #409eff;
    box-shadow: 0 2px 12px rgba(64, 158, 255, 0.1);
  }

  .drag-handle {
    margin-right: 12px;
    color: #909399;
    cursor: grab;

    &:active {
      cursor: grabbing;
    }
  }

  .content-info {
    display: flex;
    align-items: center;
    flex: 1;
    gap: 12px;

    .content-preview {
      width: 50px;
      height: 50px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 6px;
      overflow: hidden;
      background: #f5f7fa;

      .content-thumbnail {
        width: 100%;
        height: 100%;
      }

      .content-icon {
        font-size: 20px;
      }
    }

    .content-details {
      flex: 1;

      .content-title {
        font-size: 14px;
        font-weight: 500;
        color: #303133;
        margin-bottom: 6px;
      }

      .content-meta {
        display: flex;
        align-items: center;
        gap: 12px;

        .duration {
          font-size: 12px;
          color: #909399;
        }
      }
    }
  }

  .sort-info {
    margin: 0 12px;

    .sort-number {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 20px;
      height: 20px;
      background: #409eff;
      color: white;
      border-radius: 50%;
      font-size: 11px;
      font-weight: 600;
    }
  }

  .content-actions {
    display: flex;
    gap: 6px;
  }
}

// 对话框样式
:deep(.add-content-dialog) {
  .el-dialog__header {
    padding: 20px 24px 16px;
    border-bottom: 1px solid #e4e7ed;
  }
  
  .el-dialog__body {
    padding: 0;
  }
  
  .el-dialog__footer {
    padding: 16px 24px;
    border-top: 1px solid #e4e7ed;
  }
}

.dialog-header {
  display: flex;
  justify-content: space-between;
  align-items: center;

  .header-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 16px;
    font-weight: 600;
    color: #303133;

    .header-icon {
      color: #409eff;
    }
  }

  .header-stats {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 14px;

    .total-count {
      color: #909399;
    }

    .selected-count {
      color: #409eff;
      font-weight: 500;
    }
  }
}

.dialog-content {
  padding: 20px 24px;
}

.filter-bar {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
  padding: 16px;
  background: #f8f9fa;
  border-radius: 8px;

  .search-section {
    flex: 1;
    
    .search-input {
      width: 100%;
    }
  }

  .filter-section {
    .type-filter {
      width: 140px;
    }

    .type-option {
      display: flex;
      align-items: center;
      gap: 6px;
    }
  }

  .action-section {
    display: flex;
    gap: 8px;
  }
}

.content-list-container {
  min-height: 300px;
  max-height: 500px;
}

.content-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 16px;
  max-height: 500px;
  overflow-y: auto;

  .content-grid-item {
    .content-checkbox {
      width: 100%;

      :deep(.el-checkbox__label) {
        width: 100%;
        margin-left: 0;
      }

      :deep(.el-checkbox__input) {
        position: absolute;
        top: 12px;
        right: 12px;
        z-index: 2;
      }
    }

    .content-card {
      position: relative;
      background: #fff;
      border: 2px solid #e4e7ed;
      border-radius: 12px;
      overflow: hidden;
      transition: all 0.3s ease;
      cursor: pointer;

      &:hover {
        border-color: #409eff;
        box-shadow: 0 4px 20px rgba(64, 158, 255, 0.15);
        transform: translateY(-2px);
      }

      &.selected {
        border-color: #409eff;
        background: #f0f8ff;
        box-shadow: 0 4px 20px rgba(64, 158, 255, 0.2);
      }

      .content-preview {
        position: relative;
        width: 100%;
        height: 120px;
        background: #f5f7fa;
        display: flex;
        align-items: center;
        justify-content: center;

        .content-thumbnail {
          width: 100%;
          height: 100%;
          object-fit: cover;
        }

        .image-error {
          color: #c0c4cc;
          font-size: 24px;
        }

        .content-icon {
          display: flex;
          align-items: center;
          justify-content: center;
          width: 100%;
          height: 100%;
          
          .icon-text {
            font-size: 32px;
          }
        }

        .selected-badge {
          position: absolute;
          top: 8px;
          left: 8px;
          width: 24px;
          height: 24px;
          background: #409eff;
          color: white;
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 12px;
          font-weight: 600;
        }
      }

      .content-info {
        padding: 16px;

        .content-title {
          font-size: 14px;
          font-weight: 600;
          color: #303133;
          margin-bottom: 8px;
          line-height: 1.4;
          display: -webkit-box;
          -webkit-line-clamp: 2;
          -webkit-box-orient: vertical;
          overflow: hidden;
        }

        .content-meta {
          display: flex;
          flex-direction: column;
          gap: 8px;

          .type-tag {
            align-self: flex-start;
            
            .tag-icon {
              margin-right: 4px;
            }
          }

          .duration-info {
            display: flex;
            align-items: center;
            gap: 4px;
            color: #909399;
            font-size: 12px;

            .duration-icon {
              font-size: 14px;
            }
          }
        }
      }
    }
  }
}

.dialog-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;

  .footer-info {
    .selection-summary {
      color: #409eff;
      font-size: 14px;
      font-weight: 500;
    }
  }

  .footer-actions {
    display: flex;
    gap: 12px;
  }
}

.available-content-list {
  .available-content-item {
    margin-bottom: 12px;

    .content-checkbox {
      margin: 20px 0;
      width: 100%;

      :deep(.el-checkbox__label) {
        width: 100%;
      }
    }

    .content-card {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px;
      border: 1px solid #e4e7ed;
      border-radius: 6px;
      transition: all 0.3s ease;

      &:hover {
        border-color: #409eff;
        background: #f0f9ff;
      }

      .content-preview {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        overflow: hidden;
        background: #f5f7fa;

        .content-thumbnail {
          width: 100%;
          height: 100%;
        }

        .content-icon {
          font-size: 16px;
        }
      }

      .content-details {
        flex: 1;

        .content-title {
          font-size: 14px;
          font-weight: 500;
          color: #303133;
          margin-bottom: 6px;
        }

        .content-meta {
          display: flex;
          align-items: center;
          gap: 8px;

          .duration {
            font-size: 12px;
            color: #909399;
          }
        }
      }
    }
  }
}

.empty-state {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 200px;
}

// 拖拽样式
.sortable-ghost {
  opacity: 0.5;
}

.sortable-chosen {
  background: #f0f9ff;
}

.sortable-drag {
  background: #fff;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}
</style>
