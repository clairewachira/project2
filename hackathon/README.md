Kubernetes Hackathon Project
This repository contains a solution for the Kubernetes hackathon challenge, deploying a multi-tier microservices application.
Project Structure
hackathon/
├── README.md
├── apps/
│   ├── web/
│   │   ├── deployment.yaml
│   │   └── service.yaml
│   ├── api/
│   │   ├── deployment.yaml
│   │   ├── service.yaml
│   │   └── configmap.yaml
│   └── db/
│       ├── deployment.yaml
│       ├── service.yaml
│       └── pvc.yaml
├── ingress/
│   └── ingress.yaml
└── scripts/
    ├── setup.sh
    └── cleanup.sh
Components
The application consists of three main components:

Web Frontend - A web UI that communicates with the API
API Service - A backend service that processes requests and stores data in the database
Database - A PostgreSQL database for data persistence

Deployment
To deploy the application:

Clone this repository
Navigate to the hackathon directory
Run the setup script:
./scripts/setup.sh


Accessing the Application
Once deployed, the application can be accessed through:

Web UI: http://todo.local
API: http://api.todo.local

Cleanup
To remove all resources:
./scripts/cleanup.sh

Requirements Met
Container Images: Using existing images from Docker Hub
Persistent Storage: Implemented for the database
Configuration: Using ConfigMaps for API configuration
Networking: Services and Ingress rules for internal and external communication
Resource Management: Setting appropriate resource requests and limits
Health Checks: Implemented liveness and readiness probes
Security: Using non-root users within containers and appropriate security contexts