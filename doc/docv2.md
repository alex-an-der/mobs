- [Registrierung](#registrierung)
  - [Hinweis zu Regionalverbänden und Betriebssportgemeinschaften](#hinweis-zu-regionalverbänden-und-betriebssportgemeinschaften)
  - [Erste Schritte](#erste-schritte)
- [Grundlegende Bedienung](#grundlegende-bedienung)
    - [Auswahl der Ansicht](#auswahl-der-ansicht)
    - [Informationsbereich](#informationsbereich)
    - [Filteroptionen](#filteroptionen)
      - [Tabellenfilter](#tabellenfilter)
    - [Schaltflächen](#schaltflächen)
      - [Spaltenfilter](#spaltenfilter)
        - [Beispiele für Zahlenfilter](#beispiele-für-zahlenfilter)
        - [Beispiele für Datumsfilter](#beispiele-für-datumsfilter)
    - [Impressum und Datenschutzerklärung](#impressum-und-datenschutzerklärung)
- [Mitglieder](#mitglieder)
  - [Meine Daten bearbeiten](#meine-daten-bearbeiten)
  - [Meine Daten zur Bearbeitung freigeben](#meine-daten-zur-bearbeitung-freigeben)
  - [Antrag zum Wechsel der Stamm-BSG](#antrag-zum-wechsel-der-stamm-bsg)
  - [Lösche meine Daten](#lösche-meine-daten)
- [BSG Organisatoren](#bsg-organisatoren)
  - [BSG-Stammdaten bearbeiten](#bsg-stammdaten-bearbeiten)
  - [Stamm-Mitglieder aufnehmen](#stamm-mitglieder-aufnehmen)
  - [Stamm-Mitglieder bearbeiten - kompakt und erweitert](#stamm-mitglieder-bearbeiten---kompakt-und-erweitert)
  - [BSG-Mitglieder in Sparten anmelden](#bsg-mitglieder-in-sparten-anmelden)
  - [Mitgliede ohne Registrierung](#mitgliede-ohne-registrierung)
    - [Stamm-Mitglieder ohne Zugang eintragen](#stamm-mitglieder-ohne-zugang-eintragen)
    - [Mitgliederkonten zusammenführen](#mitgliederkonten-zusammenführen)
- [Mitarbeiter im Regionalverband](#mitarbeiter-im-regionalverband)
  - [Erweiterte Rechte](#erweiterte-rechte)
- [Mitarbeiter im Landesverband](#mitarbeiter-im-landesverband)
- [Systemadministratoren](#systemadministratoren)
  - [Offene Issues](#offene-issues)
  - [Error-Log](#error-log)
  - [Rollback](#rollback)
  - [Nutzerzahlen](#nutzerzahlen)
  - [Fehlermeldungen anpassen](#fehlermeldungen-anpassen)
  - [Zuweisung von Landesverbans- und Systemadminrechten](#zuweisung-von-landesverbans--und-systemadminrechten)
  - [Backdoor](#backdoor)
- [Workflows (ggf. mit Hintergründe) / grafisch?](#workflows-ggf-mit-hintergründe--grafisch)
  - [Registrieren / inkl. BSG-Sicht bis zur AUfnahme in die Stamm-BSG](#registrieren--inkl-bsg-sicht-bis-zur-aufnahme-in-die-stamm-bsg)
  - [Meldeliste](#meldeliste)
  - [Spartenanmeldung](#spartenanmeldung)
  - [Import](#import)
  - [Wechsel der BSG](#wechsel-der-bsg)
    - [für eine Sparte](#für-eine-sparte)
    - [Stamm BSG](#stamm-bsg)
  - [Eine BSG neu Einrichten](#eine-bsg-neu-einrichten)
- [Auswirkungen von UPDATE und DELETE auf die Datenbank](#auswirkungen-von-update-und-delete-auf-die-datenbank)
  - [UPDATE (Ändern von Daten)](#update-ändern-von-daten)
  - [DELETE (Löschen von Daten)](#delete-löschen-von-daten)
    - [Übersicht der wichtigsten Kettenreaktionen beim Löschen](#übersicht-der-wichtigsten-kettenreaktionen-beim-löschen)
    - [Was wird **nicht** automatisch gelöscht?](#was-wird-nicht-automatisch-gelöscht)
  - [Zusammengefasst](#zusammengefasst)
  - [Prompt für die automatische Dokumentation der Auswirkungen von UPDATE und DELETE im Datenbankschema:](#prompt-für-die-automatische-dokumentation-der-auswirkungen-von-update-und-delete-im-datenbankschema)


# Registrierung

Solltest du noch keinen Zugang besitzen, klicke bitte auf der Startseite von 
https://www.mobs24.de auf **Registrieren**.

Bitte fülle alle erforderlichen Felder vollständig aus, um deine Registrierung 
abzuschließen. Jede Information ist wichtig für die korrekte Einrichtung deines 
Zugangs im System.

## Hinweis zu Regionalverbänden und Betriebssportgemeinschaften

Falls du in der Auswahlliste deinen gewünschten Regionalverband oder deine 
Betriebssportgemeinschaft (BSG) nicht finden kannst, ist ein zusätzlicher 
Schritt erforderlich:

- Diese Organisationseinheiten müssen zunächst von einem Systemverantwortlichen 
  angelegt werden
- Bitte kontaktiere den zuständigen Administrator deiner Organisation
- Sobald die Einrichtung erfolgt ist, kannst du deine Registrierung fortsetzen

Wir danken für dein Verständnis und heißen dich bald in unserem System willkommen!

## Erste Schritte

Nach der Registrierung empfehlen wir:
- Prüfe deine Daten (und vervollständige sie gegebenenfalls)
- Lass dich von deiner BSG in den gewünschten Sparten anmelden

# Grundlegende Bedienung

### Auswahl der Ansicht

In der ersen Zeile kannst du die gewünschte Ansicht wählen. Je nach deiner Rolle und deinen Berechtigungen stehen die verschiedene Ansichten zur Verfügung.

### Informationsbereich

Im blauen Infobereich erhältst du wichtige Hinweise zur aktuellen Ansicht.

### Filteroptionen

#### Tabellenfilter
Um Begriffe in der gesamten Tabelle zu finden, kannst du den Tabellenfilter über den Schaltflächen verwenden. Die Treffer werden in der Tabelle markiert. Du kannst auch mit der **rechten Maustaste** auf eine Tabellenzelle klicken und der Inhalt dieser Zelle wird in den Tabellenfilter übernommen.

### Schaltflächen

Was eine Datenzeile ist, ergibt sich aus dem Kontext der Ansicht. Werden Mitglieder in Sparten angemeldet, ist ein Datenzeile die Anmeldung des Mitglieds. Werden Mitglieder einer BSG gelistet ist ein Datenzeile das einzelne Mitglied. 

Folgende Aktionen stehen dir mit den Schaltflächen zur Verfügung:
- **Neu laden**: Aktualisiert die Tabelle
- **Einfügen**: Fügt eine neue Datenzeile hinzu
- **Ausgewählte löschen**: Entfernt alle markierten Datenzeilen
- **Dubletten suchen**: Identifiziert doppelte Einträge. Hier weden aber tatsächlich nur komplett übereinstimmende Einträge gefunden.
- **Daten importieren**: Ermöglicht das Importieren von Datenzeilen im CSV-Format. Für Details siehe bitte die [Import-Beschreibung im Anhang](#import).
- **Exportieren**: Öffnet ein Menü mit verschiedenen Exportformaten

#### Spaltenfilter
Über jeder Spalte befindet sich ein weiteres Filterfeld. Der dortige Eintrag wird dann nur in der entsprechenden Spalte gesucht. Neben dem Textfilter gibt es auch Zahlen- und Datumsfilter, die dann aktiviert werden, wenn entsprechende Inhalte erkannt werden. Dort gibt es dann weitere Filteroptionen:

##### Beispiele für Zahlenfilter   

```  
140-190  
>137.50  
>=137.50  
<=137,50  
<137,50  
137,50  
```   

Komma (,) und Punkt (.) können gleichwertig als Dezimaltrennzeichen benutzt werden.  

##### Beispiele für Datumsfilter

Werden in einer Datumsspalte im Filter nur Jahreszahlen angegeben, gelten die Filterregeln wie bei den Zahlen:
```
1970-1984 
>1984
<=1950
...
```

Auch komplette Daten folgen diesem Muster:
```  
24.12.2027-31.12.2027
>=1.7.2026
...
``` 


### Impressum und Datenschutzerklärung

Am unteren Bildrand findest du einen Link zu "Impressum und Datenschutzerklärung" 
für weitere rechtliche Informationen.


# Mitglieder

Die Rolle "Mitglied" stellt die grundlegende Benutzerebene in unserem System dar. 
Jeder registrierte Nutzer erhält automatisch die Berechtigungen dieser Rolle, ohne 
dass zusätzliche Aktivierungen erforderlich sind. Die Mitgliedsrolle bietet Zugang 
zu allen persönlichen Basisfunktionen und -einstellungen.

## Meine Daten bearbeiten
In diesem Bereich kannst du deine persönlichen Informationen einsehen und 
aktualisieren. Die folgenden Daten werden angezeigt und können bearbeitet werden:

- **Anmelde-ID**: Eine eindeutige Identifikationsnummer im System (nicht editierbar)
- **Vorname**: Dein Vorname
- **Nachname**: Dein Nachname
- **Mail**: Deine E-Mail-Adresse für Kontakt und Benachrichtigungen
- **Geschlecht**: Deine Geschlechtsangabe
- **Geburtsdatum**: Dein Geburtsdatum im Format TT.MM.JJJJ
- **Mailbenachrichtigung**: Einstellung, ob du E-Mail-Benachrichtigungen empfangen 
  möchtest (JA/NEIN)

Die Daten werden in einer übersichtlichen Tabellenform dargestellt und können nach 
Bedarf aktualisiert werden.

## Meine Daten zur Bearbeitung freigeben
In diesem Bereich wird angezeigt, welche Betriebssportgruppen (BSG) Zugriff auf deine 
persönlichen Daten haben. Diese Dateneinsicht ist notwendig, damit die BSG dich 
verwalten und für Sparten anmelden kann. Damit eine BSG deine Daten verarbeiten kann, musst du diese hier zunächst freigeben. Dies geschieht, indem du auf *Einfügen* klickst und damit eine neue Datenzeile mit den entsprechenden Daten hinzugügst.

Eine Berechtigung kannst du erst löschen, wenn du bei dieser BSG nicht mehr angemeldet bist - weder als Stamm-BSG, noch als BSG für eine Sparte.

## Antrag zum Wechsel der Stamm-BSG
Möchtest du eine BSG wechseln, musst du dies hier initiieren. Bitte speche dich vorher mit den entsprechenden Organisatoren ab. Trägst du hier eine BSG ein, wird automatisch dieser BSG das Bearbeitungsrecht deiner Daten eingeräumt. Dies geschieht, bevor du in dieser BSG aufgenommen wirst und wird nicht wieder automatisch entfernt. Kontrolliere daher bitte regelmäßig deine erteilten Berechtigungen.

## Lösche meine Daten
Hier kannst du deine Daten unwiderbringlich aus dem System entfernen. Bedenke, dass deine Daten möglicherweise bis zum Beginn des übernächsten Jahres aufbewahrt werden müssen.

# BSG Organisatoren
Um das Recht zu erhalten, eine BSG im System zu verwalten, muss ein Berechtigter des Regionalverbandes dir dieses zuweisen.

Mit dieser Berechtigung kannst du innerhalb deiner BSG (nachdem diese vom Regionalverband angelegt wurde):

- Die Stammdaten der BSG anlegen und ändern (Ansprechpartner, Rechnungsdaten)
- Registrierte Mitglieder aufnehmen
- Die Mitgliederdaten deiner Stammmitglieder ändern
- Mitglieder in Sparten an- und abmelden  
- Mitgliederdaten manuell importieren oder anlegen

Du kannst auch für mehrere BSG verantwortlich sein.

## BSG-Stammdaten bearbeiten
Bitte vervollstänfige die Daten deiner BSG. Es muss eine Rechnungsadresse und ein registrierter Hauptansprechpartner hinterlegt werden. Hier kannst du diese Daten später auch anpassen.

## Stamm-Mitglieder aufnehmen
Hier siehst du Mitglieder, die derne in eine von die verwaltete BSG möchten. Um ein Mitglied aufzunehmen, wähle in der Spalte *BSG* die entsprechende BSG. In der Spalte *will_nach* siehst du den angegebenen Wechselwunsch. Der Basisbeitrag wird im Allgemeinen von der BSG abgeführt, in der das Mitglied im Abrechnungsjahr zuerst war.

## Stamm-Mitglieder bearbeiten - kompakt und erweitert
Neben den Mitgliedern selbst, hat auch der BSG-Verantwortliche die Möglichkeit, die Daten der Mitglieder der jeweiligen BSG zu bearbeiten. Das Kommentarfeld sowie die Angabe, ob aktiv ohder nicht sind Felder, die nur innerhalb deiner BSG relevant sind und du daher frei belegen kannst. Die Mitgliedschaft in einer BSG ist nur die Grundvorraussetzung, um an einer Sportart bzw. in einer Sparte teilnehmen zu können. Um dies zu tun, musst du deine Mitglieder in einer oder mehreren Sparten anmelden. Bitte beachte: Ein Mitglied kann auch Stammmitglied einer anderen BSG sein und trotzdem für deine BSG in einer Sparte angemeldet werden. Näheres dazu findest du im Kapitel *BSG-Mitglieder in Sparten anmelden*.

## BSG-Mitglieder in Sparten anmelden
Damit du ein Mitglied in einer Sparte anmelden kannst, müssen folgende Voraussetzungen erfüllt sein:  

Das Mitglied...  
- ... muss in einer (beliebigen) Stamm-BSG gemeldet sein.  
- ... muss deiner BSG die Bearbeitungsrechte seiner Daten geben (siehe *Meine Daten zur Bearbeitung freigeben*)
- ... darf in dieser Sparte noch nicht gemeldet sein
  
Vorhandene Datenzeilen können nicht modifiziert werden. Es können nur komplette Datenzeilen gelöscht (Abmeldung) oder eingefügt (Anmeldung) werden. Eine Anmeldung führt immer den jeweiligen Spartenbeitrag für das laufende Jahr nach sich, auch wenn das Mitglied unterjährig wieder abgemeldet wird. 

Bitte beachte, dass nicht alle spartenspezifischen Voraussetzungen in diesem System abgebildet sind, z.B. wenn eine Sparte nur Anmeldungen vor einem Stichtag entgegennimmt oder wenn bestimmte Zusatzvoraussetzungen notwendig sind. Bitte wende dich im Zweifelsfall an die jeweilige Spartenleitung.

## Mitgliede ohne Registrierung
Im **Ausnahmefall** ist es möglich, Mitglieder aufzunehmen, die sich noch nicht registriert haben. Dies sollte nur als letzte Lösung genutzt werden und soll nur den Übergang vom alten System zu MOBS24 in speziellen Fällen erleichtern. Bitte beachte folgende Punkte:

- Für Mitglieder ohne Registrierung übernimmt das Mitglied, welches die Daten importiert, die Verantwortung, dass die jeweiligen eingespielten Personen der Datenverarbeitung zustimmen.
- Eine eigene Registrierung ist weiterhin möglich. Diese soll auch angestrebt werden.
- Sollten das Mitglied sowohl registriert, als auch unregistriert eingetragen sein, kann das System dies nicht selbständig erkennen. Daher müssen diese beiden Datensätze so schnell wie möglich zusammengeführt werden, um eine doppelte Buchführung zu vermeiden.
- Sowohl der Import von Daten, als auch die Zusammenführung von Datensätzen ist sehr fehleranfällig, insbesondere auf der Seite des Anwenders. Alle Eingaben sind sorgfältigst zu prüfen - vor und nach dem Import oder Zusammenführung. MOBS24 hat nur sehr bedingte Möglichkeiten von Plausibilitätsprüfungen. 

### Stamm-Mitglieder ohne Zugang eintragen
Einzelne Mitglieder können über *Einfügen* hinzugefügt werden. Mehrere Mitglieder (auch mehrere hundert) können über die Importfunktion eingelesen werden.Näheres zur Importfunktion ist [im Anhang](#import) beschrieben.

### Mitgliederkonten zusammenführen
Ist ein Mitglied [manuell](#stamm-mitglieder-ohne-zugang-eintragen) angelegt worden und hat sich das selbe Mitglied auch [registriert](#registrierung), müssen beide Konten zusammengefügt werden. Solange dies nicht geschieht gibt es folgende Einschränkungen:

- Das Mitglied ist doppelt in einer (oder mehreren) Stamm-BSG geführt und wird auch entsprechend abgerechnet
- Beide Konten des selben Mitglieds werden unabhängig voneinander in Sparten angemeldet
- Doppelanmeldungen in Sparten sind möglich, da die Konten für das System wie zwei unterschiedliche Mitglieder geführt werden
- Ein nachträgliches Zusammenführen ist mit hohem manuellen Aufwand verbunden.

Es wird daher empfolen, die Konten **möglichst zeitnah** zusammen zu führen. In der ausgewählten Ansicht werden nur jene Mitglieder aufgeführt, die

- von dir gesehen werden dürfen (s. [*Meine Daten zur Bearbeitung freigeben*](#meine-daten-zur-bearbeitung-freigeben)) und
- einen 'Partner-Datensatz' mit identischem Geburtsdatum haben

Nach der Zusammenführung werden vom registriertem Konto die Mailadresse und das Passwort übernommen. Der Rest (**Stamm-BSG**, **Sparten**, etc.) werden vom **unregistrierten/manuellen** Konto übernommen.

Das registrierte Konto hat in der Spalte **y_id** eine Zahl stehen - die ID im Log-In-System. Um ein registriertes Konto (mit y_id) mit einem manuell angelegtem konto (keine y_id) zusammenzuführen, muss die y_id des einen Kontos in das freie Feld des anderen Kontos eingetragen werden. Danach neu laden und die Datensätze verschwinden aus der Ansicht, wenn nicht ein weiteres Konto mit dem gleichen Geburtsdatum gibt.

**Neben der Überprüfung der Geburtsdaten gibt es systemseitig keine weitere Validierung! Diese Funktion daher bitte mit großer Sorgfalt nutzen. Es wird empfolen, diesen Vorgang nicht am Handy, sondern am PC zu erledigen.** 

# Mitarbeiter im Regionalverband
Um das Recht zu erhalten, den Regionalverband zu verwalten, muss ein Berechtigter des Landesverbandes dir dieses zuweisen.

Mit dieser Berechtigung kannst du innerhalb deines Regionalverbandes (nachdem dieser vom Landesverband angelegt wurde):

- Betriebssportgemeinschaften (BSG) anlegen
- Das Recht zur Verwaltung bestimmter BSG vergeben
- Diverse Berichte lesen
  
Alle Ansichten und Aktionen sind selbsterklärend. Bitte beachte, dass du nur Regionalverbände siehst, auf die du vom Landesverband berechtigt wurdest.

## Erweiterte Rechte
Der Landesverband kann auch erweiterte Rechte auf der Ebene des Regionalverbands erteilen. Damit werden Eingaben zur Kasse und für die Einrichtung und Löschung von Sparten ermöglicht. Im einzelnen sind dies:

- **Sparten im Regionalverband einfügen, löschen und bearbeiten**
- **Zahlungseingänge**  
  Hier können die Zahlungseingänge der BSG eingegeben werden. In einer späteren Programmversion könnte dies automatisiert eingelesen werden. Die Zahlungseingänge werden im Bericht 'Salden' für alle Verwalter im Regionalverband und für die jedeilige BSG auch den Verwalter der BSG mit den fälligen Mitgliedsbeiträgen, die MOBS24 aus den Anmeldungen zusammenstellt, zur Verfügung gestellt.
- **Offene Forderungen (Notizen)**  
  Diese Ansicht dient lediglich als Notizbuch z.B. zur Verfolgung offener Forderungen, da Rechnungsverfolgung und Mahnwesen außerhalb von MOBS24 stattfindet.


# Mitarbeiter im Landesverband
Um das Recht zu erhalten, den Landesverband zu verwalten, muss ein Systemadministrator dir dieses Recht zuweisen.

- **Regionalverbände einfügen, löschen und bearbeiten**  
- **Rechte zur Verwaltung in den Regionalverbänden vergeben**  
  Die Rechtevergabe ist analog zur Rechtevergabe für BSG. Der einzige Unterschied ist das Feld 'erweiterte Rechte'. Dies bestimmt, ob das Mitglied Zugriff auf die oben beschriebenen [erweiterten Rechte](#erweiterte-rechte) erhält.  

# Systemadministratoren

## Offene Issues
## Error-Log
## Rollback
## Nutzerzahlen
## Fehlermeldungen anpassen
## Zuweisung von Landesverbans- und Systemadminrechten
## Backdoor
  
  
<hr style="border: 3px solid red;">
  
    

LOP:
# Workflows (ggf. mit Hintergründe) / grafisch?
## Registrieren / inkl. BSG-Sicht bis zur AUfnahme in die Stamm-BSG

## Meldeliste
## Spartenanmeldung
## Import
## Wechsel der BSG 
### für eine Sparte
### Stamm BSG
## Eine BSG neu Einrichten

LOP2:
Doku für Systemadmin:
- config.php (inkl. info: und ajax:)
- sys-tables
- cronjobs
- ypum (lock-Backdoor über Konsole)
- user_code
- user-includes





# Auswirkungen von UPDATE und DELETE auf die Datenbank

Dieses Dokument erklärt für Sie als Anwender, was passiert, wenn Sie Datensätze in der Datenbank **ändern (UPDATE)** oder **löschen (DELETE)**. Es wird besonders auf sogenannte „Kettenreaktionen“ geachtet, also was mit verbundenen Daten passiert.

---

## UPDATE (Ändern von Daten)

**Was bedeutet UPDATE?**  
Wenn Sie einen Datensatz ändern (z.B. den Namen eines Vereins oder einer Sparte), wird der neue Wert in der Datenbank gespeichert.  
**Wichtig:**  
- In den meisten Fällen werden die Änderungen automatisch auch in allen verbundenen Tabellen übernommen, sodass die Verknüpfungen erhalten bleiben.
- Es gehen keine Daten verloren.

**Beispiel:**  
- Sie ändern den Namen einer Sparte. Alle Mitglieder, die dieser Sparte zugeordnet sind, bleiben weiterhin korrekt zugeordnet.

---

## DELETE (Löschen von Daten)

**Was bedeutet DELETE?**  
Wenn Sie einen Datensatz löschen (z.B. eine Sparte, ein Mitglied oder einen Verein), kann dies Auswirkungen auf andere Daten haben, die mit diesem Datensatz verbunden sind.  
Je nach Einstellung werden diese Daten entweder **mitgelöscht** oder das Löschen wird **verhindert**.

### Übersicht der wichtigsten Kettenreaktionen beim Löschen

| Tabelle (was wird gelöscht?) | Was passiert mit verbundenen Daten? | Erklärung für Sie |
|-----------------------------|-------------------------------------|-------------------|
| **Sparte**                  | Alle Zuordnungen von Mitgliedern zu dieser Sparte werden ebenfalls gelöscht. | Die Mitglieder bleiben erhalten, aber ihre Zugehörigkeit zu dieser Sparte wird entfernt. |
| **Mitglied**                | Alle Zuordnungen zu Sparten, Berechtigungen und Historien werden ebenfalls gelöscht. | Das Mitglied wird komplett entfernt, inklusive aller Verknüpfungen. |
| **Verein (BSG)**            | Alle Mitglieder, Zahlungen, Forderungen, Berechtigungen und Zuordnungen zu diesem Verein werden ebenfalls gelöscht. | Der Verein und alle zugehörigen Daten werden entfernt. |
| **Regionalverband**         | Alle Vereine und Sparten dieses Verbands werden ebenfalls gelöscht, inklusive aller deren Verknüpfungen. | Der Verband und alles, was dazu gehört, wird entfernt. |
| **Mitglied in Sparte**      | Nur diese eine Zuordnung wird gelöscht. | Das Mitglied bleibt, ist aber nicht mehr in dieser Sparte. |
| **Benutzer (User)**         | Alle Rechte und Details zu diesem Benutzer werden ebenfalls gelöscht. | Der Benutzer und alle zugehörigen Informationen werden entfernt. |

### Was wird **nicht** automatisch gelöscht?

- Wenn Sie einen Datensatz löschen, der noch von anderen Daten benötigt wird (z.B. ein Verein, dem noch Mitglieder zugeordnet sind), kann das Löschen manchmal **verhindert** werden, um Datenverlust zu vermeiden.
- In manchen Fällen werden Verknüpfungen einfach entfernt, ohne dass weitere Daten gelöscht werden.

---

## Zusammengefasst

- **Ändern (UPDATE):**  
  Ihre Änderungen werden überall übernommen, Verknüpfungen bleiben erhalten.

- **Löschen (DELETE):**  
  Es können automatisch auch andere, verbundene Daten gelöscht werden. In manchen Fällen wird das Löschen verhindert, um wichtige Daten zu schützen.

## Prompt für die automatische Dokumentation der Auswirkungen von UPDATE und DELETE im Datenbankschema:

Analysiere das folgende SQL-Datenbankschema. Erstelle eine verständliche, tabellarische Übersicht für Nicht-ITler, die erklärt, was beim Ändern (UPDATE) und Löschen (DELETE) von Datensätzen in jeder Tabelle passiert.  

- Berücksichtige alle Fremdschlüssel-Constraints und deren ON DELETE/ON UPDATE-Regeln.
- Beschreibe für jede Tabelle, welche Kettenreaktionen beim Löschen oder Ändern auftreten (z.B. welche verbundenen Daten mitgelöscht werden oder erhalten bleiben).
- Gib für UPDATE eine kurze allgemeine Erklärung, was das bedeutet.
- Gib für DELETE eine Tabelle mit: „Was wird gelöscht?“, „Was passiert mit verbundenen Daten?“, „Erklärung für den Anwender“.
- Formatiere das Ergebnis als Markdown-Code.
- Die Aussagen sollen exakt zum vorliegenden Schema passen, nicht allgemein gehalten sein.

Hier ist das Schema:
[Füge hier das aktuelle SQL-Schema ein]
