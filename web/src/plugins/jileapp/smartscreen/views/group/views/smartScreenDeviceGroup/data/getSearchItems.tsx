import type { MaSearchItem } from '@mineadmin/search'
import { statusOptions } from './common'

export default function getSearchItems(t: any): MaSearchItem[] {
  return [
    {
      label: () => t('smartscreen.group.name'),
      prop: 'name',
      render: () => <el-input placeholder={t('smartscreen.group.namePlaceholder')} clearable />,
    },
    {
      label: () => t('smartscreen.group.status'),
      prop: 'status',
      render: () => (
        <el-select placeholder={t('smartscreen.group.statusPlaceholder')}>
          <el-option label={t('crud.all')} value="" />
          {statusOptions.map(option => (
            <el-option key={option.value} label={option.label} value={option.value} />
          ))}
        </el-select>
      ),
    },
    {
      label: () => t('smartscreen.group.createdAt'),
      prop: 'created_at',
      render: () => (
        <el-date-picker
          type="daterange"
          range-separator="至"
          start-placeholder={t('smartscreen.group.startDate')}
          end-placeholder={t('smartscreen.group.endDate')}
          format="YYYY-MM-DD"
          value-format="YYYY-MM-DD"
        />
      ),
    },
  ]
} 