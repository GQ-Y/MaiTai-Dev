import type { MaFormItem } from '@mineadmin/form'
import { statusOptions, colorOptions } from './common'

export default function getFormItems(t: any): MaFormItem[] {
  return [
    {
      label: () => t('smartscreen.group.name'),
      prop: 'name',
      render: () => (
        <el-input 
          placeholder={t('smartscreen.group.namePlaceholder')} 
          maxlength={100}
          show-word-limit
          clearable 
        />
      ),
      itemProps: {
        rules: [
          { required: true, message: t('smartscreen.group.nameRequired'), trigger: 'blur' },
          { max: 100, message: t('smartscreen.group.nameMaxLength'), trigger: 'blur' },
        ],
      },
    },
    {
      label: () => t('smartscreen.group.description'),
      prop: 'description',
      render: () => (
        <el-input
          type="textarea"
          placeholder={t('smartscreen.group.descriptionPlaceholder')}
          maxlength={500}
          show-word-limit
          rows={3}
        />
      ),
      itemProps: {
        rules: [
          { max: 500, message: t('smartscreen.group.descriptionMaxLength'), trigger: 'blur' },
        ],
      },
    },
    {
      label: () => t('smartscreen.group.color'),
      prop: 'color',
      render: () => (
        <el-color-picker
          show-alpha={false}
          predefine={colorOptions.map(item => item.value)}
        />
      ),
      itemProps: {
        defaultValue: '#1890ff',
      },
    },
    {
      label: () => t('smartscreen.group.sortOrder'),
      prop: 'sort_order',
      render: () => (
        <el-input-number
          placeholder={t('smartscreen.group.sortOrderPlaceholder')}
          min={0}
          max={999999}
          precision={0}
          controls-position="right"
        />
      ),
      itemProps: {
        defaultValue: 0,
      },
    },
    {
      label: () => t('smartscreen.group.status'),
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