import type { ResponseStruct } from '#/global'

export function page(data: any): Promise<ResponseStruct<any>> {
  return useHttp().get('admin/plugin/smart-screen/content/list', { params: data })
}

export function create(data: any): Promise<ResponseStruct<any>> {
  return useHttp().post('admin/plugin/smart-screen/content', data)
}

export function save(id: number, data: any): Promise<ResponseStruct<any>> {
  return useHttp().put(`admin/plugin/smart-screen/content/${id}`, data)
}

export function deleteByIds(ids: number[]): Promise<ResponseStruct<null>> {
  return useHttp().delete('admin/plugin/smart-screen/content', { data: { ids } })
}

export function upload(file: File): Promise<ResponseStruct<any>> {
  const formData = new FormData()
  formData.append('file', file)
  return useHttp().post('admin/plugin/smart-screen/content/upload', formData, {
    headers: {
      'Content-Type': 'multipart/form-data',
    },
  })
}
