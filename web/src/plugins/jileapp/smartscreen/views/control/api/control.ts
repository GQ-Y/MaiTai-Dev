import type { ResponseStruct } from '#/global'

/**
 * 切换显示模式
 */
export function switchMode(data: {
  device_ids: number[]
  display_mode: number
}): Promise<ResponseStruct<any>> {
  return useHttp().post('admin/plugin/smart-screen/control/switch-mode', data)
}

/**
 * 推送内容
 */
export function pushContent(data: {
  device_ids: number[]
  content_id: number
  is_temp?: boolean
  duration?: number
}): Promise<ResponseStruct<any>> {
  return useHttp().post('admin/plugin/smart-screen/control/push-content', data)
}

/**
 * 广播控制
 */
export function broadcast(data: {
  action: string
  device_ids?: number[]
  message?: string
}): Promise<ResponseStruct<any>> {
  return useHttp().post('admin/plugin/smart-screen/control/broadcast', data)
}

/**
 * 获取设备状态
 */
export function getDeviceStatus(data: any): Promise<ResponseStruct<any>> {
  return useHttp().post('admin/plugin/smart-screen/control/device-status', data)
}

/**
 * 获取操作历史
 */
export function getOperationHistory(data: any): Promise<ResponseStruct<any>> {
  return useHttp().post('admin/plugin/smart-screen/control/operation-history', data)
}
