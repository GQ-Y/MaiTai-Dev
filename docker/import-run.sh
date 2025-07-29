#!/bin/bash

# 脚本用于导入并运行 MineAdmin Docker 镜像

# 检查是否提供了镜像文件参数
if [ $# -eq 0 ]; then
    echo "用法: $0 <image-file.tar> [project-directory]"
    echo "示例: $0 mineadmin-server.tar /path/to/project"
    exit 1
fi

IMAGE_FILE=$1
PROJECT_DIR=${2:-"."}

# 检查镜像文件是否存在
if [ ! -f "$IMAGE_FILE" ]; then
    echo "错误：镜像文件 $IMAGE_FILE 不存在"
    exit 1
fi

# 创建项目目录（如果不存在）
mkdir -p "$PROJECT_DIR"

# 导入镜像
echo "正在导入镜像 $IMAGE_FILE..."
docker load -i "$IMAGE_FILE"

# 获取导入的镜像ID和标签
IMPORTED_IMAGE=$(docker images | grep "mineadmin_server" | head -n 1)
if [ -z "$IMPORTED_IMAGE" ]; then
    echo "错误：导入镜像失败"
    exit 1
fi

IMAGE_ID=$(echo $IMPORTED_IMAGE | awk '{print $3}')
IMAGE_TAG=$(echo $IMPORTED_IMAGE | awk '{print $2}')

echo "成功导入镜像: $IMAGE_ID:$IMAGE_TAG"

# 复制必要的文件到项目目录
echo "正在复制配置文件到项目目录..."
cp -r docker-compose-export/* "$PROJECT_DIR/" 2>/dev/null || echo "警告：未找到导出的 docker-compose 配置"

# 进入项目目录
cd "$PROJECT_DIR"

# 如果没有 docker-compose.yml，则创建一个简化版本
if [ ! -f "docker-compose.yml" ]; then
    echo "正在创建 docker-compose.yml..."
    cat > docker-compose.yml << 'EOF'
name: mineadmin

volumes:
  redis_data:
  mysql_data:

services:
  redis:
    image: redis:latest
    ports:
      - "6379:6379"
    volumes:
      - redis_data:/data
    command: redis-server --appendonly yes --requirepass 123456
    deploy:
      resources:
        limits:
          memory: 1G
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      interval: 10s
      timeout: 5s
      retries: 3
    environment:
      - TZ=Asia/Shanghai

  mysql:
    image: mysql:8.0
    volumes:
      - mysql_data:/var/lib/mysql
      - ./init.sql:/docker-entrypoint-initdb.d/init.sql
    ports:
      - "3306:3306"
    command: --default-authentication-plugin=mysql_native_password
    environment:
      MYSQL_ROOT_PASSWORD: 123456
      MYSQL_DATABASE: mineadmin
      MYSQL_USER: mineadmin
      MYSQL_PASSWORD: 123456
      MYSQL_CHARACTER_SET_SERVER: utf8mb4
      MYSQL_COLLATION_SERVER: utf8mb4_unicode_ci
      TZ: Asia/Shanghai
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      interval: 10s
      timeout: 5s
      retries: 3

  server:
    image: REPLACE_WITH_IMAGE_ID
    volumes:
      - ./runtime/logs:/opt/www/runtime/logs
      - ./storage/uploads:/opt/www/storage/uploads
      - ./plugin:/opt/www/plugin
    working_dir: /opt/www
    ports:
      - "9501:9501"   # HTTP服务端口
      - "9502:9502"   # WebSocket服务端口
      - "9509:9509"   # 通知WebSocket服务端口
    environment:
      - TZ=Asia/Shanghai
      - APP_NAME=mineadmin
    depends_on:
      mysql:
        condition: service_healthy
      redis:
        condition: service_healthy
    command: /opt/www/docker/start.sh

  web:
    image: nginx:alpine
    ports:
      - "9510:80"
    volumes:
      - ./web/dist:/usr/share/nginx/html:ro
      - ./web/nginx.conf:/etc/nginx/conf.d/default.conf:ro
    depends_on:
      - server
EOF

    # 替换镜像ID
    sed -i '' "s/REPLACE_WITH_IMAGE_ID/$IMAGE_ID:$IMAGE_TAG/g" docker-compose.yml
fi

echo "启动服务..."
docker compose up -d

echo "服务已启动！"
echo "后端服务地址: http://localhost:9501"
echo "前端服务地址: http://localhost:9510"