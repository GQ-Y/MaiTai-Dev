#!/bin/bash

# 脚本用于导出 MineAdmin 完整 Docker 环境镜像

echo "正在准备导出 MineAdmin Docker 镜像..."
cd /Users/hook/Documents/Code/MineAdmin

# 创建导出目录（如果不存在）
mkdir -p docker/servers

# 导出所有镜像到 servers 目录
echo "正在导出所有服务镜像到 docker/servers 目录..."

# 导出 redis 镜像
echo "导出 redis 镜像..."
docker save -o docker/servers/redis.tar redis:latest

# 导出 mysql 镜像
echo "导出 mysql 镜像..."
docker save -o docker/servers/mysql.tar mysql:8.0

# 导出 nginx web 镜像
echo "导出 nginx web 镜像..."
docker save -o docker/servers/nginx.tar nginx:alpine

# 导出 server 镜像
echo "导出 server 镜像..."
docker save -o docker/servers/server.tar mineadmin-server:latest

echo "所有镜像已导出到 docker/servers 目录"

# 创建导入脚本
echo "正在创建导入脚本..."
cat > docker/import.sh << 'EOF'
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
EOF

chmod +x docker/import.sh

# 创建使用手册
echo "正在创建使用手册..."
cat > docker/使用手册.md << 'EOF'
# MineAdmin Docker 镜像导出与导入使用手册

本文档说明如何导出和导入 MineAdmin 的完整 Docker 环境，以便在新的宿主机上快速部署和运行。

## 导出镜像（在源机器上执行）

导出操作已经完成，所有镜像都保存在 `docker/servers` 目录中：
- `redis.tar` - Redis 镜像
- `mysql.tar` - MySQL 镜像
- `nginx.tar` - Nginx Web 服务镜像
- `server.tar` - MineAdmin 后端服务镜像

## 导入并运行（在目标机器上执行）

### 1. 环境准备

确保目标机器已安装：
- Docker Engine
- Docker Compose

### 2. 传输文件

将整个项目目录复制到目标机器。

### 3. 导入镜像并启动服务

在项目根目录执行导入脚本：

```bash
./docker/import.sh
```

该脚本会自动完成以下操作：
1. 导入所有 Docker 镜像
2. 创建必要的目录结构
3. 启动所有服务

### 4. 访问服务

服务启动后，可以通过以下地址访问：

- 后端服务: http://localhost:9501
- 前端服务: http://localhost:9510
- MySQL: localhost:3306 (用户: mineadmin, 密码: 123456)
- Redis: localhost:6379 (密码: 123456)

## 手动导入步骤（可选）

如果不想使用自动脚本，也可以手动执行以下步骤：

```bash
# 导入镜像
docker load -i docker/servers/redis.tar
docker load -i docker/servers/mysql.tar
docker load -i docker/servers/nginx.tar
docker load -i docker/servers/server.tar

# 创建目录
mkdir -p runtime/logs storage/uploads plugin

# 启动服务
docker compose -f docker/docker-compose.yml up -d
```

## 常见问题

### 1. 导入时提示镜像已存在

可以先删除现有镜像再导入：
```bash
docker rmi redis:latest mysql:8.0 nginx:alpine mineadmin-server:latest
```

### 2. 服务启动失败

检查 Docker 和 Docker Compose 是否正常工作：
```bash
docker info
docker compose version
```
EOF

echo "导出完成！"
echo "请将整个项目目录复制到目标机器，然后在目标机器上运行 docker/import.sh 脚本。"