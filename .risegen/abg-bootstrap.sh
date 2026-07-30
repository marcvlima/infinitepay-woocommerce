#!/bin/sh
# ABG guard bootstrap — makes the abg-guard binary available to a hook.
# Committed in every gated repo by 'abg-guard install'; never fetched over the
# network (the gate repo is private). Prints the decision of the real binary,
# or exits non-zero WITHOUT output so the caller can fail closed.
set -eu

REPO="lakenbeach/risegen-agentic-backlog-gate"
DEST_DIR="${HOME}/.local/bin"
DEST="${DEST_DIR}/abg-guard"

find_guard() {
  command -v abg-guard 2>/dev/null && return 0
  [ -x "$DEST" ] && echo "$DEST" && return 0
  [ -x "${GOPATH:-$HOME/go}/bin/abg-guard" ] && echo "${GOPATH:-$HOME/go}/bin/abg-guard" && return 0
  [ -x "/usr/local/bin/abg-guard" ] && echo "/usr/local/bin/abg-guard" && return 0
  return 1
}

asset_name() {
  os="$(uname -s | tr '[:upper:]' '[:lower:]')"
  arch="$(uname -m)"
  case "$arch" in
    arm64|aarch64) arch=arm64 ;;
    x86_64|amd64)  arch=amd64 ;;
  esac
  echo "abg-guard-${os}-${arch}"
}

acquire() {
  command -v gh >/dev/null 2>&1 || {
    echo "[abg-bootstrap] gh CLI not found — cannot acquire abg-guard; install gh and authenticate (gh auth login)" >&2
    return 1
  }
  mkdir -p "$DEST_DIR"
  if gh release download --repo "$REPO" --pattern "$(asset_name)" --output "$DEST" --clobber >/dev/null 2>&1; then
    chmod +x "$DEST" && echo "$DEST" && return 0
  fi
  echo "[abg-bootstrap] gh release download failed (asset $(asset_name)) — is gh authenticated for lakenbeach? try: gh auth status" >&2
  command -v go >/dev/null 2>&1 || {
    echo "[abg-bootstrap] go not found — cannot build from source as fallback" >&2
    return 1
  }
  tmp="$(mktemp -d)"
  if gh repo clone "$REPO" "$tmp" -- --depth 1 --quiet >/dev/null 2>&1 &&
     ( cd "$tmp" && CGO_ENABLED=0 go build -trimpath -o "$DEST" ./cmd/abg-guard ) >/dev/null 2>&1; then
    rm -rf "$tmp"; chmod +x "$DEST"; echo "$DEST"; return 0
  fi
  rm -rf "$tmp"
  echo "[abg-bootstrap] clone+build fallback failed for $REPO" >&2
  return 1
}

GUARD="$(find_guard || true)"
[ -n "$GUARD" ] || GUARD="$(acquire || true)"
[ -n "$GUARD" ] || exit 1

exec "$GUARD" "$@"
