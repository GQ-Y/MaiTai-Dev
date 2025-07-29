import type { MaFormItem } from '@mineadmin/form'
import { displayModeOptions, statusOptions } from './common'
import MaRemoteSelect from '@/components/ma-remote-select/index.vue'

export default function getFormItems(formType: string, t: any, _formData: any): MaFormItem[] {
  // 设置内容模式的表单项
  if (formType === 'setContent') {
    return [
      {
        label: () => '选择新内容',
        prop: 'current_content_id',
        render: () => MaRemoteSelect,
        renderProps: {
          placeholder: '请选择要在设备上显示的内容...',
          multiple: false,
          size: 'large',
          clearable: true,
          filterable: true,
          url: '/admin/plugin/smart-screen/content/list',
          axiosConfig: {
            method: 'get',
            params: { pageSize: 9999 },
          },
          dataHandle: (response: any) => {
            return (response.data?.list || [])
              .filter((item: any) => item.status === 1)
              .map((item: any) => ({
                label: `${item.content_type === 1 ? '🌐' : item.content_type === 2 ? '🖼️' : '🎬'} ${item.title}`,
                value: item.id,
              }))
          },
        },
        itemProps: {
          style: { marginBottom: '0' },
          rules: [
            { required: false, message: '请选择要显示的内容', trigger: 'change' },
          ],
        },
      },
    ]
  }

  // 设置播放列表模式的表单项
  if (formType === 'setPlaylist') {
    return [
      {
        label: () => '选择播放列表',
        prop: 'playlist_ids',
        render: () => MaRemoteSelect,
        renderProps: {
          placeholder: '请选择要关联的播放列表（可多选）...',
          multiple: true,
          size: 'large',
          clearable: true,
          filterable: true,
          url: '/admin/plugin/smart-screen/playlist/list',
          axiosConfig: {
            method: 'get',
            params: { pageSize: 9999 },
          },
          dataHandle: (response: any) => {
            return (response.data?.list || [])
              .filter((item: any) => item.status === 1)
              .map((item: any) => ({
                label: `📋 ${item.name}`,
                value: item.id,
              }))
          },
        },
        itemProps: {
          style: { marginBottom: '0' },
          rules: [
            { required: false, message: '请选择播放列表', trigger: 'change' },
          ],
        },
      },
    ]
  }

  // 默认的新增/编辑表单项
  return [
    {
      label: () => t('smartscreen.device.mac_address'),
      prop: 'mac_address',
      render: () => <el-input placeholder={t('smartscreen.device.mac_address')} clearable />,
      renderProps: {
        placeholder: t('smartscreen.device.mac_address'),
      },
    },
    {
      label: () => t('smartscreen.device.device_name'),
      prop: 'device_name',
      render: () => <el-input placeholder={t('smartscreen.device.device_name')} clearable />,
      renderProps: {
        placeholder: t('smartscreen.device.device_name'),
      },
    },
    {
      label: () => t('smartscreen.device.status'),
      prop: 'status',
      render: () => (
        <el-radio-group>
          {statusOptions.map(option => (
            <el-radio key={option.value} value={option.value}>
              {option.label}
            </el-radio>
          ))}
        </el-radio-group>
      ),
      itemProps: {
        defaultValue: 1,
      },
    },
    {
      label: () => t('smartscreen.device.display_mode'),
      prop: 'display_mode',
      render: () => (
        <el-select placeholder={t('smartscreen.device.display_mode')}>
          {displayModeOptions.map(option => (
            <el-option key={option.value} label={option.label} value={option.value} />
          ))}
        </el-select>
      ),
      itemProps: {
        defaultValue: 1,
      },
    },
    {
      label: () => '显示内容',
      prop: 'current_content_id',
      render: () => MaRemoteSelect,
      renderProps: {
        placeholder: '请选择要显示的内容',
        multiple: false,
        url: '/admin/plugin/smart-screen/content/list',
        axiosConfig: {
          method: 'get',
          params: { pageSize: 9999 },
        },
        dataHandle: (response: any) => {
          return (response.data?.list || [])
            .filter((item: any) => item.status === 1)
            .map((item: any) => ({
              label: `[${item.content_type === 1 ? '网页' : item.content_type === 2 ? '图片' : '视频'}] ${item.title}`,
              value: item.id,
            }))
        },
      },
      itemProps: {
        rules: [
          { required: false, message: '请选择要显示的内容', trigger: 'change' },
        ],
      },
    },
  ]
}
