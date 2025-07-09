# Liste der E-Mails die vom System generiert werden

Als Grundlage für alle E-Mails wird das Template aus dem Standard-Theme verwendet.

`/themes/*theme*/templates-mail/mail.tpl`

Alle E-Mails können mit einer Signatur erweitert werden. Diese Signatur wird als Snippet angelegt:
`footer_text_mail`

## Registrieren

Können sich Benutzer am System anmelden, müssen sie ihre E-Mail Adresse bestätigen.

Übersetzungen:
* Snippet: 'account_confirm_mail'

Ersetzungen:
* `{USERNAME}` der gewählte Benutzername
* `{ACTIVATIONLINK}` die absolute URL, die der Benutzer aufrufen muss um seinen Account freizuschalten
* `{SITENAME}` (optional)


## Passwort  zurücksetzen

Hat ein Benutzer sein Passwort vergessen, kann er dieses zurücksetzen.

Übersetzungen:
* Text: `forgotten_psw_mail_info` oder das Snippet `account_reset_psw`
* Betreff: `forgotten_psw_mail_subject`

Ersetzungen:
* `{USERNAME}` der Benutzername
* `{RESET_LINK}` die (absolute) URL um das Zurücksetzen zu bestätigen

## Bestellungen (Warenkorb senden)

Sobald ein Benutzer eine Bestellung abgesendet hat, bekommt er eine Bestellbestätigung.

Übersetzungen
* Snippet: `mail_salutation_order_confirmation`
* Betreff: `mail_subject_order_sent`

Ersetzungen
* `{order_nbr}` Bestellnummer
* `{order_time}` Uhrzeit der Bestellung
* `{order_date}` Datum der Bestellung
* `{payment_status}` Status der Bezahlung
* `{invoice_address}` Rechnungsadresse
* `{shipping_address}` Lieferadresse
* `{price_subtotal}` Zwischensumme (ohne Versandkosten)
* `{price_shipping}` Versandkosten
* `{included_tax}` enthaltene Steuern
* `{currency}` Währung
* `{price_total}` Gesamtpreis
* `{order_user_comment}` Vom Benutzer angegebene Bemerkung zur Bestellung
* `{order_products}` Alle bestellten Produkte als Tabelle
* Es können __alle__ Einträge aus den Sprachdateien verwendet werden `{lang_*}`

Template: `/themes/*theme*/templates-mail/send-order-status.tpl`

<hr>

### Übersetzungen / Texte anpassen

Änderungen an den Sprachdateien sind nicht zu empfehlen, da sie bei jedem Update zurückgesetzt werden. Es gibt zwei Wege die Sprachdateien zu ändern:

* Änderungen in der offiziellen [Übersetzung bei Crowdin](https://de.crowdin.com/project/swiftyedit) beantragen
* Ein Plugin zum ersetzen / erweitern der Sprachdateien (zum Beispiel das Plugin `alp`)

