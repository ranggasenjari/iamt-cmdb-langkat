#!/bin/bash
# Collect VM data from all Proxmox nodes and output JSON array to stdout.
# Usage: ./sync-proxmox.sh | ssh iamt@192.168.4.10 "cd /home/iamt/public_html && php artisan proxmox:sync-vms --stdin"

NODES=(
  "192.168.4.1:Node 1:NODE-01"
  "192.168.4.3:Node 3:NODE-03"
  "192.168.4.4:Node 4:NODE-04"
  "192.168.4.5:Node 5:NODE-05"
  "192.168.4.6:Node 6:NODE-06"
)

SSH_OPTS="-o ConnectTimeout=10 -o StrictHostKeyChecking=accept-new"
FIRST=true

echo "["

for entry in "${NODES[@]}"; do
  HOST="${entry%%:*}"
  REST="${entry#*:}"
  LABEL="${REST%%:*}"
  SERVER="${REST#*:}"

  LIST=$(ssh $SSH_OPTS "root@$HOST" 'qm list' 2>/dev/null) || {
    $FIRST || echo ","
    echo "{\"node\":\"$HOST\",\"label\":\"$LABEL\",\"server_nama\":\"$SERVER\",\"vms\":[]}"
    FIRST=false
    continue
  }

  VMS=()
  while IFS= read -r line; do
    [[ "$line" =~ ^VMID ]] && continue
    [ -z "$line" ] && continue

    read -r VMID NAME STATUS <<< "$line"

    CONFIG=$(ssh $SSH_OPTS "root@$HOST" "qm config $VMID" 2>/dev/null)

    OS=$(echo "$CONFIG" | grep -i '^ostype:' | head -1 | cut -d: -f2- | xargs)
    VCPU=$(echo "$CONFIG" | grep -i '^cores:' | head -1 | cut -d: -f2- | xargs)
    RAM_MB=$(echo "$CONFIG" | grep -i '^memory:' | head -1 | cut -d: -f2- | xargs)
    STORAGE=0
    for disk in $(echo "$CONFIG" | grep -oP 'size=\K\d+'); do
      STORAGE=$((STORAGE + disk))
    done

    # Escape JSON special chars in name
    NAME_ESC=$(echo "$NAME" | sed 's/"/\\"/g')
    OS_ESC=$(echo "$OS" | sed 's/"/\\"/g')

    VMS+=("{\"vmid\":\"$VMID\",\"nama\":\"$NAME_ESC\",\"status\":\"$STATUS\",\"os\":\"$OS_ESC\",\"vcpu\":${VCPU:-null},\"ram_mb\":${RAM_MB:-null},\"storage_gb\":${STORAGE:-null}}")
  done <<< "$LIST"

  $FIRST || echo ","
  FIRST=false

  echo -n "{\"node\":\"$HOST\",\"label\":\"$LABEL\",\"server_nama\":\"$SERVER\",\"vms\":["
  for i in "${!VMS[@]}"; do
    [ $i -gt 0 ] && echo -n ","
    echo -n "${VMS[$i]}"
  done
  echo "]}"
done

echo "]"
