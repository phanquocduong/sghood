#!/bin/bash

# Laravel Queue Management Script
# Usage: ./queue.sh [start|stop|restart|status|monitor]

ARTISAN_PATH="php artisan"
PID_FILE="/tmp/laravel-queue.pid"

case "$1" in
    start)
        echo "Starting Laravel Queue Worker..."
        nohup $ARTISAN_PATH queue:work --queue=default --sleep=3 --tries=3 --max-time=3600 > storage/logs/queue.log 2>&1 &
        echo $! > $PID_FILE
        echo "Queue worker started with PID: $(cat $PID_FILE)"
        ;;

    stop)
        if [ -f $PID_FILE ]; then
            PID=$(cat $PID_FILE)
            if ps -p $PID > /dev/null; then
                kill $PID
                rm $PID_FILE
                echo "Queue worker stopped"
            else
                echo "Queue worker not running"
                rm $PID_FILE
            fi
        else
            echo "PID file not found. Queue worker may not be running"
        fi
        ;;

    restart)
        echo "Restarting Laravel Queue Worker..."
        $ARTISAN_PATH queue:restart
        $0 stop
        sleep 2
        $0 start
        ;;

    status)
        if [ -f $PID_FILE ]; then
            PID=$(cat $PID_FILE)
            if ps -p $PID > /dev/null; then
                echo "Queue worker is running with PID: $PID"
            else
                echo "Queue worker is not running (stale PID file)"
            fi
        else
            echo "Queue worker is not running"
        fi
        ;;

    monitor)
        echo "Monitoring queue status..."
        echo "----------------------------------------"
        echo "Jobs in queue:"
        $ARTISAN_PATH queue:monitor
        echo "----------------------------------------"
        echo "Failed jobs:"
        $ARTISAN_PATH queue:failed
        ;;

    retry-all)
        echo "Retrying all failed jobs..."
        $ARTISAN_PATH queue:retry all
        ;;

    clear-failed)
        echo "Clearing all failed jobs..."
        $ARTISAN_PATH queue:flush
        ;;

    *)
        echo "Usage: $0 {start|stop|restart|status|monitor|retry-all|clear-failed}"
        echo ""
        echo "Commands:"
        echo "  start       - Start queue worker"
        echo "  stop        - Stop queue worker"
        echo "  restart     - Restart queue worker"
        echo "  status      - Check worker status"
        echo "  monitor     - Monitor queue status"
        echo "  retry-all   - Retry all failed jobs"
        echo "  clear-failed- Clear all failed jobs"
        exit 1
        ;;
esac
