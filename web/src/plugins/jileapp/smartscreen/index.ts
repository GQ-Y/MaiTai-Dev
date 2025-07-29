import type { App } from 'vue'
import type { Plugin } from '#/global'

const pluginConfig: Plugin.PluginConfig = {
  install(_app: App) {},
  config: {
    enable: true,
    info: {
      name: 'jileapp/smartscreen',
      version: '1.0.0',
      author: 'GQ-Y',
      description: '智慧屏管理插件',
      order: 2,
    },
  },
  hooks: {
    start: (_config) => {},
    setup: () => {},
    login: (_formInfo) => {},
    logout: () => {},
    getUserInfo: (userInfo) => {
      console.log(userInfo)
    },
  },
  views: [
    {
      name: 'smartscreen:index',
      path: '/smartscreen',
      meta: {
        title: '智慧屏管理',
        i18n: 'smartscreen.menu.smartscreen',
        icon: 'ep:monitor',
        // badge: () => '新',
        type: 'M',
        hidden: false,
        breadcrumbEnable: true,
        copyright: true,
        cache: true,
      },
      children: [
        {
          name: 'device:smartScreenDevice',
          path: '/smartscreen/device',
          meta: {
            title: '设备管理',
            i18n: 'smartscreen.menu.device',
            icon: 'ep:monitor',
            type: 'M',
            hidden: false,
            breadcrumbEnable: true,
            copyright: true,
            cache: true,
          },
          component: () => import('./views/device/views/smartScreenDevice/index.vue'),
        },
        {
          name: 'group:smartScreenDeviceGroup',
          path: '/smartscreen/group',
          meta: {
            title: '分组管理',
            i18n: 'smartscreen.menu.group',
            icon: 'ep:collection',
            type: 'M',
            hidden: false,
            breadcrumbEnable: true,
            copyright: true,
            cache: true,
          },
          component: () => import('./views/group/views/smartScreenDeviceGroup/index.vue'),
        },
        {
          name: 'content:smartScreenContent',
          path: '/smartscreen/content',
          meta: {
            title: '内容管理',
            i18n: 'smartscreen.menu.content',
            icon: 'ep:document',
            type: 'M',
            hidden: false,
            breadcrumbEnable: true,
            copyright: true,
            cache: true,
          },
          component: () => import('./views/content/views/smartScreenContent/index.vue'),
        },
        {
          name: 'playlist:smartScreenPlaylist',
          path: '/smartscreen/playlist',
          meta: {
            title: '播放列表管理',
            i18n: 'smartscreen.menu.playlist',
            icon: 'ep:list',
            type: 'M',
            hidden: false,
            breadcrumbEnable: true,
            copyright: true,
            cache: true,
          },
          component: () => import('./views/playlist/views/smartScreenPlaylist/index.vue'),
        },
        {
          name: 'control:smartScreenControl',
          path: '/smartscreen/control',
          meta: {
            title: '显示控制',
            i18n: 'smartscreen.menu.control',
            icon: 'ep:operation',
            type: 'M',
            hidden: true,
            breadcrumbEnable: true,
            copyright: true,
            cache: true,
          },
          component: () => import('./views/control/index.vue'),
        },
      ],
    },
  ],
}

export default pluginConfig
