#!/bin/bash
# Collect VM data from all Proxmox nodes and output JSON lines to stdout.
# Usage: ./sync-proxmox.sh | ssh iamt@192.168.4.10 "cd /home/iamt/public_html && php artisan proxmox:sync-vms --stdin"
# Or pipe per node:
#   ./sync-proxmox.sh | while read line; do echo "$line" | ssh iamt@192.168.4.10 "cd /home/iamt/public_html && php artisan proxmox:sync-vms --stdin"; done

NODES=(
  "192.168.4.1:Node 1"
  "192.168.4.3:Node 3"
  "192.168.4.4:Node 4"
  "192.168.4.5:Node 5"
  "192.168.4.6:Node 6"
)

SSH_OPTS="-o ConnectTimeout=10 -o StrictHostKeyChecking=accept-new"

for entry in "${NODES[@]}"; do
  HOST="${entry%%:*}"
  LABEL="${entry##*:}"

  LIST=$(ssh $SSH_OPTS "root@$HOST" 'qm list' 2>/dev/null) || { echo "{\"node\":\"$HOST\",\"label\":\"$LABEL\",\"vms\":[]}"; continue; }

  VMS=()
  while IFS= read -r line; do
    [[ "$line" =~ ^VMID ]] && continue
    [ -z "$line" ] && continue

    set -- $line
    VMID=$1
    NAME=$2
    STATUS=$3

    CONFIG=$(ssh $SSH_OPTS "root@$HOST" "qm config $VMID" 2>/dev/null)

    # Parse config
    OS=$(echo "$CONFIG" | grep -i '^ostype:' | head -1 | cut -d: -f2- | xargs)
    VCPU=$(echo "$CONFIG" | grep -i '^cores:' | head -1 | cut -d: -f2- | xargs)
    RAM_MB=$(echo "$CONFIG" | grep -i '^memory:' | head -1 | cut -d: -f2- | xargs)
    STORAGE=0
    for disk in $(echo "$CONFIG" | grep -E '^(scsi|virtio|ide|sata)' | grep -oP 'size=\K\d+'); do
      STORAGE=$((STORAGE + disk))
    done

    VMS+=("{\"vmid\":\"$VMID\",\"nama\":\"$NAME\",\"status\":\"$STATUS\",\"os\":\"$OS\",\"vcpu\":$VCPU,\"ram_mb\":$RAM_MB,\"storage_gb\":$STORAGE}")
  done <<< "$LIST"

  IFS=,
  echo "{\"node\":\"$HOST\",\"label\":\"$LABEL\",\"vms\":[$(
    for i in "${!VMS[@]}"; do
      [ $i -gt 0 ] && echo -n ","
      echo -n "${VMS[$i]}"
    done
  )]}"
  unset IFS
done
