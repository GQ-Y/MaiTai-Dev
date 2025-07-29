import type { MaSearchItem } from '@mineadmin/search'
import { contentTypeOptions, statusOptions } from './common'

export default function getSearchItems(t: any): MaSearchItem[] {
  return [
    {
      label: () => t('smartscreen.content.content_title'),
      prop: 'title',
      render: () => <el-input placeholder={t('smartscreen.content.content_title')} clearable />,
    },
    {
      label: () => t('smartscreen.content.content_type'),
      prop: 'content_type',
      render: () => (
        <el-select placeholder={t('smartscreen.content.content_type')}>
          <el-option label={t('crud.all')} value="" />
          {contentTypeOptions.map(option => (
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
