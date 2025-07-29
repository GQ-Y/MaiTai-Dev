import type { MaSearchItem } from '@mineadmin/search'
import { playModeOptions, statusOptions } from './common'

export default function getSearchItems(t: any): MaSearchItem[] {
  return [
    {
      label: () => t('smartscreen.playlist.name'),
      prop: 'name',
      render: () => <el-input placeholder={t('smartscreen.playlist.name')} clearable />,
    },
    {
      label: () => '播放模式',
      prop: 'play_mode',
      render: () => (
        <el-select placeholder="播放模式">
          <el-option label={t('crud.all')} value="" />
          {playModeOptions.map(option => (
            <el-option key={option.value} label={option.label} value={option.value} />
          ))}
        </el-select>
      ),
    },
    {
      label: () => t('common.status'),
      prop: 'status',
      render: () => (
        <el-select placeholder={t('common.status')}>
          <el-option label={t('crud.all')} value="" />
          {statusOptions.map(option => (
            <el-option key={option.value} label={option.label} value={option.value} />
          ))}
        </el-select>
      ),
    },
  ]
}
