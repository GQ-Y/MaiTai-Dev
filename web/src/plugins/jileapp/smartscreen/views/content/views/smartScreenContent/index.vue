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
import { page } from '../../api/smartScreenContent'
import useDrawer from '@/hooks/useDrawer.ts'
import { useMessage } from '@/hooks/useMessage.ts'
import { ResultCode } from '@/utils/ResultCode.ts'

const uploading = ref(false)
const uploadPercent = ref(0)
const t = useTrans().globalTrans
const proTableRef = ref<MaProTableExpose>() as Ref<MaProTableExpose>
const formRef = ref()
const msg = useMessage()

// 抽屉配置
const maDrawer: UseDrawerExpose = useDrawer({
  // 保存数据
  ok: ({ formType }, okLoadingState: (state: boolean) => void) => {
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
    okLoadingState(false)
  },
})

// 参数配置
const options = ref<MaProTableOptions>({
  // 表格距离底部的像素偏移适配
  adaptionOffsetBottom: 161,
  header: {
    mainTitle: () => t('smartscreen.content.title'),
    subTitle: () => t('smartscreen.content.subtitle'),
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
          v-auth="['smartscreen:content:save']"
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
        <Form ref="formRef"
          :form-type="formType"
          :data="data"
          @uploading="val => { uploading = val }"
          @upload-progress="val => { uploadPercent = val }"
        />
        <el-alert v-if="uploading" type="info" show-icon :title="`视频正在上传...${uploadPercent}%`" />
      </template>
    </component>
  </div>
</template>

<style scoped lang="scss">
</style>
