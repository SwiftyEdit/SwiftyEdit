---
title: Seiten
description: Seiten erstellen, bearbeiten und verwalten
btn: Seiten
group: backend
priority: 190
---

# Seiten anlegen, bearbeiten und verwalten

<kbd>Backend</kbd> <kbd>Inhalte</kbd> <kbd>Alle Seiten anzeigen</kbd>

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

SwiftyEdit verfügt für jeden Seitentyp über eine eigene URL-Struktur.
Beispiel: /profile/ für die Profilseite oder /checkout/ für den Warenkorb.
Wenn du diese Seiten/URLs anpassen möchtest, kannst du einfach neue Seiten erstellen und die Nutzungsart entsprechend anpassen.

#### Normale Seite

Dies ist der Standardwert und wird für alle Seiten verwendet, die keinem bestimmten Zweck dienen.

#### Registrierung

Diese Seite wird zum Anlegen neuer Benutzer verwendet.

#### Profil

Hier können Benutzer ihre persönlichen Daten (Kontaktinformationen, Passwort usw.) ändern.

#### Suche

Die Seite für Suchergebnisse

#### Passwort zurücksetzen

Diese Seite wird verwendet, wenn ein Benutzer sein Passwort zurücksetzen muss.

#### 404 (Page not found)

Diese Seite wird angezeigt, wenn eine Seite nicht gefunden wird. 
Also, wenn die aufgerufene URL nicht existiert.

#### Posts / Produkte / Events anzeigen

Sobald du eine Seite mit dieser Nutzungsart erstellst, 
werden alle Einträge aus den Modulen Blog, Shop und Events auf dieser Seite angezeigt.

Wenn du beispielsweise mehrere Katalogseiten hast (Seiten, auf denen Produkte aus dem Shop aufgeführt werden),
auf der identische Produkte aufgelistet sind, können diese z.B. auf der Seite /details/ anzeigt werden.

#### Impressum

Wird für das Impressum verwendet.

#### Datenschutz

Wird für die Datenschutzinformationen verwendet.

#### Rechtliches

Für alle sonstigen rechtlichen Informationen.

#### Warenkorb

Für den Warenkorb.

#### Bestellungen

Hier kann der Benutzer seine Bestellungen einsehen.

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