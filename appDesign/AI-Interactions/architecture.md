# ICAS — Architecture Overview (Kubernetes-ready)

Source diagram: database/drawio/Architecture&Mockups.drawio

## Proposed stack
- PHP, Laravel, Jetstream (Fortify), Inertia, Vite, Vue 3, Tailwind, PrimeVue
- Redis (sessions, cache, pub/sub), 
- Laravel Reverb (secure WebSocket server), 
- MySQL (clustered)

### Summary
This document explains each layer from the diagram and maps responsibilities to the proposed stack. The Application (HTTP/Inertia) and Communications (WebSocket/Reverb) layers are intended to run as separate services/pods so they can scale independently on Kubernetes. Redis is used for sessions/caching and pub/sub across both layers. MySQL is the clustered persistence layer.

### Layers

1) Front Layer (Clients)
- Description: Browsers, mobile apps, terminals and video clients.
- Implementation: SPA built with Inertia + Vue + Pinia (Secure Browser Store), styled with Tailwind and PrimeVue components. Built with Vite in CI; served via the Application/Presentation Layer over HTTPS (ingress).
- Responsibility: UI rendering, user interactions, media playback. Connects via HTTPS to the Application layer and via secure WebSocket/TLS to the Communications layer when realtime is required.

2) Application / Presentation Layer
- Description: Laravel HTTP application serving Inertia pages, APIs and handling authentication/authorization.
- Stack mapping: Laravel + Jetstream/Fortify for auth; Inertia + Vue for UI; Vite for builds.
- Sessions: Use Redis as session store (SESSION_DRIVER=redis) so sessions are shared across app replicas and accessible to Reverb if needed.
- Statelessness: Offload uploads to S3 (FILESYSTEM_DISK=s3) and build assets in CI; app pods must be stateless for horizontal scaling.
- Duties: Render SPA shell, authenticate users, issue short-lived socket tokens (or validate session cookies), orchestrate business logic and persist durable data to MySQL.

3) Communications Layer (Laravel Reverb)
- Description: Dedicated secure WebSocket server (Laravel Reverb or soketi-compatible) running on separate servers/pods.
- Responsibilities:
  - Handle socket connections at scale, perform token/session validation, route events to clients.
  - Publish/subscribe to Redis to exchange events with the Laravel app (event bus).
  - Handle identification and authorization of all comms and interactions between participants.
  - In charge of contacting and maintaining state of IOT (legacy and New) independent layers, Phone Systems and Video Servers, where in requirement will have a timed request to update status of all the legacy devices. Can communicate with them using HTTP (insecure communications) on legacy devices. 
  - New IOT devices, Video Servers and Phone Systems must comply to JSON Web Tokens, API Keys or Client Certificate Authentication.
  - Must screen all communications for any security vulnerabilities or exploits.
  - All communications must be confirmed as in TCP to guarantee delivery of messages.
- Auth: Prefer short-lived tokens issued by Laravel or validated session cookies (ensure sameSite/domain and TLS are correct). All socket traffic must use TLS.
- Scale: Run independently from the HTTP app; use HPA and resource tuning.

4) Session Layer (Redis cluster)
- Description: Redis cluster(s) used for session storage, cache and pub/sub.
- Purpose:
  - Store sessions for both HTTP and WebSocket layers (low latency).
  - Provide pub/sub or streams for event exchange between Laravel and Reverb.
- HA: Use a managed Redis cluster or a Kubernetes operator/Helm chart (Bitnami/Redis, Redis Operator) rather than a single pod.

5) Persistence Layer (MySQL cluster)
- Description: MySQL primary/replica (or clustered solution) holding durable application data.
- Duties: Store users, teams, domain models, audit logs, and anything requiring durability.
- HA: Use StatefulSets + PVCs with an operator or a managed DB offering that provides replication and automated failover. Configure Laravel DB read/write splitting if necessary.

### Deployment & Operational Notes (concise)
- Separate services: Deploy Application and Communications as distinct Deployments/Services with their own Ingress or Service endpoints so they scale independently.
- Environment variables (examples)
  - SESSION_DRIVER=redis
  - CACHE_DRIVER=redis
  - QUEUE_CONNECTION=redis
  - BROADCAST_DRIVER=pusher (configure to point to Reverb/soketi)
  - PUSHER_APP_ID / PUSHER_KEY / PUSHER_SECRET / PUSHER_HOST -> internal soketi/Reverb service
- WebSocket auth: Issue short-lived JWT-like tokens from Laravel for socket connect, or share Redis-backed session validation — prefer tokens if sockets are on a different host.
- Queues/workers: Use Redis-backed queues; run workers as separate scalable pods.
- Assets & files: Build Vite assets in CI and serve via CDN/ingress; store uploads in S3 to keep pods stateless.
- Observability & safety: Add liveness/readiness probes, resource requests/limits, HPA, logs (EFK) and metrics (Prometheus/Grafana).
- Security: Terminate TLS at ingress, enforce CORS, validate origins on sockets, and rotate socket tokens often.

### Recommended next steps
- Configure Laravel to use Redis for sessions/cache/queues and BROADCAST_DRIVER pointing to Reverb/soketi.
- Create k8s manifests (Deployment + Service + HPA + Ingress) for:
  - Laravel app (stateless)
  - Reverb/soketi (stateful ephemeral connections)
  - Redis cluster (StatefulSet/operator)
  - MySQL cluster (StatefulSet/operator or managed DB)
- Implement socket auth flow (Laravel issues tokens; Reverb validates via Redis or via Laravel API).
- Test end-to-end locally using distinct hostnames (or hostAliases) to ensure cookie/token behavior matches production.

### If you want, I can:
- Add a sample k8s manifest set for Application and Communications.
- Generate example config/broadcasting.php and .env snippets for Reverb/soketi integration.