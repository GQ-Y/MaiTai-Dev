/**
 * MineAdmin is committed to providing solutions for quickly building web applications
 * Please view the LICENSE file that was distributed with this source code,
 * For the full copyright and license information.
 * Thank you very much for using MineAdmin.
 *
 * @Author X.Mo<root@imoi.cn>
 * @Link   https://github.com/mineadmin
 */
import { useNotificationStore } from '../../../../../../jileapp/notification-center/stores/notification'

export default defineComponent({
  name: 'notification',
  setup() {
    const selected = ref<string>('message')
    const router = useRouter()
    const notificationStore = useNotificationStore()

    // 初始化通知中心
    onMounted(() => {
      notificationStore.init()
    })

    // 清理资源
    onBeforeUnmount(() => {
      notificationStore.cleanup()
    })

    // 格式化时间
    const formatTime = (dateStr: string) => {
      const date = new Date(dateStr)
      const now = new Date()
      const diff = now.getTime() - date.getTime()
      
      if (diff < 60000) return '刚刚'
      if (diff < 3600000) return `${Math.floor(diff / 60000)}分钟前`
      if (diff < 86400000) return `${Math.floor(diff / 3600000)}小时前`
      return date.toLocaleDateString()
    }

    // 获取优先级颜色
    const getPriorityColor = (priority: number) => {
      switch (priority) {
        case 3: return 'text-red-5'
        case 2: return 'text-orange-5'
        default: return 'text-gray-5'
      }
    }

    // 获取优先级文本
    const getPriorityText = (priority: number) => {
      switch (priority) {
        case 3: return '紧急'
        case 2: return '重要'
        default: return '普通'
      }
    }

    // 标记消息为已读
    const markAsRead = (id: number, type: 'message' | 'notification' | 'todo') => {
      notificationStore.markMessageAsRead(id, type)
    }

    // 标记所有为已读
    const markAllAsRead = () => {
      const type = selected.value === 'message' ? 'message' 
                 : selected.value === 'notice' ? 'notification' 
                 : 'todo'
      notificationStore.markAllAsRead(type as any)
    }

    // 跳转到列表页面
    const gotoList = () => {
      const routes = {
        message: '/notification-center/messages',
        notice: '/notification-center/notifications',
        todo: '/notification-center/todos'
      }
      router.push(routes[selected.value as keyof typeof routes])
    }

    // 处理待办完成
    const completeTodo = (id: number) => {
      notificationStore.completeTodo(id)
    }

    return () => (
      <div class="hidden lg:block">
        <m-dropdown
          class="max-w-[350px] min-w-[350px]"
          triggers={['click']}
          style="position: relative; top: 3px"
          v-slots={{
            default: () => (
              <div class="relative">
                <ma-svg-icon
                  className="tool-icon"
                  name="heroicons:bell"
                  size={20}
                />
                {notificationStore.hasUnreadItems && (
                  <div class="absolute right-0 top-0 h-4 w-4 flex items-center justify-center rounded-full bg-red-5 text-[10px] text-white font-bold">
                    {notificationStore.stats.totalUnread > 99 ? '99+' : notificationStore.stats.totalUnread}
                  </div>
                )}
                {/* WebSocket连接状态指示器 */}
                <div class={`absolute -right-1 -top-1 h-2 w-2 rounded-full ${
                  notificationStore.isWebSocketConnected ? 'bg-green-5' : 'bg-gray-4'
                }`} />
              </div>
            ),
            popper: () => (
              <div>
                <m-tabs
                  v-model={selected.value}
                  options={[
                    { 
                      icon: 'i-ph:chat-circle-text', 
                      label: `${useTrans('mineAdmin.notification.message')} (${notificationStore.stats.unreadMessages})`, 
                      value: 'message' 
                    },
                    { 
                      icon: 'i-ic:baseline-notifications-none', 
                      label: `${useTrans('mineAdmin.notification.notice')} (${notificationStore.stats.unreadNotifications})`, 
                      value: 'notice' 
                    },
                    { 
                      icon: 'i-pajamas:todo-done', 
                      label: `${useTrans('mineAdmin.notification.todo')} (${notificationStore.stats.pendingTodos})`, 
                      value: 'todo' 
                    },
                  ]}
                />
                <div class="notification-box">
                  {selected.value === 'message' && (
                    <ul class="message-box">
                      {notificationStore.unreadMessages.length === 0 ? (
                        <li class="text-center text-gray-5 py-4">暂无未读消息</li>
                      ) : (
                        notificationStore.unreadMessages.slice(0, 8).map(message => (
                          <li key={message.id} class="cursor-pointer hover:bg-gray-1" onClick={() => markAsRead(message.id, 'message')}>
                            <div class="w-2/12 flex items-start justify-center">
                              <div class="h-8 w-8 rounded-full bg-blue-1 flex items-center justify-center">
                                <ma-svg-icon name="i-ph:chat-circle-text" size={16} class="text-blue-5" />
                              </div>
                            </div>
                            <div class="w-10/12">
                              <div class="flex items-center justify-between">
                                <span class="mr-0.5 text-[rgb(var(--ui-primary))] font-medium">{message.sender_name || '系统'}</span>
                                <span class="text-xs text-gray-5">{formatTime(message.created_at)}</span>
                              </div>
                              <div class="font-medium">{message.title}</div>
                              <div class="mt-1 truncate text-gray-5 dark-text-gray-4 text-sm">
                                {message.content}
                              </div>
                              {message.is_urgent && (
                                <div class="mt-1">
                                  <span class="text-xs bg-red-1 text-red-5 px-2 py-0.5 rounded">紧急</span>
                                </div>
                              )}
                            </div>
                          </li>
                        ))
                      )}
                    </ul>
                  )}
                  {selected.value === 'notice' && (
                    <ul class="notice-box">
                      {notificationStore.unreadNotifications.length === 0 ? (
                        <li class="text-center text-gray-5 py-4">暂无未读通知</li>
                      ) : (
                        notificationStore.unreadNotifications.slice(0, 8).map(notification => (
                          <li key={notification.id} class="cursor-pointer hover:bg-gray-1" onClick={() => markAsRead(notification.id, 'notification')}>
                            <div class="flex items-center justify-between">
                              <span class="w-8/12 truncate font-medium">{notification.title}</span>
                              <span class="text-gray-5 text-xs">{formatTime(notification.created_at)}</span>
                            </div>
                            <div class="flex items-center justify-between mt-1">
                              <span class="text-xs text-gray-5">{notification.sender_name || '系统'}</span>
                              <span class={`text-xs ${getPriorityColor(notification.priority)}`}>
                                {getPriorityText(notification.priority)}
                              </span>
                            </div>
                            <div class="mt-2 truncate text-gray-5 dark-text-gray-4 text-sm">
                              {notification.content}
                            </div>
                            {notification.is_urgent && (
                              <div class="mt-1">
                                <span class="text-xs bg-red-1 text-red-5 px-2 py-0.5 rounded">紧急</span>
                              </div>
                            )}
                          </li>
                        ))
                      )}
                    </ul>
                  )}
                  {selected.value === 'todo' && (
                    <ul class="todo-box">
                      {notificationStore.pendingTodos.length === 0 ? (
                        <li class="text-center text-gray-5 py-4">暂无待办事项</li>
                      ) : (
                        notificationStore.pendingTodos.slice(0, 8).map(todo => (
                          <li key={todo.id} class="hover:bg-gray-1">
                            <div class="flex items-center justify-between">
                              <span class="w-8/12 truncate font-medium">{todo.title}</span>
                              <div class="flex items-center space-x-2">
                                <span class={`text-xs ${getPriorityColor(todo.priority)}`}>
                                  {getPriorityText(todo.priority)}
                                </span>
                                <button 
                                  class="text-xs bg-green-1 text-green-5 px-2 py-0.5 rounded hover:bg-green-2"
                                  onClick={(e) => {
                                    e.stopPropagation()
                                    completeTodo(todo.id)
                                  }}
                                >
                                  完成
                                </button>
                              </div>
                            </div>
                            <div class="flex items-center justify-between mt-1">
                              <span class="text-xs text-gray-5">{todo.sender_name || '系统'}</span>
                              <span class="text-xs text-gray-5">{formatTime(todo.created_at)}</span>
                            </div>
                            <div class="mt-2 truncate text-gray-5 dark-text-gray-4 text-sm">
                              {todo.content}
                            </div>
                            {todo.is_urgent && (
                              <div class="mt-1">
                                <span class="text-xs bg-red-1 text-red-5 px-2 py-0.5 rounded">紧急</span>
                              </div>
                            )}
                          </li>
                        ))
                      )}
                    </ul>
                  )}
                </div>
                <div class="box-footer">
                  <a class="link cursor-pointer" onClick={markAllAsRead}>
                    {useTrans('mineAdmin.notification.allRead')}
                  </a>
                  <a class="link cursor-pointer" onClick={gotoList}>
                    {useTrans('mineAdmin.notification.gotoTheList')}
                  </a>
                </div>
                {/* 连接状态显示 */}
                <div class="px-3 py-1 text-xs text-gray-5 border-t">
                  WebSocket: 
                  <span class={notificationStore.isWebSocketConnected ? 'text-green-5' : 'text-red-5'}>
                    {notificationStore.isWebSocketConnected ? '已连接' : '未连接'}
                  </span>
                </div>
              </div>
            ),
          }}
        />
      </div>
    )
  },
})
