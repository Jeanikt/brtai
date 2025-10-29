docker-compose up -d redis

docker exec -it brtai-redis-1 redis-cli FLUSHALL

