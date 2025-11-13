param(
    [switch]$Push,
    [string]$RemoteUrl = ''
)

# Script: create_commits.ps1
# Usage examples:
#  - Dry run / local commits only: .\create_commits.ps1
#  - Create commits and push: .\create_commits.ps1 -Push -RemoteUrl "https://github.com/chedi-ouerghi/trading_plateforme.git"

function Run-Git {
    param($Args)
    $cmd = "git $Args"
    Write-Host "> $cmd"
    & git $Args
    return $LASTEXITCODE
}

Push-Location $PSScriptRoot

# Ensure git is available
if (-not (Get-Command git -ErrorAction SilentlyContinue)) {
    Write-Error "Git n'est pas installé ou n'est pas dans le PATH. Install/Configure git d'abord."
    exit 1
}

# Initialize repo if needed
if (-not (Test-Path .git)) {
    Write-Host "Initialisation du dépôt git..."
    Run-Git "init"
    Run-Git "branch -M main"
} else {
    Write-Host ".git existe déjà — le script va réutiliser le dépôt existant."
}

# A list of logical commits (message + paths). Adjust messages/paths if nécessaire.
$commits = @(
    @{msg = "Init: Add core backend files (composer, artisan)"; paths = @("backend/composer.json","backend/composer.lock","backend/artisan")},
    @{msg = "Backend: Add bootstrap and config"; paths = @("backend/bootstrap","backend/config")},
    @{msg = "Backend: Add Models"; paths = @("backend/app/Models")},
    @{msg = "Backend: Add Services"; paths = @("backend/app/Services","backend/app/Helpers/cotation_generator.php")},
    @{msg = "Backend: Add Controllers and Requests"; paths = @("backend/app/Http/Controllers","backend/app/Http/Requests")},
    @{msg = "Backend: Add Jobs and Mail classes"; paths = @("backend/app/Jobs","backend/app/Mail")},
    @{msg = "Backend: Add Migrations"; paths = @("backend/database/migrations")},
    @{msg = "Backend: Add Factories & Seeders"; paths = @("backend/database/factories","backend/database/seeders")},
    @{msg = "Backend: Add Routes & Kernel"; paths = @("backend/routes","backend/app/Console/Kernel.php","backend/app/Http/Kernel.php")},
    @{msg = "Backend: Add Resources (views, emails, assets)"; paths = @("backend/resources","backend/public","backend/storage/api-docs")},
    @{msg = "Backend: Add Tests"; paths = @("backend/tests")},
    @{msg = "Backend: Add docs and README"; paths = @("backend/docs","backend/README.md")},

    @{msg = "Frontend: add package and config files"; paths = @("frontend/package.json","frontend/vite.config.ts","frontend/tsconfig.json","frontend/tailwind.config.cjs","frontend/postcss.config.cjs")},
    @{msg = "Frontend: add index, main and App"; paths = @("frontend/index.html","frontend/src/main.ts","frontend/src/App.vue")},
    @{msg = "Frontend: add router and services"; paths = @("frontend/src/router","frontend/src/services","frontend/src/types")},
    @{msg = "Frontend: add pages (landing & signin)"; paths = @("frontend/src/pages/Landing.vue","frontend/src/pages/SignIn.vue")},
    @{msg = "Frontend: add admin pages"; paths = @("frontend/src/pages/admin")},
    @{msg = "Frontend: add client pages"; paths = @("frontend/src/pages/client")},
    @{msg = "Frontend: add UI components"; paths = @("frontend/src/components","frontend/src/components/ui")},
    @{msg = "Frontend: add styles and assets"; paths = @("frontend/src/style.css","frontend/public","frontend/components.json")},
    @{msg = "Frontend: add README and scenario docs"; paths = @("frontend/README.md","frontend/Scenario_ADMIN_CLIENT.md")},

    @{msg = "Chore: add gitignore, editorconfig, gitattributes"; paths = @(".gitignore",".editorconfig",".gitattributes")},
    @{msg = "Chore: package.json backend (vite/npm scripts)"; paths = @("backend/package.json","backend/package-lock.json")},
    @{msg = "Chore: project root README"; paths = @("README.md")},
    @{msg = "Docs: final documentation updates"; paths = @("backend/docs","frontend/Scenario_ADMIN_CLIENT.md")},
    @{msg = "Style: global css and UI tweaks"; paths = @("frontend/src/style.css","backend/resources/css")},
    @{msg = "Final: cleanup and remove temp files"; paths = @("backend/.phpunit.result.cache","frontend/.gitignore")}
)

# Iterate and create commits
foreach ($c in $commits) {
    $paths = $c.paths -join ' '
    try {
        # Use -- to avoid issues with paths that start with -
        $paths = $c.paths -join ' '
        & git add -- $paths 2>$null
    } catch {
        # continue — file/path may not exist
    }

    # Check if anything staged (use --cached for compatibility)
    $staged = & git diff --cached --name-only
    if ($staged -and $staged.Trim().Length -gt 0) {
        Run-Git "commit -m `"$($c.msg)`""
    } else {
        Write-Host "[SKIP] Aucun changement stagé pour: $($c.msg)"
    }
}

Write-Host "\n--- Résumé des commits (les 50 derniers) ---\n"
try {
    & git --no-pager log --oneline -n 50
} catch {
    Write-Host "Aucun historique git (le dépôt n'a peut-être pas été initialisé)."
}

if ($Push) {
    if (-not $RemoteUrl) {
        Write-Host "Vous avez demandé -Push mais n'avez pas fourni -RemoteUrl. Entrez l'URL distante:"
        $RemoteUrl = Read-Host "Remote URL"
    }

    if ($RemoteUrl) {
        # Add remote if it doesn't exist or show current
        $existing = & git remote get-url origin 2>$null
        if ($LASTEXITCODE -eq 0) {
            Write-Host "Remote 'origin' existe déjà: $existing"
            $confirm = Read-Host "Voulez-vous remplacer l'URL 'origin' par $RemoteUrl ? (y/N)"
            if ($confirm -match '^[Yy]') {
                Run-Git "remote set-url origin $RemoteUrl"
            } else {
                Write-Host "Conserver l'origin existant. Pushing vers origin."
            }
        } else {
            Run-Git "remote add origin $RemoteUrl"
        }

        Write-Host "Pushing vers $RemoteUrl ..."
        $code = Run-Git "push -u origin main"
        if ($code -ne 0) {
            Write-Error "Push échoué. Vérifiez vos identifiants/permissions ou lancez manuellement: git push -u origin main"
        }
    } else {
        Write-Error "Aucune URL distante fournie. Abandon du push."
    }
} else {
    Write-Host "\nScript terminé. Aucun push effectué (mode dry-run). Pour pousser, relancer avec -Push -RemoteUrl 'https://...'
"
}

Pop-Location
