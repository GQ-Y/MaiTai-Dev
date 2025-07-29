import type { MaSearchItem } from '@mineadmin/search'
import { displayModeOptions, statusOptions } from './common'

export default function getSearchItems(t: any): MaSearchItem[] {
  return [
    {
      label: () => t('smartscreen.device.device_name'),
      prop: 'device_name',
      render: () => <el-input placeholder={t('smartscreen.device.device_name')} clearable />,
    },
    {
      label: () => t('smartscreen.device.mac_address'),
      prop: 'mac_address',
      render: () => <el-input placeholder={t('smartscreen.device.mac_address')} clearable />,
    },
    {
      label: () => t('smartscreen.device.status'),
      prop: 'status',
      render: () => (
        <el-select placeholder={t('smartscreen.device.status')}>
          <el-option label={t('crud.all')} value="" />
          {statusOptions.map(option => (
            <el-option key={option.value} label={option.label} value={option.value} />
          ))}
        </el-select>
      ),
    },
  ]
}
