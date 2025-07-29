// 状态选项
export const statusOptions = [
  { label: '启用', value: 1 },
  { label: '禁用', value: 0 },
]

// 预设颜色选项
export const colorOptions = [
  { label: '蓝色', value: '#1890ff', color: '#1890ff' },
  { label: '绿色', value: '#52c41a', color: '#52c41a' },
  { label: '橙色', value: '#fa8c16', color: '#fa8c16' },
  { label: '红色', value: '#f5222d', color: '#f5222d' },
  { label: '紫色', value: '#722ed1', color: '#722ed1' },
  { label: '青色', value: '#13c2c2', color: '#13c2c2' },
  { label: '粉色', value: '#eb2f96', color: '#eb2f96' },
  { label: '黄色', value: '#fadb14', color: '#fadb14' },
]

// 获取状态文本
export function getStatusText(status: number): string {
  const option = statusOptions.find(item => item.value === status)
  return option?.label || '未知'
}

// 获取状态标签类型
export function getStatusTagType(status: number): 'success' | 'danger' {
  return status === 1 ? 'success' : 'danger'
}

// 获取颜色名称
export function getColorName(color: string): string {
  const option = colorOptions.find(item => item.value === color)
  return option?.label || '自定义'
} 