import type { ResponseStruct } from '#/global'

export function page(data: any): Promise<ResponseStruct<any>> {
  return useHttp().get('admin/plugin/smart-screen/playlist/list', { params: data })
}

export function create(data: any): Promise<ResponseStruct<any>> {
  return useHttp().post('admin/plugin/smart-screen/playlist', data)
}

export function save(id: number, data: any): Promise<ResponseStruct<any>> {
  return useHttp().put(`admin/plugin/smart-screen/playlist/${id}`, data)
}

export function deleteByIds(ids: number[]): Promise<ResponseStruct<null>> {
  return useHttp().delete('admin/plugin/smart-screen/playlist', { data: { ids } })
}
