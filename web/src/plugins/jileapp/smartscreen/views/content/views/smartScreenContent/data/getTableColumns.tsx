import type { MaProTableColumns } from '@mineadmin/pro-table'
import { ElImage, ElTag } from 'element-plus'
import { deleteByIds } from '../../../api/smartScreenContent'
import { contentTypeOptions, statusOptions } from './common'
import { useMessage } from '@/hooks/useMessage.ts'

export default function getTableColumns(drawer, proTableRef, t): MaProTableColumns[] {
  const msg = useMessage()

  return [
    { type: 'selection', label: () => t('crud.selection'), width: 50 },
    {
      label: () => t('crud.id'),
      prop: 'id',
      width: 80,
    },
    {
      label: () => t('smartscreen.content.content_title'),
      prop: 'title',
      minWidth: 150,
    },
    // {
    //   label: () => t('smartscreen.content.content_type'),
    //   prop: 'content_type',
    //   width: 100,
    //   cellRender: ({ row }) => {
    //     const option = contentTypeOptions.find(item => item.value === row.content_type)
    //     const colorMap = { 
    //       1: 'primary',    // 网页 - 蓝色
    //       2: 'warning',    // 图片 - 橙色
    //       3: 'info',       // 视频 - 灰色
    //       4: 'success',    // 直播流 - 绿色
    //       5: 'danger'      // 音频 - 红色
    //     }
    //     return <ElTag type={colorMap[row.content_type]}>{option?.label}</ElTag>
    //   },
    // },
    {
      label: () => t('smartscreen.content.thumbnail'),
      prop: 'thumbnail',
      width: 120,
      cellRender: ({ row }) => {
        // 优先显示缩略图
        if (row.thumbnail) {
          return (
            <ElImage
              style={{
                width: '50px',
                height: '50px',
                borderRadius: '4px',
                margin: '0 auto',
                display: 'block',
                boxSizing: 'border-box',
              }}
              src={row.thumbnail}
              fit="cover"
              preview-src-list={[row.thumbnail]}
              preview-teleported={true}
              lazy
            />
          )
        }

        // 如果没有缩略图，但是图片类型，尝试显示内容图片
        if (row.content_type === 2 && row.content_url) {
          return (
            <ElImage
              style={{
                width: '50px',
                height: '50px',
                borderRadius: '4px',
                margin: '0 auto',
                display: 'block',
                boxSizing: 'border-box',
              }}
              src={row.content_url}
              fit="cover"
              preview-src-list={[row.content_url]}
              preview-teleported={true}
              lazy
            />
          )
        }

        // 根据内容类型显示不同的默认图标
        const iconMap = {
          1: 'material-symbols:web',           // 网页
          2: 'material-symbols:image-outline', // 图片
          3: 'material-symbols:video-file',    // 视频
          4: 'material-symbols:live-tv',       // 直播流
          5: 'material-symbols:audio-file'     // 音频
        }
        
        const iconName = iconMap[row.content_type] || 'material-symbols:image-outline'
        
        return (
          <div
            style={{
              width: '50px',
              height: '50px',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              backgroundColor: '#f5f5f5',
              borderRadius: '4px',
              color: '#999',
              margin: '0 auto',
              boxSizing: 'border-box',
            }}
          >
            <ma-svg-icon name={iconName} size="24" />
          </div>
        )
      },
    },
    {
      label: () => t('smartscreen.content.duration'),
      prop: 'duration',
      width: 100,
      cellRender: ({ row }) => row.duration === 0 ? '永久' : `${row.duration}秒`,
    },
    {
      label: () => t('common.status'),
      prop: 'status',
      width: 80,
      cellRender: ({ row }) => {
        const option = statusOptions.find(item => item.value === row.status)
        return <ElTag type={row.status === 1 ? 'success' : 'danger'}>{option?.label}</ElTag>
      },
    },
    {
      label: () => t('smartscreen.content.sort_order'),
      prop: 'sort_order',
      width: 80,
    },
    {
      label: () => t('crud.created_at'),
      prop: 'created_at',
      width: 160,
    },
    {
      type: 'operation',
      label: () => t('crud.operation'),
      width: 160,
      fixed: 'right',
      operationConfigure: {
        actions: [
          {
            name: 'edit',
            icon: 'material-symbols:edit',
            text: () => t('crud.edit'),
            onClick: ({ row }) => {
              drawer.setTitle(t('crud.edit'))
              drawer.open({ formType: 'edit', data: row })
            },
          },
          {
            name: 'delete',
            icon: 'material-symbols:delete',
            text: () => t('crud.delete'),
            onClick: ({ row }) => {
              msg.confirm(t('crud.confirmDelete')).then(() => {
                deleteByIds([row.id]).then(() => {
                  msg.success(t('crud.deleteSuccess'))
                  proTableRef.value?.refresh()
                }).catch((err) => {
                  msg.error(err.message || '删除失败')
                })
              })
            },
          },
        ],
      },
    },
  ]
}
