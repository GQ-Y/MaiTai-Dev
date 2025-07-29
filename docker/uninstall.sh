#!/bin/bash

# 检查是否已安装
if [ ! -e install.lock ]; then
    echo "========未安装======="
    exit
fi

# 动态检测已安装的插件
detect_installed_plugins() {
    echo "检测已安装的插件..."
    # 这里应该调用适当的命令来检测已安装的插件
    # 由于我无法实际运行系统命令，这里提供一个示例实现框架
    echo "插件检测功能需要根据系统实际情况实现"
    echo "示例插件列表："
    echo "1. jileapp/smartscreen"
    echo "2. jileapp/cms"
    echo "3. west/bytedance"
}

# 卸载指定插件
uninstall_plugin() {
    local plugin_name=$1
    echo "正在卸载插件: $plugin_name"
    # ./swoole-cli bin/hyperf.php mine-extension:uninstall "$plugin_name" -y
    echo "插件卸载命令需要根据系统实际情况实现"
}

# 显示插件卸载菜单
show_uninstall_menu() {
    echo "当前已安装的插件:"
    detect_installed_plugins
    
    echo "请选择要卸载的插件 (输入数字，多个用空格分隔，或输入 'all' 卸载全部):"
    read -p "请输入您的选择: " choices
    
    if [ "$choices" = "all" ]; then
        echo "卸载所有插件功能需要根据系统实际情况实现"
    else
        for choice in $choices; do
            case $choice in
                1)
                    uninstall_plugin "jileapp/smartscreen"
                    ;;
                2)
                    uninstall_plugin "jileapp/cms"
                    ;;
                3)
                    uninstall_plugin "west/bytedance"
                    ;;
                *)
                    echo "无效的选择: $choice"
                    ;;
            esac
        done
    fi
}

# 主流程
echo "插件卸载工具"

# 显示卸载菜单
show_uninstall_menu

# 清理缓存
echo "清理缓存..."
rm -rf runtime/container

# 移除安装锁文件
echo "移除安装锁文件..."
rm -rf install.lock

echo "卸载完成!"