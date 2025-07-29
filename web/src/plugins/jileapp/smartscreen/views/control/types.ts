/**
 * MineAdmin is committed to providing solutions for quickly building web applications
 * Please view the LICENSE file that was distributed with this source code,
 * For the full copyright and license information.
 * Thank you very much for using MineAdmin.
 *
 * @Author X.Mo<root@imoi.cn>
 * @Link   https://github.com/mineadmin
 */

// 定义设备类型
export interface Device {
  id: number
  device_name: string
  mac_address: string
  display_mode: string
  online: boolean
  disabled?: boolean
  status?: number
  is_online?: boolean
  websocket_status?: string
  current_content?: {
    title: string
    [key: string]: any
  }
  groups?: DeviceGroup[]
}

export interface DeviceGroup {
  id: number
  name: string
  device_count?: number
  devices?: Device[]
  status?: number
  created_at?: string
  updated_at?: string
}

export interface ResponseStruct<T = any> {
  success: boolean
  code: number
  message: string
  data: T
}

export type CheckboxValueType = string | number | boolean

// 定义内容类型
export interface Content {
  id: number
  title: string
  content_type: number
  file_path?: string
  url?: string
  duration?: number
  created_at?: string
  updated_at?: string
}

// 推送表单类型
export interface PushForm {
  content_id?: number
  is_temp: boolean
  duration: number
}

// 模式表单类型
export interface ModeForm {
  display_mode: number
}

// 广播表单类型
export interface BroadcastForm {
  action: string
  message: string
}

// 加载状态类型
export interface LoadingState {
  deviceStatus: boolean
  pushContent: boolean
  switchMode: boolean
  broadcast: boolean
  quickAction: boolean
}
