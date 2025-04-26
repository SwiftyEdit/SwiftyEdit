---
title: Snippets
description: Snippets (Textvorlagen)
btn: Snippets
group: backend
priority: 200
---
# Snippets verwalten, erstellen oder bearbeiten

<kbd>admin/snippets/</kbd>

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

<table class="table">
<tr>
<td><pre>Name</pre></td>
<td>Anhand diesem Namen wird Dein Snippet eingebunden. Du kannst den Namen öfter als einmal vergeben.
Eingebunden wird immer das zuletzt angelegte Snippet. Natürlich wird die Sprache berücksichtigt.</td>
</tr>
<tr>
<td><pre>Titel</pre></td>
<td>In den meisten Templates wird der Titel zu einer Headline (H1-H6).</td>
</tr>
<tr>
<td><pre>Inhalt</pre></td>
<td>Hier wird der eigentliche Inhalt des Snippets gespeichert.</td>
</tr>
<tr>
<td><pre>Schlüsselwörter</pre></td>
<td>Falls Du ein Plugin einsetzt, welches Snippets nach Schlüsselwörtern filtert, kannst Du diese hier eingeben.
Weitere Informationen findest Du in der Dokumentation des Plugins.</td>
</tr>
<tr>
<td><pre>Klassen</pre></td>
<td>Je nach verwendetem Template/Theme kannst Du Deinem Snippet CSS Klassen zuweisen.
Diese werden dann an das Template übergeben.</td>
</tr>
<tr>
<td><pre>Label</pre></td>
<td>Über dieses Feld kannst Du Deinem Snippet ein Label wie z.B. "Neu" oder "Exklusiv" zuweisen. 
Dieses Feld ist nicht zu verwechseln mit den Labels die im Backend für sämtliche Datensätze vergeben werden können.</td>
</tr>
<tr>
<td><pre>Bilder</pre></td>
<td>Falls Du Deinem Snippet Bilder zuweisen möchtest, kann Du hier aus den Uploads die gewünschten Bilder auswählen.</td>
</tr>
<tr>
<td><pre>URL</pre></td>
<td>Wenn Dein Snippet als Link verwendet wird, kannst Du hier die URL und die Link bezeichnung eingeben.</td>
</tr>
<tr>
<td><pre>Eigenes Template</pre></td>
<td>Falls die installierten Themes die Snippet-Funktionen unterstützen, werden diese hier in der Auswahl angezeigt.</td>
</tr>
</table>


## Vordefinierte Snippets

<table class="table">
<tr>
<th>Snippet</th>
<th>Beschreibung</th>
</tr>
<tr>
<td><code>account_confirm</code></td>
<td>Diese Meldung wird angezeigt, sobald ein Benutzer seinen Account bestätigt hat. </td>
</tr>
<tr>
<td><code>account_confirm_mail</code></td>
<td>Wird als E-Mail versendet damit ein Benutzer seinen Account freischalten kann.
Dieses Snippet enthält einige Variablen:

* <code>{USERNAME}</code> der Benutzername
* <code>{SITENAME}</code> der Name der Seite (wird in den Einstellungen festgelegt)
* <code>{ACTIVATIONLINK}</code> wird von SwiftyEdit automatisch ersetzt.

</td>
</tr>
<tr>
<td><code>no_access</code></td>
<td>Falls ein Benutzer eine Seite aufruft, für die er keine Berechtigung besitzt, wird diese Meldung ausgegeben.</td>
</tr>
</table>


### Reservierte Namen

Einige Module können direkt auf Snippets zugreifen und diese einbinden.
Meistens wird Dir direkt eine Auswahl mit den passenden Snippets angezeigt.

#### Produkte, Posts, Events

* Bei der Lieferzeit kannst Du alle Snippets mit dem Namen <code>shop_delivery_time</code> auswählen.
* Alle Snippets mit dem Namen <code>%post_price%</code> können zu den Preisen hinzugefügt werden.
* Alle Snippets mit dem Namen <code>%post_text%</code> können zu den Texten hinzugefügt werden.
* Plugins für Bezahlmethoden nutzen Snippets mit den Namen <code>`cart_pm_%`</code>
* Plugins für den Versand nutzen Snippets mit den Namen <code>`cart_dm_%`</code>
* Im Warenkorb wird das Snippet <code>`cart_agree_term`</code> für die Bestätigung der AGB genutzt.