---
title: Weiterleitungen
description: Weiterleitungen
group: tips
priority: 0
---

### Weiterleitungen

#### Kurzlink

Wird ein Kurzlink angegeben, wird automatisch von diesem Kurzlink auf den Wert aus <i>Permalink</i> weitergeleitet
sobald dieser Kurzlink aufgerufen wird. Die Zahl neben dem Eingabefeld zeigt an, wie oft der Kurzlink aufgerufen wurde.

#### Trichter URL

Falls man eine URL nachträglich ändern muss, kann man hier die alte URL eingeben. Damit wird automatisch eine
Weiterleitung zur neuen URL (dem Wert aus dem Feld <i>Permalink</i>) angelegt.

#### Umleitung

Wird hier eine URL eingetragen, ist die Seite nicht mehr erreichbar 
bzw. beim Aufruf wird direkt auf diesen Wert umgeleitet.

 - 301 Moved Permanently → Dauerhafte Weiterleitung. Suchmaschinen übernehmen die neue URL in den Index.
 - 302 Found (früher "Moved Temporarily") → Temporäre Weiterleitung, ursprüngliche URL bleibt bestehen.
 - 303 See Other → Ressource woanders, wird fast immer für "nach einem POST weiterleiten" genutzt (z. B. nach einem Formular).
 - 304 Not Modified → Kein Redirect im klassischen Sinn, sondern "Inhalt hat sich nicht geändert" → Browser soll Cache nutzen.
 - 305 Use Proxy → Sollte nur über einen Proxy abgerufen werden. (Unsicher, heute praktisch nicht genutzt).
 - 306 (Unused) → Reserviert, aber nie verwendet.
 - 307 Temporary Redirect → Wie 302, aber garantiert gleiche Methode (POST bleibt POST, kein Wechsel zu GET).
 - 308 Permanent Redirect → Wie 301, aber ebenfalls mit Beibehaltung der HTTP-Methode.
 - 309 aktuell nicht vergeben und nicht Teil des offiziellen HTTP-Standards