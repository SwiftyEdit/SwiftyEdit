document.addEventListener('DOMContentLoaded', function () {
    const textareas = document.querySelectorAll('textarea.count-chars');
    if (!textareas.length) return;

    textareas.forEach(function (textarea) {
        // Info-Span erstellen
        const infoSpan = document.createElement('span');
        infoSpan.style.display = 'block';
        infoSpan.style.marginTop = '5px';
        infoSpan.style.fontSize = '12px';
        infoSpan.style.color = '#666';

        // Direkt unter das aktuelle Textarea einfügen
        textarea.insertAdjacentElement('afterend', infoSpan);

        // Funktion zur Berechnung der Pixelbreite
        function getTextWidth(text, font = '14px Arial') {
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            context.font = font;
            return context.measureText(text).width;
        }

        // Aktualisierungsfunktion
        function updateInfo() {
            const text = textarea.value;
            const length = text.length;
            const width = Math.round(getTextWidth(text));
            const maxWidth = 1000;

            infoSpan.innerHTML = `<i class="bi bi-textarea-t"></i> ${length} &nbsp;|&nbsp; ${width}px ` +
                (width > maxWidth
                    ? `<span class="text-danger"><i class="bi bi-exclamation-triangle-fill"></i></span>`
                    : '');
        }

        // Initial und bei Eingabe aktualisieren
        updateInfo();
        textarea.addEventListener('input', updateInfo);
    });
});