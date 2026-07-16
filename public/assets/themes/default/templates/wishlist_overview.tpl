<h2>{$lang_label_my_wishlists}</h2>

<div class="row">
    <div class="col-md-5">
        <div id="wishlist-list-column" hx-get="/xhr/se/wishlist/?form=lists" hx-trigger="load, update_wishlists from:body" hx-swap="innerHTML">
            <div class="d-flex align-items-center htmx-indicator">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                <span class="sr-only">{$lang_loading}</span>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div id="wishlist-detail-panel"></div>
    </div>
</div>
