@echo off
cd /d "C:\project\nkhoj\OmniRoute"
node --max-old-space-size=8192 scripts/dev/run-next.mjs dev
