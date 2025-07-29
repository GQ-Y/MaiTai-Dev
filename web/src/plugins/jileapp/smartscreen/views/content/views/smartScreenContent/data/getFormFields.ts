import { contentTypeOptions, statusOptions } from './common'

export default function getFormFields(t: any) {
  return [
    {
      prop: 'title',
      label: t('smartscreen.content.content_title'),
      type: 'input' as const,
      placeholder: t('smartscreen.content.content_title'),
      required: true,
    },
    {
      prop: 'content_type',
      label: t('smartscreen.content.content_type'),
      type: 'select' as const,
      placeholder: t('smartscreen.content.content_type'),
      options: contentTypeOptions,
      required: true,
    },
    {
      prop: 'content_url',
      label: t('smartscreen.content.content_url'),
      type: 'custom' as const,
      customRender: 'content_url_custom',
    },
    {
      prop: 'thumbnail',
      label: t('smartscreen.content.thumbnail'),
      type: 'custom' as const,
      show: (formData: any) => formData.content_type === 2 || formData.content_type === 3,
      customRender: 'thumbnail_custom',
    },
    {
      prop: 'duration',
      label: t('smartscreen.content.duration'),
      type: 'inputNumber' as const,
      placeholder: t('smartscreen.content.duration'),
      min: 1,
      max: 3600,
      suffix: '秒',
    },
    {
      prop: 'sort_order',
      label: t('smartscreen.content.sort_order'),
      type: 'inputNumber' as const,
      placeholder: t('smartscreen.content.sort_order'),
      min: 0,
    },
    {
      prop: 'status',
      label: t('common.status'),
      type: 'radio' as const,
      options: statusOptions,
    },
  ]
}
