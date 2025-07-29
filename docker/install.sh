#!/bin/bash

# 检查是否已经安装
if [ -e install.lock ]; then
    echo "========已安装======="
    exit
fi

# 查找 composer 的位置并设置为变量
COMPOSER=$(which composer)
if [ -z "$COMPOSER" ]; then
    echo "未找到 composer，请确保已安装 composer"
    exit 1
fi

echo "Composer路径: $COMPOSER"

# 安装 Composer 依赖
install_composer_dependencies() {
    if [ -f "vendor/autoload.php" ]; then
        echo "依赖已安装，跳过 composer install 步骤"
    else
        echo "开始安装Composer依赖..."
        # 使用 swoole-cli 执行 composer install 安装依赖
        ./swoole-cli $COMPOSER install --no-dev -vvv
        echo "Composer依赖安装完成"
    fi
}

# 运行数据库迁移
run_migrations() {
    echo "运行数据库迁移..."
    if command -v swoole-cli &> /dev/null; then
        swoole-cli bin/hyperf.php migrate --force
    elif [ -f "./swoole-cli" ]; then
        ./swoole-cli bin/hyperf.php migrate --force
    else
        php bin/hyperf.php migrate --force
    fi
    echo "数据库迁移完成"
}

# 运行数据库种子填充
run_seeds() {
    echo "运行数据库种子填充..."
    if command -v swoole-cli &> /dev/null; then
        swoole-cli bin/hyperf.php db:seed --force
    elif [ -f "./swoole-cli" ]; then
        ./swoole-cli bin/hyperf.php db:seed --force
    else
        php bin/hyperf.php db:seed --force
    fi
    echo "数据库种子填充完成"
}

# 安装默认插件
install_plugins() {
    echo "安装默认插件..."
    if command -v swoole-cli &> /dev/null; then
        # 安装默认插件，这里以 mine-admin/demo-plugin 为例
        swoole-cli bin/hyperf.php mine-extension:install jileapp/smartscreen -y
    elif [ -f "./swoole-cli" ]; then
        ./swoole-cli bin/hyperf.php mine-extension:install jileapp/smartscreen -y
    else
        php bin/hyperf.php mine-extension:install jileapp/smartscreen -y
    fi
    echo "默认插件安装完成"
}

# 主流程
echo "开始安装过程..."

# 安装 Composer 依赖
install_composer_dependencies

# 运行数据库迁移
run_migrations

# 运行数据库种子填充
run_seeds

# 安装默认插件
install_plugins

echo "清理缓存..."
rm -rf runtime/container

echo "创建安装锁文件..."
echo 1 > install.lock

echo "基础安装完成!"