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
import PlaylistContentManager from './components/PlaylistContentManager.vue'
import { page } from '../../api/smartScreenPlaylist'
import useDrawer from '@/hooks/useDrawer.ts'
import { useMessage } from '@/hooks/useMessage.ts'
import { ResultCode } from '@/utils/ResultCode.ts'

defineOptions({ name: 'playlist:smartScreenPlaylist' })

const t = useTrans().globalTrans
const proTableRef = ref<MaProTableExpose>() as Ref<MaProTableExpose>
const formRef = ref()
const contentManagerRef = ref()
const msg = useMessage()

// 表单抽屉配置
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

// 内容管理抽屉配置
const contentDrawer: UseDrawerExpose = useDrawer({
  width: '80%', // 大抽屉，占屏幕宽度80%
  ok: () => {
    // 内容管理完成后关闭抽屉
    contentDrawer.close()
    // 可以选择性地刷新主表格数据
    proTableRef.value.refresh()
  },
  okText: '完成',
  cancelText: '关闭',
})

// 参数配置
const options = ref<MaProTableOptions>({
  // 表格距离底部的像素偏移适配
  adaptionOffsetBottom: 161,
  header: {
    mainTitle: () => '播放列表管理',
    subTitle: () => '管理智慧屏播放列表，设置播放模式和内容顺序',
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
  // 表格列，传入内容管理抽屉
  tableColumns: getTableColumns(maDrawer, proTableRef, t, contentDrawer),
})
</script>

<template>
  <div class="mine-layout pt-3">
    <MaProTable ref="proTableRef" :options="options" :schema="schema">
      <template #actions>
        <el-button
          v-auth="['smartscreen:playlist:save']"
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

    <!-- 播放列表表单抽屉 -->
    <component :is="maDrawer.Drawer">
      <template #default="{ formType, data }">
        <Form ref="formRef" :form-type="formType" :data="data" />
      </template>
    </component>

    <!-- 播放列表内容管理抽屉 -->
    <component :is="contentDrawer.Drawer">
      <template #default="{ data }">
        <PlaylistContentManager 
          ref="contentManagerRef"
          :playlist-id="data?.playlistId || 0"
          :playlist-name="data?.playlistName || ''"
        />
      </template>
    </component>
  </div>
</template>

<style scoped lang="scss">
</style>
