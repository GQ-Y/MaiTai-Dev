import type { MaFormItem } from '@mineadmin/form'
import { contentTypeOptions, statusOptions } from './common'
import MaUploadImage from '@/components/ma-upload-image/index.vue'
import MaUploadFile from '@/components/ma-upload-file/index.vue'
import MaResourcePicker from '@/components/ma-resource-picker/index.vue'
import MaUploadVideo from '@/components/ma-upload-video/index.vue'
import { defineComponent, ref } from 'vue'

export default function getFormItems(formType: string, t: any, formData: any): MaFormItem[] {
  console.log('getFormItems called with formData:', formData)

  const items: MaFormItem[] = [
    // 内容标题
    {
      label: () => t('smartscreen.content.content_title'),
      prop: 'title',
      render: 'input',
      renderProps: {
        placeholder: t('smartscreen.content.content_title'),
        clearable: true,
      },
      itemProps: {
        rules: [
          { required: true, message: t('form.pleaseInput', { msg: t('smartscreen.content.content_title') }) },
        ],
      },
    },

    // 内容类型 - 使用 JSX 方式，默认为网页类型
    {
      label: () => t('smartscreen.content.content_type'),
      prop: 'content_type',
      render: () => (
        <el-select placeholder={t('smartscreen.content.content_type')} style={{ width: '100%' }}>
          {contentTypeOptions.map(option => (
            <el-option key={option.value} label={option.label} value={option.value} />
          ))}
        </el-select>
      ),
      itemProps: {
        defaultValue: 1, // 默认为网页类型
        rules: [
          { required: true, message: t('form.pleaseSelect', { msg: t('smartscreen.content.content_type') }) },
        ],
      },
    },

    // 网页类型 - 使用textarea输入URL，使用show控制显示
    {
      label: () => t('smartscreen.content.content_url'),
      prop: 'content_url',
      render: 'input',
      show: (_, model) => model.content_type === 1 || model.content_type === '1',
      renderProps: {
        type: 'textarea',
        rows: 3,
        placeholder: '请输入网页URL地址，如：https://www.example.com',
        clearable: true,
      },
      itemProps: {
        rules: [
          { required: true, message: '请输入网页URL地址' },
        ],
      },
    },

    // 图片类型 - 内容图片，使用show控制显示
    {
      label: () => '内容图片',
      prop: 'content_url',
      render: () => MaUploadImage,
      show: (_, model) => model.content_type === 2 || model.content_type === '2',
      renderProps: {
        placeholder: '请选择内容图片',
        multiple: false,
      },
    },

    // 图片类型 - 缩略图，使用show控制显示
    {
      label: () => t('smartscreen.content.thumbnail'),
      prop: 'thumbnail',
      render: () => MaUploadImage,
      show: (_, model) => model.content_type === 2 || model.content_type === '2',
      renderProps: {
        placeholder: '请选择缩略图',
        multiple: false,
      },
    },

    // 视频类型 - 视频文件上传和视频URL，二选一
    {
      label: () => '视频文件',
      prop: 'content_url',
      render: () => MaUploadVideo,
      show: (_, model) => model.content_type === 3 || model.content_type === '3',
      renderProps: {
        title: '选择视频文件',
        placeholder: '请选择视频文件',
        multiple: false,
        fileType: ['video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/flv', 'video/mkv', 'video/webm', 'video/m4v'],
        fileSize: 3221225472, // 3GB
        modelValue: formData.content_url,
        'onUpdate:modelValue': (val: string) => {
          formData.content_url = val
          formData.video_url = val // 关键：同步到视频URL
        },
      },
      itemProps: {
        rules: [
          {
            validator: (_: any, value: any, callback: any) => {
              if (!formData.content_url && !formData.video_url) {
                callback('请选择视频文件或填写视频URL')
              } else {
                callback()
              }
            },
            trigger: 'blur',
          },
        ],
      },
    },
    {
      label: () => '视频URL',
      prop: 'video_url',
      render: 'input',
      show: (_, model) => model.content_type === 3 || model.content_type === '3',
      renderProps: {
        placeholder: '请输入视频地址（如 https://...mp4）',
        clearable: true,
      },
      itemProps: {
        rules: [
          {
            validator: (_: any, value: any, callback: any) => {
              if (!formData.content_url && !formData.video_url) {
                callback('请选择视频文件或填写视频URL')
              } else {
                callback()
              }
            },
            trigger: 'blur',
          },
        ],
      },
    },

    // 直播流类型 - 直播流地址，使用show控制显示
    {
      label: () => '直播流地址',
      prop: 'content_url',
      render: 'input',
      show: (_, model) => model.content_type === 4 || model.content_type === '4',
      renderProps: {
        type: 'textarea',
        rows: 3,
        placeholder: '请输入直播流地址，如：rtmp://live.example.com/stream 或 https://example.com/live.m3u8',
        clearable: true,
      },
      itemProps: {
        rules: [
          { required: true, message: '请输入直播流地址' },
        ],
      },
    },

    // 直播流类型 - 直播封面，使用show控制显示
    {
      label: () => '直播封面',
      prop: 'thumbnail',
      render: () => MaUploadImage,
      show: (_, model) => model.content_type === 4 || model.content_type === '4',
      renderProps: {
        placeholder: '请选择直播封面（可选）',
        multiple: false,
      },
    },

    // 音频类型 - 音频文件，使用show控制显示
    {
      label: () => '音频文件',
      prop: 'content_url',
      render: () => MaUploadFile,
      show: (_, model) => model.content_type === 5 || model.content_type === '5',
      renderProps: {
        title: '选择音频文件',
        placeholder: '请选择音频文件',
        multiple: false,
        fileType: ['mp3', 'wav', 'ogg', 'aac', 'flac', 'm4a', 'wma'],
        fileSize: 50 * 1024 * 1024, // 50MB
      },
      itemProps: {
        rules: [
          { required: true, message: '请选择音频文件' },
        ],
      },
    },

    // 音频类型 - 音频封面，使用show控制显示
    {
      label: () => '音频封面',
      prop: 'thumbnail',
      render: () => MaUploadImage,
      show: (_, model) => model.content_type === 5 || model.content_type === '5',
      renderProps: {
        placeholder: '请选择音频封面（可选）',
        multiple: false,
      },
    },

    // 通用字段：播放时长，0表示永久播放
    {
      label: () => t('smartscreen.content.duration'),
      prop: 'duration',
      render: 'inputNumber',
      renderProps: {
        placeholder: '播放时长（0表示永久播放）',
        min: 0,
        max: 3600,
        style: { width: '100%' },
      },
      renderSlots: {
        append: () => '秒',
      },
      itemProps: {
        defaultValue: 0, // 默认永久播放
        rules: [
          { required: true, message: '请输入播放时长' },
          { type: 'number', min: 0, max: 3600, message: '播放时长必须在0-3600秒之间（0表示永久）' },
        ],
      },
    },

    // 通用字段：排序，默认为0
    {
      label: () => t('smartscreen.content.sort_order'),
      prop: 'sort_order',
      render: 'inputNumber',
      renderProps: {
        placeholder: '排序值（数字越小越靠前）',
        min: 0,
        max: 9999,
        style: { width: '100%' },
        precision: 0,
      },
      itemProps: {
        defaultValue: 0, // 默认排序为0
      },
    },

    // 通用字段：状态 - 使用 JSX 方式，默认启用
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
        defaultValue: 1, // 默认启用
      },
    },
  ]

  return items
}
