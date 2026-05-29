# Deployment Guide

## Auto Deployment (GitHub Actions)

Every push to `main` automatically deploys to the live server via SSH.

### What happens on deploy:
1. GitHub Actions SSHs into the cPanel server
2. Runs `git pull origin main`
3. Caches config, routes, and views
4. Runs pending migrations
5. Restarts queue workers

### Workflow:
```
git add .
git commit -m "your message"
git push
```
That's it — the site goes live automatically.

### Monitor deploys:
Check status at: https://github.com/yewho2021/MyBusiness/actions

---

## Setup (one-time)

### 1. Generate SSH key on your server

```bash
ssh mybusiness@server28.synctechhosting.com -p 6262
ssh-keygen -t ed25519 -C "github-deploy" -f ~/.ssh/github_deploy -N ""
cat ~/.ssh/github_deploy.pub >> ~/.ssh/authorized_keys
cat ~/.ssh/github_deploy
```

Copy the private key output from the last command.

### 2. Add GitHub Secrets

Go to: https://github.com/yewho2021/MyBusiness/settings/secrets/actions

Add these 4 secrets:

| Secret Name      | Value                                |
|------------------|--------------------------------------|
| SERVER_HOST      | server28.synctechhosting.com         |
| SERVER_PORT      | 6262                                 |
| SERVER_USER      | mybusiness                           |
| SERVER_SSH_KEY   | (paste the private key from step 1)  |

### 3. Test it

Push any small change to `main` and check the Actions tab on GitHub.

---

## Manual Deployment (fallback)

If auto-deploy fails, you can still deploy manually:

```bash
ssh mybusiness@server28.synctechhosting.com -p 6262
cd ~/office.mybusiness.com.my
git pull
```
