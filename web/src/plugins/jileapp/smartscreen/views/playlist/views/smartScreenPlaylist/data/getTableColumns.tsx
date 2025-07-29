import type { MaProTableColumns } from '@mineadmin/pro-table'
import { ElTag } from 'element-plus'
import { deleteByIds } from '../../../api/smartScreenPlaylist'
import { playModeOptions, statusOptions } from './common'
import { useMessage } from '@/hooks/useMessage.ts'

export default function getTableColumns(drawer, proTableRef, t, contentDrawer): MaProTableColumns[] {
  const msg = useMessage()

  return [
    { type: 'selection', label: () => t('crud.selection'), width: 50 },
    {
      label: () => t('crud.id'),
      prop: 'id',
      width: 80,
    },
    {
      label: () => t('smartscreen.playlist.name'),
      prop: 'name',
      minWidth: 200,
    },
    {
      label: () => '播放模式',
      prop: 'play_mode',
      width: 120,
      cellRender: ({ row }) => {
        const option = playModeOptions.find(item => item.value === row.play_mode)
        const colorMap = { 1: 'primary', 2: 'warning', 3: 'info' }
        return <ElTag type={colorMap[row.play_mode]}>{option?.label}</ElTag>
      },
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
      label: () => t('crud.created_at'),
      prop: 'created_at',
      width: 160,
    },
    {
      label: () => t('crud.updated_at'),
      prop: 'updated_at',
      width: 160,
    },
    {
      type: 'operation',
      label: () => t('crud.operation'),
      width: 200,
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
            name: 'manageContent',
            icon: 'material-symbols:playlist-add',
            text: () => '管理内容',
            onClick: ({ row }) => {
              // 打开内容管理抽屉
              contentDrawer.setTitle(`管理播放列表：${row.name}`)
              contentDrawer.open({ 
                formType: 'manageContent', 
                data: { 
                  playlistId: row.id, 
                  playlistName: row.name 
                } 
              })
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
