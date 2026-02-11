#!/bin/sh
set -e
# Garante que as dependências estão instaladas (sync com package.json montado)
npm ci
exec npm run dev -- --host 0.0.0.0
