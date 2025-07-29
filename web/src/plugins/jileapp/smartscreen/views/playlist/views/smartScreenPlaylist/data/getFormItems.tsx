import type { MaFormItem } from '@mineadmin/form'
import { playModeOptions, statusOptions } from './common'

export default function getFormItems(formType: string, t: any, _formData: any): MaFormItem[] {
  return [
    {
      label: () => t('smartscreen.playlist.name'),
      prop: 'name',
      render: () => <el-input placeholder={t('smartscreen.playlist.name')} clearable />,
      renderProps: {
        placeholder: t('smartscreen.playlist.name'),
      },
      itemProps: {
        rules: [
          { required: true, message: '请输入播放列表名称', trigger: 'blur' },
          { max: 200, message: '播放列表名称不能超过200个字符', trigger: 'blur' },
        ],
      },
    },
    {
      label: () => '播放模式',
      prop: 'play_mode',
      render: () => (
        <el-select placeholder="请选择播放模式" style={{ width: '100%' }}>
          {playModeOptions.map(option => (
            <el-option key={option.value} label={option.label} value={option.value} />
          ))}
        </el-select>
      ),
      itemProps: {
        defaultValue: 1,
        rules: [
          { required: true, message: '请选择播放模式', trigger: 'change' },
        ],
      },
    },
    {
      label: () => t('common.status'),
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
  ]
}
