#!/bin/bash

# Zeige den aktuellen Branch an
git branch

# Frage, ob der richtige Branch ausgecheckt ist
read -p "ACHTUNG! Es wird alles mit den Daten vom remote überschrieben! Weiter und ist der richtige Branch ausgecheckt? (y/n): " answer
# Konvertiere die Antwort in Kleinbuchstaben
answer=$(echo "$answer" | tr '[:upper:]' '[:lower:]')

# Überprüfe die Antwort
if [[ "$answer" == "j" || "$answer" == "ja" || "$answer" == "y" || "$answer" == "yes" ]]; then
    # Starte den SSH-Agenten und füge den SSH-Schlüssel hinzu
    eval "$(ssh-agent -s)"
    ssh-add ~/.ssh/github

    # Führe git pull aus
    git fetch origin
    git reset --hard origin/main
    git pull --force
else
    echo "Abbruch. Bitte den richtigen Branch auschecken."
    exit 1
fi
