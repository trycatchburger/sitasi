# Admin Dashboard Header Navigation Architecture

## Overview
This diagram shows the architecture and flow of the admin dashboard with the header navigation implementation.

```mermaid
graph TD
    A[User Request] --> B{Is Admin Page?}
    B -->|No| C[Regular Layout]
    B -->|Yes| D[Admin Layout]
    
    C --> E[main.php without admin nav]
    D --> F[main.php with header nav]
    
    F --> G[Header Navigation]
    G --> H[Dashboard Link]
    G --> I[Admin Management Link]
    G --> J[Repository Link]
    
    F --> K[Page Content]
    K --> L{Requested Page}
    L --> M[Dashboard]
    L --> N[Admin Management]
    L --> O[Repository Management]
    
    H --> M
    I --> N
    J --> O
    
    M --> P[AdminController::dashboard()]
    N --> Q[AdminController::adminManagement()]
    O --> R[AdminController::repositoryManagement()]
    
    P --> S[dashboard.php view]
    Q --> T[admin_management.php view]
    R --> U[repository_management.php view]
    
    style G fill:#e1f5fe
    style F fill:#f3e5f5
    style D fill:#fff3e0
```

## Component Structure

```mermaid
graph TD
    A[app/views/main.php] --> B{Is Admin User?}
    B -->|No| C[Standard Layout]
    B -->|Yes| D[Admin Layout]
    
    D --> E[Header Navigation]
    D --> F[Page Content]
    
    E --> G[Navigation Links]
    G --> H[Dashboard]
    G --> I[Admin Management]
    G --> J[Repository]
    
    F --> K{Current Page}
    K --> L[dashboard.php]
    K --> M[admin_management.php]
    K --> N[repository_management.php]
    K --> O[create_admin.php]
    
    style E fill:#e8f5e8
    style D fill:#fff3e0
```

## Data Flow

```mermaid
sequenceDiagram
    participant U as User
    participant C as Controller
    participant V as View
    participant R as Repository
    
    U->>C: Request admin page
    C->>C: Check authentication
    alt Authenticated
        C->>R: Fetch required data
        R-->>C: Return data
        C->>V: Render with data
        V->>V: Include header navigation
        V-->>U: Display admin page with header navigation
    else Not authenticated
        C-->>U: Redirect to login
    end
```

## File Structure

```
app/
├── Controllers/
│   └── AdminController.php (existing - no changes needed)
├── Repositories/
│   └── AdminRepository.php (existing - no changes needed)
├── views/
│   ├── main.php (modified - moved admin navigation to header)
│   ├── dashboard.php (modified - adjust layout)
│   ├── admin_management.php (existing)
│   └── repository_management.php (modified - adjust layout)
public/
└── css/
    └── style.css (existing - no major changes needed)
```

## CSS Class Structure

```mermaid
graph TD
    A[.admin-layout] --> B[.header-navigation]
    A --> C[.main-content]
    
    B --> D[.nav-links]
    D --> E[a.active]
    D --> F[a]
    
    C --> G[.container]
    
    style A fill:#e3f2fd
    style B fill:#f3e5f5
    style C fill:#e8f5e8