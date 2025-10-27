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
- git
- nodejs
- composer
- symfony-cli
- docker

if not found then install what you need with choco:
```powershell
choco install composer
choco install git
choco install nodejs
choco install symfony-cli
choco install docker-desktop
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
```
symfony server:start --port=80
```
API requests should be done are as followed: *localhost/api/{your query}*
#### Frontend
```
cd frontend/
```

or ```cd ../frontend``` if you were in ```backend/``` directory and vice versa
```
npm run dev
```

---

## Docker Run (dev)
### In project root run the following:
```angular2html
docker compose up --build
```
docker should build and run all the containers, and you should be able to see the page at the ```localhost```.
API requests are forwarded automatically through nginx (API requests will look like this: *localhost/api/{query}*), but backend is running at the port 9000.

Hot-reloading in docker is only configured for php right now.

## nginx proxy
- supported in docker

## Makefile
// TODO

## Scripts
// TODO
