# Production server reboot

Handle system reboot and docker restarting automatically is quite simple: using service.

## Create sevice file

Crea il file del serivizio

```console
sudo nano /etc/systemd/system/truck26-docker.service
```
Entra nel file e scrivi..

```ini
[Unit]
Description=Truck26 Docker Compose
Requires=docker.service
After=docker.service
Wants=network-online.target
After=network-online.target

[Service]
Type=oneshot
WorkingDirectory=/home/debian/truck26
EnvironmentFile=/etc/truck26/prod.env
ExecStart=/usr/bin/docker compose -f compose.yaml -f compose.prod.yaml up -d
ExecStop=/usr/bin/docker compose down
RemainAfterExit=yes

[Install]
WantedBy=multi-user.targe
```

Prepara il file con le variabil d'ambiente

```console
sudo nano /etc/truck26/prod.env
```

nel quale inserire..

```ini
SERVER_NAME=truck26.natalinitrasporti.it
APP_SECRET= ## NON lasciare questo codice nel repository
CADDY_MERCURE_JWT_SECRET= ## NON lasciare questo codice nel repository
```

Proteggi il file da lettura e scrittura (CHMOD 600) e abilita il servizio al riavvio

```console
sudo systemctl daemon-reload
sudo systemctl enable truck26-docker.service
```

## DUMP del database
```console
docker exec -t database pg_dumpall -U tu_utente_postgres | gzip > /percorso/backup/db_dump_$(date +\%Y\%m\%d_\%H\%m\%s).sql.gz
```

## UPLOAD backup via FTP

```console
curl -T "$(ls -t /percorso/backup/db_dump_*.sql.gz | head -n 1)" ftp://indirizzo_ip_ftp/cartella_remota/ --user "username_ftp:password_ftp"
```
