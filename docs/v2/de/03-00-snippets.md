---
title: Snippets
description: Manage and use Snippets
btn: Snippets
group: backend
priority: 200
---
# Manage and use Snippets

<kbd>Backend</kbd> <kbd>Contents</kbd> <kbd>Snippets</kbd>

Die Möglichkeiten wie Snippets eingesetzt werden können sind grenzenlos.
Immer wenn Du bestimmte Textphrasen mehr als einmal benötigst, können diese als Snippet gespeichert
und dann per Shortcode eingesetzt werden.

Auf der Übersicht werden alle Snippets aufgelistet.
Du kannst die Liste sortieren, nach Sprachen filtern und natürlich auch durchsuchen.
Falls Du mit sehr vielen Snippets arbeitest, kannst Du mit den Labels Ordnung halten.

__Tipp:__ Die Snippets sind mehrsprachig. Wenn Du eine mehrsprachige Seite verwendest,
wird immer das Snippet mit der passenden Sprache in Deinen Inhalt geladen.
Sollte das Snippet nicht in der gewünschten Sprache verfügbar sein, wird automatisch das Snippet
in der Standard-Sprache geladen.

## Eingabefelder

| Feld             | Beschreibung                                                                                                                                                                                                     |
|------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Name             | Anhand diesem Namen wird Dein Snippet eingebunden. Du kannst den Namen öfter als einmal vergeben. Eingebunden wird immer das zuletzt angelegte Snippet. Natürlich wird die Sprache berücksichtigt.               |
| Titel            | In den meisten Templates wird der Titel zu einer Headline (H1-H6).                                                                                                                                               |
| Inhalt           | Hier wird der eigentliche Inhalt des Snippets gespeichert.                                                                                                                                                       |
| Schlüsselwörter  | Falls Du ein Plugin einsetzt, welches Snippets nach Schlüsselwörtern filtert, kannst Du diese hier eingeben. Weitere Informationen findest Du in der Dokumentation des Plugins.                                  |
| Klassen          | Je nach verwendetem Template/Theme kannst Du Deinem Snippet CSS Klassen zuweisen. Diese werden dann an das Template übergeben                                                                                    |
| Label            | Über dieses Feld kannst Du Deinem Snippet ein Label wie z.B. "Neu" oder "Exklusiv" zuweisen. Dieses Feld ist nicht zu verwechseln mit den Labels die im Backend für sämtliche Datensätze vergeben werden können. |
| Bilder           | Falls Du Deinem Snippet Bilder zuweisen möchtest, kann Du hier aus den Uploads die gewünschten Bilder auswählen.                                                                                                 |
| URL              | Wenn Dein Snippet als Link verwendet wird, kannst Du hier die URL und die Link bezeichnung eingeben.                                                                                                             |
| Eigenes Template | Falls die installierten Themes die Snippet-Funktionen unterstützen, werden diese hier in der Auswahl angezeigt.                                                                                                  |


## Vordefinierte Snippets

| Snippet                | Beschreibung                                                                                                           |
|------------------------|------------------------------------------------------------------------------------------------------------------------|
| `account_confirm`      | Diese Meldung wird angezeigt, sobald ein Benutzer seinen Account bestätigt hat.                                        |
| `no_access`            | Falls ein Benutzer eine Seite aufruft, für die er keine Berechtigung besitzt, wird diese Meldung ausgegeben.           |
| `account_confirm_mail` | Wird als E-Mail versendet damit ein Benutzer seinen Account freischalten kann. Dieses Snippet enthält einige Variablen |

* `{USERNAME}` der Benutzername
* `{SITENAME}` der Name der Seite (wird in den Einstellungen festgelegt)
* `{ACTIVATIONLINK}` wird von SwiftyEdit automatisch ersetzt.


### Reservierte Namen

Einige Module können direkt auf Snippets zugreifen und diese einbinden.
Meistens wird dir direkt eine Auswahl mit den passenden Snippets angezeigt.

#### Produkte, Posts, Events

* Bei der Lieferzeit kannst Du alle Snippets mit dem Namen `shop_delivery_time` auswählen.
* Alle Snippets mit dem Namen `%post_price%` können zu den Preisen hinzugefügt werden.
* Alle Snippets mit dem Namen `%post_text%` können zu den Texten hinzugefügt werden.
* Plugins für Bezahlmethoden nutzen Snippets mit den Namen `cart_pm_%`
* Plugins für den Versand nutzen Snippets mit den Namen `cart_dm_%`
* Im Warenkorb wird das Snippet `cart_agree_term` für die Bestätigung der AGB genutzt.