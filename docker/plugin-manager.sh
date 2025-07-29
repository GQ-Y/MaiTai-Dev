#!/bin/bash

# 插件管理脚本

# 获取脚本所在目录
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

# 切换到项目根目录
cd "$PROJECT_DIR" || exit 1

# 动态检测可用插件
detect_available_plugins() {
    local plugins=()
    if [ -d "plugin" ]; then
        while IFS= read -r -d '' vendor_dir; do
            # 跳过隐藏目录
            [[ $(basename "$vendor_dir") == .* ]] && continue
            
            vendor_name=$(basename "$vendor_dir")
            while IFS= read -r -d '' plugin_dir; do
                # 跳过隐藏目录
                [[ $(basename "$plugin_dir") == .* ]] && continue
                
                plugin_name=$(basename "$plugin_dir")
                plugins+=("$vendor_name/$plugin_name")
            done < <(find "$vendor_dir" -maxdepth 1 -type d -not -path "$vendor_dir" -print0 2>/dev/null)
        done < <(find "plugin" -maxdepth 1 -type d -not -path "plugin" -print0 2>/dev/null)
    fi
    
    echo "${plugins[@]}"
}

# 动态检测已安装插件（需要根据实际系统实现）
# 这里需要根据 MineAdmin 系统的具体实现来检测已安装的插件
detect_installed_plugins() {
    # 示例实现 - 实际使用时需要根据系统情况修改
    echo ""  # 返回空列表，实际实现时应检测已安装的插件
}

# 安装插件
install_plugin() {
    local plugin_name=$1
    echo "正在安装插件: $plugin_name"
    # 确保使用正确的路径执行命令
    if [ -f "./swoole-cli" ]; then
        ./swoole-cli bin/hyperf.php mine-extension:install "$plugin_name" -y
    else
        php bin/hyperf.php mine-extension:install "$plugin_name" -y
    fi
}

# 卸载插件
uninstall_plugin() {
    local plugin_name=$1
    echo "正在卸载插件: $plugin_name"
    # 确保使用正确的路径执行命令
    if [ -f "./swoole-cli" ]; then
        ./swoole-cli bin/hyperf.php mine-extension:uninstall "$plugin_name" -y
    else
        php bin/hyperf.php mine-extension:uninstall "$plugin_name" -y
    fi
}

# 显示可用插件菜单
show_install_menu() {
    local plugins=($(detect_available_plugins))
    
    if [ ${#plugins[@]} -eq 0 ]; then
        echo "未找到任何可用插件"
        return 1
    fi
    
    echo "可用插件列表:"
    for i in "${!plugins[@]}"; do
        echo "$((i+1)). ${plugins[i]}"
    done
    
    echo ""
    echo "请选择要安装的插件 (输入数字，多个用空格分隔，或输入 'all' 安装全部):"
    read -p "请输入您的选择: " choices
    
    if [ "$choices" = "all" ]; then
        for plugin in "${plugins[@]}"; do
            install_plugin "$plugin"
        done
    else
        for choice in $choices; do
            if [[ $choice =~ ^[0-9]+$ ]] && [ $choice -ge 1 ] && [ $choice -le ${#plugins[@]} ]; then
                install_plugin "${plugins[$((choice-1))]}"
            else
                echo "无效的选择: $choice"
            fi
        done
    fi
}

# 显示已安装插件菜单
show_uninstall_menu() {
    # 这里需要根据实际系统实现来检测已安装的插件
    echo "注意：插件卸载功能需要根据系统实际情况实现"
    echo "当前版本仅显示示例插件列表"
    
    echo "示例插件列表:"
    echo "1. jileapp/smartscreen"
    echo "2. jileapp/cms"
    echo "3. west/bytedance"
    
    echo ""
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

# 主菜单
show_main_menu() {
    echo "========================="
    echo "MineAdmin 插件管理工具"
    echo "========================="
    echo "1. 安装插件"
    echo "2. 卸载插件"
    echo "3. 退出"
    echo ""
    
    read -p "请选择操作 [1-3]: " choice
    
    case $choice in
        1)
            show_install_menu
            ;;
        2)
            show_uninstall_menu
            ;;
        3)
            echo "退出插件管理工具"
            exit 0
            ;;
        *)
            echo "无效选择，请重新输入"
            show_main_menu
            ;;
    esac
}

# 主程序入口
main() {
    if [ ! -f "install.lock" ]; then
        echo "系统尚未安装，请先运行安装脚本"
        exit 1
    fi
    
    # 检查是否在 Docker 容器中运行
    if [ -f /.dockerenv ]; then
        echo "在 Docker 容器中运行插件管理工具"
    else
        echo "在宿主机中运行插件管理工具"
    fi
    
    show_main_menu
}

# 执行主程序
main "$@"