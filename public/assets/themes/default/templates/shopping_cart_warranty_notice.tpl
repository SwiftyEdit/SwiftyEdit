{* EU notice on the legal guarantee (Regulation (EU) 2025/1960), picked in the shop settings.
   It is shown as it is, readable but limited in width - a click opens it in full size. *}
{if is_array($warranty_notice)}
    <div class="card h-100 sc-warranty-notice">
        <div class="card-header">{$lang_label_warranty_notice}</div>
        <div class="card-body text-center">
            <a href="{$warranty_notice.src}" class="lightbox" title="{$lang_label_warranty_notice}">
                <img src="{$warranty_notice.src}" alt="{$warranty_notice.alt}" class="img-fluid" style="max-width: 480px; width: 100%;">
            </a>
        </div>
    </div>
{/if}
