#!/bin/bash

# 脚本用于导出 MineAdmin Docker 镜像

echo "正在构建 MineAdmin Docker 镜像..."
cd /Users/hook/Documents/Code/MineAdmin

# 构建服务端镜像
docker compose -f docker/docker-compose.yml build server

# 获取镜像ID
IMAGE_ID=$(docker images | grep "mineadmin_server" | awk '{print $3}' | head -n 1)

if [ -z "$IMAGE_ID" ]; then
    echo "错误：未找到 MineAdmin 服务端镜像"
    exit 1
fi

echo "找到镜像 ID: $IMAGE_ID"

# 导出镜像为 tar 文件
echo "正在导出镜像到 mineadmin-server.tar..."
docker save -o mineadmin-server.tar $IMAGE_ID

echo "镜像导出完成：mineadmin-server.tar"

# 同时导出完整的 docker-compose 配置
cp -r docker docker-compose-export
echo "Docker Compose 配置已导出到 docker-compose-export 目录"