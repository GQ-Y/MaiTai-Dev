#!/bin/bash

# MineAdmin 导入并运行脚本

# 检查是否在正确的目录中
if [ ! -d "docker/servers" ]; then
    echo "错误：请在项目根目录运行此脚本"
    exit 1
fi

echo "正在导入 MineAdmin Docker 镜像..."

# 导入所有镜像
echo "导入 redis 镜像..."
docker load -i docker/servers/redis.tar

echo "导入 mysql 镜像..."
docker load -i docker/servers/mysql.tar

echo "导入 nginx web 镜像..."
docker load -i docker/servers/nginx.tar

echo "导入 server 镜像..."
docker load -i docker/servers/server.tar

# 创建必要的目录结构
echo "创建必要的目录结构..."
mkdir -p runtime/logs
mkdir -p storage/uploads
mkdir -p plugin

# 启动服务
echo "启动 MineAdmin 服务..."
docker compose -f docker/docker-compose.yml up -d

echo "MineAdmin 服务已启动！"
echo ""
echo "服务访问地址:"
echo "  后端服务: http://localhost:9501"
echo "  前端服务: http://localhost:9510"
echo "  MySQL: localhost:3306 (用户: mineadmin, 密码: 123456)"
echo "  Redis: localhost:6379 (密码: 123456)"
