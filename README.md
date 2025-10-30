# VLru-OFF Redesign Project

## Built on
- PHP/Symfony 8.4/7.3 REST API
- Vue.js 3 frontend
- python 3.13 FastAPI ML Microservice

*services communcation*
<img width="561" height="431" alt="Scheme drawio" src="https://github.com/user-attachments/assets/056ac001-13c2-4a5d-8f01-f1fc045c40b0" />

*DB Scheme*
<img width="561" height="537" alt="image_2025-10-31_07-46-02" src="https://github.com/user-attachments/assets/d8d400fe-d9bf-408c-9c35-f107f16a1438" />

## Table of Contents
- [Installation](#installation)
    - [Windows](#windows)
    - [Linux](#linux)
    - [Mac](#mac)
- [Development](#development)
    - [Backend](#backend)
    - [Frontend](#frontend)
- [Docker Development](#docker-development)
- [Tools](#tools)

## Installation

### **!!!important!!!**
If you see this here, then you need to download files from this [Google Drive](https://drive.google.com/drive/folders/1Dc3PtGoSADKMINf0exuZIXvfly5JbND7?usp=sharing) and put them into ```.predict/``` in order to get current configuration working.

---

### Windows

open powershell with administrator privileges

#### install Chocolatey (package manager)
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

## Development

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

## Docker Development
first, run this:
```
cd backend/
composer install
cd ../frontend
npm run build
cd ..
```
### In project root run the following:
```
docker compose up --build
```
```bash
docker exec -it vlru-off-renewal-php-1 bash
```
```bash
php bin/console doctrine:database:create
php bin/console make:migration
php bin/console doctrine:migrations:migrate
php bin/console app:generate-fake-data
exit
```

docker should build and run all the containers, and you should be able to see the page at the ```localhost```.
API requests are forwarded automatically through nginx (API requests will look like this: *localhost/api/{query}*), but backend is running at the port 9000.

Hot-reloading in docker is only configured for php right now.

## Tools
if you need to generate fake data on the fly, go into php container's bash:
```bash
docker exec -it vlru-off-renewal-php-1 bash
```
and run app command I created:
```bash
php bin/console app:generate-fake-data
```

## OFF#Predict
- implemented

work needed

endpoints available at ```forecast:8000``` (```localhost:8000```)

TODO: pass through backend and cache result

## nginx proxy
- supported in docker

## Makefile
TODO

## Scripts
TODO

