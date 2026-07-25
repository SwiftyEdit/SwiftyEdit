---
title: se_paypal-pay - Payment Addon
description: Einstellungen für das Addon se_paypal-pay
btn: Einstellungen
group: addons
priority: 500
---

# se_paypal-pay Einstellungen

Damit dieses Plugin im Warenkorb angezeigt wird, muss es unter

<kbd>Einstellungen</kbd> <kbd>Shop</kbd> <kbd>Zahlungen & Versand</kbd>

aktiviert werden.

### Additional Costs
Dieser Betrag wird im Warenkorb auf den Gesamtbetrag addiert, sobald diese Zahlungsmethode ausgewählt wurde.

### Snippet for Shopping Cart
Hier werden alle Snippets angezeigt, die du für Zahlungsarten verwenden kannst. Dies sind alle Snippets, die mit `cart_pm_*` benannt sind.
Der Inhalt des Snippets wird im Warenkorb als Erklärung zu dieser Zahlungsmethode angezeigt.
Der Titel des Snippets erscheint in der Auswahl der Zahlungsarten.

### Modus

* __Sandbox:__ Wird verwendet, um das Plugin, deine Zugangsdaten und die PayPal Anbindung zu testen. Solange du im Sandbox Mode bist, können keine Kosten entstehen und es wird kein echtes Geld bewegt.
* __Live Account:__ Hier werden deine echten Zugangsdaten verwendet.

### Client-ID und Client-Secret
Diese Schlüssel findest du in deinem PayPal Account.

### Cancel URL
Auf diese Seite wird der Kunde geleitet, wenn er den Bazahlvorgang bei PayPal abbricht.

### Return URL
Auf diese Seite wird der Kunde geleitet, sobald die Zahlung bei PayPal abgeschlossen wurde.
Erstelle dazu über das Seiten Management eine normale Seite, deren Permalink exakt mit dem
Pfad dieser Return URL übereinstimmt. Ein Plugin muss dieser Seite **nicht** zugewiesen werden
(Zahlungs-Plugins stehen im Tab Plugins gar nicht zur Auswahl) – SwiftyEdit erkennt die Seite
automatisch anhand der URL und erfasst die Zahlung bei PayPal, sobald der Kunde darauf geleitet wird.
Damit werden Zahlungen deiner Kunden direkt in der Bestellung berücksichtigt und die Bestellung als bezahlt markiert.