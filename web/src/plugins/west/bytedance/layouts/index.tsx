/**
 * MineAdmin is committed to providing solutions for quickly building web applications
 * Please view the LICENSE file that was distributed with this source code,
 * For the full copyright and license information.
 * Thank you very much for using MineAdmin.
 *
 * @Author X.Mo<root@imoi.cn>
 * @Link   https://github.com/mineadmin
 */
import { KeepAlive, Transition } from 'vue'
import { RouterView } from 'vue-router'
import MineSearchPanel from './components/search-panel'
import MineMainAside from './components/main-aside'
import MineSubAside from './components/sub-aside'
import MineBars from './components/bars'
import MineFooter from './components/footer'
import MineBackTop from './components/back-top'
import MineToolbar from './components/bars/toolbar'
import '../assets/style/index.scss'
import type { SystemSettings } from '#/global'
import handleResize from '@/utils/handleResize'
import { useBytedanceStore } from '@/plugins/west/bytedance/utils/bytedanceStore'
import { useColorMode } from '@vueuse/core'

export default defineComponent({
  name: 'MineContainer',
  setup() {
    const {
      getSettings,
      showMineHeader,
      isMixedLayout,
      isClassicLayout,
      isBannerLayout,
      isColumnsLayout,
      openGlobalWatermark,
      clearGlobalWatermark,
      getSearchPanelEnable,
      getMobileState,
      getAsideDark,
    } = useSettingStore()
    const colorMode = useColorMode()
    const ocWorkerArea = ref<HTMLElement | null>(null)
    const subAsideEl = ref()
    const keepAliveStore = useKeepAliveStore()
    const appSetting = getSettings('app') as SystemSettings.app
    const menuStore = useMenuStore()
    const bytedanceStore = useBytedanceStore()
    const isScrolled = ref(false)

    watch(() => appSetting.enableWatermark, (v: boolean | undefined) => {
      v && openGlobalWatermark(useUserStore().getUserInfo().username)
      v || clearGlobalWatermark()
      // 设置水印文字
    }, { immediate: true })

    // 处理滚动事件
    const handleScroll = () => {
      // 在这里编写你希望在滚动时执行的代码
      const scrollPosition = ocWorkerArea.value?.scrollTop || 0
      // 如果滚动距离超过60px，修改背景色
      if (scrollPosition >= 60) {
        isScrolled.value = true
      }
      else {
        isScrolled.value = false
      }
    }

    // 组件卸载时移除事件监听器
    onMounted(() => {
      if (menuStore.subMenu.length > 0 && appSetting?.layout === 'columns') {
        menuStore.setSubAsideWidthByDefault()
      }
      else if (menuStore.subMenu.length === 0 && appSetting?.layout === 'mixed') {
        menuStore.setSubAsideWidthByZero()
      }
      else if (appSetting?.layout === 'columns') {
        menuStore.setSubAsideWidthByZero()
      }
      else {
        menuStore.setSubAsideWidthByDefault()
      }
      handleResize(subAsideEl)

      if (ocWorkerArea.value) {
        ocWorkerArea.value.addEventListener('scroll', handleScroll)
      }
    })

    function updateCSSVariables(settings: Record<string, any> = {}) {
      const { bgImage, filter } = settings // 解构赋值，简化代码
      const url = bgImage
        ? `url(${bgImage})`
        : ''

      const blurValue = filter !== undefined // 处理 filter 的默认值
        ? `blur(${filter}px)`
        : 'blur(1px)'

      document.documentElement.style.setProperty('--bg-image', url)
      document.documentElement.style.setProperty('--bg-filter', blurValue)
    }

    // 监听状态变化，动态更新 CSS 变量
    watch(
      () => bytedanceStore.settings,
      (newValue) => {
        updateCSSVariables(newValue)
      },
    )
    return () => (
      <div class="app-container">
        <div
          class={{
            'bg-white shadow-[0_2px_8px_0_rgba(0,_0,_0,_0.04)] relative': isScrolled.value && colorMode.value === 'light',
          }}
        >
          <MineToolbar />
        </div>
        <div class={{
          'mine-wrapper': true,
          'mine-wrapper-full': !showMineHeader() || getMobileState(),
          'mine-wrapper-not-full': showMineHeader() && !getMobileState(),
        }}
        >
          <div class="mine-main">
            <div
              class={{
                'group mine-aside flex rounded-10px': true,
                'bg-white': isColumnsLayout(),
                'w-0 !m-0': getMobileState(), // 保留 getMobileState() 条件
                'asideDark': getAsideDark(),
                'h-[calc(100vh-80px)]': !showMineHeader() || isMixedLayout(),
                'ml-16px': isColumnsLayout(),
              }}
              v-show={!isBannerLayout()}
            >
              {(!isClassicLayout() && !isMixedLayout()) && <MineMainAside />}
              <MineSubAside ref={subAsideEl} />
            </div>
            <div class="co-worker-content">
              <Transition name="mine-aside-animate">
                <MineBars />
              </Transition>
              <div class="oc-worker-area" ref={ocWorkerArea}>
                <RouterView class="router-view">
                  {({ Component }) => (
                    <Transition name={appSetting.pageAnimate} mode="out-in">
                      <KeepAlive include={keepAliveStore.list}>
                        {keepAliveStore.getShowState() && <Component />}
                      </KeepAlive>
                    </Transition>
                  )}
                </RouterView>
              </div>
              <MineFooter />
            </div>
            <MineBackTop />
          </div>

        </div>
        <div class="mine-max-size-exit" onClick={() => useTabStore().exitMaxSizeTab()}>
          <ma-svg-icon name="i-material-symbols:close" size={50} />
        </div>

        <MineSearchPanel v-show={getSearchPanelEnable()} />
      </div>
    )
  },
})
