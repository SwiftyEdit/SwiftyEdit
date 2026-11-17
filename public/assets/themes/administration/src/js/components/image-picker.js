/**
 * image picker (acp/templates/widgets/select-img.twig)
 * a click on an image (or Enter/Space) adds it to the drop zone (#imgdropper),
 * as an alternative to dragging it there - drag and drop (SortableJS) stays as it is
 *
 * the drop zone's MutationObserver in backend.js adds the hidden input and the
 * remove button to the new item, exactly like for a dropped image
 */

function addImageToDropzone(item) {
    const dropzone = document.getElementById('imgdropper');
    if (!dropzone) return;

    // the same image twice is almost always a double click by accident
    const imageId = item.getAttribute('data-id');
    const existing = [...dropzone.querySelectorAll('.picker-item')]
        .find((el) => el.getAttribute('data-id') === imageId);
    if (existing) {
        existing.classList.add('picker-item-exists');
        setTimeout(() => existing.classList.remove('picker-item-exists'), 600);
        return;
    }

    const clone = item.cloneNode(true);
    // only the list items are buttons, inside the drop zone they are sortable entries
    clone.removeAttribute('role');
    clone.removeAttribute('tabindex');

    // the drop zone sits above the list and grows with every image - scroll by the
    // same amount, so the list stays under the cursor for the next click
    const topBefore = item.getBoundingClientRect().top;
    dropzone.appendChild(clone);
    const shift = item.getBoundingClientRect().top - topBefore;
    if (shift !== 0) {
        window.scrollBy(0, shift);
    }
}

document.addEventListener('click', (event) => {
    const item = event.target.closest('.image-picker-list .picker-item');
    if (item) {
        addImageToDropzone(item);
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Enter' && event.key !== ' ') return;
    const item = event.target.closest('.image-picker-list .picker-item');
    if (item) {
        // Enter would submit the surrounding form, Space would scroll
        event.preventDefault();
        addImageToDropzone(item);
    }
});
