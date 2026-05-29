# Deployment Guide

## Push changes to live server

### Step 1 — On your PC
git add .
git commit -m "your message"
git push

### Step 2 — On server (to go live)
ssh mybusiness@server28.synctechhosting.com -p 6262
cd ~/office.mybusiness.com.my
git pull