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
import { onMounted, ref } from 'vue'

defineOptions({ name: 'welcome' })
const userinfo = useUserStore().getUserInfo()

// 获取当前时间问候语
const getGreeting = () => {
  const hour = new Date().getHours()
  if (hour < 6) return '夜深了'
  if (hour < 12) return '早上好'
  if (hour < 14) return '中午好'
  if (hour < 18) return '下午好'
  if (hour < 22) return '晚上好'
  return '夜深了'
}

// 获取当前日期
const getCurrentDate = () => {
  const now = new Date()
  const options: Intl.DateTimeFormatOptions = {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    weekday: 'long'
  }
  return now.toLocaleDateString('zh-CN', options)
}

// 快速导航数据 - 智慧屏管理
const quickActions = [
  {
    title: '设备管理',
    icon: 'ep:monitor',
    description: '管理智慧屏设备',
    path: '/smartscreen/device'
  },
  {
    title: '内容管理',
    icon: 'ep:document',
    description: '管理播放内容',
    path: '/smartscreen/content'
  },
  {
    title: '播放列表',
    icon: 'ep:list',
    description: '管理播放列表',
    path: '/smartscreen/playlist'
  }
]

// 系统状态数据 - 智慧屏状态（响应式）
const systemStats = ref([
  {
    label: '在线设备',
    value: '加载中...',
    icon: 'ep:monitor',
    color: 'text-blue-500'
  },
  {
    label: '播放内容',
    value: '加载中...',
    icon: 'ep:document',
    color: 'text-green-500'
  }
])

// 加载智慧屏统计数据
const loadSmartScreenStats = async () => {
  try {
    // 使用智慧屏插件的API函数
    const deviceApi = () => useHttp().get('admin/plugin/smart-screen/device/list', { params: { page: 1, pageSize: 9999 } })
    const contentApi = () => useHttp().get('admin/plugin/smart-screen/content/list', { params: { page: 1, pageSize: 9999 } })
    
    // 并行请求设备列表和内容列表
    const [deviceRes, contentRes] = await Promise.all([deviceApi(), contentApi()])

    // 处理设备统计
    if (deviceRes.data?.list) {
      const devices = deviceRes.data.list
      const onlineDevices = devices.filter((device: any) => device.is_online === 1)
      systemStats.value[0].value = `${onlineDevices.length}/${devices.length}`
    }

    // 处理内容统计
    if (contentRes.data?.list) {
      const contents = contentRes.data.list
      const activeContents = contents.filter((content: any) => content.status === 1)
      systemStats.value[1].value = activeContents.length.toString()

      // 统计内容类型数量
      const contentTypes = new Set(activeContents.map((content: any) => content.content_type))
      systemStats.value[2].value = `${contentTypes.size}种类型`
    }
  } catch (error) {
    console.error('加载智慧屏统计数据失败:', error)
    // 设置错误状态
    systemStats.value.forEach(stat => {
      if (stat.value === '加载中...') {
        stat.value = '加载失败'
      }
    })
  }
}

// 页面挂载时加载数据
onMounted(() => {
  loadSmartScreenStats()
})
</script>

<template>
  <div class="welcome-container">
    <!-- 欢迎横幅 -->
    <div class="welcome-banner">
      <div class="flex items-center gap-6">
        <el-avatar :src="userinfo?.avatar" :size="80" class="welcome-avatar">
          <span v-if="!userinfo?.avatar" class="text-3xl font-medium">
            {{ userinfo.username[0].toUpperCase() }}
          </span>
        </el-avatar>
        <div class="welcome-info">
          <h1 class="welcome-title">
            {{ getGreeting() }}，{{ userinfo.username }}！
          </h1>
          <p class="welcome-subtitle">
            {{ getCurrentDate() }}
          </p>
          <p class="welcome-description">
            欢迎使用 智慧屏管理系统，开始您的高效工作之旅
          </p>
        </div>
      </div>
    </div>

         <!-- 快速导航 -->
     <div class="quick-actions">
       <h2 class="section-title">智慧屏管理</h2>
      <div class="actions-grid">
        <div 
          v-for="action in quickActions" 
          :key="action.title"
          class="action-card"
          @click="$router.push(action.path)"
        >
          <div class="action-icon">
            <ma-svg-icon :name="action.icon" :size="24" />
          </div>
          <div class="action-content">
            <h3 class="action-title">{{ action.title }}</h3>
            <p class="action-description">{{ action.description }}</p>
          </div>
        </div>
      </div>
    </div>

         <!-- 系统状态 -->
     <div class="system-stats">
       <h2 class="section-title">智慧屏状态</h2>
      <div class="stats-grid">
        <div 
          v-for="stat in systemStats" 
          :key="stat.label"
          class="stat-card"
        >
          <div class="stat-icon" :class="stat.color">
            <ma-svg-icon :name="stat.icon" :size="20" />
          </div>
          <div class="stat-content">
            <div class="stat-value">{{ stat.value }}</div>
            <div class="stat-label">{{ stat.label }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.welcome-container {
  @apply min-h-screen bg-gray-50 dark:bg-dark-9 p-6;
}

.welcome-banner {
  @apply bg-white dark:bg-dark-8 rounded-2xl p-8 mb-8 shadow-sm;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  position: relative;
  overflow: hidden;
  
  &::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: float 6s ease-in-out infinite;
  }
  
  .welcome-avatar {
    @apply border-4 border-white/20 shadow-lg;
  }
  
  .welcome-info {
    @apply flex-1;
  }
  
  .welcome-title {
    @apply text-3xl font-bold mb-2;
  }
  
  .welcome-subtitle {
    @apply text-lg opacity-90 mb-3;
  }
  
  .welcome-description {
    @apply text-base opacity-80;
  }
}

.section-title {
  @apply text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4;
}

.quick-actions {
  @apply mb-8;
  
  .actions-grid {
    @apply grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4;
  }
  
  .action-card {
    @apply bg-white dark:bg-dark-8 rounded-xl p-6 shadow-sm hover:shadow-md 
           transition-all duration-300 cursor-pointer border border-gray-100 dark:border-dark-6
           hover:border-blue-200 dark:hover:border-blue-800 hover:-translate-y-1;
    
    .action-icon {
      @apply w-12 h-12 bg-blue-50 dark:bg-blue-900/20 rounded-lg flex items-center justify-center
             text-blue-600 dark:text-blue-400 mb-4;
    }
    
    .action-title {
      @apply text-lg font-medium text-gray-800 dark:text-gray-200 mb-2;
    }
    
    .action-description {
      @apply text-sm text-gray-600 dark:text-gray-400;
    }
  }
}

.system-stats {
  .stats-grid {
    @apply grid grid-cols-1 md:grid-cols-3 gap-4;
  }
  
  .stat-card {
    @apply bg-white dark:bg-dark-8 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-dark-6;
    
    .stat-icon {
      @apply w-10 h-10 bg-gray-50 dark:bg-gray-800 rounded-lg flex items-center justify-center mb-4;
    }
    
    .stat-value {
      @apply text-2xl font-bold text-gray-800 dark:text-gray-200 mb-1;
    }
    
    .stat-label {
      @apply text-sm text-gray-600 dark:text-gray-400;
    }
  }
}

@keyframes float {
  0%, 100% { transform: translateY(0px) rotate(0deg); }
  50% { transform: translateY(-20px) rotate(180deg); }
}

// 响应式设计
@media (max-width: 768px) {
  .welcome-container {
    @apply p-4;
  }
  
  .welcome-banner {
    @apply p-6;
    
    .flex {
      @apply flex-col text-center gap-4;
    }
    
    .welcome-title {
      @apply text-2xl;
    }
  }
  
  .actions-grid {
    @apply grid-cols-1 gap-3;
  }
  
  .stats-grid {
    @apply grid-cols-1 gap-3;
  }
}
</style>
