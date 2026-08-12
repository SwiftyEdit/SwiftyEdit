---
title: Seiten
description: Seiten erstellen, bearbeiten und verwalten
btn: Seiten
group: backend
priority: 190
---

# Seiten anlegen, bearbeiten und verwalten

<kbd>Backend</kbd> ▶ <kbd>Seiten</kbd>

## Geordnete Seiten {#ordered-pages}
Die Seiten, die eine Sortierungsnummer enthalten, werden als geordnete Seiten aufgelistet.
Diese Seiten werden automatisch in der (Haupt-)Navigation aufgeführt.

## Einzelne Seiten {#single-pages}
Die einzelnen Seiten werden nicht in die Navigation einbezogen –
können aber dennoch in der Sitemap oder den Suchergebnissen enthalten sein.

Du kannst die Seiten nach Status (Öffentlich, Unsichtbar, Privat, Entwurf) und Sprache filtern.
Zusätzlich gibt es natürlich eine Suchfunktion.

## Sortierung {#sorting}

So sortierst Du Seiten ganz einfach.

### Einzelseite

- Einzelseiten haben keine Sortierfunktion.
- Sie sind nicht in der Navigation enthalten.
- Das Eingabefeld für Position hat hier **keine** Funktion.

### Portal

- Portalseiten können auch als Startseiten bezeichnet werden.
- Jede aktivierte oder verwendete Sprache sollte eine Portalseite haben.
- Auch hier hat das Eingabefeld für Position **keine** Funktion.

### Hauptmenü

- Hauptmenü-Seiten erstellen, wie der Name schon sagt, einen Eintrag im Hauptmenü.
- Das Feld „Position“ bestimmt, wo die Seite einsortiert wird.

### Diese Seite ist eine Unterseite von ...

- Hier kann dem Menü eine Seite (als Unterseite) hinzugefügt werden.
- Das Feld "Position" bestimmt auch hier wieder, an welcher Stelle die Seite einsortiert wird.

Tipp: Das Feld "Position" darf nur eine Zahl oder das Wort portal enthalten. Oder leer bleiben.

### Sortierbeispiel

| Sortieren | Seite      |
|-----------|------------|
| 100       | Startseite |
| 200       | Produkte   |
| 200.100   | Äpfel      |
| 200.200   | Birnen     |
| 300       | Kontakt    |


#### Ergebnis:

- Startseite
- Produkte
    - Äpfel
    - Birnen
- Kontakt

```html
<ul>
    <li>Startseite</li>
    <li>Produkte
        <ul>
            <li>Äpfel</li>
            <li>Birnen</li>
        </ul>
    </li>
    <li>Kontakt</li>
</ul>
```


### Aktiviere den Blog, Events oder den Shop

Über die Auswahl der Beitragstypen legst Du fest, welche Einträge auf dieser Seite angezeigt werden.
Der eigentliche Seiteninhalt wird dann durch diese Einträge ersetzt.

Die Beitragstypen <kbd>Nachricht</kbd>, <kbd>Bild</kbd>, <kbd>Galerie</kbd>, <kbd>Video</kbd>,
<kbd>Link</kbd> und <kbd>Download</kbd> aktivieren den Blog.

Der Beitragstyp <kbd>Events</kbd> aktiviert das Event-Modul und <kbd>Produkte</kbd> den Shop.


### Status {#page-status}

| Status        | Beschreibung                                                                                                |
|---------------|-------------------------------------------------------------------------------------------------------------|
| Öffentlich    | Die Seite ist für alle sichtbar                                                                             |
| Unsichtbar    | Die Seite ist für alle sichtbar, wird jedoch nicht in der Navigation, der Sitemap oder der Suche aufgeführt |
| Privat        | Nur Administratoren oder zugelassene Benutzergruppen können die Seite anzeigen                              |
| Entwurf       | Nur Administratoren können die Seite anzeigen                                                               |


### Weiterleitung {#redirects}

Die Weiterleitung ist ein eigenes Feld (kein Status). Trägst Du hier eine Zieladresse ein,
leitet die Seite sofort dorthin um. Den HTTP-Statuscode (z. B. 301 oder 302) kannst Du
über das zugehörige Auswahlfeld festlegen.


### Nutzungsart {#page-usage}

SwiftyEdit bringt für die wichtigsten Funktionsseiten (Registrierung, Profil, Suche, Passwort
zurücksetzen, Warenkorb, Bestellungen, Bestellung widerrufen, Listen, Beiträge/Produkte/Events
anzeigen sowie die 404-Seite) bereits fest einprogrammierte Standard-URLs mit, z. B. /profile/
für die Profilseite oder /checkout/ für den Warenkorb. Diese funktionieren automatisch, auch
wenn du dafür keine eigene Seite angelegt hast.

Legst du stattdessen eine eigene Seite mit der passenden Nutzungsart an, ersetzt diese Seite
(mit ihrem eigenen Permalink und Inhalt drumherum) die von SwiftyEdit vorgegebene Standard-URL.
So kannst du z. B. /profile/ durch /mein-konto/ ersetzen.

Für Impressum, Datenschutz und Rechtliches gibt es dagegen **keine** vorgegebene Standard-URL –
hier musst du selbst eine Seite mit der entsprechenden Nutzungsart anlegen, damit sie z. B. im
Footer verlinkt werden kann.

| Nutzungsart                        | Standard-URL*                            | Beschreibung                                                                                                            |
|-------------------------------------|-------------------------------------------|---------------------------------------------------------------------------------------------------------------------------|
| Normale Seite                      | –                                         | Standardwert für alle Seiten, die keinem bestimmten Zweck dienen.                                                       |
| Registrierung                      | `/register/`                              | Zum Anlegen neuer Benutzer.                                                                                              |
| Profil                             | `/profile/`                               | Hier können Benutzer ihre persönlichen Daten (Kontaktinformationen, Passwort usw.) ändern.                              |
| Suche                              | `/search/`                                | Die Seite für Suchergebnisse.                                                                                           |
| Passwort zurücksetzen              | `/password/`                              | Wird verwendet, wenn ein Benutzer sein Passwort zurücksetzen muss.                                                      |
| 404 (Page not found)               | *(greift automatisch bei jeder unbekannten URL)* | Wird angezeigt, wenn die aufgerufene URL nicht existiert.                                                        |
| Posts / Produkte / Events anzeigen | – (kein fester Standardpfad)              | Zeigt alle Einträge aus Blog, Shop und Events auf dieser Seite an. Praktisch z. B. für zusätzliche Katalogseiten wie `/details/`. |
| Impressum                          | – (keine Standard-URL)                    | Wird für das Impressum verwendet.                                                                                       |
| Datenschutz                        | – (keine Standard-URL)                    | Wird für die Datenschutzinformationen verwendet.                                                                        |
| Rechtliches                        | – (keine Standard-URL)                    | Für alle sonstigen rechtlichen Informationen.                                                                           |
| Warenkorb                          | `/checkout/`                              | Für den Warenkorb.                                                                                                       |
| Bestellungen                       | `/orders/`                                | Hier kann der Benutzer seine Bestellungen einsehen.                                                                     |
| Bestellung widerrufen              | `/order_withdrawal/`                      | Formular zum Widerruf einer Bestellung, siehe [unten](#page-usage-order-withdrawal).                                    |
| Listen                             | `/wishlist/`                              | Seite für die [Listen-Funktion](05-00-shop.md#listen), siehe [unten](#page-usage-lists).                                |

\* Die Standard-URL gilt nur, solange keine eigene Seite mit dieser Nutzungsart existiert.
Legst du eine Seite mit dieser Nutzungsart an, ersetzt deren Permalink die Standard-URL
(siehe Erklärung oben).

#### Bestellung widerrufen {#page-usage-order-withdrawal}

Ein Formular, mit dem Kunden eine Bestellung widerrufen können (z.B. zur Erfüllung des
EU-Widerrufsrechts). Der Kunde muss dazu die Bestellnummer und die bei der Bestellung
hinterlegte E-Mail-Adresse eingeben; die Anfrage wird anschließend per E-Mail an den
Administrator gesendet. Von der Seite "Bestellungen" aus können Kunden dieses Formular
vorausgefüllt für eine bestimmte Bestellung öffnen.

#### Listen {#page-usage-lists}

Die Seite für die [Listen-Funktion](05-00-shop.md#listen). Angemeldete Kunden sehen und
verwalten hier ihre persönlichen Listen; eine einzelne öffentliche Liste wird hier auch
angezeigt, wenn sie über ihren Freigabe-Link geöffnet wird. Diese Nutzungsart ist nur
relevant, wenn Listen unter Einstellungen → Shop aktiviert sind.

---

## Sortierung der Suchergebnisse

Die Suchergebnisse werden nach Relevanz sortiert. Ziel ist es, Seiten zuerst anzuzeigen, die dem Suchbegriff möglichst genau entsprechen.

Dabei werden folgende Kriterien in dieser Reihenfolge berücksichtigt:

1.	__URL / Permalink__<br>
Seiten, deren URL den Suchbegriff enthält, erscheinen ganz oben in den Ergebnissen.
2.	__Meta-Keywords (exakte Übereinstimmung)__<br>
Seiten mit einer exakten Übereinstimmung des Suchbegriffs in den Meta-Keywords werden höher bewertet.
3.	__Meta-Keywords (teilweise Übereinstimmung)__<br>
Seiten, bei denen der Suchbegriff als Teil der Keywords vorkommt (z. B. Wortanfang oder Wortbestandteil), folgen danach.
4.	__Meta-Beschreibung (Meta Description)__<br>
Treffer in der Meta-Beschreibung werden berücksichtigt, da sie den Seiteninhalt gezielt zusammenfassen.
5.	__Seitentitel__<br>
Seiten, deren Titel den Suchbegriff enthält, werden zusätzlich höher eingestuft.
6.	__Seiteninhalt__<br>
Treffer im eigentlichen Seiteninhalt werden ebenfalls berücksichtigt, jedoch nach URL, SEO-Daten und Titel.
7.	__Seitenpriorität__<br>
Wenn mehrere Seiten gleich relevant sind, entscheidet die manuell vergebene Seitenpriorität.
Seiten mit höherer Priorität erscheinen weiter oben.

Kurz erklärt: Je näher der Suchbegriff an URL, SEO-Daten und Titel liegt, 
desto relevanter ist das Ergebnis.
Der Seiteninhalt dient als ergänzendes Kriterium.
Bei gleicher Relevanz entscheidet die Seitenpriorität.