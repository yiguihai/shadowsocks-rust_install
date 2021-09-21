#!/bin/bash

#本脚本使用了(复制粘贴的缝合怪)了以下下项目的代码，在此表示由衷的感谢！
# https://github.com/zhanhb/kcptun-sip003-wrapper
# https://raw.githubusercontent.com/shadowsocks/ShadowsocksX-NG/develop/ShadowsocksX-NG/kcptun/kcptun.sh

SS_ENV_NAMES=(SS_REMOTE_HOST SS_REMOTE_PORT SS_LOCAL_HOST SS_LOCAL_PORT)

for i in "${SS_ENV_NAMES[@]}"; do
	if [ -z ${!i} ]; then
		echo Not found env variable $i
		exit
	fi
done

# Split options
IFS=';' read -ra _OPTS <<<"${SS_PLUGIN_OPTIONS}"

# Prepend `--`
OPTS=()
for i in "${_OPTS[@]}"; do
	OPTS+=("--$i")
done

CMD="$(dirname "${BASH_SOURCE[0]}")/kcptun-server"

# Check if it is an IPv6 address
if [[ $SS_REMOTE_HOST == *":"* ]]; then
	SS_REMOTE_ADDR="[${SS_REMOTE_HOST}]:${SS_REMOTE_PORT}"
else
	SS_REMOTE_ADDR="${SS_REMOTE_HOST}:${SS_REMOTE_PORT}"
fi

# Check if it is an IPv6 address
if [[ $SS_LOCAL_HOST == *":"* ]]; then
	SS_LOCAL_ADDR="[${SS_LOCAL_HOST}]:${SS_LOCAL_PORT}"
else
	SS_LOCAL_ADDR="${SS_LOCAL_HOST}:${SS_LOCAL_PORT}"
fi

# Update this line when adapted other plugin.
# echo -l "${SS_REMOTE_ADDR}" -t "${SS_LOCAL_ADDR}" ${OPTS[@]}

child_pid=

func_trap() {
	local signal="$1"
	# we can't kill background child process with signal INT
	[ "$signal" != INT ] || signal=TERM
	if [ -n "$child_pid" ]; then
		kill -s "$signal" "$child_pid" || true
	fi
}

trap_with_arg() {
	local func sig
	func="$1"
	shift
	for sig; do
		trap "$func $sig" "$sig"
	done
}

has_builtin() {
	[ "$(command -v "$1")" = "$1" ]
}

if has_builtin wait && has_builtin trap && has_builtin kill; then
	"$CMD" -l "${SS_REMOTE_ADDR}" -t "${SS_LOCAL_ADDR}" ${OPTS[@]} &
	child_pid=$!
	if [ -z "$child_pid" ]; then
		echo Unknown error occur, cannot get process id of child process. >&2
		exit 1
	fi
	# Send all signal to kcptun
	trap_with_arg func_trap HUP INT QUIT ILL TRAP ABRT BUS FPE USR1 SEGV USR2 PIPE ALRM TERM
	wait_result=0
	while true; do
		value=0
		wait "$child_pid" 2>/dev/null || value=$?
		# 127 means this pid is not child process of our shell.
		[ "$value" -ne 127 ] || break
		wait_result="$value"
		[ "$value" -ne 0 ] || break
		# yield control of the CPU
		sleep 0.1 || sleep 1
		kill -0 "$child_pid" 2>/dev/null || break
	done
	child_pid=
	return $wait_result
else
	"$CMD" -l "${SS_REMOTE_ADDR}" -t "${SS_LOCAL_ADDR}" ${OPTS[@]}
fi
