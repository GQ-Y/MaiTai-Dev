import type { ResponseStruct } from '#/global'

export function page(data: any): Promise<ResponseStruct<any>> {
  return useHttp().get('admin/plugin/smart-screen/playlist-content/list', { params: data })
}

export function create(data: any): Promise<ResponseStruct<any>> {
  return useHttp().post('admin/plugin/smart-screen/playlist-content', data)
}

export function save(id: number, data: any): Promise<ResponseStruct<any>> {
  return useHttp().put(`admin/plugin/smart-screen/playlist-content/${id}`, data)
}

export function deleteByIds(ids: number[]): Promise<ResponseStruct<null>> {
  return useHttp().delete('admin/plugin/smart-screen/playlist-content', { data: { ids } })
}

// 批量更新排序
export function updateSortOrder(data: { id: number, sort_order: number }[]): Promise<ResponseStruct<any>> {
  return useHttp().post('admin/plugin/smart-screen/playlist-content/update-sort', { items: data })
}
