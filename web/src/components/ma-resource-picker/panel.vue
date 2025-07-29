<!--
 - MineAdmin is committed to providing solutions for quickly building web applications
 - Please view the LICENSE file that was distributed with this source code,
 - For the full copyright and license information.
 - Thank you very much for using MineAdmin.
 -
 - @Author X.Mo<root@imoi.cn>
 - @Link   https://github.com/mineadmin
-->
<i18n lang="yaml">
  en:
    searchPlaceholder: Search for icons under this category
    tips: Are you sure you want to delete this data?
    cancelMessage: Deletion operation has been canceled
    errorMessage: An error occurred during the deletion process
    all: All
    image: Image
    video: Video
    audio: Audio
    document: Document
    maxSelect: You can select up to {limit} items.
    select: Select
    deselect: Deselect
    singleSelect: Select only this item
    view: View
    delete: Delete
    cancel: Cancel
    confirm: Confirm
    dragUpload: "Drag files here to upload"
    uploadSuccess: "File {name} uploaded successfully"
    uploadFailed: "File {name} upload failed"
    unsupportedFormat: "File {name} format not supported, supported formats: {formats}"
    selectAll: "Select All"
    clearSelection: "Clear Selection"
    batchDelete: "Delete Selected {count} Files"
    selectFilesToDelete: "Please select files to delete"
    confirmBatchDelete: "Are you sure you want to delete the selected {count} files?"
    batchDeleteSuccess: "Successfully deleted {count} files"
    batchDeleteFailed: "{count} files failed to delete"
  zh_CN:
    searchPlaceholder: 搜索此分类下的资源
    tips: 你确定要删除这条数据吗？
    cancelMessage: 删除操作已取消
    errorMessage: 删除过程中发生了错误
    all: 所有
    image: 图片
    video: 视频
    audio: 音频
    document: 文档
    maxSelect: 最多选择{limit}个
    select: 选中
    deselect: 取消选中
    singleSelect: 独选此项
    view: 查看
    delete: 删除
    cancel: 取消
    confirm: 确认
    dragUpload: "拖拽文件到此处上传"
    uploadSuccess: "文件{name}上传成功"
    uploadFailed: "文件{name}上传失败"
    unsupportedFormat: "文件{name}格式不支持，支持的格式：{formats}"
    selectAll: "全选当前页"
    clearSelection: "清空选择"
    batchDelete: "删除选中的{count}个文件"
    selectFilesToDelete: "请先选择要删除的文件"
    confirmBatchDelete: "确定要删除选中的{count}个文件吗？"
    batchDeleteSuccess: "成功删除{count}个文件"
    batchDeleteFailed: "{count}个文件删除失败"
  
  zh_TW:
    searchPlaceholder: 搜索此分類下的資源
    tips: 你確定要刪除這條資料嗎？
    cancelMessage: 刪除操作已取消
    errorMessage: 刪除過程中發生了錯誤
    all: 所有
    image: 圖片
    video: 視頻
    audio: 音頻
    document: 文件
    maxSelect: 最多選擇{limit}個
    select: 選中
    deselect: 取消選中
    singleSelect: 獨選此項
    view: 查看
    delete: 刪除
    cancel: 取消
    confirm: 確認
    dragUpload: "拖拽文件到此處上傳"
    uploadSuccess: "文件{name}上傳成功"
    uploadFailed: "文件{name}上傳失敗"
    unsupportedFormat: "文件{name}格式不支持，支持的格式：{formats}"
    selectAll: "全選當前頁"
    clearSelection: "清空選擇"
    batchDelete: "刪除選中的{count}個文件"
    selectFilesToDelete: "請先選擇要刪除的文件"
    confirmBatchDelete: "確定要刪除選中的{count}個文件嗎？"
    batchDeleteSuccess: "成功刪除{count}個文件"
    batchDeleteFailed: "{count}個文件刪除失敗"
  </i18n>
  
  <script setup lang="ts">
  import { OverlayScrollbarsComponent } from 'overlayscrollbars-vue'
  import ContextMenu from '@imengyu/vue3-context-menu'
  import type { FileType, Resource, ResourcePanelProps } from './type.ts'
  import { deleteById } from '~/base/api/attachment.ts'
  import { ResultCode } from '@/utils/ResultCode.ts'
  
  import { useImageViewer } from '@/hooks/useImageViewer.ts'
  import { useMessage } from '@/hooks/useMessage.ts'
  import type { TransType } from '@/hooks/auto-imports/useTrans.ts'
  import type { Resources } from '#/global'
  import useParentNode from '@/hooks/useParentNode.ts'
  import { uploadLocal } from '@/utils/uploadLocal.ts'
  
  defineOptions({ name: 'MaResourcePanel' })
  
  const props = withDefaults(defineProps<ResourcePanelProps>(), {
    multiple: false,
    limit: undefined,
    showAction: true,
    pageSize: 30,
    dbClickConfirm: false,
  })
  const emit = defineEmits<{
    (e: 'cancel'): void
    (e: 'confirm', value: any[]): void
  }>()
  const i18n = useTrans() as TransType
  const t = i18n.globalTrans
  
  const msg = useMessage()
  const resourceStore = useResourceStore()
  
  const modelValue = defineModel<string | string[] | undefined>()
  
  const fileTypeSelected = ref(props.defaultFileType ?? '')
  const returnType = 'url'
  
  const fileTypes = ref<FileType[]>([
    { label: () => t('all'), value: '', icon: 'ri:gallery-view-2', suffix: '' },
    { label: () => t('image'), value: 'image', icon: 'ri:image-line', suffix: 'png,jpg,jpeg,gif,bmp' },
    { label: () => t('video'), value: 'video', icon: 'ri:folder-video-line', suffix: 'mp4,avi,wmv,mov,flv,mkv,webm' },
    { label: () => t('audio'), value: 'audio', icon: 'ri:file-music-line', suffix: 'mp3,wav,ogg,wma,aac,flac,ape,wavpack' },
    { label: () => t('document'), value: 'document', icon: 'ri:file-text-line', suffix: 'doc,docx,xls,xlsx,ppt,pptx,pdf' },
  ])
  
  /**
   * 加载状态
   */
  const loading = ref(false)
  
  /**
   * 当前资源列表
   */
  const resources = ref<Resource[]>([])
  
  /**
   * 资源总数
   */
  const total = ref<number>(0)
  
  /**
   * 选中资源的key列表,该数据可用做直接返回
   */
  const selectedKeys = ref<Array<string | number>>([])
  
  const selected = ref<Resource[]>([])
  
  /**
   * 查询参数
   */
  const queryParams = ref<Record<string, any>>({
    page: 1,
    page_size: props.pageSize,
    origin_name: '',
    suffix: [],
  })
  
  /**
   * 拖拽相关状态
   */
  const isDragOver = ref(false)
  const dragCounter = ref(0)
  const uploadingFiles = ref<Set<string>>(new Set())
  const uploadProgress = ref<Map<string, number>>(new Map())
  
  async function getResourceList(params: Resource = {}) {
    loading.value = true
    const { data } = await useHttp().get(
      '/admin/attachment/list',
      { params: Object.assign({ page_size: queryParams.value.page_size, page: queryParams.value.page }, params) },
    )
    total.value = data.total
    resources.value = data.list
    loading.value = false
  }
  
  // 监听v-model变化，更新selectedKeys
  watch(() => modelValue.value, (newValue) => {
    selectedKeys.value = Array.isArray(newValue) ? newValue : newValue ? [newValue] : []
  }, { deep: true })
  
  // 监听selectedKeys变化，更新v-model
  watch(() => selectedKeys.value, (newKeys) => {
    const newValue = props.multiple ? newKeys : newKeys[0]
    // 同样，只有在modelValue真正改变时才更新
    if (modelValue.value && modelValue.value !== newValue) {
      modelValue.value = newValue as string | string[]
    }
  }, { deep: true })
  
  /**
   * 加载占位符数量
   */
  const skeletonNum = computed(() => {
    return loading.value ? queryParams.value.page_size : 30
  })
  
  function onfileTypesChange(value: any) {
    fileTypeSelected.value = value
    queryParams.value.suffix = (fileTypes.value.find(i => i.value === value)?.suffix || []) as string[]
    getResourceList(queryParams.value)
  }
  
  /**
   * 获取封面
   * @param resource
   */
  function getCover(resource: Resource): string | undefined {
    if (resource?.mime_type?.startsWith('image')) {
      return resource.url
    }
    return undefined
  }
  
  /**
   * 判断是否被选中
   * @param resource
   */
  function isSelected(resource: Resource) {
    const key = resource[returnType] as string
    return selectedKeys.value.includes(key)
  }
  
  /**
   * 判断是否能预览
   * @param resource
   */
  function canPreview(resource: Resource) {
    return resource?.mime_type?.startsWith('image')
  }
  
  /**
   * 选中资源
   */
  function select(resource: Resource) {
    const key = resource[returnType] as string
    // 单选
    if (props.multiple) {
      // 判断是否上限
      if (props.limit && selectedKeys.value.length >= props.limit) {
        return msg.warning(t('maxSelect', { limit: props.limit }))
      }
      selectedKeys.value.push(key)
      if (!selected.value.find(i => i[returnType] === key)) {
        selected.value.push(resource)
      }
    }
    else {
      selected.value = [resource]
      selectedKeys.value = [key]
    }
  }
  
  /**
   * 取消选中
   */
  function unSelect(resource: Resource) {
    const key = resource[returnType] as string
    selectedKeys.value = selectedKeys.value.filter(i => i !== key)
    selected.value = selected.value.filter(i => i[returnType] !== key)
  }
  
  /**
   * 清空选中
   */
  function clearSelected() {
    selectedKeys.value = []
    selected.value = []
  }
  
  function cancel() {
    emit('cancel')
  }
  
  function confirm() {
    emit('confirm', selected.value)
  }
  
  /**
   * 处理点击资源事件
   */
  function handleClick(resource: Resource, event?: MouseEvent) {
    if (event && (event.ctrlKey || event.metaKey)) {
      // 按住Ctrl/Cmd键，切换选择状态
      isSelected(resource) ? unSelect(resource) : select(resource)
    } else if (event && event.shiftKey && selected.value.length > 0) {
      // 按住Shift键，范围选择
      const lastSelectedIndex = resources.value.findIndex(r => r.id === selected.value[selected.value.length - 1].id)
      const currentIndex = resources.value.findIndex(r => r.id === resource.id)
      
      if (lastSelectedIndex !== -1 && currentIndex !== -1) {
        const start = Math.min(lastSelectedIndex, currentIndex)
        const end = Math.max(lastSelectedIndex, currentIndex)
        
        clearSelected()
        for (let i = start; i <= end; i++) {
          select(resources.value[i])
        }
      }
    } else {
      // 普通点击
      if (props.multiple) {
        isSelected(resource) ? unSelect(resource) : select(resource)
      } else {
        // 单选模式，替换选择
        clearSelected()
        select(resource)
      }
    }
  }
  
  /**
   * 处理双击资源事件
   */
  function handleDbClick(resource: Resource) {
    // 双击确认选中单个元素
    clearSelected()
    select(resource)
    confirm()
  }
  
  /**
   * 删除选中
   */
  async function onDel(id: number): Promise<void> {
    try {
      // 弹出删除确认框
      const confirmed = await msg.confirm(t('tips'))
  
      if (confirmed) {
        // 如果用户确认删除，进行删除操作
        const res = await deleteById(id)// 异步删除操作
        if (res.code !== ResultCode.SUCCESS) {
          msg.error(res.message)// 删除失败提示
          return
        }
        msg.success(res.message)// 删除成功提示
        await getResourceList()// 更新资源列表
      }
      else {
        // 用户取消删除操作
        msg.info(t('cancelMessage'))
      }
    }
    catch (error) {
      // 异常处理，捕获任何错误
      if (error === 'cancel') {
        msg.info(t('cancelMessage'))
      }
      else {
        msg.error(t('errorMessage'))
      }
    }
  }
  
  /**
   * 批量删除选中的资源
   */
  async function onBatchDel(): Promise<void> {
    if (selected.value.length === 0) {
      msg.warning(t('selectFilesToDelete'))
      return
    }
  
    try {
      const confirmed = await msg.confirm(t('confirmBatchDelete', { count: selected.value.length }))
      
      if (confirmed) {
        const deletePromises = selected.value.map(resource => {
          if (resource?.id !== undefined) {
            return deleteById(resource.id)
          }
          return Promise.resolve({ code: 404 })
        })
        
        const results = await Promise.allSettled(deletePromises)
        
        let successCount = 0
        let failCount = 0
        
        results.forEach((result, index) => {
          if (result.status === 'fulfilled' && result.value.code === 200) {
            successCount++
          } else {
            failCount++
          }
        })
        
        if (successCount > 0) {
          msg.success(t('batchDeleteSuccess', { count: successCount }))
          clearSelected()
          await getResourceList(queryParams.value)
        }
        
        if (failCount > 0) {
          msg.error(t('batchDeleteFailed', { count: failCount }))
        }
      } else {
        msg.info(t('cancelMessage'))
      }
    } catch (error) {
      if (error === 'cancel') {
        msg.info(t('cancelMessage'))
      } else {
        msg.error(t('errorMessage'))
      }
    }
  }
  
  /**
   * 右键菜单
   */
  function executeContextmenu(e: MouseEvent, resource: Resource) {
    e.preventDefault()
    
    // 如果右键点击的资源没有被选中，则根据是否按住Ctrl/Cmd键决定选择行为
    if (!isSelected(resource)) {
      if (e.ctrlKey || e.metaKey) {
        // 按住Ctrl/Cmd键，添加到选择
        select(resource)
      } else {
        // 没有按住Ctrl/Cmd键，替换选择
        clearSelected()
        select(resource)
      }
    }
    
    const hasSelection = selected.value.length > 0
    const isMultipleSelected = selected.value.length > 1
    
    ContextMenu.showContextMenu({
      x: e.x,
      y: e.y,
      zIndex: 9999,
      iconFontClass: '',
      customClass: 'mine-contextmenu',
      items: [
        {
          label: t('select'),
          hidden: isSelected(resource),
          icon: 'i-ri:check-fill',
          onClick: () => {
            select(resource)
          },
        },
        {
          label: t('deselect'),
          hidden: !isSelected(resource),
          icon: 'i-ri:close-fill',
          onClick: () => {
            unSelect(resource)
          },
        },
        // 独选此项
        {
          label: t('singleSelect'),
          icon: 'i-ri:checkbox-circle-line',
          hidden: !isMultipleSelected,
          divided: true,
          onClick: () => {
            clearSelected()
            select(resource)
          },
        },
        // 全选当前页面
        {
          label: t('selectAll'),
          icon: 'i-ri:checkbox-multiple-line',
          hidden: resources.value.length === 0,
          onClick: () => {
            clearSelected()
            resources.value.forEach(res => select(res))
          },
        },
        // 清空选择
        {
          label: t('clearSelection'),
          icon: 'i-ri:checkbox-blank-line',
          hidden: !hasSelection,
          divided: true,
          onClick: () => {
            clearSelected()
          },
        },
        {
          label: t('view'),
          icon: 'i-ri:search-eye-line',
          disabled: !canPreview(resource),
          hidden: isMultipleSelected,
          onClick: () => {
            useImageViewer([resource?.url ?? ''])
          },
        },
        {
          label: isMultipleSelected ? t('batchDelete', { count: selected.value.length }) : t('delete'),
          icon: 'i-material-symbols:delete-outline',
          divided: true,
          onClick: () => {
            if (isMultipleSelected) {
              onBatchDel()
            } else if (resource?.id !== undefined) {
              onDel(resource.id)
            }
          },
        },
      ],
    })
  }
  
  function handleFile(ev: Event, btn: Resources.Button) {
    const target = ev.target as HTMLInputElement
    if (target.files) {
      btn.upload?.(target.files, { btn, getResourceList })
    }
  }
  
  /**
   * 拖拽上传相关函数
   */
  function handleDragEnter(e: DragEvent) {
    e.preventDefault()
    e.stopPropagation()
    dragCounter.value++
    if (e.dataTransfer?.items && e.dataTransfer.items.length > 0) {
      isDragOver.value = true
    }
  }
  
  function handleDragLeave(e: DragEvent) {
    e.preventDefault()
    e.stopPropagation()
    dragCounter.value--
    if (dragCounter.value === 0) {
      isDragOver.value = false
    }
  }
  
  function handleDragOver(e: DragEvent) {
    e.preventDefault()
    e.stopPropagation()
    if (e.dataTransfer) {
      e.dataTransfer.dropEffect = 'copy'
    }
  }
  
  async function handleDrop(e: DragEvent) {
    e.preventDefault()
    e.stopPropagation()
    
    isDragOver.value = false
    dragCounter.value = 0
    
    const files = e.dataTransfer?.files
    if (!files || files.length === 0) return
    
    // 验证文件类型
    const validFiles: File[] = []
    const currentSuffix = fileTypes.value.find(i => i.value === fileTypeSelected.value)?.suffix || ''
    const allowedSuffixes = currentSuffix ? currentSuffix.split(',') : []
    
    for (let i = 0; i < files.length; i++) {
      const file = files[i]
      if (allowedSuffixes.length > 0) {
        const fileExtension = file.name.split('.').pop()?.toLowerCase() || ''
        if (!allowedSuffixes.includes(fileExtension)) {
          msg.warning(t('unsupportedFormat', { name: file.name, formats: allowedSuffixes.join(', ') }))
          continue
        }
      }
      validFiles.push(file)
    }
    
    if (validFiles.length === 0) return
    
    // 上传文件
    await uploadFiles(validFiles)
  }
  
  async function uploadFiles(files: File[]) {
    for (const file of files) {
      const fileId = `${file.name}_${Date.now()}`
      uploadingFiles.value.add(fileId)
      uploadProgress.value.set(fileId, 0)
      
      try {
        await uploadLocal({
          file,
          onProgress: (progressEvent: { percent: number }) => {
            uploadProgress.value.set(fileId, progressEvent.percent)
          },
          onSuccess: (res: any) => {
            msg.success(t('uploadSuccess', { name: file.name }))
            getResourceList(queryParams.value)
          },
          onError: (res: any) => {
            msg.error(t('uploadFailed', { name: file.name }) + ': ' + (res.message || '上传失败'))
          }
        })
      } catch (error) {
        msg.error(t('uploadFailed', { name: file.name }))
        console.error('Upload error:', error)
      } finally {
        uploadingFiles.value.delete(fileId)
        uploadProgress.value.delete(fileId)
      }
    }
  }
  
  onMounted(async () => {
    await getResourceList()
  
    const apps = document.getElementsByClassName('res-app') as HTMLCollectionOf<HTMLDivElement>
  
    for (let i = 0; i < apps.length; i++) {
      const app = apps[i] as HTMLDivElement
      app.addEventListener('click', (e: MouseEvent) => {
        e.stopPropagation()
        const node = useParentNode(e, 'div')
        const fileInput = node.children[0]
        const btn = resourceStore.getAllButton()?.find(item => item.name === fileInput.getAttribute('name'))
        if (btn?.click) {
          btn?.click?.(btn, selected as any)
        }
        if (btn?.upload) {
          fileInput?.click?.()
        }
      })
      app?.parentElement?.addEventListener('mouseover', () => {
        const index = i
        app.className = 'res-app main-effect'
  
        if (index === 0) {
          if (apps[1]) {
            apps[1].className = 'res-app second-effect'
          }
          if (apps[2]) {
            apps[2].className = 'res-app third-effect'
          }
        }
        else if (index === apps.length - 1) {
          if (apps[index - 1]) {
            apps[index - 1].className = 'res-app second-effect'
          }
          if (apps[index - 2]) {
            apps[index - 2].className = 'res-app third-effect'
          }
        }
        else {
          if (apps[index - 1]) {
            apps[index - 1].className = 'res-app second-effect'
          }
          if (apps[index + 1]) {
            apps[index + 1].className = 'res-app second-effect'
          }
  
          if (index - 2 > -1 && apps[index - 2]) {
            apps[index - 2].className = 'res-app third-effect'
          }
  
          if (index + 2 < apps.length && apps[index + 2]) {
            apps[index + 2].className = 'res-app third-effect'
          }
        }
      })
  
      app?.parentElement?.addEventListener('mouseout', () => {
        for (const app of apps) {
          app.className = 'res-app'
        }
      })
    }
  })
  
  onUnmounted(() => {
    // 取消监听
    document.querySelectorAll('.ma-resource-dock .res-app').forEach((app) => {
      app.removeEventListener('mousemove', () => {})
      app.removeEventListener('mouseout', () => {})
      app.removeEventListener('click', () => {})
    })
  })
  </script>
  
  <template>
    <div 
      class="ma-resource-panel h-full flex flex-col"
      :class="{ 'drag-over': isDragOver }"
      @dragenter="handleDragEnter"
      @dragleave="handleDragLeave"
      @dragover="handleDragOver"
      @drop="handleDrop"
    >
      <!-- 拖拽覆盖层 -->
      <div v-if="isDragOver" class="drag-overlay">
        <div class="drag-content">
          <ma-svg-icon name="ri:upload-cloud-2-line" :size="48" />
          <p class="drag-text">{{ t('dragUpload') }}</p>
        </div>
      </div>
  
      <!-- 上传进度显示 -->
      <div v-if="uploadingFiles.size > 0" class="upload-progress-container">
        <div v-for="[fileId, progress] in uploadProgress" :key="fileId" class="upload-progress-item">
          <div class="upload-file-info">
            <ma-svg-icon name="ri:file-line" :size="16" />
            <span class="upload-file-name">{{ fileId.split('_')[0] }}</span>
          </div>
          <div class="upload-progress-bar">
            <div class="upload-progress-fill" :style="{ width: `${progress}%` }"></div>
          </div>
          <span class="upload-progress-text">{{ progress }}%</span>
        </div>
      </div>
  
      <div class="flex flex-col justify-between gap-y-1 md:flex-row">
        <div>
          <el-segmented
            v-model="fileTypeSelected"
            :options="fileTypes as any" size="default"
            block
            @change="onfileTypesChange"
          >
            <template #default="{ item }">
              <div class="flex items-center justify-center">
                <ma-svg-icon
                  v-if="(item as FileType)?.icon" :name="(item as FileType).icon || ''" :size="17"
                  class="mr-1 flex items-center justify-center"
                />
                <span>{{ typeof (item as FileType).label === 'function' ? ((item as FileType).label as () => string)() : (item as FileType).label }}</span>
              </div>
            </template>
          </el-segmented>
        </div>
  
        <div class="flex justify-end items-center gap-2">
          <el-tooltip 
            v-if="props.multiple"
            content="提示：按住 Ctrl/Cmd 键多选，按住 Shift 键范围选择，右键可批量操作"
            placement="top"
          >
            <ma-svg-icon name="i-material-symbols:help-outline" :size="16" class="text-gray-400 cursor-help" />
          </el-tooltip>
          <el-input
            v-model="queryParams.origin_name" :placeholder="t('searchPlaceholder')" clearable class="w-full md:w-[180px]" @input="() => {
              getResourceList(queryParams)
            }"
          >
            <template #suffix>
              <ma-svg-icon name="i-material-symbols:search-rounded" :size="20" />
            </template>
          </el-input>
        </div>
      </div>
      <div class="mt-2 min-h-0 flex-1">
        <OverlayScrollbarsComponent
          v-if="loading || resources.length" class="max-h-full"
          :options="{ scrollbars: { autoHide: 'leave', autoHideDelay: 100 } }"
        >
          <div class="flex flex-wrap px-[2px] pt-[2px]">
            <el-space fill wrap :fill-ratio="9">
              <template v-for="resource in resources" :key="resource.id">
                <div
                  class="resource-item" :class="{ active: isSelected(resource) }" @click="(e: MouseEvent) => handleClick(resource, e)"
                  @dblclick="handleDbClick(resource)" @contextmenu="(e: MouseEvent) => executeContextmenu(e, resource)"
                >
                  <div class="resource-item__cover">
                    <template v-if="getCover(resource)">
                      <el-image :src="getCover(resource)" fit="cover" class="h-full w-full" lazy>
                        <template #error>
                          <div
                            class="relative m-[8px] h-[calc(100%-16px)] w-[calc(100%-16px)] flex items-center justify-center overflow-hidden"
                          >
                            <div class="cursor-default overflow-hidden text-ellipsis whitespace-pre-wrap">
                              {{ resource.origin_name }}
                            </div>
                          </div>
                        </template>
                      </el-image>
                    </template>
                    <template v-else>
                      <div
                        class="relative m-[8px] h-[calc(100%-16px)] w-[calc(100%-16px)] flex items-center justify-center overflow-hidden"
                      >
                        <div class="cursor-default overflow-hidden text-ellipsis whitespace-pre-wrap">
                          {{ resource.origin_name }}
                        </div>
                      </div>
                    </template>
                  </div>
                  <div v-if="getCover(resource)" class="resource-item__name cursor-default">
                    {{ resource.origin_name }}
                  </div>
                  <div class="resource-item__selected">
                    <ma-svg-icon class="resource-item__selected-icon" name="gravity-ui:circle-check-fill" :size="18" />
                  </div>
                </div>
              </template>
              <template v-if="resources.length === 0">
                <el-skeleton v-for="i in skeletonNum" :key="i" class="resource-skeleton relative" animated>
                  <template #template>
                    <el-skeleton-item class="absolute h-full w-full" variant="rect" />
                  </template>
                </el-skeleton>
              </template>
              <div v-for="i in 10" :key="i" class="resource-placeholder" />
            </el-space>
          </div>
        </OverlayScrollbarsComponent>
        <div v-else class="h-full w-full flex flex-1 items-center justify-center">
          <el-empty />
        </div>
      </div>
      <div class="ma-resource-panel__footer flex justify-between pt-2">
        <div class="flex items-center">
          <el-tag
            v-if="props.multiple && props.limit" size="large" class="mr-2"
            :class="{ 'color-[var(--el-color-danger)]': props.limit && selectedKeys.length >= props.limit }"
          >
            {{ selectedKeys.length }}
            <template v-if="props.multiple && props.limit">
              /{{ props.limit }}
            </template>
          </el-tag>
          <el-tag
            v-if="props.multiple && selectedKeys.length > 0" size="small" class="mr-2"
            type="info"
          >
            已选择 {{ selectedKeys.length }} 个文件
          </el-tag>
          <el-pagination
            v-model:current-page="queryParams.page" :disabled="loading" :total="total"
            :page-size="queryParams.page_size" background layout="prev, pager, next" :pager-count="5"
            @change="(p: number) => {
              queryParams.page = p
              getResourceList(queryParams)
            }"
          />
        </div>
        <div v-if="props.showAction">
          <slot name="actions">
            <el-button @click="cancel">
              {{ t('cancel') }}
            </el-button>
            <el-button type="primary" @click="confirm">
              {{ t('confirm') }}
            </el-button>
          </slot>
        </div>
      </div>
  
      <div class="ma-resource-dock">
        <template v-for="btn in resourceStore.getAllButton()">
          <div class="res-app-container">
            <div class="res-app">
              <m-tooltip :text="btn.label">
                <input
                  type="file"
                  :name="btn.name"
                  class="hidden"
                  v-bind="btn?.uploadConfig ?? {}"
                  @change="handleFile($event, btn)"
                  @click.stop="() => {}"
                >
                <ma-svg-icon :name="btn.icon" class="res-app-icon" />
              </m-tooltip>
            </div>
          </div>
        </template>
      </div>
    </div>
  </template>
  
  <style scoped lang="scss">
  @keyframes fadeIn {
    from {
      opacity: 0;
    }
    to {
      opacity: 1;
    }
  }
  
  .ma-resource-panel{
    @apply relative;
    --resource-item-size:120px;
  
    // 拖拽状态
    &.drag-over {
      @apply ring-2 ring-[rgb(var(--ui-primary))] ring-opacity-50;
    }
  
    // 拖拽覆盖层
    .drag-overlay {
      @apply absolute inset-0 z-50 bg-[rgb(var(--ui-primary))] bg-opacity-10 
      flex items-center justify-center backdrop-blur-sm;
      
      .drag-content {
        @apply flex flex-col items-center justify-center 
        bg-white dark:bg-dark-8 rounded-xl p-8 shadow-lg
        border-2 border-dashed border-[rgb(var(--ui-primary))];
        
        .drag-text {
          @apply mt-4 text-lg font-medium text-[rgb(var(--ui-primary))];
        }
      }
    }
  
    // 上传进度容器
    .upload-progress-container {
      @apply mb-4 p-3 bg-gray-50 dark:bg-dark-8 rounded-lg;
      
      .upload-progress-item {
        @apply flex items-center gap-3 mb-2 last:mb-0;
        
        .upload-file-info {
          @apply flex items-center gap-2 min-w-0 flex-1;
          
          .upload-file-name {
            @apply text-sm text-gray-600 dark:text-gray-300 truncate;
          }
        }
        
        .upload-progress-bar {
          @apply flex-1 h-2 bg-gray-200 dark:bg-dark-6 rounded-full overflow-hidden;
          
          .upload-progress-fill {
            @apply h-full bg-[rgb(var(--ui-primary))] transition-all duration-300;
          }
        }
        
        .upload-progress-text {
          @apply text-xs text-gray-500 dark:text-gray-400 min-w-[3rem] text-right;
        }
      }
    }
  
    .ma-resource-dock {
      @apply absolute bg-gray-2 dark-bg-dark-9
      rounded-xl p-1 flex items-center justify-center gap-x-0.5
      ;
      height: 40px;
      left: 50%;
      transform: translate(-50%, 0%);
      bottom: 0;
  
      .res-app-container {
        @apply relative h-40px flex items-center;
      }
  
      // 白色圆点
      .activate::after {
        content: "";
        @apply block b-2 b-solid b-white rounded-full w-0 h-0 absolute bottom-2px left-50%;
      }
  
      .res-app {
        @apply w-40px h-40px shadow-inset shadow-md rounded-10px transition-all duration-300 flex items-center justify-center
        bg-gray-3 dark-bg-dark-4
          dark-shadow-dark-9
        ;
      }
  
      .res-app-icon {
        @apply w-55px h-55px !text-2xl transform-all duration-300 text-dark-1 dark-text-gray-2 cursor-pointer
        ;
      }
  
      // 主放大效果
      .main-effect {
        width: 80px;
        height: 80px;
        transform: translateY(-40px);
      }
  
      .main-effect .res-app-icon {
        @apply w-80px h-80px !text-[60px];
      }
  
      // 次放大效果
      .second-effect {
        width: 60px;
        height: 60px;
        transform: translateY(-20px);
      }
  
      .second-effect .res-app-icon {
        @apply w-60px h-60px !text-[40px];
      }
  
      // 最次放大效果
      .third-effect {
        width: 50px;
        height: 50px;
        transform: translateY(-10px);
      }
  
      .third-effect .res-app-icon {
        @apply w-50px h-50px !text-[30px];
      }
    }
  }
  .resource-item{
    animation: fadeIn 0.38s ease-out forwards;
    --un-bg-opacity: 0.3;
    @apply relative min-w-[var(--resource-item-size)] pb-[100%] rounded overflow-hidden border-box bg-gray-1  dark-bg-dark-3;
  }
  .resource-item__cover{
    @apply absolute bottom-0 left-0 h-full w-full;
  }
  .resource-item__name{
    @apply absolute bottom-0 left-0 h-24px w-[calc(100%-20px)] overflow-hidden bg-gray:60 px-10px text-12px leading-24px whitespace-nowrap text-ellipsis c-white;
  
  }
  .resource-item__selected{
    @apply absolute top--30px right--30px w-40px h-40px;
    //transition: all 0.1s ease-in-out;
    background-image: linear-gradient(to top right, transparent 50%, rgb(var(--ui-primary)) 50%);
  }
  .resource-item__selected-icon{
    @apply absolute top-0 right-0 p-2px c-white;
  }
  .resource-item.active .resource-item__selected{
    @apply top-0 right-0;
  }
  
  .resource-placeholder{
    @apply min-w-[var(--resource-item-size)] h-0 pointer-events-none p-0;
  }
  .resource-skeleton{
    @apply min-w-[var(--resource-item-size)] pb-[100%];
  }
  
  .resource-item:hover,
  .resource-item.active {
    @apply ring-2 ring-[rgb(var(--ui-primary))];
  }
  </style>
  