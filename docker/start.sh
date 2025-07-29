#!/bin/bash

# 启动脚本：处理后端初始化和依赖安装

echo "正在启动MineAdmin初始化进程..."

# 设置时区
ln -sf /usr/share/zoneinfo/Asia/Shanghai /etc/localtime

# 检查是否已经安装了Composer依赖
if [ ! -f "/opt/www/vendor/autoload.php" ]; then
    echo "正在安装Composer依赖..."
    cd /opt/www
    composer install --no-dev -vvv
    echo "Composer依赖安装完成。"
else
    echo "Composer依赖已安装，跳过安装步骤。"
fi

# 等待数据库准备就绪
echo "等待数据库准备就绪..."
sleep 3

# 检查是否已经运行过完整的安装过程
if [ ! -f "/opt/www/runtime/.initialized" ]; then
    echo "正在运行完整安装..."
    
    # 运行安装脚本（安装插件等）
    echo "正在运行安装脚本..."
    bash /opt/www/docker/install.sh
    
    # 标记已完全初始化
    touch /opt/www/runtime/.initialized
    echo "完整初始化完成。"
else
    echo "已完成完整初始化，跳过完整安装步骤。"
fi

# 启动后端服务
echo "正在启动后端服务..."
cd /opt/www

# 检查swoole-cli是否存在，优先使用系统路径中的swoole-cli，如果不存在则使用php
if command -v swoole-cli &> /dev/null; then
    swoole-cli -d "swoole.use_shortname='Off'" bin/hyperf.php start
elif [ -f "./swoole-cli" ]; then
    ./swoole-cli -d "swoole.use_shortname='Off'" bin/hyperf.php start
else
    php bin/hyperf.php start
fi