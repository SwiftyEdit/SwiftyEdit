import Sortable from 'sortablejs';

export function initWishlistSortable(content) {

    const lists = content.querySelectorAll('.wishlist-sortable');

    lists.forEach(function (list) {

        new Sortable(list, {
            animation: 150,
            handle: '.wishlist-drag-handle',
            ghostClass: 'bg-info-subtle',
            onEnd: function () {

                const csrfInput = document.querySelector('input[name="csrf_token"]');
                const params = new URLSearchParams();
                params.append('reorder_wishlist', list.dataset.wishlistId);

                list.querySelectorAll('[data-item-id]').forEach(function (el) {
                    params.append('item_ids[]', el.dataset.itemId);
                });

                if (csrfInput) {
                    params.append('csrf_token', csrfInput.value);
                }

                fetch('/xhr/se/wishlist/', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: params.toString()
                });
            }
        });
    });
}

export function copyWishlistLink(button) {

    const input = document.getElementById(button.dataset.copyTarget);
    if (!input) {
        return;
    }

    navigator.clipboard.writeText(input.value).then(function () {

        const original = button.innerHTML;
        button.innerHTML = '<i class="bi bi-check2"></i>';

        setTimeout(function () {
            button.innerHTML = original;
        }, 1500);
    });
}
