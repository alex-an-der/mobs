# MOBS (Mitgliederorganisation im Betriebssport) - Handbuch
Aktuelle Version: v0.0.6
- [MOBS (Mitgliederorganisation im Betriebssport) - Handbuch](#mobs-mitgliederorganisation-im-betriebssport---handbuch)
- [Grundlegendes](#grundlegendes)
  - [Bedienungskonzepte](#bedienungskonzepte)
    - [Anlegen von Datensätzen](#anlegen-von-datensätzen)
    - [Löschen von Datensätzen](#löschen-von-datensätzen)
    - [Auswahl von Datensätzen](#auswahl-von-datensätzen)
    - [Filtern](#filtern)
    - [Speichern](#speichern)
    - [Dubletten](#dubletten)
    - [Support](#support)
- [Ich bin ein Mitglied - was muss ich tun?](#ich-bin-ein-mitglied---was-muss-ich-tun)
  - [Registrieren](#registrieren)
  - [Verwaltung deiner Daten](#verwaltung-deiner-daten)
    - [Meine Daten bearbeiten](#meine-daten-bearbeiten)
    - [Meine Daten zur Bearbeitung freigeben](#meine-daten-zur-bearbeitung-freigeben)
    - [Sonstige Funktionen](#sonstige-funktionen)
- [Ich möchte im Regionalverband Sparten bearbeiten](#ich-möchte-im-regionalverband-sparten-bearbeiten)
- [Ich verwalte eine BSG - was muss ich tun?](#ich-verwalte-eine-bsg---was-muss-ich-tun)
  - [BSG Stammdaten pflegen](#bsg-stammdaten-pflegen)
  - [BSG Stamm-Mitglieder pflegen](#bsg-stamm-mitglieder-pflegen)
  - [BSG-Mitglieder in Sparten anmelden](#bsg-mitglieder-in-sparten-anmelden)

# Grundlegendes 
Mit MOBS lassen sich alle Elemente eines Landesbetriebssportverbandes verwalten. Dabei werden folgende Begrifflichkeiten verwendet:
| Name | Abkürzung | Erklärung | Beispiel | Angaben zum Datenschutz |
| --- | --- | --- | --- | --- 
| Landesverband | LV | Der Dachverband in einem Bundesland. | LBSV Niedersachsen | Der Landesverband darf alle Mitgliederdaten einsehen und darf Regionalverbände einrichten, sowie die Rechte darauf vergeben. |
| Regionalverband | RV | Dem Landesverband untergeordnetete Regionalverbände. Ein RV gehört genau zu einem LV. | BSV Hannover | Der Regionalverband darf alle Mitgliederdaten in seinem Regionalverband einsehen. Er darf Sparten und Betriebssportgruppen einrichten und Rechte darauf vergeben.|
| Sparte |   | Die Sparten der jeweiligen Regionalverbände. Eine Sparte ist immer genau einem RV zugeordnet. Gleinamige Sparten verschiedener RV sind verschiedene Sparten. Es kann aber verschiedene Sportarten geben, die die selbe Sportart ausüben. Eine Sparte muss genau einer Sportart zugewiesen werden. | Darts, Bowling | Da Sparten von den RV organisiert sind, gelten die gleichen Ansichtsrechte wie beim RV.|
| Betriebssportgruppe | BSG | Eine Betriebssportgruppe ist die Gruppe der Betriebssport-Teilnehmer, die einem gemeinsamen Unternehmen oder Verein zugeordnet sind. Eine BSG kann Mitglieder in verschiedenen Sparten anmelden, jedoch nur innerhalb eines RV. | BSG Volkswagen | Um von einer BSG gesehen zu werden, muss jeder Teilnehmer die Ansicht freigeben. |
| Mitglied |  | Jedes Mitglied wird in MOBS erfasst und ist verantwortlich für die Aktualität seiner Daten. Jedes Mitglied muss genau **einer** Stamm-BSG zugeordnet sein. Die Stamm-BSG führt die Basis-Mitgliedsbeiträge ab. Für die Sparten können auch verschiedene BSG ausgewählt werden. Voraussetzung: Die BSG sind einverstanden und haben das Ansichtsrecht für die Daten.|  | Es gelten die oben genannten Ansichtsrechte. Die Ansicht und Bearbeitung durch eine BSG muss vom Mitglied ausdrücklich genehmigt werden. Ohne Erlaubnis kann anderseits keine Bearbeitung erfolgen. |
Alle Daten werden auf Servern innerhalb der EU gespeichert. Es gelten die Datenschutzbestimmungen des LBSV Niedersachsen.

[zurück](#mobs-mitgliederorganisation-im-betriebssport---handbuch)  
## Bedienungskonzepte
Die verschiedenen Ansichten werden aucb Tabellen genannt und können über die oberere Auswahlbox angezeigt werden. Die Auswahl der möglichen Ansichten ist abhängig von den Rollen und damit einhhergehenden Rechten des angemeldeten Nutzers. Bis du z.B. für keine BSG als Verantwortlicher vom RV eingetragen, siehst du die Ansichten zur BSG-Verwaltung auch nicht. In der Hauptansicht unten ist prinzipiell eine Zeile ein Datensatz. Die Begriffe werden hier synonym verwendet.
### Anlegen von Datensätzen
Neue Zeilen werden mit _Einfügen_ angelegt. Dabei ist es kontextabhängig, was ein neuer Datensatz ist. So wird mit _einfügen_ auf der Seite zur Vergabe von Berechtigungen eine neue Berechtigung eingefügt, auf der Seite der Betriebssportgemeinschaften eine neue BSG. 
### Löschen von Datensätzen
Um einen Datensatz zu enfernen, kann der Knopf _Ausgewähltes löschen_ benutzt werden. Bitte sei dir bewusst, dass dies meist eine Kaskade an Löschungen auslöst. Löscht du zB. ein Mitglied aus deiner BSG, so wird er auch aus den Sparten gelöscht, in denen er für die BSG angetreten ist.  
### Auswahl von Datensätzen
Für einige Aktionen (z.B. _Löschen_) ist eine Auswahl von einem oder mehreren Datensätzen erforderlich. Dies geschieht mit den Checkboxen zu beginn jeder Zeile. Gibt es keine Checkboxen, muss auch nichts ausgewählt werden. Wird die Checkbox im Tabellenkopf aktiviert, werden alle sichtbaren Datensätze aktiviert.  
### Filtern
In der Filterzeile (über der Tabelle) kannst du einen Text eingeben. Nach diesem wird die gesamte Tabelle gefiltert - egal, wo dieser Text steht. Klickst du mit der rechten Maustaste auf ein Tabellenfeld, wird der Inhalt als Filtertext übernommen. Klickst du erneut darauf wird er wieder gelöscht. Mit einem blauem Hintergrund werden die Zellen markiert, die den gesuchten Text enthalten. Zum Löschen des Filers, lösche die Filter-Eingabe.
### Speichern
Die Daten werden beim Verlassen des Feldes automatisch an die Datenbank gesendet (= gespeichert). Wird das Feld rot, konnten die Daten nicht gespeichert werden. Entweder ist keine Verbindung vorhanden (bitte Internetverbindung prüfen) oder das Datenformat stimmt nicht mit dem erwartetem Format überein (z.B. Buchstaben im Geburtsdatum). Das Feld wird grün, wenn ein erfolgreicher Verifizierungslauf durchgeführt wurde: Dann wurden die Daten gespeichert und kontrolliert.
### Dubletten
Bitte prüfe deine Tabellen regelmäßig auf Doppeleinträge, indem du nach Dubletten suchst (Knopf in der Bedienleiste).
### Support 
Fragen kannst du jederzeit an support@mobs24.de richten.

[zurück](#mobs-mitgliederorganisation-im-betriebssport---handbuch)  
# Ich bin ein Mitglied - was muss ich tun?  
## Registrieren
Wenn noch nicht geschehen, registriere dich bitte, idem du bei der Anmeldung auf 'Registrieren' klickst. Fülle die Felder wahrheitsgemäß aus. Falsche Angaben können zur Verlust des Versicherungsschutzes oder zum Ausschluss aus der Sparte, RV oder LV führen. Alle Daten sind später noch bearbeitbar. Zum Abschluss der Registrierung folge bitte den Angaben des Registrierungsprozesses. Du kannst später deine Daten selbständig löschen und jederzeit einsehen, welche personenbezogebeb Daten von dir gespeichert sind.  
## Verwaltung deiner Daten
Nach dem erfolgreichen Login, siehst du folgende Punkte:
### Meine Daten bearbeiten
Passe hier deine Daten an, wenn es Veränderungen gibt.
### Meine Daten zur Bearbeitung freigeben
Um die innerhalb einer BSG bearbeiten zu können, müssen die Verwalter dieser BSG auf deine Daten zugreifen können. Hier kannst du deine Daten entsprechend einer oder mehreren BSG freigeben. Du kannst neue Berechtigungen mit 'einfügen' anlegen und mit 'Ausgewählte löschen' wieder entfernen. 
### Sonstige Funktionen
Du kannst unter dem entsprechenden Punkt deine Daten löschen oder verschiedene Berichte einsehen.

[zurück](#mobs-mitgliederorganisation-im-betriebssport---handbuch)  
# Ich möchte im Regionalverband Sparten bearbeiten
Für das Einrichten von Sparten benötigst du besondere Systemrechte. Bitte nehme Kontakt mit einem Administrator auf: support@mobs24.de

[zurück](#mobs-mitgliederorganisation-im-betriebssport---handbuch)  
# Ich verwalte eine BSG - was muss ich tun?
**Wichtig:** Um ein Mitglied auswählen oder bearbeiten zu können, muss das jeweilige Mitglied der BSG die Bearbeitungsrechte einräumen (siehe [Meine Daten zur Bearbeitung freigeben](#meine-daten-zur-bearbeitung-freigeben)).
## BSG Stammdaten pflegen
Unter BSG-Stammdaten bitte den Hauptansprechpartner und die Rechnungsdaten pflegen.
## BSG Stamm-Mitglieder pflegen
Jedes Mitglied benötigt zunächst eine Stamm-BSG, die den Basis-Verbandsbeitrag abführt. Dies funktioniert wie folgt:
1. Ein Mitglied räumt einer BSG Bearbeitungsrechte ein.
2. Daraufhin erscheint das Mitglied ohne BSG-Zuweisung ('---') in allen freigegebenen BSG.
3. Die Stamm-BSG weist die zutreffende BSG dem Mitglied zu (klick auf '---' und entsprechende Auswahl).

Wenn das Mitglied nicht sichtbar ist, kann dies verschiedene Ursachen haben:
1. Das Mitglied hat sich noch nie eingeloggt (erst dann wird das Mitglied im System angelegt).
2. Das Mitglied hat der BSG nicht die Bearbeitungsrechte eingeräumt.
3. Das Mitglied hat bereits eine andere Stamm-BSG. Bitte zuerst dort wieder auf '---' setzen. Erst dann kann eine neue BSG zugewiesen werden.
   
Um die Stammmitgliedschaft eines Mitglieds zu beenden, bitte als BSG wieder '---' auswählen.

## BSG-Mitglieder in Sparten anmelden
Mit der Aufnahme in die BSG als Stammmitglied ist dem Mitglied noch keine Sparte zugewiesen. Dies muss hier gemacht werden - auch wenn die BSG nur eine einzige Sparte bedient. Auch nicht-Stammmitglieder können hier Sparten zugewiesen werden, wenn Sie in diesen für die BSG antreten.

Um eine Anmeldung durchzuführen, klicke auf _Einfügen_ und wähle die Felder entsprechend aus. Um ein Mitglied abzumelden, lösche in dieser Ansicht die entsprechende Zeile.

Wenn ein Mitglied nicht sichtbar ist, , kann dies verschiedene Ursachen haben:
1. Das Mitglied hat sich noch nie eingeloggt (erst dann wird das Mitglied im System angelegt).
2. Das Mitglied hat der BSG nicht die Bearbeitungsrechte eingeräumt.
3. Das Mitglied hat noch keine Stamm-BSG. Eine Stamm-BSG ist die Grundvoraussetzung für eine Spartenzuordnung.  
[zurück](#mobs-mitgliederorganisation-im-betriebssport---handbuch)  