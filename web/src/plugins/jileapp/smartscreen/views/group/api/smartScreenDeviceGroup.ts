import type { ResponseStruct } from '#/global'

/**
 * 获取分组分页列表
 */
export function page(data: any): Promise<ResponseStruct<any>> {
  return useHttp().get('admin/plugin/smart-screen/device-group/list', { params: data })
}

/**
 * 获取所有启用的分组（用于选择器）
 */
export function getAllEnabled(): Promise<ResponseStruct<any>> {
  return useHttp().get('admin/plugin/smart-screen/device-group/all-enabled')
}

/**
 * 获取分组详情，包含设备信息
 */
export function getGroupWithDevices(id: number): Promise<ResponseStruct<any>> {
  return useHttp().get(`admin/plugin/smart-screen/device-group/${id}`)
}

/**
 * 创建分组
 */
export function create(data: any): Promise<ResponseStruct<any>> {
  return useHttp().post('admin/plugin/smart-screen/device-group', data)
}

/**
 * 更新分组
 */
export function save(id: number, data: any): Promise<ResponseStruct<any>> {
  return useHttp().put(`admin/plugin/smart-screen/device-group/${id}`, data)
}

/**
 * 删除分组
 */
export function deleteById(id: number): Promise<ResponseStruct<null>> {
  return useHttp().delete(`admin/plugin/smart-screen/device-group/${id}`)
}

/**
 * 批量删除分组
 */
export function deleteByIds(ids: number[]): Promise<ResponseStruct<null>> {
  return useHttp().delete('admin/plugin/smart-screen/device-group', { data: { ids } })
}

/**
 * 获取分组的设备列表
 */
export function getGroupDevices(groupId: number): Promise<ResponseStruct<any>> {
  return useHttp().get(`admin/plugin/smart-screen/device-group/${groupId}/devices`)
}

/**
 * 获取可添加到分组的设备列表
 */
export function getAvailableDevices(groupId: number): Promise<ResponseStruct<any>> {
  return useHttp().get(`admin/plugin/smart-screen/device-group/${groupId}/available-devices`)
}

/**
 * 添加设备到分组
 */
export function addDevicesToGroup(groupId: number, deviceIds: number[]): Promise<ResponseStruct<any>> {
  return useHttp().post(`admin/plugin/smart-screen/device-group/${groupId}/devices`, {
    device_ids: deviceIds,
  })
}

/**
 * 从分组中移除设备
 */
export function removeDevicesFromGroup(groupId: number, deviceIds: number[]): Promise<ResponseStruct<any>> {
  return useHttp().delete(`admin/plugin/smart-screen/device-group/${groupId}/devices`, {
    data: { device_ids: deviceIds },
  })
}

/**
 * 更新设备在分组中的排序
 */
export function updateDeviceSort(groupId: number, sortData: Array<{ device_id: number, sort_order: number }>): Promise<ResponseStruct<any>> {
  return useHttp().put(`admin/plugin/smart-screen/device-group/${groupId}/devices/sort`, {
    sort_data: sortData,
  })
}

/**
 * 获取设备所属的分组
 */
export function getDeviceGroups(deviceId: number): Promise<ResponseStruct<any>> {
  return useHttp().get(`admin/plugin/smart-screen/device-group/device/${deviceId}/groups`)
}

/**
 * 批量设置设备分组
 */
export function batchSetDeviceGroups(deviceIds: number[], groupIds: number[]): Promise<ResponseStruct<any>> {
  return useHttp().post('admin/plugin/smart-screen/device-group/batch-set-device-groups', {
    device_ids: deviceIds,
    group_ids: groupIds,
  })
}

/**
 * 获取分组统计信息
 */
export function getGroupStats(): Promise<ResponseStruct<any>> {
  return useHttp().get('admin/plugin/smart-screen/device-group/statistics')
}

/**
 * 批量设置分组内设备的显示内容
 */
export function batchSetGroupDevicesContent(groupId: number, contentId: number | null): Promise<ResponseStruct<any>> {
  return useHttp().post(`admin/plugin/smart-screen/device-group/${groupId}/batch-set-content`, {
    content_id: contentId,
  })
}

/**
 * 批量设置分组内设备的播放列表
 */
export function batchSetGroupDevicesPlaylist(groupId: number, playlistIds: number[]): Promise<ResponseStruct<any>> {
  return useHttp().post(`admin/plugin/smart-screen/device-group/${groupId}/batch-set-playlist`, {
    playlist_ids: playlistIds,
  })
} 