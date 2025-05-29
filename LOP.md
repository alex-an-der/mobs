Was ist der Unterschied individuell und BSG? Kann man das nicht über BSG-Check machen?
Beide checken die BSG-Berechtigungen. Der Unterschied ist lediglich der Eingangsparameter:
FIND_IN_SET(BSG, berechtigte_elemente($uid, 'BSG')) > 0 => Darf $uid die BSG sehen?
FIND_IN_SET(m.id, berechtigte_elemente($uid, 'individuelle_mitglieder')) > 0 => Darf $uid diese m.id sehen?
Den Rest macht dann eben die SQL-Function

In der DB ist das anders dargestellt: (daher ggf. etwas verwirrend, aber das hat 2 verschiedene POV)
RV und BSG-Rechte: Einem Nutzer werden Rechte gewährt (Alex -> Nutzis := Alex darf alle Nutzis sehen)
indiv. Rechte:     Ein Nutzer gewährt einer BSG die Rechte (Martin -> Nutzis := Die Nutzis dürfen die Daten von Martin sehen)
Beides in Kombination: Alex darf die Daten von Martin sehen und das funktioniert immer über berechtigte_elemente, egal mit welchem Eingangsparameter.

INSERT INTO b_mitglieder (Vorname,Nachname,BSG,Stammmitglied_seit,Mail,Geschlecht,Geburtsdatum,aktiv) 
VALUES ('Tommy Manuell','Nocker',1,'1966-06-06','NeueMail@Nocker.de',3,'1966-06-06',1);


# Rechnungserstellung
- Rechnungserzeugung? PDF??
- Rechnungen können in der Cloud abgelegt werden - Link kann gespeichert werden

## Nächste Schritte


## In der Prod-DB einfügen 
aaaa@a.a
```

```


SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `adm_issues`;
TRUNCATE TABLE `adm_log`;
TRUNCATE TABLE `adm_rollback`;
TRUNCATE TABLE `adm_usercount`;
TRUNCATE TABLE `b___an_aus`;
TRUNCATE TABLE `b___geschlecht`;
TRUNCATE TABLE `b___sportart`;
TRUNCATE TABLE `b_bsg`;
TRUNCATE TABLE `b_bsg_deleted`;
TRUNCATE TABLE `b_bsg_rechte`;
TRUNCATE TABLE `b_bsg_wechselantrag`;
TRUNCATE TABLE `b_forderungen`;
TRUNCATE TABLE `b_individuelle_berechtigungen`;
TRUNCATE TABLE `b_mitglieder`;
TRUNCATE TABLE `b_mitglieder_deleted`;
TRUNCATE TABLE `b_mitglieder_in_sparten`;
TRUNCATE TABLE `b_regionalverband`;
TRUNCATE TABLE `b_regionalverband_rechte`;
TRUNCATE TABLE `b_sparte`;
TRUNCATE TABLE `b_zahlungseingaenge`;
TRUNCATE TABLE `y_deleted_users`;
TRUNCATE TABLE `y_roles`;
TRUNCATE TABLE `y_sites`;
TRUNCATE TABLE `y_user`;
TRUNCATE TABLE `y_user_details`;
TRUNCATE TABLE `y_user_fields`;
SET FOREIGN_KEY_CHECKS = 1;