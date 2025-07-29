#!/bin/bash

# 脚本用于导出 MineAdmin 完整 Docker 环境镜像

echo "正在准备导出 MineAdmin Docker 镜像..."
cd /Users/hook/Documents/Code/MineAdmin

# 创建导出目录
EXPORT_DIR="mineadmin-export-$(date +%Y%m%d-%H%M%S)"
mkdir -p "$EXPORT_DIR"

# 导出所有镜像
echo "正在导出所有服务镜像..."

# 导出 redis 镜像
echo "导出 redis 镜像..."
docker pull redis:latest
docker save -o "$EXPORT_DIR/redis.tar" redis:latest

# 导出 mysql 镜像
echo "导出 mysql 镜像..."
docker pull mysql:8.0
docker save -o "$EXPORT_DIR/mysql.tar" mysql:8.0

# 导出 nginx web 镜像
echo "导出 nginx web 镜像..."
docker pull nginx:alpine
docker save -o "$EXPORT_DIR/nginx.tar" nginx:alpine

# 导出 server 镜像
echo "导出 server 镜像..."
docker save -o "$EXPORT_DIR/server.tar" mineadmin-server:latest

# 复制配置文件
echo "正在复制配置文件..."
cp -r docker "$EXPORT_DIR/config"
cp -r web/nginx.conf "$EXPORT_DIR/config/"
cp -r databases/migrations "$EXPORT_DIR/config/database-migrations" 2>/dev/null || echo "警告：未找到数据库迁移文件"
cp -r databases/seeders "$EXPORT_DIR/config/database-seeders" 2>/dev/null || echo "警告：未找到数据库种子文件"

# 创建导入脚本
echo "正在创建导入脚本..."
cat > "$EXPORT_DIR/import.sh" << 'EOF'
#!/bin/bash

# MineAdmin 导入并运行脚本

echo "正在导入 MineAdmin Docker 镜像..."

# 导入所有镜像
echo "导入 redis 镜像..."
docker load -i redis.tar

echo "导入 mysql 镜像..."
docker load -i mysql.tar

echo "导入 nginx web 镜像..."
docker load -i nginx.tar

echo "导入 server 镜像..."
docker load -i server.tar

# 创建必要的目录结构
mkdir -p runtime/logs
mkdir -p storage/uploads
mkdir -p plugin

# 复制配置文件
if [ -d "config" ]; then
    echo "复制配置文件..."
    cp -r config/* ../docker/ 2>/dev/null || echo "警告：无法复制配置文件到 ../docker/"
fi

# 启动服务
echo "启动 MineAdmin 服务..."
cd ..
docker compose -f docker/docker-compose.yml up -d

echo "MineAdmin 服务已启动！"
echo "后端服务地址: http://localhost:9501"
echo "前端服务地址: http://localhost:9510"
echo "MySQL: localhost:3306 (用户: mineadmin, 密码: 123456)"
echo "Redis: localhost:6379 (密码: 123456)"
EOF

chmod +x "$EXPORT_DIR/import.sh"

# 创建 README 文件
echo "正在创建 README 文件..."
cat > "$EXPORT_DIR/README.md" << 'EOF'
# MineAdmin 完整环境导出包

这个目录包含了运行 MineAdmin 所需的所有 Docker 镜像和配置文件。

## 文件说明

- `redis.tar` - Redis 镜像
- `mysql.tar` - MySQL 镜像
- `nginx.tar` - Nginx Web 服务镜像
- `server.tar` - MineAdmin 后端服务镜像
- `config/` - 配置文件目录
- `import.sh` - 导入并运行脚本

## 使用方法

1. 将整个目录复制到目标机器
2. 在目录中运行导入脚本：
   ```bash
   ./import.sh
   ```

## 服务访问地址

- 后端服务: http://localhost:9501
- 前端服务: http://localhost:9510
- MySQL: localhost:3306 (用户: mineadmin, 密码: 123456)
- Redis: localhost:6379 (密码: 123456)
EOF

echo "导出完成！导出包位于: $EXPORT_DIR"