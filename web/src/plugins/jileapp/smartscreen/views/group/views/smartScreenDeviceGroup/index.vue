<!--
 - MineAdmin is committed to providing solutions for quickly building web applications
 - Please view the LICENSE file that was distributed with this source code,
 - For the full copyright and license information.
 - Thank you very much for using MineAdmin.
 -
 - @Author X.Mo<root@imoi.cn>
 - @Link   https://github.com/mineadmin
-->
<template>
  <div class="mine-layout pt-3">
    <MaProTable ref="proTableRef" :options="options" :schema="schema">
      <template #actions>
        <el-button
          v-auth="['smartscreen:deviceGroup:save']"
          type="primary"
          @click="() => {
            maDrawer.setTitle(t('crud.add'))
            maDrawer.open({ formType: 'add' })
          }"
        >
          {{ t('crud.add') }}
        </el-button>
        <el-button
          v-auth="['plugin:smart-screen:device-group:read']"
          @click="handleViewStats"
        >
          <template #icon>
            <ma-svg-icon name="material-symbols:bar-chart" />
          </template>
          {{ t('smartscreen.group.viewStats') }}
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

<script setup lang="tsx">
import type { MaProTableExpose, MaProTableOptions, MaProTableSchema } from '@mineadmin/pro-table'
import type { UseDrawerExpose } from '@/hooks/useDrawer.ts'
import type { Ref } from 'vue'
import getTableColumns from './data/getTableColumns'
import getSearchItems from './data/getSearchItems'
import Form from './form.vue'
import { page } from '../../api/smartScreenDeviceGroup'
import useDrawer from '@/hooks/useDrawer.ts'
import { useMessage } from '@/hooks/useMessage.ts'
import { ResultCode } from '@/utils/ResultCode.ts'

defineOptions({ name: 'group:smartScreenDeviceGroup' })

const t = useTrans().globalTrans
const proTableRef = ref<MaProTableExpose>() as Ref<MaProTableExpose>
const formRef = ref()
const msg = useMessage()

// 抽屉配置
const maDrawer: UseDrawerExpose = useDrawer({
  // 使用50%宽度，适合内容管理界面
  width: '50%',
  size: '50%',
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
    else if (formType === 'manageDevices') {
      // 设备管理不需要保存操作，直接关闭
      maDrawer.close()
    }
    else if (formType === 'manageContent') {
      // 内容管理不需要保存操作，直接关闭
      maDrawer.close()
      // 刷新列表以更新设备数量等信息
      proTableRef.value.refresh()
    }
    okLoadingState(false)
  },
})

// 参数配置
const options = ref<MaProTableOptions>({
  // 表格距离底部的像素偏移适配
  adaptionOffsetBottom: 161,
  header: {
    mainTitle: () => t('smartscreen.group.title'),
    subTitle: () => t('smartscreen.group.subtitle'),
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

// 查看统计信息
function handleViewStats() {
  msg.info('统计功能正在开发中...')
  // TODO: 实现统计功能
}
</script>

<style scoped lang="scss">
</style> 