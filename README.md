# 

# Installation

### Windows

open powershell with administrator privileges

#### install choco (package manager)
```powershell
Set-ExecutionPolicy AllSigned
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))
```

#### check your choco install, should be available immediately
```powershell
choco
```
#### check if required tools are installed
- check git
- check nodejs
- check composer
- check symfony-cli

if not found then
```powershell
choco install composer
choco install git
choco install nodejs
choco install symfony-cli
```

### Linux
// TODO

TL;DR: use your distro's package manager to install composer, git, nodejs, symfony

### Mac
most popular package manager is ```brew```. Install it and use it to install packages the same way as above

---

# Run (dev)

#### Backend
```
cd backend/
```
```
composer install
```

#### Frontend
```
cd frontend/
```

or ```cd ../frontend``` if you were in ```backend/``` directory and vise versa
```
npm run dev
```

---

## Docker
// TODO

## nginx proxy
// TODO

## Makefile
// TODO

## Scripts
// TODO
