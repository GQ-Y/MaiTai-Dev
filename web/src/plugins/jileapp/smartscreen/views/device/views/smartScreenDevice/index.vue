<!--
 - MineAdmin is committed to providing solutions for quickly building web applications
 - Please view the LICENSE file that was distributed with this source code,
 - For the full copyright and license information.
 - Thank you very much for using MineAdmin.
 -
 - @Author X.Mo<root@imoi.cn>
 - @Link   https://github.com/mineadmin
-->
<script setup lang="tsx">
import type { MaProTableExpose, MaProTableOptions, MaProTableSchema } from '@mineadmin/pro-table'
import type { UseDrawerExpose } from '@/hooks/useDrawer.ts'
import type { Ref } from 'vue'
import getTableColumns from './data/getTableColumns'
import getSearchItems from './data/getSearchItems'
import Form from './form.vue'
import { page } from '../../api/smartScreenDevice'
import useDrawer from '@/hooks/useDrawer.ts'
import { useMessage } from '@/hooks/useMessage.ts'
import { ResultCode } from '@/utils/ResultCode.ts'

defineOptions({ name: 'device:smartScreenDevice' })

const t = useTrans().globalTrans
const proTableRef = ref<MaProTableExpose>() as Ref<MaProTableExpose>
const formRef = ref()
const msg = useMessage()

// 抽屉配置
const maDrawer: UseDrawerExpose = useDrawer({
  // 抽屉尺寸配置
  width: (formType: string) => {
    // 设置内容时使用更大的宽度
    return formType === 'setContent' ? '800px' : '600px'
  },
  // 保存数据
  ok: ({ formType, data: _data }, okLoadingState: (state: boolean) => void) => {
    okLoadingState(true)
    if (['add', 'edit'].includes(formType)) {
      // 根据表单类型调用相应方法
      switch (formType) {
        // 新增
        case 'add':
          formRef.value.add().then((res: any) => {
            res.code === ResultCode.SUCCESS ? msg.success(t('crud.createSuccess')) : msg.error(res.message)
            maDrawer.close()
            proTableRef.value.refresh()
          }).catch((err: any) => {
            msg.alertError(err)
          })
          break
        // 修改
        case 'edit':
          formRef.value.edit().then((res: any) => {
            res.code === ResultCode.SUCCESS ? msg.success(t('crud.updateSuccess')) : msg.error(res.message)
            maDrawer.close()
            proTableRef.value.refresh()
          }).catch((err: any) => {
            msg.alertError(err)
          })
          break
      }
    }
    else if (formType === 'setContent') {
      // 设置内容
      formRef.value.setDeviceContent().then((res: any) => {
        res.code === ResultCode.SUCCESS ? msg.success('内容设置成功') : msg.error(res.message)
        maDrawer.close()
        proTableRef.value.refresh()
      }).catch((err: any) => {
        msg.alertError(err)
      })
    }
    else if (formType === 'setPlaylist') {
      // 设置播放列表
      formRef.value.setDevicePlaylist().then((res: any) => {
        res.code === ResultCode.SUCCESS ? msg.success('播放列表设置成功') : msg.error(res.message)
        maDrawer.close()
        proTableRef.value.refresh()
      }).catch((err: any) => {
        msg.alertError(err)
      })
    }
    okLoadingState(false)
  },
})

// 参数配置
const options = ref<MaProTableOptions>({
  // 表格距离底部的像素偏移适配
  adaptionOffsetBottom: 161,
  header: {
    mainTitle: () => t('smartscreen.device.title'),
    subTitle: () => t('smartscreen.device.subtitle'),
  },
  // 请求配置
  requestOptions: {
    api: page,
  },
})

// 架构配置
const schema = ref<MaProTableSchema>({
  // 搜索项
  searchItems: getSearchItems(t),
  // 表格列
  tableColumns: getTableColumns(maDrawer, proTableRef, t),
})
</script>

<template>
  <div class="mine-layout pt-3">
    <MaProTable ref="proTableRef" :options="options" :schema="schema">
      <template #actions>
        <el-button
          v-auth="['smartscreen:device:save']"
          type="primary"
          @click="() => {
            maDrawer.setTitle(t('crud.add'))
            maDrawer.open({ formType: 'add' })
          }"
        >
          {{ t('crud.add') }}
        </el-button>
      </template>
    </MaProTable>

    <component :is="maDrawer.Drawer">
      <template #default="{ formType, data }">
        <Form ref="formRef" :form-type="formType" :data="data" />
      </template>
    </component>
  </div>
</template>

<style scoped lang="scss">
</style>
