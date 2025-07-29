import type { MaProTableColumns } from '@mineadmin/pro-table'
import { ElTag, ElEmpty, ElTable, ElTableColumn } from 'element-plus'
import { deleteByIds } from '../../../api/smartScreenDeviceGroup'
import { getStatusText, getStatusTagType, getColorName } from './common'
import { useMessage } from '@/hooks/useMessage.ts'

export default function getTableColumns(dialog, proTableRef, t): MaProTableColumns[] {
  const msg = useMessage()

  // 获取设备状态文本
  const getDeviceStatusText = (status: number) => {
    return status === 1 ? t('smartscreen.device.active') : t('smartscreen.device.inactive')
  }

  // 获取设备状态标签类型
  const getDeviceStatusTagType = (status: number) => {
    return status === 1 ? 'success' : 'danger'
  }

  // 获取在线状态文本
  const getOnlineStatusText = (isOnline: boolean) => {
    return isOnline ? t('smartscreen.device.online') : t('smartscreen.device.offline')
  }

  // 获取在线状态标签类型
  const getOnlineStatusTagType = (isOnline: boolean) => {
    return isOnline ? 'success' : 'info'
  }

  return [
    { 
      type: 'expand',
      width: 50,
      cellRender: ({ row }) => {
        if (!row.devices || row.devices.length === 0) {
          return (
            <div style="padding: 20px; text-align: center;">
              <ElEmpty description={t('smartscreen.group.noDevicesInGroup')} />
            </div>
          )
        }

        return (
          <div style="padding: 16px; background-color: #fafafa;">
            <div style="margin-bottom: 12px; font-weight: 600; color: #333; font-size: 14px;">
              {t('smartscreen.group.deviceList')} ({row.devices.length} 台设备)
            </div>
            <ElTable 
              data={row.devices} 
              border 
              style={{ width: '100%' }}
            >
              <ElTableColumn prop="device_name" label={t('smartscreen.group.deviceName')} minWidth={150} />
              <ElTableColumn prop="mac_address" label={t('smartscreen.group.macAddress')} width={150} />
              <ElTableColumn 
                prop="status" 
                label={t('smartscreen.device.status')} 
                width={100}
                v-slots={{
                  default: ({ row: device }) => (
                    <ElTag type={getDeviceStatusTagType(device.status)}>
                      {getDeviceStatusText(device.status)}
                    </ElTag>
                  )
                }}
              />
              <ElTableColumn 
                prop="is_online" 
                label={t('smartscreen.group.onlineStatus')} 
                width={100}
                v-slots={{
                  default: ({ row: device }) => (
                    <ElTag type={getOnlineStatusTagType(device.is_online)}>
                      {getOnlineStatusText(device.is_online)}
                    </ElTag>
                  )
                }}
              />
              {/* <ElTableColumn prop="created_at" label={t('smartscreen.group.addedAt')} width={180} /> */}
            </ElTable>
          </div>
        )
      }
    },
    { type: 'selection', label: () => t('crud.selection'), width: 50 },
    {
      label: () => t('crud.id'),
      prop: 'id',
      width: 80,
    },
    {
      label: () => t('smartscreen.group.name'),
      prop: 'name',
      minWidth: 150,
    },
    {
      label: () => t('smartscreen.group.description'),
      prop: 'description',
      minWidth: 200,
      showOverflowTooltip: true,
    },
    {
      label: () => t('smartscreen.group.color'),
      prop: 'color',
      width: 100,
      cellRender: ({ row }) => (
        <div style="display: flex; align-items: center; gap: 8px;">
          <div 
            style={{
              width: '16px',
              height: '16px',
              borderRadius: '3px',
              backgroundColor: row.color,
              border: '1px solid #dcdfe6'
            }}
          />
          <span style="font-size: 12px; color: #666;">{getColorName(row.color)}</span>
        </div>
      ),
    },
    {
      label: () => t('smartscreen.group.deviceCount'),
      prop: 'device_count',
      width: 100,
      cellRender: ({ row }) => (
        <ElTag type="info" size="small">
          {row.device_count} 台
        </ElTag>
      ),
    },
    {
      label: () => t('common.status'),
      prop: 'status',
      width: 80,
      cellRender: ({ row }) => (
        <ElTag type={getStatusTagType(row.status)}>
          {getStatusText(row.status)}
        </ElTag>
      ),
    },
    {
      label: () => t('crud.created_at'),
      prop: 'created_at',
      width: 160,
    },
    {
      type: 'operation',
      label: () => t('crud.operation'),
      width: 200,
      operationConfigure: {
        actions: [
          {
            name: 'manageDevices',
            icon: 'material-symbols:devices',
            text: () => t('smartscreen.group.manageDevices'),
            onClick: ({ row }) => {
              dialog.setTitle(`${t('smartscreen.group.manageDevices')} - ${row.name}`)
              // 设备管理使用默认的1400px宽度
              dialog.open({ formType: 'manageDevices', data: row })
            },
          },
          {
            name: 'manageContent',
            icon: 'material-symbols:playlist-play',
            text: () => t('smartscreen.group.manageContent'),
            onClick: ({ row }) => {
              dialog.setTitle(`${t('smartscreen.group.manageContent')} - ${row.name}`)
              // 内容管理使用默认的1400px宽度
              dialog.open({ formType: 'manageContent', data: row })
            },
          },
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
            name: 'delete',
            icon: 'material-symbols:delete-outline',
            text: () => t('crud.delete'),
            onClick: ({ row }, proxy) => {
              msg.confirm(t('crud.delMessage')).then(async () => {
                const response = await deleteByIds([row.id])
                if (response.code === 200) {
                  msg.success(t('crud.delSuccess'))
                  await proxy.refresh()
                }
              })
            },
          },
        ],
      },
    },
  ]
} 