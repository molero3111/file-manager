# Kubernetes Setup for File Manager

This guide explains how to deploy the File Manager application and its dependencies on Kubernetes. The setup includes MySQL, Redis, and the application API, each with their own deployments and services.

## 1. Create the Namespace

First, create a dedicated namespace for isolation:

```sh
kubectl apply -f namespace.yaml
```

## 2. Create ConfigMaps and Secrets

Environment variables are separated into two files:

- `file-manager-config.env`: Non-sensitive configuration (for ConfigMap)
- `file-manager-secret.env`: Sensitive data (for Secret)

**Create these files in the `k8s/` directory.**

Create the ConfigMap and Secret using:

```sh
kubectl create configmap file-manager-config --from-env-file=k8s/configmap-secrets/configmap.env -n file-manager

kubectl create secret generic file-manager-secret --from-env-file=k8s/configmap-secrets/secrets.env -n file-manager
```

> **Note:**  
> If you update the environment variable files and need to re-create the ConfigMap or Secret, first delete the existing ones:
> 
> ```sh
> kubectl delete configmap file-manager-config -n file-manager
> kubectl delete secret file-manager-secret -n file-manager
> ```
> Then re-run the create commands above.
> ```
> You may need to restart your pods with command:
> kubectl rollout restart deployment 
> api-fm
 -n file-manager

## 3. Deploy the Database (MySQL)

### a. Persistent Volume and Claim

Provision storage for MySQL:

```sh
kubectl apply -f database/dbdata-pv.yaml
```

This creates:
- A PersistentVolume ([database/dbdata-pv.yaml](database/dbdata-pv.yaml))

### b. MySQL Deployment and Service

Deploy MySQL and expose it internally:

```sh
kubectl apply -f database/db-fm-deployment.yaml
kubectl apply -f database/db-fm-service.yaml
```

- Deployment: [database/db-fm-deployment.yaml](database/db-fm-deployment.yaml)
- A PersistentVolumeClaim (in [database/db-fm-deployment.yaml](database/db-fm-deployment.yaml))
- Service: [database/db-fm-service.yaml](database/db-fm-service.yaml)

## 4. Deploy Redis

Deploy Redis for caching and queues:

```sh
kubectl apply -f redis/redis-fm-deployment.yaml
kubectl apply -f redis/redis-fm-service.yaml
```

- Deployment: [redis/redis-fm-deployment.yaml](redis/redis-fm-deployment.yaml)
- Service: [redis/redis-fm-service.yaml](redis/redis-fm-service.yaml)

## 5. Deploy the Application API

Deploy the main application (PHP/Laravel API):

```sh
kubectl apply -f api/api-fm-deployment.yaml
kubectl apply -f api/api-fm-service.yaml
```

- Deployment: [api/api-fm-deployment.yaml](api/api-fm-deployment.yaml)
- Service: [api/api-fm-service.yaml](api/api-fm-service.yaml)

The service exposes the app on NodePort 8080.

## 6. Accessing the Application

After all resources are created, access the app via the NodePort (e.g., `http://<node-ip>:8080`).

---

**Summary of Resource Files:**

- Namespace: [namespace.yaml](namespace.yaml)
- MySQL: [database/dbdata-pv.yaml](database/dbdata-pv.yaml), [database/db-fm-deployment.yaml](database/db-fm-deployment.yaml), [database/db-fm-service.yaml](database/db-fm-service.yaml)
- Redis: [redis/redis-fm-deployment.yaml](redis/redis-fm-deployment.yaml), [redis/redis-fm-service.yaml](redis/redis-fm-service.yaml)
- API: [api/api-fm-deployment.yaml](api/api-fm-deployment.yaml), [api/api-fm-service.yaml](api/api-fm-service.yaml)

TODO:: build docker image properly before starting api service