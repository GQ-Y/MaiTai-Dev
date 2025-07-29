import type { MaProTableColumns } from '@mineadmin/pro-table'
import { ElTag } from 'element-plus'
import { activate, deactivate, deleteByIds } from '../../../api/smartScreenDevice'
import { displayModeOptions, onlineStatusOptions, statusOptions } from './common'
import { useMessage } from '@/hooks/useMessage.ts'

export default function getTableColumns(dialog, proTableRef, t): MaProTableColumns[] {
  const msg = useMessage()

  return [
    { type: 'selection', label: () => t('crud.selection'), width: 50 },
    {
      label: () => t('crud.id'),
      prop: 'id',
      width: 80,
    },
    {
      label: () => t('smartscreen.device.mac_address'),
      prop: 'mac_address',
      width: 140,
    },
    {
      label: () => t('smartscreen.device.device_name'),
      prop: 'device_name',
      minWidth: 150,
    },
    {
      label: () => t('smartscreen.device.status'),
      prop: 'status',
      width: 80,
      cellRender: ({ row }) => {
        const option = statusOptions.find(item => item.value === row.status)
        return <ElTag type={row.status === 1 ? 'success' : 'danger'}>{option?.label}</ElTag>
      },
    },
    {
      label: () => t('smartscreen.device.is_online'),
      prop: 'is_online',
      width: 80,
      cellRender: ({ row }) => {
        const option = onlineStatusOptions.find(item => item.value === row.is_online)
        return <ElTag type={row.is_online === 1 ? 'success' : 'danger'}>{option?.label}</ElTag>
      },
    },
    {
      label: () => t('smartscreen.device.display_mode'),
      prop: 'display_mode',
      width: 120,
      cellRender: ({ row }) => {
        const option = displayModeOptions.find(item => item.value === row.display_mode)
        const colorMap = { 1: 'primary', 2: 'warning', 3: 'info', 4: 'success' }
        return <ElTag type={colorMap[row.display_mode]}>{option?.label}</ElTag>
      },
    },
    {
      label: () => t('smartscreen.device.current_content_id'),
      prop: 'current_content_title',
      width: 150,
      cellRender: ({ row }) => {
        if (!row.current_content_id || !row.current_content_title) {
          return <ElTag type="info">未设置内容</ElTag>
        }
        const typeColorMap = { 1: 'primary', 2: 'warning', 3: 'success' }
        const typeTextMap = { 1: '网页', 2: '图片', 3: '视频', 4: '直播', 5: '音频' }
        return (
          <div>
            <ElTag type={typeColorMap[row.current_content_type] || 'info'} size="small">
              {typeTextMap[row.current_content_type] || '未知'}
            </ElTag>
            <div style="font-size: 12px; color: #666; margin-top: 2px;">
              {row.current_content_title}
            </div>
          </div>
        )
      },
    },
    {
      label: () => '关联播放列表',
      prop: 'playlist_names',
      width: 150,
      cellRender: ({ row }) => {
        if (!row.playlist_count || row.playlist_count === 0) {
          return <ElTag type="info">未设置列表</ElTag>
        }
        return (
          <div>
            <ElTag type="primary" size="small">
              {row.playlist_count}
              个列表
            </ElTag>
            <div style="font-size: 12px; color: #666; margin-top: 2px; word-break: break-all;">
              {row.playlist_names || '暂无'}
            </div>
          </div>
        )
      },
    },
    {
      label: () => t('smartscreen.device.last_online_time'),
      prop: 'last_online_time',
      width: 160,
    },
    {
      label: () => t('crud.created_at'),
      prop: 'created_at',
      width: 160,
    },
    {
      type: 'operation',
      label: () => t('crud.operation'),
      width: 240,
      fixed: 'right',
      operationConfigure: {
        actions: [
          {
            name: 'edit',
            icon: 'material-symbols:edit',
            text: () => t('crud.edit'),
            onClick: ({ row }) => {
              dialog.setTitle(t('crud.edit'))
              dialog.open({ formType: 'edit', data: row })
            },
          },
          {
            name: 'setContent',
            icon: 'material-symbols:content-copy',
            text: () => '设置内容',
            onClick: ({ row }) => {
              dialog.setTitle('设置显示内容')
              dialog.open({ formType: 'setContent', data: row })
            },
          },
          {
            name: 'setPlaylist',
            icon: 'material-symbols:playlist-play',
            text: () => '设置播放列表',
            onClick: ({ row }) => {
              dialog.setTitle('设置播放列表')
              dialog.open({ formType: 'setPlaylist', data: row })
            },
          },
          {
            name: 'activate',
            icon: 'material-symbols:check-circle',
            text: () => t('smartscreen.device.activate'),
            onClick: ({ row }) => {
              activate(row.id).then(() => {
                msg.success(t('smartscreen.device.activated'))
                proTableRef.value?.refresh()
              }).catch((err) => {
                msg.error(err.message || '激活失败')
              })
            },
            show: ({ row }) => row.status === 0,
          },
          {
            name: 'deactivate',
            icon: 'material-symbols:cancel',
            text: () => t('smartscreen.device.deactivate'),
            onClick: ({ row }) => {
              deactivate(row.id).then(() => {
                msg.success(t('smartscreen.device.deactivated'))
                proTableRef.value?.refresh()
              }).catch((err) => {
                msg.error(err.message || '禁用失败')
              })
            },
            show: ({ row }) => row.status === 1,
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
