# Skill assessment test (Laravel/PHP) 

L'obbiettivo di questo progetto è quello di poter valutare in modo oggettivo 
le abilità di programmazione dei candidati alla posizione di backend developer (PHP/Laravel) presso Kromin

L'applicativo è una semplice todo list nella quale ogni utente può loggarsi e può accedere alla propria liste di cose da fare.

Le entità principali sono:
- User
- Todos

Per far partire l'applicazione sarà necessario avere installato docker. 
Quindi nel caso non lo aveste installato sulla vostra macchina vi converrà farlo.

Qui di seguito ci sono le istruzioni per farlo partire da una linea di comando unix.
Essendo containerizzato per far partire l'applicazione dovrebbe essere sufficiente lanciare il comando di system start

## Comandi

I comandi sono pensati per partire da console dalla directory dove avete trovato questo readme

System start
`docker-compose up`

Entra nel docker per eseguire operazioni direttamente nel container tramite bash
`docker exec -it hr-backend-laravel bash`

Inizializza il file .env
`cp .env.example .env`

Esegui migrazioni
`php artisan migrate`

Genera chiave dell'applicazione
`php artisan key:generate`

Genera client per l'autenticazione.
`php artisan passport:install`

## Test API

Per il test delle API è possibile utilizzare il file Kromin.json importandolo su Postman