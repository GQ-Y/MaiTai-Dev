import type { ResponseStruct } from '#/global'

export function page(data: any): Promise<ResponseStruct<any>> {
  return useHttp().get('admin/plugin/smart-screen/device/list', { params: data })
}

export function create(data: any): Promise<ResponseStruct<any>> {
  return useHttp().post('admin/plugin/smart-screen/device', data)
}

export function save(id: number, data: any): Promise<ResponseStruct<any>> {
  return useHttp().put(`admin/plugin/smart-screen/device/${id}`, data)
}

export function deleteByIds(ids: number[]): Promise<ResponseStruct<null>> {
  return useHttp().delete('admin/plugin/smart-screen/device', { data: { ids } })
}

export function activate(id: number): Promise<ResponseStruct<any>> {
  return useHttp().post('admin/plugin/smart-screen/device/activate', { id })
}

export function deactivate(id: number): Promise<ResponseStruct<any>> {
  return useHttp().post('admin/plugin/smart-screen/device/deactivate', { id })
}

export function setContent(deviceId: number, contentId: number | null): Promise<ResponseStruct<any>> {
  return useHttp().post('admin/plugin/smart-screen/device/set-content', {
    device_id: deviceId,
    content_id: contentId,
  })
}

export function setPlaylist(deviceId: number, playlistIds: number[]): Promise<ResponseStruct<any>> {
  return useHttp().post('admin/plugin/smart-screen/device/set-playlist', {
    device_id: deviceId,
    playlist_ids: playlistIds,
  })
}
