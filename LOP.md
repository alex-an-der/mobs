Das aktuelle Problem:

"Anmelden in Sparten" sehe ich nicht die manuell angelegten Mitglieder, da diese nicht durch den check "WHERE FIND_IN_SET(m.id, berechtigte_elemente($uid, 'individuelle_mitglieder')) > 0 " kommen.

Grundsätrzlich klären und dokumentieren: Was ist der Unterschied individuell und BSG? Kann man das nicht über BSG-Check machen?





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